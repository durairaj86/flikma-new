<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Models\Finance\Account\Account;
use App\Models\Log\LogHistory;
use App\Models\Master\Company;
use App\Models\Payroll\EmployeeLoan;
use App\Models\Payroll\PayrollRecord;
use App\Models\User;
use App\Services\Payroll\PayrollCalculationService;
use App\Services\Payroll\PayrollLedger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PayrollRunController extends Controller
{
    public function __construct(
        private readonly PayrollCalculationService $calculator,
        private readonly PayrollLedger $ledger,
    ) {
    }

    public function index(Request $request): View
    {
        $month = (int) $request->query('month', now()->month);
        $year = (int) $request->query('year', now()->year);

        $records = PayrollRecord::with('employee:id,name,department,employee_code')
            ->where('month', $month)
            ->where('year', $year)
            ->when($request->query('employee_id'), fn ($q, $id) => $q->where('employee_id', $id))
            ->join('users', 'users.id', '=', 'payroll_records.employee_id')
            ->orderBy('users.name')
            ->select('payroll_records.*')
            ->get();

        $employees = User::where('is_employee', true)->orderBy('name')->get(['id', 'name']);

        // Salary is paid out of cash or a bank account only — offering every non-income account let a salary
        // be "paid from" the Salary Expense account itself (debit and credit cancelling, nothing leaving the bank).
        $paymentAccounts = Account::query()->active()->posting()->cashOrBank()
            ->orderBy('code')
            ->get(['id', 'code', 'name', 'type']);

        $monthLabel = Carbon::create($year, $month, 1)->format('F Y');

        return view('modules.payroll.runs.index', compact('records', 'employees', 'month', 'year', 'monthLabel', 'paymentAccounts'));
    }

    public function show(PayrollRecord $payroll_record): View
    {
        $record = $payroll_record->load(['employee', 'bankAccount']);

        $loans = EmployeeLoan::with('installments')
            ->where('employee_id', $record->employee_id)
            ->orderByDesc('id')->get();

        // Instalment taken from this salary (recorded on the payment date).
        $recovered = $record->isPaid()
            ? $loans->flatMap->installments->filter(fn ($i) => $i->paid_date && $record->payment_date
                && $i->paid_date->toDateString() === $record->payment_date->toDateString())->sum('amount')
            : 0;

        $entries = $this->ledger->entriesFor(PayrollRecord::class, $record->id);
        $payer = $entries->isNotEmpty() ? User::withoutGlobalScopes()->find($entries->first()->user_id) : null;

        $audit = LogHistory::where('loggable_type', PayrollRecord::class)->where('loggable_id', $record->id)->latest('id')->get();

        return view('modules.payroll.runs.show', compact('record', 'payer', 'loans', 'recovered', 'entries', 'audit'));
    }

    /** Printable salary slip (browser print / save as PDF). */
    public function slip(PayrollRecord $payroll_record): View
    {
        $record = $payroll_record->load(['employee', 'bankAccount']);
        $company = authUserCompany();

        return view('modules.payroll.runs.slip', compact('record', 'company'));
    }

    public function create(Request $request): View
    {
        $month = (int) $request->query('month', now()->month);
        $year = (int) $request->query('year', now()->year);

        $existingEmployeeIds = PayrollRecord::where('month', $month)->where('year', $year)->pluck('employee_id');

        $preview = User::where('is_employee', true)
            ->orderBy('name')
            ->get()
            ->map(function (User $employee) use ($month, $year, $existingEmployeeIds) {
                if ($existingEmployeeIds->contains($employee->id)) {
                    return ['employee' => $employee, 'status' => 'exists', 'calc' => null];
                }

                $calc = $this->calculator->calculate($employee, $month, $year);

                return [
                    'employee' => $employee,
                    'status' => $calc ? 'ready' : 'skipped',
                    'calc' => $calc,
                ];
            });

        $monthLabel = Carbon::create($year, $month, 1)->format('F Y');

        return view('modules.payroll.runs.create', compact('preview', 'month', 'year', 'monthLabel'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'year' => ['required', 'integer', 'min:2020', 'max:2100'],
            'employee_ids' => ['required', 'array', 'min:1'],
            'employee_ids.*' => ['exists:users,id'],
        ]);

        $existingEmployeeIds = PayrollRecord::where('month', $data['month'])->where('year', $data['year'])->pluck('employee_id');

        $created = 0;
        $skipped = [];

        foreach ($data['employee_ids'] as $employeeId) {
            if ($existingEmployeeIds->contains((int) $employeeId)) {
                continue;
            }

            $employee = User::find($employeeId);
            $calc = $employee ? $this->calculator->calculate($employee, $data['month'], $data['year']) : null;

            if (!$calc) {
                $skipped[] = $employee?->name ?? "#{$employeeId}";
                continue;
            }

            PayrollRecord::create(array_merge(
                [
                    'employee_id' => $employeeId,
                    'payroll_number' => sprintf('PR-%d%02d-%d', $data['year'], $data['month'], $employeeId),
                    'month' => $data['month'],
                    'year' => $data['year'],
                    'status' => 'draft',
                ],
                collect($calc)->only([
                    'basic_salary', 'housing_allowance', 'transportation_allowance', 'other_allowances',
                    'total_earnings', 'loan_deduction', 'other_deductions', 'total_deductions', 'net_payable',
                ])->toArray()
            ));
            $created++;
        }

        $message = __(':count payroll record(s) generated.', ['count' => $created]);
        if ($skipped) {
            $message .= ' ' . __('Skipped (no salary structure): :names', ['names' => implode(', ', $skipped)]);
        }

        return redirect()->route('payroll.runs.index', ['month' => $data['month'], 'year' => $data['year']])
            ->with('success', $message);
    }

    public function pay(Request $request, PayrollRecord $payroll_record): RedirectResponse
    {
        if ($payroll_record->status === 'paid') {
            return back()->withErrors(['bank_account_id' => __('This payroll record has already been paid.')]);
        }

        $data = $request->validate([
            'bank_account_id' => ['required', 'exists:accounts,id'],
            'payment_date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        if (!Account::query()->active()->posting()->cashOrBank()->whereKey($data['bank_account_id'])->exists()) {
            return back()->withErrors(['bank_account_id' => __('Pay From Account must be a cash or bank account.')]);
        }
        $this->assertPeriodOpen($data['payment_date'], 'payment_date');

        $description = ($data['description'] ?? null)
            ?: __('Salary payment - :name (:month/:year)', [
                'name' => $payroll_record->employee->name,
                'month' => $payroll_record->month,
                'year' => $payroll_record->year,
            ]);

        DB::transaction(function () use ($data, $payroll_record, $description) {
            $payroll_record->update([
                'status' => 'paid',
                'payment_date' => $data['payment_date'],
                'bank_account_id' => $data['bank_account_id'],
            ]);

            $this->applyLoanRepayment($payroll_record->fresh());
            $this->ledger->postPayroll($payroll_record->fresh(), (int) $data['bank_account_id'], $description);
        });

        return redirect()->route('payroll.runs.index', ['month' => $payroll_record->month, 'year' => $payroll_record->year])
            ->with('success', __('Salary paid successfully.'));
    }

    /**
     * Undo a salary payment: everything it posted to the ledger is removed, the employee's loan instalment is
     * given back, and the record returns to draft so it can be corrected and paid again.
     */
    public function disapprove(PayrollRecord $payroll_record): RedirectResponse
    {
        if ($payroll_record->status !== 'paid') {
            return back()->withErrors(['payroll_record' => __('Only a paid payroll record can be disapproved.')]);
        }
        $this->assertPeriodOpen($payroll_record->payment_date, 'payment_date');

        DB::transaction(function () use ($payroll_record) {
            $paidOn = $payroll_record->payment_date;

            $payroll_record->update([
                'status' => 'draft',
                'payment_date' => null,
                'bank_account_id' => null,
            ]);

            $this->ledger->removePayroll($payroll_record);
            $this->reverseLoanRepayment($payroll_record, $paidOn);
        });

        return redirect()->route('payroll.runs.index', ['month' => $payroll_record->month, 'year' => $payroll_record->year])
            ->with('success', __('Salary payment disapproved. The record is back in draft.'));
    }

    /** Give back the loan instalment taken when this salary was paid (matched on employee, amount and pay date). */
    protected function reverseLoanRepayment(PayrollRecord $record, $paidOn): void
    {
        if ((float) $record->loan_deduction <= 0) {
            return;
        }
        $loans = EmployeeLoan::where('employee_id', $record->employee_id)->orderByDesc('id')->get();
        foreach ($loans as $loan) {
            $installment = $loan->installments()
                ->where('amount', $record->loan_deduction)
                ->when($paidOn, fn ($q) => $q->whereDate('paid_date', $paidOn))
                ->orderByDesc('id')->first();
            if ($installment) {
                $loan->paid_amount = max(0, (float) $loan->paid_amount - (float) $installment->amount);
                $loan->remaining_amount = (float) $loan->remaining_amount + (float) $installment->amount;
                if ($loan->status === 'closed' && $loan->remaining_amount > 0) {
                    $loan->status = 'active';
                }
                $loan->save();
                $installment->delete();
                return;
            }
        }
    }

    public function destroy(PayrollRecord $payroll_record): RedirectResponse
    {
        if ($payroll_record->status === 'paid') {
            return back()->withErrors(['payroll_record' => __('Cannot delete a paid payroll record.')]);
        }

        $month = $payroll_record->month;
        $year = $payroll_record->year;
        $payroll_record->delete();

        return redirect()->route('payroll.runs.index', ['month' => $month, 'year' => $year])
            ->with('success', __('Payroll record deleted.'));
    }

    /** Reduce the employee's active loan by the instalment withheld from this salary (same rule as the Payroll Details pay flow). */
    protected function applyLoanRepayment(PayrollRecord $record): void
    {
        if ((float) $record->loan_deduction <= 0) {
            return;
        }
        $loan = EmployeeLoan::where('employee_id', $record->employee_id)->where('status', 'active')->first();
        if (!$loan) {
            return;
        }
        $amount = min((float) $record->loan_deduction, (float) $loan->remaining_amount);
        if ($amount <= 0) {
            return;
        }
        $loan->installments()->create([
            'company_id' => $record->company_id,
            'amount' => $amount,
            'paid_date' => $record->payment_date ?? now(),
        ]);
        $loan->paid_amount += $amount;
        $loan->remaining_amount -= $amount;
        if ($loan->remaining_amount <= 0) {
            $loan->status = 'closed';
            $loan->remaining_amount = 0;
        }
        $loan->save();
    }
}
