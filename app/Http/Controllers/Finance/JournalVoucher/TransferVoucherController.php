<?php

namespace App\Http\Controllers\Finance\JournalVoucher;

use App\Enums\JournalVoucherStatusEnum;
use App\Enums\JournalVoucherTypeEnum;
use App\Models\Finance\Account\Account;
use App\Models\Finance\JournalVoucher\JournalVoucher;
use App\Models\Finance\JournalVoucher\JournalVoucherItem;
use App\Services\DocumentNumber;
use App\Services\Finance\BankReconciliationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

/**
 * Transfer Voucher (Finances > Transfer Voucher): money moved from one cash / bank account to another, with an optional
 * bank charge. It is a journal voucher of type Transfer (same tables, same approve / draft / disapprove / print), with a
 * simple form and its own list, number series (TV/yy/0001), ledger code (TV) and rights. Lines it writes, in this order:
 *   Dr To account (amount) · Dr charge account (charge, when there is one) · Cr From account (amount + charge)
 */
class TransferVoucherController extends JournalVoucherController
{
    protected const LEDGER_CODE = 'TV';
    protected const LEDGER_NAME = 'Transfer Voucher';
    protected const SOURCE_TYPE = 'transfer_voucher';
    protected const JS_OBJECT = 'TRANSFER_VOUCHER';

    protected function vouchers()
    {
        return JournalVoucher::query()->where('voucher_type', JournalVoucherTypeEnum::TRANSFER->value);
    }

    protected function message(string $key): string
    {
        return match ($key) {
            'status' => __('Transfer voucher status updated successfully'),
            'disapproved' => __('Transfer voucher disapproved successfully'),
            'only_draft' => __('Only draft transfer vouchers can be deleted'),
            'deleted' => __('Transfer voucher deleted successfully'),
        };
    }

    public function index()
    {
        return view('modules.finance.transfer_voucher.list');
    }

    public function modal()
    {
        return $this->form(new JournalVoucher());
    }

    public function edit($id)
    {
        return $this->form($this->vouchers()->with('journalVoucherItems')->findOrFail($id));
    }

    private function form(JournalVoucher $voucher)
    {
        [$to, $charge, $from] = $this->split($voucher);
        $keep = array_filter([$to?->account_id, $charge?->account_id, $from?->account_id]);

        return view('modules.finance.transfer_voucher.form', [
            'voucher' => $voucher,
            'fromAccountId' => $from?->account_id,
            'toAccountId' => $to?->account_id,
            'amount' => $to ? (float) $to->debit_amount : null,
            'chargeAmount' => $charge ? (float) $charge->debit_amount : null,
            'chargeAccountId' => $charge?->account_id ?: Account::where('code', '5260')->value('id'),
            'bankAccounts' => $this->bankAccounts($keep),
            'chargeAccounts' => $this->chargeAccounts($keep),
        ]);
    }

    /** The saved lines back as [to, charge, from]: the credit line is From, the first debit To, a second debit the charge. */
    private function split(JournalVoucher $voucher): array
    {
        $items = $voucher->exists ? $voucher->journalVoucherItems->sortBy('id')->values() : collect();
        $debits = $items->filter(fn ($i) => (float) $i->debit_amount > 0)->values();

        return [$debits->get(0), $debits->get(1), $items->first(fn ($i) => (float) $i->credit_amount > 0)];
    }

    /** Cash and bank accounts that take postings (the leaves under 1110 / 1120, as Bank Reconciliation uses). */
    private function bankAccounts(array $keep = [])
    {
        $ids = app(BankReconciliationService::class)->accounts()->pluck('id')->all();

        return $this->postableAccounts($keep)->filter(fn ($a) => in_array($a->id, $ids) || in_array($a->id, $keep))->values();
    }

    /** Where a bank charge can go: active expense accounts that take postings. */
    private function chargeAccounts(array $keep = [])
    {
        $expense = Account::where('type', 'Expense')->pluck('id')->all();

        return $this->postableAccounts($keep)->filter(fn ($a) => in_array($a->id, $expense) || in_array($a->id, $keep))->values();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'voucher_date' => 'required|date',
            'from_account_id' => 'required|integer',
            'to_account_id' => 'required|integer|different:from_account_id',
            'amount' => 'required|numeric|gt:0|max:9999999999999',
            'currency' => 'required|string|size:3',
            'currency_rate' => 'required|numeric|gt:0',
            'reference_no' => 'nullable|string|max:191',
            'notes' => 'nullable|string|max:1000',
            'charge_amount' => 'nullable|numeric|min:0|max:9999999999999',
            'charge_account_id' => 'nullable|integer',
        ], [
            'to_account_id.different' => __('From and To must be different accounts.'),
        ], [
            'voucher_date' => __('Date'), 'from_account_id' => __('From Account'), 'to_account_id' => __('To Account'),
            'amount' => __('Amount'), 'charge_amount' => __('Bank Charge'), 'charge_account_id' => __('Charge Account'),
        ]);

        if ((float) ($validated['charge_amount'] ?? 0) > 0 && empty($validated['charge_account_id'])) {
            throw ValidationException::withMessages(['charge_account_id' => __('Choose the account for the bank charge.')]);
        }
        $this->assertPeriodOpen($validated['voucher_date'], 'voucher_date');

        $voucher = $request->filled('journal_voucher_id')
            ? $this->vouchers()->with('journalVoucherItems')->findOrFail($request->input('journal_voucher_id'))
            : new JournalVoucher();
        if ($voucher->exists && (int) $voucher->status === JournalVoucherStatusEnum::APPROVED->value) {
            return $this->unbalanced(__('An approved voucher cannot be edited. Move it to Draft first.'));
        }

        // accounts the voucher already used stay allowed on edit, anything else must be a cash / bank (or expense) posting account
        [$oldTo, $oldCharge, $oldFrom] = $this->split($voucher);
        $keep = array_filter([$oldTo?->account_id, $oldCharge?->account_id, $oldFrom?->account_id]);
        $banks = $this->bankAccounts($keep)->pluck('id')->all();
        if (!in_array((int) $validated['from_account_id'], $banks) || !in_array((int) $validated['to_account_id'], $banks)) {
            return $this->unbalanced(__('From and To must be cash or bank accounts.'));
        }
        $amount = round((float) $validated['amount'], 2);
        $charge = round((float) ($validated['charge_amount'] ?? 0), 2);
        if ($charge > 0 && !$this->chargeAccounts($keep)->pluck('id')->contains((int) $validated['charge_account_id'])) {
            return $this->unbalanced(__('The bank charge must go to an expense account.'));
        }
        $rate = (float) $validated['currency_rate'];
        $total = $amount + $charge;

        return DB::transaction(function () use ($voucher, $validated, $amount, $charge, $rate, $total) {
            if (!$voucher->exists) {
                $prefix = 'TV/' . date('y') . '/';
                $voucher->unique_row_no = DocumentNumber::next(JournalVoucher::class, $prefix);
                $voucher->row_no = $prefix . sprintf('%04d', $voucher->unique_row_no);
                $voucher->status = JournalVoucherStatusEnum::DRAFT->value;
                $voucher->company_id = companyId();
                $voucher->created_by = Auth::id();
            } else {
                $voucher->updated_by = Auth::id();
            }
            $voucher->voucher_type = JournalVoucherTypeEnum::TRANSFER->value;
            $voucher->voucher_date = $validated['voucher_date'];
            $voucher->reference_no = $validated['reference_no'] ?? null;
            $voucher->currency = strtoupper($validated['currency']);
            $voucher->currency_rate = $rate;
            $voucher->debit_total = $total;
            $voucher->credit_total = $total;
            $voucher->base_debit_total = round($total * $rate, 2);
            $voucher->base_credit_total = round($total * $rate, 2);
            $voucher->notes = $validated['notes'] ?? null;
            $voucher->save();

            $names = Account::whereIn('id', [$validated['from_account_id'], $validated['to_account_id']])->pluck('name', 'id');
            $line = fn (int $account, float $debit, float $credit, string $description) => [
                'journal_voucher_id' => $voucher->id, 'account_id' => $account, 'company_id' => companyId(),
                'description' => $description, 'debit_amount' => $debit, 'credit_amount' => $credit,
                'base_debit_amount' => round($debit * $rate, 2), 'base_credit_amount' => round($credit * $rate, 2),
                'tax_amount' => 0, 'base_tax_amount' => 0, 'created_at' => now(), 'updated_at' => now(),
            ];
            $lines = [$line((int) $validated['to_account_id'], $amount, 0, 'Transfer from ' . ($names[$validated['from_account_id']] ?? ''))];
            if ($charge > 0) {
                $lines[] = $line((int) $validated['charge_account_id'], $charge, 0, 'Bank charge on transfer ' . $voucher->row_no);
            }
            $lines[] = $line((int) $validated['from_account_id'], 0, $total, 'Transfer to ' . ($names[$validated['to_account_id']] ?? ''));

            JournalVoucherItem::where('journal_voucher_id', $voucher->id)->where('company_id', companyId())->delete();
            DB::table('journal_voucher_items')->insert($lines);

            return response()->json([
                'status' => 'success',
                'message' => __('Transfer voucher saved successfully'),
                'journal_voucher_id' => $voucher->id,
            ]);
        });
    }

    public function fetchAllRows(Request $request)
    {
        $query = $this->vouchers()
            ->with(['journalVoucherItems:id,journal_voucher_id,account_id,debit_amount,credit_amount', 'journalVoucherItems.account:id,code,name'])
            ->select(['journal_vouchers.id', 'row_no', 'voucher_date', 'reference_no', 'currency', 'debit_total', 'notes', 'status', 'journal_vouchers.company_id'])
            ->when($request->tab && $request->tab !== 'all', fn ($q) => $q->where('status', JournalVoucherStatusEnum::fromName($request->tab)))
            ->orderBy('journal_vouchers.id', 'desc');

        $statusCounts = $this->vouchers()->select('status', DB::raw('COUNT(*) as total'))->groupBy('status')->pluck('total', 'status')->toArray();
        $allCounts = [];
        foreach (JournalVoucherStatusEnum::cases() as $status) {
            $allCounts[$status->name] = $statusCounts[$status->value] ?? 0;
        }
        $allCounts['all'] = array_sum($allCounts);

        $account = fn ($item) => $item && $item->account ? $item->account->code . ' - ' . __($item->account->name) : '-';

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('from_account', function ($model) use ($account) {
                return $account($this->split($model)[2]);
            })
            ->addColumn('to_account', function ($model) use ($account) {
                return $account($this->split($model)[0]);
            })
            ->addColumn('charge', function ($model) {
                $charge = $this->split($model)[1];

                return $charge ? number_format((float) $charge->debit_amount, decimals()) : '';
            })
            ->addColumn('amount', function ($model) {
                $to = $this->split($model)[0];

                return number_format((float) ($to->debit_amount ?? 0), decimals());
            })
            ->editColumn('voucher_date', fn ($model) => \Carbon\Carbon::parse($model->voucher_date)->format('d-m-Y'))
            ->editColumn('currency', fn ($model) => strtoupper($model->currency))
            ->editColumn('status', fn ($model) => JournalVoucherStatusEnum::from($model->status)->label())
            ->with(['statusCounts' => $allCounts])
            ->toJson();
    }
}
