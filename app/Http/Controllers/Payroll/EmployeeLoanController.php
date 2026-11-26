<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;

use App\Models\Finance\Account\Account;
use App\Models\Payroll\EmployeeLoan;
use App\Models\Payroll\LoanInstallment;
use App\Models\User;
use App\Services\Payroll\PayrollLedger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EmployeeLoanController extends Controller
{
    public function index(Request $request): View
    {
        $query = EmployeeLoan::with('employee');

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $loans = $query->withCount('installments')->latest()->paginate(15);
        $employees = User::where('is_employee', true)->orderBy('name')->get(['id', 'name']);

        return view('modules.payroll.loans.index', compact('loans', 'employees'));
    }

    public function create(): View
    {
        $employees = User::where('is_employee', true)->orderBy('name')->get(['id', 'name']);
        $paymentAccounts = $this->paymentAccounts();
        return view('modules.payroll.loans.create', compact('employees', 'paymentAccounts'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'employee_id' => ['required', Rule::exists('users', 'id')->where('company_id', companyId())->where('is_employee', true)],
            'loan_amount' => ['required', 'numeric', 'min:1'],
            'total_installments' => ['required', 'integer', 'min:1'],
            'installment_amount' => ['required', 'numeric', 'min:1'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'paid_from_account_id' => ['required', 'integer'],
            'description' => ['nullable', 'string', 'max:1000'],
        ], ['paid_from_account_id.required' => __('Select the cash / bank account the loan was paid from.')]);

        $this->assertInstallmentsMatch($data);
        $this->assertPaymentAccount($data['paid_from_account_id']);
        $data['start_date'] = formDate($data['start_date']);
        $data['end_date'] = formDate($data['end_date'] ?? null);
        $this->assertPeriodOpen($data['start_date'], 'start_date');   // the loan is posted on its start date

        DB::transaction(fn () => app(PayrollLedger::class)->postLoanDisbursement(EmployeeLoan::create([
            'company_id' => companyId(),
            'employee_id' => $data['employee_id'],
            'loan_amount' => $data['loan_amount'],
            'total_installments' => $data['total_installments'],
            'installment_amount' => $data['installment_amount'],
            'paid_amount' => 0,
            'remaining_amount' => $data['loan_amount'],
            'paid_from_account_id' => $data['paid_from_account_id'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'description' => $data['description'] ?? null,
            'status' => 'active',
        ])));

        return redirect()->route('employee-loans.index')->with('success', __('Loan created and posted to the ledger.'));
    }

    public function show(EmployeeLoan $employeeLoan): View
    {
        $employeeLoan->load(['employee', 'installments.bankAccount', 'paidFromAccount:id,code,name']);
        // Repayments are received into cash or a bank account only.
        $paymentAccounts = $this->paymentAccounts();
        $entries = app(PayrollLedger::class)->entriesFor(EmployeeLoan::class, $employeeLoan->id);
        return view('modules.payroll.loans.show', compact('employeeLoan', 'paymentAccounts', 'entries'));
    }

    public function edit(EmployeeLoan $employeeLoan): View
    {
        $employees = User::where('is_employee', true)->orderBy('name')->get(['id', 'name']);
        $paymentAccounts = $this->paymentAccounts();
        return view('modules.payroll.loans.edit', compact('employeeLoan', 'employees', 'paymentAccounts'));
    }

    public function update(Request $request, EmployeeLoan $employeeLoan): RedirectResponse
    {
        $data = $request->validate([
            'employee_id' => ['required', Rule::exists('users', 'id')->where('company_id', companyId())->where('is_employee', true)],
            'loan_amount' => ['required', 'numeric', 'min:1'],
            'total_installments' => ['required', 'integer', 'min:1'],
            'installment_amount' => ['required', 'numeric', 'min:1'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'in:active,closed,cancelled'],
            'paid_from_account_id' => ['nullable', 'integer'],
            'update_reason' => ['required', 'string', 'min:3', 'max:500'],
        ], ['update_reason.required' => __('Please tell us why you are updating this loan.')]);
        $reason = trim($data['update_reason']);
        unset($data['update_reason']);
        if ($data['paid_from_account_id'] ?? null) {
            $this->assertPaymentAccount($data['paid_from_account_id']);
        } else {
            unset($data['paid_from_account_id']);   // older loans have none; leaving it empty keeps what is there
        }
        $posted = app(PayrollLedger::class)->entriesFor(EmployeeLoan::class, $employeeLoan->id)->isNotEmpty();

        // Once any instalment has been deducted from salary, the loan amount is fixed — changing it would
        // contradict what has already been recovered and posted to the ledger.
        if ($employeeLoan->hasDeductions()) {
            if ((int) $data['employee_id'] !== (int) $employeeLoan->employee_id) {
                return back()->withInput()->withErrors(['employee_id' => __('The employee cannot be changed because instalments have already been deducted.')]);
            }
            if (abs((float) $data['loan_amount'] - (float) $employeeLoan->loan_amount) > 0.001) {
                return back()->withInput()->withErrors(['loan_amount' => __('The loan amount cannot be changed because instalments have already been deducted.')]);
            }
            if ($posted && isset($data['paid_from_account_id']) && (int) $data['paid_from_account_id'] !== (int) $employeeLoan->paid_from_account_id) {
                return back()->withInput()->withErrors(['paid_from_account_id' => __('The paid-from account cannot be changed because instalments have already been deducted.')]);
            }
        } else {
            // Nothing recovered yet: the balance still owed follows the (possibly new) loan amount.
            $data['remaining_amount'] = $data['loan_amount'];
        }

        $this->assertInstallmentsMatch($data);
        $data['start_date'] = formDate($data['start_date']);
        $data['end_date'] = formDate($data['end_date'] ?? null);
        $employeeLoan->fill($data);
        $changes = [];
        foreach ($employeeLoan->getDirty() as $field => $new) {
            $changes[$field] = ['old' => $employeeLoan->getOriginal($field), 'new' => $new];
        }
        // The posting follows the loan while nothing is recovered yet (amount, date, account, cancelled = never given).
        // Once recovered it stays as posted; an older loan without a posting gets one when its account is filled in.
        $repost = $employeeLoan->paid_from_account_id && (!$employeeLoan->hasDeductions() || !$posted);
        if ($repost || ($posted && $employeeLoan->isDirty('status') && $employeeLoan->status === 'cancelled' && !$employeeLoan->hasDeductions())) {
            $this->assertPeriodOpen($employeeLoan->start_date, 'start_date');
        }
        DB::transaction(function () use ($employeeLoan, $repost) {
            $employeeLoan->save();
            if ($repost) {
                app(PayrollLedger::class)->postLoanDisbursement($employeeLoan->fresh('employee'));
            }
        });

        // Every update is traceable together with the reason the user gave (mandatory).
        \App\Models\Log\LogHistory::create([
            'company_id' => companyId(),
            'loggable_type' => EmployeeLoan::class,
            'loggable_id' => $employeeLoan->id,
            'loggable_number' => 'Loan #' . $employeeLoan->id,
            'loggable_name' => $employeeLoan->employee?->name,
            'user_id' => ['id' => auth()->id(), 'name' => auth()->user()->name],
            'action' => 'updated',
            'changes' => ['old' => collect($changes)->map->old->all(), 'new' => collect($changes)->map->new->all() + ['reason' => $reason]],
        ]);

        return redirect()->route('employee-loans.index')->with('success', 'Loan updated.');
    }

    public function destroy(EmployeeLoan $employeeLoan): RedirectResponse
    {
        // Instalments already deducted from salary (and posted to the ledger) mean the loan is part of the
        // books. It can be cancelled (edit → status), never deleted.
        if ($employeeLoan->hasDeductions()) {
            return back()->withErrors(['loan' => __('This loan cannot be deleted because instalments have already been deducted from salary (:amount recovered). Cancel or close it instead.', ['amount' => number_format((float) $employeeLoan->paid_amount, 2)])]);
        }
        if (app(PayrollLedger::class)->entriesFor(EmployeeLoan::class, $employeeLoan->id)->isNotEmpty()) {
            $this->assertPeriodOpen($employeeLoan->start_date, 'loan');
        }
        DB::transaction(function () use ($employeeLoan) {
            app(PayrollLedger::class)->removeLoanDisbursement($employeeLoan);
            $employeeLoan->delete();
        });
        return back()->with('success', __('Loan deleted.'));
    }

    public function payInstallment(Request $request, EmployeeLoan $employeeLoan): RedirectResponse
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'paid_date' => ['required', 'string'],
            'bank_account_id' => ['required', 'integer'],
            'remarks' => ['nullable', 'string', 'max:500'],
        ], [
            'bank_account_id.required' => __('Select the cash / bank account the payment was received into.'),
        ]);

        if (!\App\Models\Finance\Account\Account::query()->active()->posting()->cashOrBank()->whereKey($request->bank_account_id)->exists()) {
            return back()->withInput()->withErrors(['bank_account_id' => __('Received In must be a cash or bank account.')]);
        }
        $paidDate = formDate($request->paid_date);
        if (!$paidDate || \Illuminate\Support\Carbon::parse($paidDate)->gt(now()->endOfDay())) {
            return back()->withInput()->withErrors(['paid_date' => __('Payment date is invalid or in the future.')]);
        }
        $this->assertPeriodOpen($paidDate, 'paid_date');   // the repayment is posted on its date

        $error = null;
        DB::transaction(function () use ($request, $employeeLoan, $paidDate, &$error) {
            // Lock the loan so a double click / second tab cannot record the same payment twice.
            $loan = EmployeeLoan::whereKey($employeeLoan->id)->lockForUpdate()->first();
            $amount = round((float) $request->amount, 2);
            if ($loan->status !== 'active' || $amount > (float) $loan->remaining_amount + 0.001) {
                $error = __('The amount exceeds the remaining balance (:r) or the loan is already closed.', ['r' => number_format((float) $loan->remaining_amount, 2)]);
                return;
            }

            $inst = LoanInstallment::create([
                'company_id' => companyId(),
                'loan_id' => $loan->id,
                'amount' => $amount,
                'paid_date' => $paidDate,
                'bank_account_id' => $request->bank_account_id,
                'remarks' => $request->remarks,
            ]);

            $loan->paid_amount += $amount;
            $loan->remaining_amount -= $amount;
            if ($loan->remaining_amount <= 0.001) {
                $loan->status = 'closed';
                $loan->remaining_amount = 0;
            }
            $loan->save();

            app(\App\Services\Payroll\PayrollLedger::class)->postLoanRepayment($inst);
        });

        if ($error) {
            return back()->withInput()->withErrors(['amount' => $error]);
        }

        return redirect()->route('employee-loans.show', $employeeLoan)->with('success', __('Payment recorded and posted to the ledger.'));
    }

    /** Loans are paid out of, and repaid into, cash or a bank account only. */
    protected function paymentAccounts()
    {
        return Account::query()->active()->posting()->cashOrBank()->orderBy('code')->get(['id', 'code', 'name', 'type']);
    }

    protected function assertPaymentAccount($id): void
    {
        if (!Account::query()->active()->posting()->cashOrBank()->whereKey((int) $id)->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages(['paid_from_account_id' => __('Paid From must be a cash or bank account.')]);
        }
    }

    /** Installment amount × number of installments must equal the loan amount (0.01 tolerance for rounding). */
    protected function assertInstallmentsMatch(array $data): void
    {
        $total = round((float) $data['installment_amount'] * (int) $data['total_installments'], 2);
        if (abs($total - (float) $data["loan_amount"]) >= 0.011) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'installment_amount' => __('Installment amount × installments (:total) must equal the loan amount (:loan).', ['total' => number_format($total, 2), 'loan' => number_format((float) $data['loan_amount'], 2)]),
            ]);
        }
    }
}
