<?php

namespace App\Console\Commands;

use App\Enums\CollectionEnum;
use App\Enums\CreditNoteEnum;
use App\Enums\CustomerInvoiceEnum;
use App\Enums\DebitNoteEnum;
use App\Enums\ExpenseEnum;
use App\Enums\PaymentEnum;
use App\Enums\ProformaInvoiceEnum;
use App\Enums\SupplierInvoiceEnum;
use App\Http\Controllers\Customer\CustomerController;
use App\Http\Controllers\Finance\Adjustment\CreditNoteController;
use App\Http\Controllers\Finance\Adjustment\DebitNoteController;
use App\Http\Controllers\Finance\Asset\AssetController;
use App\Http\Controllers\Finance\Expense\ExpenseController;
use App\Http\Controllers\Finance\Invoice\CustomerInvoiceController;
use App\Http\Controllers\Finance\Invoice\ProformaInvoiceController;
use App\Http\Controllers\Finance\Invoice\SupplierInvoiceController;
use App\Http\Controllers\Finance\JournalVoucher\JournalVoucherController;
use App\Http\Controllers\Finance\OpeningBalance\OpeningBalanceController;
use App\Http\Controllers\Master\PeriodClosingController;
use App\Http\Controllers\Payroll\EmployeeLoanController;
use App\Http\Controllers\Payroll\PayrollRunController;
use App\Http\Controllers\Supplier\SupplierController;
use App\Http\Controllers\Transaction\CollectionController;
use App\Http\Controllers\Transaction\PaymentController;
use App\Models\Customer\Customer;
use App\Models\Finance\Adjustment\CreditNote;
use App\Models\Finance\Adjustment\DebitNote;
use App\Models\Finance\BankReconciliation\BankReconciliation;
use App\Models\Finance\Collection\Collection;
use App\Models\Finance\CustomerInvoice\CustomerInvoice;
use App\Models\Finance\Expense\Expense;
use App\Models\Finance\JournalVoucher\JournalVoucher;
use App\Models\Finance\Payment\Payment;
use App\Models\Finance\ProformaInvoice\ProformaInvoice;
use App\Models\Finance\SupplierInvoice\SupplierInvoice;
use App\Models\Master\PeriodClosing;
use App\Models\Payroll\EmployeeLoan;
use App\Models\Payroll\LoanInstallment;
use App\Models\Payroll\PayrollRecord;
use App\Models\Supplier\Supplier;
use App\Services\Finance\BankReconciliationService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Posting audit of every module that writes the ledger (finance / finance_subs), through the real controllers:
 * for each document type: a draft posts nothing, approve posts one balanced voucher on the right accounts (party, job,
 * VAT, currency), edit / re-approve re-posts, and draft / cancel / disapprove / delete takes it out again (and gives back
 * the settled amount on the invoice). Also: proforma, opening balance, bank reconciliation entries, period locks,
 * payroll pay / disapprove, employee loans and repayments, asset depreciation schedule.
 * Everything runs in one transaction that is rolled back (nothing is kept, no email is sent). Writes an Excel report.
 *   php artisan finance:module-audit [--out=path.xlsx]
 */
class FinanceModuleAudit extends FinanceChainTest
{
    protected $signature = 'finance:module-audit {--out= : report file}';
    protected $description = 'Check the ledger entries every module posts (approve / edit / disapprove / cancel / delete), including payroll';

    protected const AUDIT = 'MODAUDIT';
    protected const GROUP_BANK = 4, OWNER_CAPITAL_CODE = '3100';

    protected string $reportPrefix = 'Finance-Module-Audit-Report';
    protected string $reportTitle = 'Finance posting audit per module (approve / edit / disapprove / cancel / delete), incl. payroll and loans';

    /** [module, what posts, what was tested, result, fixed] filled while running */
    protected array $modules = [];

    public function handle(): int
    {
        auth()->loginUsingId(1);
        session(['company_id' => 1]);

        // two spare levels: a controller that commits once too often (catch → rollBack, finally → commit) must never
        // reach the real COMMIT of this test transaction
        DB::beginTransaction();
        DB::beginTransaction();
        DB::beginTransaction();
        try {
            $this->step('Setup (customer, job, supplier chain)', function () {
                $this->customerChain();
                $this->supplierChain();
            });
            foreach ([
                'Customer invoice' => 'customerInvoice', 'Supplier invoice' => 'supplierInvoice', 'Proforma invoice' => 'proforma',
                'Credit note' => 'creditNote', 'Debit note' => 'debitNote', 'Expense' => 'expenseAudit', 'Journal voucher' => 'journalVoucher',
                'Payment' => 'payment', 'Collection' => 'collection', 'Opening balance' => 'openingBalance', 'Bank reconciliation' => 'bankReconciliation',
                'Payroll' => 'payrollAudit', 'Employee loan' => 'loans', 'Asset' => 'asset', 'Period closing' => 'periodLocks',
            ] as $area => $fn) {
                $this->step($area, fn () => $this->$fn());
                $this->guardTransaction($area);
            }
            $this->step('Whole ledger', fn () => $this->wholeLedger());
        } finally {
            DB::rollBack(0);
            Cache::forget('period-closing-lock:1');
        }

        $file = $this->report();
        $this->addModuleSheet($file);
        $fail = collect($this->checks)->where(4, false)->count();
        $this->info(count($this->checks) . ' checks, ' . $fail . ' failed. Report: ' . $file . ' (everything rolled back)');
        foreach (collect($this->checks)->where(4, false) as [$area, $name, $exp, $act]) {
            $this->line("  FAIL [$area] $name — expected $exp, got $act");
        }

        return $fail ? 1 : 0;
    }

    // ───────────── plumbing ─────────────

    /** Like the chain test's step, but an exception names the app file and line it came from. */
    protected function step(string $area, callable $fn): void
    {
        try {
            $fn();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->checks[] = [$area, 'Completed without error', 'no error', 'Validation: ' . json_encode($e->errors()), false];
        } catch (\Throwable $e) {
            $at = collect($e->getTrace())->first(fn ($t) => str_contains($t['file'] ?? '', '/app/'));
            $this->checks[] = [$area, 'Completed without error', 'no error', get_class($e) . ': ' . $e->getMessage()
                . ' (' . basename($at['file'] ?? $e->getFile()) . ':' . ($at['line'] ?? $e->getLine()) . ')', false];
        }
    }

    protected function guardTransaction(string $area): void
    {
        $level = DB::transactionLevel();
        if ($level < 3) {
            $this->checks[] = [$area, 'Controller left the transaction level as it found it', 3, $level, false];
            while (DB::transactionLevel() < 3) {
                DB::beginTransaction();
            }
        }
    }

    /** Vouchers (finance headers) of one document. */
    protected function vouchers(string $type, int $id)
    {
        return DB::table('finance')->where('linked_type', $type)->where('linked_id', $id)->get();
    }

    protected function linesOf(string $type, int $id)
    {
        return DB::table('finance_subs')->whereIn('finance_id', $this->vouchers($type, $id)->pluck('id'))->get();
    }

    protected function none(string $area, string $label, string $type, int $id): void
    {
        $this->chk($area, $label . ': no ledger entry', 0, $this->vouchers($type, $id)->count() + $this->linesOf($type, $id)->count(), 0);
    }

    /**
     * One balanced voucher, header totals = its lines, and the movement per account.
     * $expect: account_id => [debit, credit] in the company currency.  $party: account_id => [column => id] that line must carry.
     */
    protected function posted(string $area, string $label, string $type, int $id, array $expect, array $party = [], ?int $jobId = null, ?int $vatAccount = null): void
    {
        $v = $this->vouchers($type, $id);
        $this->chk($area, "$label: one voucher", 1, $v->count(), 0);
        if ($v->isEmpty()) return;
        $lines = $this->linesOf($type, $id);
        $dr = round($lines->sum('base_debit'), 2);
        $cr = round($lines->sum('base_credit'), 2);
        $this->chk($area, "$label: base debit = base credit", $dr, $cr);
        $this->chk($area, "$label: document-currency debit = credit", round($lines->sum('debit'), 2), round($lines->sum('credit'), 2));
        $this->chk($area, "$label: header base total = lines", round($v->sum('base_total_debit'), 2), $dr);
        foreach ($expect as $acc => [$eDr, $eCr]) {
            $a = $lines->where('account_id', $acc);
            $this->chk($area, "$label: account $acc debit", $eDr, round($a->sum('base_debit'), 2));
            $this->chk($area, "$label: account $acc credit", $eCr, round($a->sum('base_credit'), 2));
        }
        $other = $lines->whereNotIn('account_id', array_keys($expect))->pluck('account_id')->unique()->values()->all();
        $this->ok($area, "$label: no other accounts", empty($other), 'also on ' . implode(',', $other));
        foreach ($party as $acc => $cols) {
            foreach ($cols as $col => $want) {
                $bad = $lines->where('account_id', $acc)->filter(fn ($l) => (int) $l->$col !== (int) $want)->count();
                $this->chk($area, "$label: account $acc lines carry $col $want", 0, $bad, 0);
            }
        }
        if ($jobId) {
            $this->chk($area, "$label: every line carries the job", 0, $lines->filter(fn ($l) => (int) $l->job_id !== $jobId)->count(), 0);
        }
        if ($vatAccount) {
            $this->chk($area, "$label: VAT line flagged as tax line", 0, $lines->where('account_id', $vatAccount)->where('is_tax_line', '!=', 1)->count(), 0);
        }
    }

    protected function module(string $name, string $posts, string $tested, string $fixed = ''): void
    {
        $this->modules[$name] = [$name, $posts, $tested, null, $fixed];
    }

    protected function redirectMessage($res): string
    {
        if ($res instanceof \Illuminate\Http\RedirectResponse) {
            $errors = session('errors');
            $msg = $errors ? implode(' ', $errors->all()) : (string) session('success');
            session()->forget(['errors', 'success']);
            return $msg;
        }
        if (is_object($res) && method_exists($res, 'getData')) {
            return (string) ($res->getData(true)['message'] ?? '');
        }
        return '';
    }

    /** Run something that must be refused; returns the message it gave. */
    protected function attempt(callable $fn): string
    {
        try {
            return $this->redirectMessage($fn());
        } catch (\Illuminate\Validation\ValidationException $e) {
            return collect($e->errors())->flatten()->implode(' ');
        } catch (\Throwable $e) {
            return get_class($e) . ': ' . $e->getMessage();
        }
    }

    protected function newInvoice(string $key, float $price, array $extra = []): CustomerInvoice
    {
        $this->send(app(CustomerInvoiceController::class), 'store', array_merge([
            'job_id' => $this->ids['job'], 'customer' => encodeId($this->ids['customer']), 'invoice_date' => '2026-11-14', 'due_date' => '2026-12-14',
            'currency' => 'SAR', 'currency_rate' => 1, 'terms' => self::AUDIT . ' ' . $key,
            'description_id' => ['2'], 'comment' => [self::AUDIT . ' ' . $key], 'unit_id' => ['1'],
            'quantity' => [1], 'unit_price' => [(string) $price], 'tax' => ['STANDARD'], 'account' => [self::REVENUE],
        ], $extra));

        return CustomerInvoice::where('terms', self::AUDIT . ' ' . $key)->latest('id')->firstOrFail();
    }

    protected function newBill(string $key, float $price, ?int $supplierId = null, array $extra = []): SupplierInvoice
    {
        $payload = array_merge([
            'job_id' => $this->ids['job'], 'supplier' => encodeId($supplierId ?? $this->ids['supplier']), 'invoice_date' => '2026-11-14',
            'invoice_number' => self::AUDIT . '-' . $key, 'due_date' => '2026-12-14', 'currency' => 'SAR', 'currency_rate' => 1, 'terms' => self::AUDIT . ' ' . $key,
            'description_id' => ['2'], 'comment' => [self::AUDIT . ' ' . $key], 'unit_id' => ['1'],
            'quantity' => [1], 'unit_price' => [(string) $price], 'tax' => ['STANDARD'], 'account' => [self::COST],
        ], $extra);
        $this->send(app(SupplierInvoiceController::class), 'store', $payload);

        return SupplierInvoice::where('terms', $payload['terms'])->latest('id')->firstOrFail();
    }

    protected function party(string $kind, string $name, string $currency): int
    {
        $controller = $kind === 'customer' ? CustomerController::class : SupplierController::class;
        $model = $kind === 'customer' ? Customer::class : Supplier::class;
        $this->send(app($controller), 'store', [
            'name_en' => self::AUDIT . ' ' . $name, 'name_ar' => 'اختبار', 'currency' => $currency, 'business_type' => 'unregistered',
            'country' => 'Saudi Arabia', 'email' => strtolower(str_replace(' ', '.', $name)) . '.audit@example.test', 'phone' => '+9665000' . random_int(10000, 99999), 'credit_days' => 30,
        ]);

        return (int) $model::where('name_en', self::AUDIT . ' ' . $name)->latest('id')->value('id');
    }

    // ───────────── customer invoice ─────────────

    protected function customerInvoice(): void
    {
        $A = 'Customer invoice';
        $C = app(CustomerInvoiceController::class);
        $cid = $this->ids['customer'];
        $job = $this->ids['job'];

        $inv = $this->newInvoice('CI-A', 1000);
        $this->none($A, 'Draft', CustomerInvoice::class, $inv->id);
        $this->status($C, $inv->id, CustomerInvoiceEnum::APPROVED->value);
        $this->posted($A, 'Approved 1,000 + VAT', CustomerInvoice::class, $inv->id,
            [self::AR => [1150, 0], self::REVENUE => [0, 1000], self::VAT_OUT => [0, 150]], [self::AR => ['customer_id' => $cid]], $job, self::VAT_OUT);

        // editing an approved invoice must not leave its old entry behind
        $before = $inv->fresh();
        $this->send($C, 'store', [
            'data-id' => $inv->id, 'job_id' => $job, 'customer' => encodeId($cid), 'invoice_date' => '2026-11-14', 'due_date' => '2026-12-14',
            'currency' => 'SAR', 'currency_rate' => 1, 'terms' => self::AUDIT . ' CI-A', 'description_id' => ['2'], 'comment' => ['edit'], 'unit_id' => ['1'],
            'quantity' => [1], 'unit_price' => ['2000'], 'tax' => ['STANDARD'], 'account' => [self::REVENUE],
        ]);
        $after = $inv->fresh();
        $edited = (float) $after->grand_total !== (float) $before->grand_total;
        $this->ok($A, 'Editing an APPROVED invoice is refused (the books would no longer match the document)', !$edited,
            'saved: total ' . $before->grand_total . ' → ' . $after->grand_total . ', status ' . (int) $before->status . ' → ' . (int) $after->status . ', vouchers ' . $this->vouchers(CustomerInvoice::class, $inv->id)->count());

        $this->status($C, $inv->id, CustomerInvoiceEnum::DRAFT->value);
        $this->none($A, 'Moved back to draft', CustomerInvoice::class, $inv->id);
        $this->send($C, 'store', [
            'data-id' => $inv->id, 'job_id' => $job, 'customer' => encodeId($cid), 'invoice_date' => '2026-11-14', 'due_date' => '2026-12-14',
            'currency' => 'SAR', 'currency_rate' => 1, 'terms' => self::AUDIT . ' CI-A', 'description_id' => ['2', '2'], 'comment' => ['a', 'b'], 'unit_id' => ['1', '1'],
            'quantity' => [1, 2], 'unit_price' => ['500', '250'], 'tax' => ['STANDARD', 'STANDARD'], 'account' => [self::REVENUE, 38],
        ]);
        $this->none($A, 'Draft edited (2 lines, 2 income accounts)', CustomerInvoice::class, $inv->id);
        $this->status($C, $inv->id, CustomerInvoiceEnum::APPROVED->value);
        $this->posted($A, 'Re-approved after edit', CustomerInvoice::class, $inv->id,
            [self::AR => [1150, 0], self::REVENUE => [0, 500], 38 => [0, 500], self::VAT_OUT => [0, 150]], [self::AR => ['customer_id' => $cid]], $job, self::VAT_OUT);
        $this->status($C, $inv->id, CustomerInvoiceEnum::CANCELLED->value);
        $this->none($A, 'Approved → cancelled', CustomerInvoice::class, $inv->id);

        // an approved invoice that is already collected must not be cancelled (its receivable would vanish, the cash stays)
        $res = $this->status($C, $this->ids['inv1'], CustomerInvoiceEnum::CANCELLED->value);
        $this->ok($A, 'Cancelling an approved invoice that has an approved collection / credit note is refused',
            (int) CustomerInvoice::find($this->ids['inv1'])->status === CustomerInvoiceEnum::APPROVED->value, (string) ($res['message'] ?? ''));
        if ((int) CustomerInvoice::find($this->ids['inv1'])->status !== CustomerInvoiceEnum::APPROVED->value) {
            $this->status($C, $this->ids['inv1'], CustomerInvoiceEnum::APPROVED->value);
        }

        // foreign currency, rounding: 3 × 333.33 USD at 3.7513
        $usd = $this->party('customer', 'USD Customer', 'USD');
        $this->ids['usd_customer'] = $usd;
        $fx = $this->newInvoice('CI-FX', 333.33, [
            'customer' => encodeId($usd), 'currency' => 'USD', 'currency_rate' => 3.7513,
            'description_id' => ['2', '2', '2'], 'comment' => ['a', 'b', 'c'], 'unit_id' => ['1', '1', '1'],
            'quantity' => [1, 1, 1], 'unit_price' => ['333.33', '333.33', '333.33'], 'tax' => ['STANDARD', 'STANDARD', 'STANDARD'], 'account' => [self::REVENUE, self::REVENUE, self::REVENUE],
        ]);
        $this->status($C, $fx->id, CustomerInvoiceEnum::APPROVED->value);
        $fx->refresh();
        $lines = $this->linesOf(CustomerInvoice::class, $fx->id);
        $this->chk($A, 'USD invoice: voucher in USD at 3.7513', 'USD|3.7513', (string) ($lines->first()->currency ?? '') . '|' . rtrim(rtrim((string) ($lines->first()->exchange_rate ?? ''), '0'), '.'));
        $this->chk($A, 'USD invoice: base debit = base credit', round($lines->sum('base_debit'), 2), round($lines->sum('base_credit'), 2));
        $this->chk($A, 'USD invoice: receivable in SAR = invoice base total', (float) $fx->base_grand_total, round($lines->where('account_id', self::AR)->sum('base_debit'), 2));
        $this->chk($A, 'USD invoice: receivable in USD = invoice total', (float) $fx->grand_total, round($lines->where('account_id', self::AR)->sum('debit'), 2));

        $msg = $this->attempt(fn () => $this->newInvoice('CI-BANKLINE', 100, ['account' => [self::BANK]]));
        $this->ok($A, 'An invoice line on the bank account (income booked as cash) is refused', !CustomerInvoice::where('terms', self::AUDIT . ' CI-BANKLINE')->exists(), $msg);
        $msg = $this->attempt(fn () => $this->newInvoice('CI-ARLINE', 100, ['account' => [self::AR]]));
        $this->ok($A, 'An invoice line on 1130 Receivable is refused', !CustomerInvoice::where('terms', self::AUDIT . ' CI-ARLINE')->exists(), $msg);

        $draft = $this->newInvoice('CI-DEL', 10);
        $this->status($C, $draft->id, CustomerInvoiceEnum::APPROVED->value);
        $this->status($C, $draft->id, CustomerInvoiceEnum::DRAFT->value);
        $C->delete($draft->id);
        $this->none($A, 'Approved, back to draft, deleted', CustomerInvoice::class, $draft->id);

        $this->module($A, 'Approve: Dr 1130 Receivable (customer, job) / Cr income account per line (fallback 4160) / Cr 2130 Output VAT. Draft, cancel: entry removed.',
            'Draft, approve, edit while approved, back to draft, edit draft with 2 income accounts, re-approve, cancel; cancel with collection; USD 3 × 333.33 @ 3.7513; approve → draft → delete.');
    }

    // ───────────── supplier invoice ─────────────

    protected function supplierInvoice(): void
    {
        $A = 'Supplier invoice';
        $C = app(SupplierInvoiceController::class);
        $sid = $this->ids['supplier'];
        $job = $this->ids['job'];

        $bill = $this->newBill('SI-A', 1000);
        $this->none($A, 'Draft', 'supplier_invoice', $bill->id);
        $this->status($C, $bill->id, SupplierInvoiceEnum::APPROVED->value);
        $this->posted($A, 'Approved 1,000 + VAT', 'supplier_invoice', $bill->id,
            [self::COST => [1000, 0], self::VAT_IN => [150, 0], self::AP => [0, 1150]], [self::AP => ['supplier_id' => $sid]], $job, self::VAT_IN);

        $before = $bill->fresh();
        $this->send($C, 'store', [
            'data-id' => $bill->id, 'job_id' => $job, 'supplier' => encodeId($sid), 'invoice_date' => '2026-11-14', 'invoice_number' => self::AUDIT . '-SI-A',
            'due_date' => '2026-12-14', 'currency' => 'SAR', 'currency_rate' => 1, 'terms' => self::AUDIT . ' SI-A', 'description_id' => ['2'], 'comment' => ['edit'],
            'unit_id' => ['1'], 'quantity' => [1], 'unit_price' => ['3000'], 'tax' => ['STANDARD'], 'account' => [self::COST],
        ]);
        $after = $bill->fresh();
        $this->ok($A, 'Editing an APPROVED supplier invoice is refused', (float) $after->grand_total === (float) $before->grand_total,
            'saved: total ' . $before->grand_total . ' → ' . $after->grand_total . ', status ' . (int) $before->status . ' → ' . (int) $after->status . ', vouchers ' . $this->vouchers('supplier_invoice', $bill->id)->count());

        $this->status($C, $bill->id, SupplierInvoiceEnum::DRAFT->value);
        $this->none($A, 'Moved back to draft', 'supplier_invoice', $bill->id);
        $this->status($C, $bill->id, SupplierInvoiceEnum::APPROVED->value);
        $this->status($C, $bill->id, SupplierInvoiceEnum::APPROVED->value);
        $this->chk($A, 'Approved twice: still one voucher', 1, $this->vouchers('supplier_invoice', $bill->id)->count(), 0);

        // the supplier's own bill number is only unique per supplier: another supplier's bill with the same number
        $other = $this->party('supplier', 'Second Supplier', 'SAR');
        $twin = $this->newBill('SI-A', 400, $other, ['terms' => self::AUDIT . ' SI-TWIN']);
        $this->status($C, $twin->id, SupplierInvoiceEnum::APPROVED->value);
        $this->posted($A, 'Other supplier, same bill number, approved', 'supplier_invoice', $twin->id,
            [self::COST => [400, 0], self::VAT_IN => [60, 0], self::AP => [0, 460]], [self::AP => ['supplier_id' => $other]], $job, self::VAT_IN);
        $this->chk($A, 'First bill keeps its voucher after the second supplier\'s bill with the same number is approved', 1, $this->vouchers('supplier_invoice', $bill->id)->count(), 0);
        $this->status($C, $twin->id, SupplierInvoiceEnum::CANCELLED->value);
        $this->none($A, 'Second supplier\'s bill cancelled', 'supplier_invoice', $twin->id);
        $this->chk($A, 'First bill keeps its voucher after the other bill with the same number is cancelled', 1, $this->vouchers('supplier_invoice', $bill->id)->count(), 0);

        $this->status($C, $bill->id, SupplierInvoiceEnum::CANCELLED->value);
        $this->none($A, 'Approved → cancelled', 'supplier_invoice', $bill->id);

        $res = $this->status($C, $this->ids['supplier_invoice'], SupplierInvoiceEnum::CANCELLED->value);
        $this->ok($A, 'Cancelling an approved supplier invoice that has payments / a debit note is refused',
            (int) SupplierInvoice::find($this->ids['supplier_invoice'])->status === SupplierInvoiceEnum::APPROVED->value, (string) ($res['message'] ?? ''));
        if ((int) SupplierInvoice::find($this->ids['supplier_invoice'])->status !== SupplierInvoiceEnum::APPROVED->value) {
            $this->status($C, $this->ids['supplier_invoice'], SupplierInvoiceEnum::APPROVED->value);
        }

        $msg = $this->attempt(fn () => $this->newBill('SI-GRPLINE', 100, null, ['account' => [self::GROUP_BANK]]));
        $this->ok($A, 'A bill line on a group account (1120) is refused', !SupplierInvoice::where('terms', self::AUDIT . ' SI-GRPLINE')->exists(), $msg);

        $usd = $this->party('supplier', 'USD Supplier', 'USD');
        $this->ids['usd_supplier'] = $usd;
        $fx = $this->newBill('SI-FX', 333.33, $usd, [
            'currency' => 'USD', 'currency_rate' => 3.7513, 'description_id' => ['2', '2', '2'], 'comment' => ['a', 'b', 'c'], 'unit_id' => ['1', '1', '1'],
            'quantity' => [1, 1, 1], 'unit_price' => ['333.33', '333.33', '333.33'], 'tax' => ['STANDARD', 'STANDARD', 'STANDARD'], 'account' => [self::COST, self::COST, self::COST],
        ]);
        $this->status($C, $fx->id, SupplierInvoiceEnum::APPROVED->value);
        $fx->refresh();
        $this->ids['usd_bill'] = $fx->id;
        $lines = $this->linesOf('supplier_invoice', $fx->id);
        $this->chk($A, 'USD bill: base debit = base credit', round($lines->sum('base_debit'), 2), round($lines->sum('base_credit'), 2));
        $this->chk($A, 'USD bill: payable in SAR = bill base total', (float) $fx->base_grand_total, round($lines->where('account_id', self::AP)->sum('base_credit'), 2));

        $this->module($A, 'Approve: Dr cost account per line / Dr 1150 Input VAT / Cr 2110 Payable (supplier, job). Draft, cancel: entry removed.',
            'Draft, approve, edit while approved, back to draft, approve twice, same bill number at another supplier (approve + cancel), cancel with payments, USD 3 × 333.33 @ 3.7513.');
    }

    // ───────────── proforma ─────────────

    protected function proforma(): void
    {
        $A = 'Proforma invoice';
        $before = DB::table('finance_subs')->count();
        $this->send(app(ProformaInvoiceController::class), 'store', [
            'job_id' => $this->ids['job'], 'invoice_date' => '2026-11-14', 'currency' => 'SAR', 'currency_rate' => 1, 'terms' => self::AUDIT . ' PFI',
            'description_id' => ['2'], 'comment' => ['advance'], 'unit_id' => ['1'], 'quantity' => [1], 'unit_price' => ['800'], 'tax' => ['STANDARD'],
        ]);
        $pf = ProformaInvoice::where('terms', self::AUDIT . ' PFI')->firstOrFail();
        $this->status(app(ProformaInvoiceController::class), $pf->id, ProformaInvoiceEnum::APPROVED->value);
        $this->chk($A, 'Approved proforma posts nothing', $before, DB::table('finance_subs')->count(), 0);
        $this->chk($A, 'No voucher linked to the proforma', 0, DB::table('finance')->where('linked_type', ProformaInvoice::class)->where('linked_id', $pf->id)->count(), 0);
        $this->status(app(ProformaInvoiceController::class), $pf->id, ProformaInvoiceEnum::CANCELLED->value);
        $this->chk($A, 'Cancelled proforma posts nothing', $before, DB::table('finance_subs')->count(), 0);
        $this->module($A, 'Nothing (a request for payment, not a tax invoice).', 'Save, approve, cancel: ledger line count unchanged.');
    }

    // ───────────── credit / debit note ─────────────

    protected function creditNote(): void
    {
        $A = 'Credit note';
        $C = app(CreditNoteController::class);
        $inv = $this->newInvoice('CN-INV', 1000);
        app(CustomerInvoiceController::class)->updateStatus($inv->id, CustomerInvoiceEnum::APPROVED->value);
        $this->send($C, 'store', [
            'credit_note_type' => '1', 'invoice_id' => $inv->id, 'customer' => encodeId($this->ids['customer']), 'credit_note_date' => '2026-11-15',
            'job_id' => $this->ids['job'], 'reason' => self::AUDIT, 'terms' => self::AUDIT . ' CN', 'description_id' => ['2'],
            'comment' => ['x'], 'unit_id' => ['1'], 'quantity' => [1], 'unit_price' => ['100'], 'tax' => ['STANDARD'], 'account' => [self::REVENUE], 'confirm_credit' => 1,
        ]);
        $cn = CreditNote::where('terms', self::AUDIT . ' CN')->latest('id')->firstOrFail();
        $this->none($A, 'Draft', CreditNote::class, $cn->id);
        $this->chk($A, 'Draft does not settle the invoice', 0, (float) $inv->fresh()->paid_amount);
        $this->status($C, $cn->id, CreditNoteEnum::APPROVED->value);
        $this->posted($A, 'Approved 100 + VAT', CreditNote::class, $cn->id,
            [self::REVENUE => [100, 0], self::VAT_OUT => [15, 0], self::AR => [0, 115]], [self::AR => ['customer_id' => $this->ids['customer']]], $this->ids['job'], self::VAT_OUT);
        $this->chk($A, 'Approved note settles 115 on the invoice', 115, (float) $inv->fresh()->paid_amount);
        $this->send($C, 'store', [
            'data-id' => $cn->id, 'credit_note_type' => '1', 'invoice_id' => $inv->id, 'customer' => encodeId($this->ids['customer']), 'credit_note_date' => '2026-11-15',
            'job_id' => $this->ids['job'], 'reason' => self::AUDIT, 'terms' => self::AUDIT . ' CN', 'description_id' => ['2'],
            'comment' => ['x'], 'unit_id' => ['1'], 'quantity' => [1], 'unit_price' => ['300'], 'tax' => ['STANDARD'], 'account' => [self::REVENUE], 'confirm_credit' => 1,
        ]);
        $this->chk($A, 'Editing an APPROVED credit note is refused (total unchanged)', 115, (float) $cn->fresh()->grand_total);
        $res = $this->status(app(CustomerInvoiceController::class), $inv->id, CustomerInvoiceEnum::CANCELLED->value);
        $this->ok($A, 'Its invoice cannot be cancelled while the approved note settles it', (int) $inv->fresh()->status === CustomerInvoiceEnum::APPROVED->value, (string) ($res['message'] ?? ''));
        $this->status($C, $cn->id, CreditNoteEnum::CANCELLED->value);
        $this->none($A, 'Approved → cancelled', CreditNote::class, $cn->id);
        $this->chk($A, 'Cancel gives the 115 back to the invoice', 0, (float) $inv->fresh()->paid_amount);
        $this->module($A, 'Approve: Dr income account per line / Dr 2130 Output VAT / Cr 1130 Receivable (customer, job); adds the total to the invoice\'s settled amount. Cancel: removed, settled amount given back.',
            'Draft, approve, cancel; invoice settled amount after each step.');
    }

    protected function debitNote(): void
    {
        $A = 'Debit note';
        $C = app(DebitNoteController::class);
        $bill = $this->newBill('DN-BILL', 1000);
        app(SupplierInvoiceController::class)->updateStatus($bill->id, SupplierInvoiceEnum::APPROVED->value);
        $this->send($C, 'store', [
            'invoice_id' => $bill->id, 'supplier' => encodeId($this->ids['supplier']), 'debit_note_date' => '2026-11-15', 'job_id' => $this->ids['job'],
            'reason' => self::AUDIT, 'terms' => self::AUDIT . ' DN', 'description_id' => ['2'], 'comment' => ['x'],
            'unit_id' => ['1'], 'quantity' => [1], 'unit_price' => ['100'], 'tax' => ['STANDARD'], 'account' => [self::COST],
        ]);
        $dn = DebitNote::where('terms', self::AUDIT . ' DN')->latest('id')->firstOrFail();
        $this->none($A, 'Draft', DebitNote::class, $dn->id);
        $this->status($C, $dn->id, DebitNoteEnum::APPROVED->value);
        $this->posted($A, 'Approved 100 + VAT', DebitNote::class, $dn->id,
            [self::AP => [115, 0], self::COST => [0, 100], self::VAT_IN => [0, 15]], [self::AP => ['supplier_id' => $this->ids['supplier']]], $this->ids['job'], self::VAT_IN);
        $this->chk($A, 'Approved note settles 115 on the bill', 115, (float) $bill->fresh()->paid_amount);
        $this->ids['audit_dn'] = $dn->id;
        $this->status($C, $dn->id, DebitNoteEnum::CANCELLED->value);
        $this->none($A, 'Approved → cancelled', DebitNote::class, $dn->id);
        $this->chk($A, 'Cancel gives the 115 back to the bill', 0, (float) $bill->fresh()->paid_amount);
        $this->module($A, 'Approve: Dr 2110 Payable (supplier, job) / Cr cost account per line / Cr 1150 Input VAT; adds to the bill\'s settled amount. Cancel: removed, given back.',
            'Draft, approve, cancel; bill settled amount after each step.');
    }

    // ───────────── expense ─────────────

    protected function expensePayload(string $ref, float $price, array $extra = []): array
    {
        return array_merge([
            'posted_at' => '2026-11-15', 'reference_number' => $ref, 'payment_status' => 'paid', 'main_account' => self::BANK,
            'paid_amount' => $price * 1.15, 'amount_excluding_vat' => $price, 'amount_including_vat' => $price * 1.15, 'job_id' => $this->ids['job'],
            'account' => [self::EXPENSE], 'comment' => [self::AUDIT . ' expense'], 'quantity' => [1], 'unit_price' => [(string) $price],
            'tax' => ['STANDARD'], 'item_id' => [], 'employee_id' => [''],
        ], $extra);
    }

    protected function expenseAudit(): void
    {
        $A = 'Expense';
        $C = app(ExpenseController::class);
        $this->send($C, 'store', $this->expensePayload(self::AUDIT . '-EXP', 200));
        $e = Expense::where('reference_number', self::AUDIT . '-EXP')->latest('id')->firstOrFail();
        $this->none($A, 'Pending', Expense::class, $e->id);
        $this->send($C, 'updateStatus', [], [$e->id, ExpenseEnum::APPROVED->value]);
        $this->posted($A, 'Approved 200 + VAT paid from bank', Expense::class, $e->id,
            [self::EXPENSE => [200, 0], self::VAT_IN => [30, 0], self::BANK => [0, 230]], [], $this->ids['job'], self::VAT_IN);

        $this->send($C, 'store', $this->expensePayload(self::AUDIT . '-EXP', 300, ['data-id' => $e->id]));
        $e->refresh();
        $v = $this->vouchers(Expense::class, $e->id);
        $lines = $this->linesOf(Expense::class, $e->id);
        $stale = (int) $e->status !== ExpenseEnum::APPROVED->value && $v->count() > 0;
        $this->ok($A, 'Editing an approved expense does not leave an entry on a pending expense', !$stale,
            'status ' . (int) $e->status . ', ' . $v->count() . ' voucher(s), bank credit ' . round($lines->where('account_id', self::BANK)->sum('base_credit'), 2) . ', expense total ' . $e->grand_total);
        if ((int) $e->status === ExpenseEnum::APPROVED->value) {
            $this->posted($A, 'Edited while approved: re-posted 300 + VAT', Expense::class, $e->id,
                [self::EXPENSE => [300, 0], self::VAT_IN => [45, 0], self::BANK => [0, 345]], [], $this->ids['job'], self::VAT_IN);
        } else {
            $this->send($C, 'updateStatus', [], [$e->id, ExpenseEnum::APPROVED->value]);
            $this->posted($A, 'Edited (back to pending) and re-approved: 300 + VAT', Expense::class, $e->id,
                [self::EXPENSE => [300, 0], self::VAT_IN => [45, 0], self::BANK => [0, 345]], [], $this->ids['job'], self::VAT_IN);
        }
        $this->send($C, 'updateStatus', [], [$e->id, ExpenseEnum::CANCELLED->value]);
        $this->none($A, 'Approved → cancelled', Expense::class, $e->id);

        $msg = $this->attempt(fn () => $this->send($C, 'store', $this->expensePayload(self::AUDIT . '-EXP-GRP', 50, ['main_account' => self::GROUP_BANK])));
        $g = Expense::where('reference_number', self::AUDIT . '-EXP-GRP')->latest('id')->first();
        if ($g) {
            $this->send($C, 'updateStatus', [], [$g->id, ExpenseEnum::APPROVED->value]);
        }
        $onGroup = $g ? $this->linesOf(Expense::class, $g->id)->where('account_id', self::GROUP_BANK)->count() : 0;
        $this->chk($A, 'Paid from a group account (1120 Bank Accounts) is refused ' . $msg, 0, $onGroup, 0);

        $this->module($A, 'Approve: Dr expense account per line (job) / Dr 1150 Input VAT / Cr the paid-from cash or bank account. Pending, cancel: removed.',
            'Pending, approve, edit while approved, re-approve, cancel; paid from group account 1120.');
    }

    // ───────────── journal voucher ─────────────

    protected function jvPayload(string $note, array $extra = []): array
    {
        return array_merge([
            'voucher_type' => 1, 'job_id' => $this->ids['job'], 'voucher_date' => '2026-11-15', 'reference_no' => self::AUDIT . '-JV', 'currency' => 'SAR', 'currency_rate' => 1,
            'notes' => $note, 'account_ids' => [self::EXPENSE, self::AR], 'descriptions' => ['expense', 'customer'],
            'debit_amounts' => [115, 0], 'credit_amounts' => [0, 115], 'entity_types' => [null, 'customer'], 'entity_ids' => [null, $this->ids['customer']],
            'tax_ids' => [self::VAT_IN, null], 'tax_amounts' => [15, 0],
        ], $extra);
    }

    protected function journalVoucher(): void
    {
        $A = 'Journal voucher';
        $C = app(JournalVoucherController::class);
        $this->send($C, 'store', $this->jvPayload(self::AUDIT . ' JV1'));
        $jv = JournalVoucher::where('notes', self::AUDIT . ' JV1')->latest('id')->firstOrFail();
        $this->none($A, 'Draft', JournalVoucher::class, $jv->id);
        $this->status($C, $jv->id, 2);
        $this->posted($A, 'Approved (100 expense + 15 VAT / customer 115)', JournalVoucher::class, $jv->id,
            [self::EXPENSE => [100, 0], self::VAT_IN => [15, 0], self::AR => [0, 115]], [self::AR => ['customer_id' => $this->ids['customer']]], $this->ids['job'], self::VAT_IN);

        $this->status($C, $jv->id, 1);
        $this->none($A, 'Approved → draft', JournalVoucher::class, $jv->id);
        $this->status($C, $jv->id, 2);
        $msg = $this->attempt(fn () => $C->setDisapprovalReason($this->request(['reason' => self::AUDIT]), $jv->id));
        $jv->refresh();
        $this->ok($A, 'Disapproving an APPROVED voucher (disapprove endpoint) takes its entry out of the books', $this->vouchers(JournalVoucher::class, $jv->id)->isEmpty(),
            'status ' . (int) ($jv->status instanceof \BackedEnum ? $jv->status->value : $jv->status) . ', ' . $this->vouchers(JournalVoucher::class, $jv->id)->count() . ' voucher(s) left ' . $msg);

        $this->send($C, 'store', $this->jvPayload(self::AUDIT . ' JV-UNBAL', ['credit_amounts' => [0, 100]]));
        $u = JournalVoucher::where('notes', self::AUDIT . ' JV-UNBAL')->latest('id')->first();
        if ($u) {
            $this->status($C, $u->id, 2);
        }
        $this->ok($A, 'An unbalanced voucher is refused (save or approve)', !$u || $this->vouchers(JournalVoucher::class, $u->id)->isEmpty());

        $this->send($C, 'store', $this->jvPayload(self::AUDIT . ' JV-FX', [
            'currency' => 'USD', 'currency_rate' => 3.755, 'account_ids' => [self::EXPENSE, self::EXPENSE, self::EXPENSE, self::BANK], 'descriptions' => ['a', 'b', 'c', 'd'],
            'debit_amounts' => [10.01, 10.01, 10.01, 0], 'credit_amounts' => [0, 0, 0, 30.03], 'entity_types' => [null, null, null, null], 'entity_ids' => [null, null, null, null],
            'tax_ids' => [null, null, null, null], 'tax_amounts' => [0, 0, 0, 0],
        ]));
        $fx = JournalVoucher::where('notes', self::AUDIT . ' JV-FX')->latest('id')->first();
        if ($fx) {
            $this->status($C, $fx->id, 2);
            $l = $this->linesOf(JournalVoucher::class, $fx->id);
            $this->chk($A, 'USD voucher 3 × 10.01 @ 3.755: base balances to the cent', round($l->sum('base_debit'), 2), round($l->sum('base_credit'), 2));
        }

        $this->module($A, 'Approve: one line per item on its account (customer / supplier / job), a line with a tax account is split into net + VAT (tax line). Draft / disapprove: removed.',
            'Draft, approve, back to draft, re-approve, disapprove endpoint on an approved voucher, unbalanced voucher, USD 3 × 10.01 @ 3.755 rounding.');
    }

    // ───────────── payment / collection ─────────────

    protected function payment(): void
    {
        $A = 'Payment';
        $C = app(PaymentController::class);
        $bill = $this->newBill('PAY-BILL', 1000);
        app(SupplierInvoiceController::class)->updateStatus($bill->id, SupplierInvoiceEnum::APPROVED->value);
        $mk = function (string $key, float $amount, array $extra = []) use ($C, $bill) {
            $this->send($C, 'store', array_merge([
                'supplier' => encodeId($this->ids['supplier']), 'payment_date' => '2026-11-16', 'account' => [self::BANK], 'reference_no' => self::AUDIT,
                'currency' => 'SAR', 'currency_rate' => 1, 'supplier_invoice_ids' => [$bill->id], 'invoice_amounts' => [$bill->id => $amount], 'notes' => self::AUDIT . ' ' . $key,
            ], $extra));

            return Payment::where('notes', self::AUDIT . ' ' . $key)->latest('id')->firstOrFail();
        };
        $p = $mk('PAY-A', 600);
        $this->none($A, 'Draft', Payment::class, $p->id);
        $this->status($C, $p->id, PaymentEnum::APPROVED->value);
        $this->posted($A, 'Approved 600', Payment::class, $p->id, [self::AP => [600, 0], self::BANK => [0, 600]], [self::AP => ['supplier_id' => $this->ids['supplier']]]);
        $this->chk($A, 'Approved payment settles 600 on the bill', 600, (float) $bill->fresh()->paid_amount);
        $this->status($C, $p->id, PaymentEnum::DRAFT->value);
        $this->none($A, 'Approved → draft', Payment::class, $p->id);
        $this->chk($A, 'Back to draft gives the 600 back', 0, (float) $bill->fresh()->paid_amount);

        $this->status($C, $p->id, PaymentEnum::APPROVED->value);
        $this->status($C, $p->id, PaymentEnum::CANCELLED->value);
        $this->none($A, 'Approved → cancelled (status menu)', Payment::class, $p->id);
        $this->chk($A, 'Approved → cancelled gives the 600 back to the bill', 0, (float) $bill->fresh()->paid_amount);

        $p2 = $mk('PAY-B', 300);
        $this->status($C, $p2->id, PaymentEnum::APPROVED->value);
        $C->setDisapprovalReason($this->request(['reason' => self::AUDIT]), $p2->id);
        $this->none($A, 'Approved → disapproved (with reason)', Payment::class, $p2->id);
        $this->chk($A, 'Disapprove gives the 300 back to the bill', 0, (float) $bill->fresh()->paid_amount);

        // USD bill booked at 3.7513, paid at 3.80: the difference is an exchange loss, the supplier ends at 0 in SAR
        $usdBill = SupplierInvoice::find($this->ids['usd_bill'] ?? 0);
        if ($usdBill) {
            $this->send($C, 'store', [
                'supplier' => encodeId($this->ids['usd_supplier']), 'payment_date' => '2026-11-16', 'account' => [self::BANK], 'reference_no' => self::AUDIT,
                'currency' => 'USD', 'currency_rate' => 3.80, 'supplier_invoice_ids' => [$usdBill->id], 'invoice_amounts' => [$usdBill->id => (float) $usdBill->grand_total], 'notes' => self::AUDIT . ' PAY-FX',
            ]);
            $px = Payment::where('notes', self::AUDIT . ' PAY-FX')->latest('id')->first();
            if ($px) {
                $this->status($C, $px->id, PaymentEnum::APPROVED->value);
                $l = $this->linesOf(Payment::class, $px->id);
                $this->chk($A, 'USD payment: base debit = base credit (with exchange difference)', round($l->sum('base_debit'), 2), round($l->sum('base_credit'), 2));
                $ap = (float) DB::table('finance_subs')->where('supplier_id', $this->ids['usd_supplier'])->where('account_id', self::AP)->selectRaw('COALESCE(SUM(base_credit - base_debit),0) n')->value('n');
                $this->chk($A, 'USD supplier owes 0.00 in SAR after full payment', 0, round($ap, 2));
                $this->ok($A, 'USD payment books an exchange difference line', $l->where('account_id', \App\Models\Finance\Account\Account::exchangeAccountId())->isNotEmpty());
            } else {
                $this->ok($A, 'USD payment saved', false);
            }
        }

        $this->module($A, 'Approve: Dr 2110 Payable per bill (supplier, at the bill\'s booked rate) / Cr bank (+ bank / other charges, exchange difference); adds to the bill\'s settled amount. Draft, cancel, disapprove: removed, given back.',
            'Draft, approve, back to draft, approve → cancelled via status, approve → disapprove with reason, USD bill paid at another rate.');
    }

    protected function collection(): void
    {
        $A = 'Collection';
        $C = app(CollectionController::class);
        $inv = $this->newInvoice('COL-INV', 1000);
        app(CustomerInvoiceController::class)->updateStatus($inv->id, CustomerInvoiceEnum::APPROVED->value);
        $mk = function (string $key, float $amount) use ($C, $inv) {
            $this->send($C, 'store', [
                'customer' => encodeId($this->ids['customer']), 'collection_date' => '2026-11-16', 'account' => [self::BANK], 'reference_no' => self::AUDIT,
                'currency' => 'SAR', 'currency_rate' => 1, 'customer_invoice_ids' => [$inv->id], 'invoice_amounts' => [$inv->id => $amount], 'credit_to' => [],
                'notes' => self::AUDIT . ' ' . $key,
            ]);

            return Collection::where('notes', self::AUDIT . ' ' . $key)->latest('id')->firstOrFail();
        };
        $c = $mk('COL-A', 500);
        $this->none($A, 'Draft', Collection::class, $c->id);
        $this->status($C, $c->id, CollectionEnum::APPROVED->value);
        $this->posted($A, 'Approved 500', Collection::class, $c->id, [self::BANK => [500, 0], self::AR => [0, 500]], [self::AR => ['customer_id' => $this->ids['customer']]]);
        $this->chk($A, 'Approved collection settles 500 on the invoice', 500, (float) $inv->fresh()->paid_amount);
        $this->status($C, $c->id, CollectionEnum::DRAFT->value);
        $this->none($A, 'Approved → draft', Collection::class, $c->id);
        $this->chk($A, 'Back to draft gives the 500 back', 0, (float) $inv->fresh()->paid_amount);

        $this->status($C, $c->id, CollectionEnum::APPROVED->value);
        $this->status($C, $c->id, CollectionEnum::CANCELLED->value);
        $this->none($A, 'Approved → cancelled (status menu)', Collection::class, $c->id);
        $this->chk($A, 'Approved → cancelled gives the 500 back to the invoice', 0, (float) $inv->fresh()->paid_amount);

        $c2 = $mk('COL-B', 200);
        $this->status($C, $c2->id, CollectionEnum::APPROVED->value);
        $C->setDisapprovalReason($this->request(['reason' => self::AUDIT]), $c2->id);
        $this->none($A, 'Approved → disapproved (with reason)', Collection::class, $c2->id);
        $this->chk($A, 'Disapprove gives the 200 back to the invoice', 0, (float) $inv->fresh()->paid_amount);

        $this->module($A, 'Approve: Dr bank / Cr 1130 Receivable per invoice (customer); adds to the invoice\'s settled amount; customer credit applied moves between invoices without cash. Draft, cancel, disapprove: removed, given back.',
            'Draft, approve, back to draft, approve → cancelled via status, approve → disapprove with reason (customer credit path is covered by finance:chain-test).');
    }

    // ───────────── opening balance ─────────────

    protected function openingBalance(): void
    {
        $A = 'Opening balance';
        $capital = (int) DB::table('accounts')->where('code', self::OWNER_CAPITAL_CODE)->value('id');
        $C = app(OpeningBalanceController::class);
        $cid = $this->party('customer', 'OB Customer', 'SAR');
        $res = $C->store($this->request(['posted_at' => '31-10-2026', 'balances' => [
            ['customer_id' => $cid, 'debit' => 700, 'credit' => 0, 'notes' => self::AUDIT . ' OB customer'],
            ['account_id' => $capital, 'debit' => 0, 'credit' => 700, 'notes' => self::AUDIT . ' OB capital'],
        ]]));
        $msg = $this->redirectMessage($res);
        $lines = DB::table('finance_subs')->where('voucher_type', 'OB')->where('description', 'like', self::AUDIT . ' OB%')->get();
        $this->chk($A, 'Two opening lines saved ' . $msg, 2, $lines->count(), 0);
        $this->chk($A, 'Customer opening balance sits on 1130 Receivable with the customer', 700, round($lines->where('account_id', self::AR)->where('customer_id', $cid)->sum('base_debit'), 2));
        $this->chk($A, 'Opening voucher balances', round($lines->sum('base_debit'), 2), round($lines->sum('base_credit'), 2));
        $f = $lines->first() ? DB::table('finance')->find($lines->first()->finance_id) : null;
        $this->chk($A, 'Opening voucher header total = lines', 700, round((float) ($f->base_total_debit ?? 0), 2));

        $res = $C->store($this->request(['posted_at' => '31-10-2026', 'balances' => [
            ['account_id' => 57, 'debit' => 50, 'credit' => 0, 'notes' => self::AUDIT . ' OB unbalanced'],
        ]]));
        $this->ok($A, 'Unbalanced opening balance is refused', !DB::table('finance_subs')->where('description', self::AUDIT . ' OB unbalanced')->exists(), $this->redirectMessage($res));

        // removing one side keeps the voucher balanced through the automatic balancing line
        if ($f) {
            $svc = app(\App\Services\Finance\OpeningBalanceService::class);
            DB::table('finance_subs')->where('finance_id', $f->id)->where('account_id', $capital)->where('is_auto_generated', 0)->delete();
            $svc->rebalance($f->id);
            $l = DB::table('finance_subs')->where('finance_id', $f->id)->get();
            $this->chk($A, 'After a line is removed the voucher still balances (balancing line on 3100)', round($l->sum('base_debit'), 2), round($l->sum('base_credit'), 2));
        }
        $this->module($A, 'Save: one balanced OB voucher (customer on 1130, supplier on 2110, other accounts direct); edits / deletes are re-balanced on 3100 Owner Capital.',
            'Customer + capital, unbalanced set refused, removing a line re-balances.');
    }

    // ───────────── bank reconciliation ─────────────

    protected function bankReconciliation(): void
    {
        $A = 'Bank reconciliation';
        $svc = app(BankReconciliationService::class);
        $rec = BankReconciliation::create([
            'company_id' => 1, 'account_id' => self::BANK, 'statement_date' => '2026-11-30', 'statement_balance' => 0, 'status' => 'draft',
            'created_by' => 1,
        ]);
        $fid = $svc->addEntry($rec, 'out', 25, '2026-11-17', self::AUDIT . ' bank charge', 54);
        $lines = DB::table('finance_subs')->where('finance_id', $fid)->get();
        $this->chk($A, 'Bank charge entry: Dr 5260 Bank Charges 25', 25, round($lines->where('account_id', 54)->sum('base_debit'), 2));
        $this->chk($A, 'Bank charge entry: Cr bank 25', 25, round($lines->where('account_id', self::BANK)->sum('base_credit'), 2));
        $this->chk($A, 'Bank charge line is ticked on the reconciliation', 1, DB::table('bank_reconciliation_lines')->where('bank_reconciliation_id', $rec->id)->count(), 0);
        $fid2 = $svc->addEntry($rec, 'in', 12.5, '2026-11-17', self::AUDIT . ' interest', 38);
        $l2 = DB::table('finance_subs')->where('finance_id', $fid2)->get();
        $this->chk($A, 'Bank credit entry: Dr bank 12.50 / Cr income 12.50', '12.5|12.5', round($l2->where('account_id', self::BANK)->sum('base_debit'), 2) . '|' . round($l2->where('account_id', 38)->sum('base_credit'), 2));
        $msg = $this->attempt(fn () => $svc->addEntry($rec, 'out', 5, '2026-11-17', self::AUDIT . ' group', self::GROUP_BANK));
        $this->ok($A, 'Other side on a group account (1120 Bank Accounts) is refused', !DB::table('finance_subs')->where('description', self::AUDIT . ' group')->exists(), $msg);
        $this->module($A, 'Bank charge / bank credit from the screen: a BA voucher Dr/Cr bank and the chosen account, ticked at once. Ticking / finishing posts nothing.',
            'Money out to 5260, money in to income, other side on a group account.');
    }

    // ───────────── payroll ─────────────

    protected function payrollEmployee(): int
    {
        return (int) DB::table('salary_structures')->where('company_id', 1)->whereNull('effective_to')->value('employee_id');
    }

    protected function payrollAudit(): void
    {
        $A = 'Payroll';
        $emp = $this->payrollEmployee();
        if (!$emp) {
            $this->ok($A, 'An employee with a salary structure exists', false);
            return;
        }
        $runs = app(PayrollRunController::class);
        PayrollRecord::where('employee_id', $emp)->where('month', 10)->where('year', 2026)->delete();
        $runs->store($this->request(['month' => 10, 'year' => 2026, 'employee_ids' => [$emp]]));
        $pr = PayrollRecord::where('employee_id', $emp)->where('month', 10)->where('year', 2026)->first();
        $this->ok($A, 'Payroll generated for October 2026', (bool) $pr);
        if (!$pr) return;
        $this->none($A, 'Draft payroll', PayrollRecord::class, $pr->id);

        $msg = $this->redirectMessage($runs->pay($this->request(['bank_account_id' => self::GROUP_BANK, 'payment_date' => '2026-11-18']), $pr));
        $this->ok($A, 'Paying from a group account is refused', $pr->fresh()->status !== 'paid', $msg);

        $runs->pay($this->request(['bank_account_id' => self::BANK, 'payment_date' => '2026-11-18']), $pr);
        $pr->refresh();
        $net = round((float) $pr->net_payable, 2);
        $loan = round((float) $pr->loan_deduction, 2);
        $salary = (int) DB::table('accounts')->where('code', '5200')->value('id');
        $loanAcc = (int) DB::table('accounts')->where('code', '1180')->value('id');
        $expect = [$salary => [$net + $loan, 0], self::BANK => [0, $net]];
        if ($loan > 0) $expect[$loanAcc] = [0, $loan];
        $this->posted($A, 'Paid (Dr 5200 Staff Salaries / Cr bank' . ($loan > 0 ? ' / Cr 1180 loan' : '') . ')', PayrollRecord::class, $pr->id, $expect);
        $lines = $this->linesOf(PayrollRecord::class, $pr->id);
        $this->chk($A, 'Salary lines carry the employee (partner)', 0, $lines->filter(fn ($l) => $l->partner_type !== 'employee' || (int) $l->partner_id !== $emp)->count(), 0);
        $this->chk($A, 'Voucher dated on the payment date', '2026-11-18', (string) ($lines->first()->reference_date ?? ''));

        $msg = $this->redirectMessage($runs->pay($this->request(['bank_account_id' => self::BANK, 'payment_date' => '2026-11-18']), $pr));
        $this->chk($A, 'Paying twice is refused: still one voucher', 1, $this->vouchers(PayrollRecord::class, $pr->id)->count(), 0);
        $msg = $this->redirectMessage($runs->destroy($pr->fresh()));
        $this->ok($A, 'A paid payroll record cannot be deleted', PayrollRecord::whereKey($pr->id)->exists(), $msg);

        $runs->disapprove($pr->fresh());
        $this->none($A, 'Paid → disapproved', PayrollRecord::class, $pr->id);
        $this->chk($A, 'Disapproved record is back in draft', 'draft', $pr->fresh()->status);
        $runs->destroy($pr->fresh());
        $this->ok($A, 'Draft payroll record deletes', !PayrollRecord::whereKey($pr->id)->exists());

        $this->module($A, 'Pay: Dr 5200 Staff Salaries (net + loan) / Cr cash-bank (net) / Cr 1180 Employee Loan Receivable (instalment). Disapprove: removed, instalment given back. Draft: nothing.',
            'Generate, draft, pay from group account, pay, pay twice, delete paid, disapprove, delete draft.');
    }

    protected function loans(): void
    {
        $A = 'Employee loan';
        $emp = $this->payrollEmployee();
        if (!$emp) return;
        $loanAcc = (int) DB::table('accounts')->where('code', '1180')->value('id');
        EmployeeLoan::where('employee_id', $emp)->where('status', 'active')->update(['status' => 'closed']);
        app(EmployeeLoanController::class)->store($this->request([
            'employee_id' => $emp, 'loan_amount' => 1200, 'total_installments' => 4, 'installment_amount' => 300,
            'start_date' => '2026-11-01', 'end_date' => '2027-02-28', 'paid_from_account_id' => self::BANK, 'description' => self::AUDIT . ' loan',
        ]));
        $loan = EmployeeLoan::where('employee_id', $emp)->where('description', self::AUDIT . ' loan')->latest('id')->first();
        $this->ok($A, 'Loan saved', (bool) $loan);
        if (!$loan) return;
        $this->posted($A, 'Giving the loan 1,200 (Dr 1180 / Cr bank)', EmployeeLoan::class, $loan->id, [$loanAcc => [1200, 0], self::BANK => [0, 1200]], [$loanAcc => ['partner_id' => $emp]]);

        // salary with the instalment withheld
        $runs = app(PayrollRunController::class);
        PayrollRecord::where('employee_id', $emp)->where('month', 11)->where('year', 2026)->delete();
        $runs->store($this->request(['month' => 11, 'year' => 2026, 'employee_ids' => [$emp]]));
        $pr = PayrollRecord::where('employee_id', $emp)->where('month', 11)->where('year', 2026)->first();
        if ($pr) {
            $this->chk($A, 'November payroll withholds the 300 instalment', 300, (float) $pr->loan_deduction);
            $runs->pay($this->request(['bank_account_id' => self::BANK, 'payment_date' => '2026-11-19']), $pr);
            $l = $this->linesOf(PayrollRecord::class, $pr->id);
            $this->chk($A, 'Salary voucher credits 1180 with the instalment', 300, round($l->where('account_id', $loanAcc)->sum('base_credit'), 2));
            $this->chk($A, 'Loan line carries the employee', $emp, (int) ($l->where('account_id', $loanAcc)->first()->partner_id ?? 0), 0);
            $this->chk($A, 'Loan remaining 900 after the salary instalment', 900, (float) $loan->fresh()->remaining_amount);
            $runs->disapprove($pr->fresh());
            $this->chk($A, 'Disapproving the salary gives the instalment back (remaining 1,200)', 1200, (float) $loan->fresh()->remaining_amount);
            $runs->destroy($pr->fresh());
        }

        // a repayment in cash
        $res = app(EmployeeLoanController::class)->payInstallment($this->request(['amount' => 250, 'paid_date' => '2026-11-19', 'bank_account_id' => self::BANK, 'remarks' => self::AUDIT]), $loan);
        $inst = LoanInstallment::where('loan_id', $loan->id)->latest('id')->first();
        $this->ok($A, 'Cash repayment recorded ' . $this->redirectMessage($res), (bool) $inst);
        if ($inst) {
            $this->posted($A, 'Cash repayment 250 (Dr bank / Cr 1180)', LoanInstallment::class, $inst->id, [self::BANK => [250, 0], $loanAcc => [0, 250]], [$loanAcc => ['partner_id' => $emp]]);
        }
        $msg = $this->attempt(fn () => app(EmployeeLoanController::class)->payInstallment($this->request(['amount' => 5000, 'paid_date' => '2026-11-19', 'bank_account_id' => self::BANK]), $loan->fresh()));
        $this->chk($A, 'Repayment above the balance is refused ' . $msg, 1, LoanInstallment::where('loan_id', $loan->id)->count(), 0);
        $msg = $this->attempt(fn () => app(EmployeeLoanController::class)->payInstallment($this->request(['amount' => 10, 'paid_date' => '2026-11-19', 'bank_account_id' => self::GROUP_BANK]), $loan->fresh()));
        $this->chk($A, 'Repayment into a group account is refused ' . $msg, 1, LoanInstallment::where('loan_id', $loan->id)->count(), 0);

        $this->module($A, 'Loan given: nothing. Salary instalment: Cr 1180 on the salary voucher. Cash repayment: Dr cash-bank / Cr 1180 (voucher LV).',
            'Create loan, salary with instalment (pay / disapprove), cash repayment, above balance, group account.');
    }

    // ───────────── asset ─────────────

    protected function asset(): void
    {
        $A = 'Asset';
        $before = DB::table('finance_subs')->count();
        $C = app(AssetController::class);
        $res = $this->send($C, 'store', [
            'name_en' => self::AUDIT . ' Laptop', 'name_ar' => 'حاسوب', 'acquisition_date' => '2026-11-01', 'cost' => 3600, 'residual_value' => 0,
            'useful_life_months' => 36, 'depreciation_method' => 'straight_line', 'depreciation_start_date' => '2026-11-01',
        ]);
        $asset = \App\Models\Finance\Asset\Asset::where('name_en', self::AUDIT . ' Laptop')->latest('id')->first();
        $this->ok($A, 'Asset saved', (bool) $asset, json_encode($res));
        if (!$asset) return;
        $C->generateSchedule($this->request(), $asset->id);
        $deps = DB::table('asset_depreciations')->where('asset_id', $asset->id)->get();
        $this->chk($A, 'Schedule: 36 months', 36, $deps->count(), 0);
        $this->chk($A, 'Schedule: 100.00 per month', 100, (float) ($deps->first()->amount ?? 0));
        $this->chk($A, 'Schedule adds up to the cost', 3600, round($deps->sum('amount'), 2));
        $this->chk($A, 'Generating the schedule posts nothing to the ledger', $before, DB::table('finance_subs')->count(), 0);
        $this->module($A, 'Nothing: the schedule rows (asset_depreciations.posted) are never posted; buying the asset is booked through a supplier invoice / expense.',
            'Create asset 3,600 / 36 months, generate schedule.');
    }

    // ───────────── period locks ─────────────

    protected function periodLocks(): void
    {
        $A = 'Period closing';
        $PC = app(PeriodClosingController::class);

        // documents approved while the period is open, dated 13-11
        $jvC = app(JournalVoucherController::class);
        $this->send($jvC, 'store', $this->jvPayload(self::AUDIT . ' JV-LOCK', ['voucher_date' => '2026-11-13']));
        $jv = JournalVoucher::where('notes', self::AUDIT . ' JV-LOCK')->latest('id')->firstOrFail();
        $this->status($jvC, $jv->id, 2);
        $this->send($jvC, 'store', $this->jvPayload(self::AUDIT . ' JV-LOCK2', ['voucher_date' => '2026-11-13']));
        $jv2 = JournalVoucher::where('notes', self::AUDIT . ' JV-LOCK2')->latest('id')->firstOrFail();
        $this->status($jvC, $jv2->id, 2);

        $bill = $this->newBill('LOCK-BILL', 500, null, ['invoice_date' => '2026-11-13']);
        app(SupplierInvoiceController::class)->updateStatus($bill->id, SupplierInvoiceEnum::APPROVED->value);
        $this->send(app(DebitNoteController::class), 'store', [
            'invoice_id' => $bill->id, 'supplier' => encodeId($this->ids['supplier']), 'debit_note_date' => '2026-11-13', 'job_id' => $this->ids['job'],
            'reason' => self::AUDIT, 'terms' => self::AUDIT . ' DN-LOCK', 'description_id' => ['2'], 'comment' => ['x'],
            'unit_id' => ['1'], 'quantity' => [1], 'unit_price' => ['50'], 'tax' => ['STANDARD'], 'account' => [self::COST],
        ]);
        $dn = DebitNote::where('terms', self::AUDIT . ' DN-LOCK')->latest('id')->firstOrFail();
        $this->status(app(DebitNoteController::class), $dn->id, DebitNoteEnum::APPROVED->value);

        $this->send(app(PaymentController::class), 'store', [
            'supplier' => encodeId($this->ids['supplier']), 'payment_date' => '2026-11-13', 'account' => [self::BANK], 'reference_no' => self::AUDIT,
            'currency' => 'SAR', 'currency_rate' => 1, 'supplier_invoice_ids' => [$bill->id], 'invoice_amounts' => [$bill->id => 100], 'notes' => self::AUDIT . ' PAY-LOCK',
        ]);
        $pay = Payment::where('notes', self::AUDIT . ' PAY-LOCK')->latest('id')->firstOrFail();
        $this->status(app(PaymentController::class), $pay->id, PaymentEnum::APPROVED->value);

        $inv = $this->newInvoice('LOCK-INV', 300, ['invoice_date' => '2026-11-13']);
        app(CustomerInvoiceController::class)->updateStatus($inv->id, CustomerInvoiceEnum::APPROVED->value);
        $this->send(app(CollectionController::class), 'store', [
            'customer' => encodeId($this->ids['customer']), 'collection_date' => '2026-11-13', 'account' => [self::BANK], 'reference_no' => self::AUDIT,
            'currency' => 'SAR', 'currency_rate' => 1, 'customer_invoice_ids' => [$inv->id], 'invoice_amounts' => [$inv->id => 100], 'credit_to' => [], 'notes' => self::AUDIT . ' COL-LOCK',
        ]);
        $col = Collection::where('notes', self::AUDIT . ' COL-LOCK')->latest('id')->firstOrFail();
        $this->status(app(CollectionController::class), $col->id, CollectionEnum::APPROVED->value);

        $emp = $this->payrollEmployee();
        $loan = EmployeeLoan::where('employee_id', $emp)->where('description', self::AUDIT . ' loan')->latest('id')->first();

        $res = $this->send($PC, 'store', ['year' => 2026, 'closing_date' => '13-11-2026', 'notes' => self::AUDIT]);
        $pc = PeriodClosing::find($res['module_id'] ?? 0);
        if (!$pc) {
            $this->ok($A, 'Period saved', false, json_encode($res));
            return;
        }
        $PC->close($pc->id);
        try {
            $this->ok($A, 'Closed through 13-11-2026', PeriodClosing::isLocked('2026-11-13') && !PeriodClosing::isLocked('2026-11-14'));
            $count = fn (string $t, int $id) => $this->vouchers($t, $id)->count();

            $m = $this->attempt(fn () => $jvC->updateStatus($jv->id, 1));
            $this->chk($A, 'Approved JV dated 13-11 → draft is refused ' . $m, 1, $count(JournalVoucher::class, $jv->id), 0);
            $m = $this->attempt(fn () => $jvC->setDisapprovalReason($this->request(['reason' => 'x']), $jv2->id));
            $this->chk($A, 'Approved JV dated 13-11 → disapprove is refused ' . $m, 1, $count(JournalVoucher::class, $jv2->id), 0);
            $m = $this->attempt(fn () => app(DebitNoteController::class)->updateStatus($dn->id, DebitNoteEnum::CANCELLED->value));
            $this->chk($A, 'Approved debit note dated 13-11 → cancel is refused ' . $m, 1, $count(DebitNote::class, $dn->id), 0);
            $m = $this->attempt(fn () => app(PaymentController::class)->setDisapprovalReason($this->request(['reason' => 'x']), $pay->id));
            $this->chk($A, 'Approved payment dated 13-11 → disapprove is refused ' . $m, 1, $count(Payment::class, $pay->id), 0);
            $m = $this->attempt(fn () => app(CollectionController::class)->setDisapprovalReason($this->request(['reason' => 'x']), $col->id));
            $this->chk($A, 'Approved collection dated 13-11 → disapprove is refused ' . $m, 1, $count(Collection::class, $col->id), 0);
            $m = $this->attempt(fn () => app(CustomerInvoiceController::class)->updateStatus($inv->id, CustomerInvoiceEnum::CANCELLED->value));
            $this->chk($A, 'Approved invoice dated 13-11 → cancel is refused ' . $m, 1, $count(CustomerInvoice::class, $inv->id), 0);
            if ($loan) {
                $n = LoanInstallment::where('loan_id', $loan->id)->count();
                $m = $this->attempt(fn () => app(EmployeeLoanController::class)->payInstallment($this->request(['amount' => 10, 'paid_date' => '2026-11-12', 'bank_account_id' => self::BANK]), $loan->fresh()));
                $this->chk($A, 'Loan repayment dated 12-11 is refused ' . $m, $n, LoanInstallment::where('loan_id', $loan->id)->count(), 0);
            }
            $rec = BankReconciliation::where('account_id', self::BANK)->where('status', 'draft')->latest('id')->first();
            if ($rec) {
                $m = $this->attempt(fn () => app(BankReconciliationService::class)->addEntry($rec, 'out', 3, '2026-11-12', self::AUDIT . ' locked', 54));
                $this->ok($A, 'Bank charge dated 12-11 is refused', !DB::table('finance_subs')->where('description', self::AUDIT . ' locked')->exists(), $m);
            }
            $m = $this->attempt(fn () => $this->send(app(ExpenseController::class), 'store', $this->expensePayload(self::AUDIT . '-EXP-LOCK', 10, ['posted_at' => '2026-11-12'])));
            $this->ok($A, 'Expense dated 12-11 is refused', !Expense::where('reference_number', self::AUDIT . '-EXP-LOCK')->exists(), $m);
        } finally {
            $PC->reopen($pc->id);
        }
        $this->module($A, 'Nothing itself: a closed period refuses every posting, un-posting and status change dated on or before the closing date.',
            'Closed through 13-11; tried JV → draft, JV disapprove, debit note cancel, payment / collection disapprove, invoice cancel, loan repayment, bank charge, expense.');
    }

    // ───────────── whole ledger ─────────────

    protected function wholeLedger(): void
    {
        $A = 'Whole ledger';
        $bad = DB::table('finance_subs')->groupBy('finance_id')->havingRaw('ABS(SUM(base_debit) - SUM(base_credit)) > 0.005')->pluck('finance_id');
        $this->chk($A, 'Every voucher (incl. those made by this audit) balances', 0, $bad->count(), 0);
        $hdr = DB::table('finance as f')->join(DB::raw('(SELECT finance_id, SUM(base_debit) d FROM finance_subs GROUP BY finance_id) s'), 's.finance_id', '=', 'f.id')
            ->whereRaw('ABS(f.base_total_debit - s.d) > 0.005')->count();
        $this->chk($A, 'Every header total equals its lines', 0, $hdr, 0);
        $orphan = DB::table('finance')->leftJoin('finance_subs as s', 's.finance_id', '=', 'finance.id')->whereNull('s.id')->count();
        $this->chk($A, 'No voucher header without lines', 0, $orphan, 0);
        $group = DB::table('finance_subs as s')->join('accounts as a', 'a.id', '=', 's.account_id')->where('a.is_last', 0)->count();
        $this->line("   info: ledger lines on group (non-posting) accounts: $group");
    }

    // ───────────── report ─────────────

    protected function addModuleSheet(string $file): void
    {
        $book = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
        $sh = $book->createSheet(1)->setTitle('Modules');
        $sh->fromArray(['Module', 'What posts to the ledger', 'What was tested', 'Result', 'Fixed (file: change)'], null, 'A1');
        $fixed = $this->fixes();
        $r = 2;
        foreach ($this->modules as $name => $row) {
            $list = collect($this->checks)->where(0, $name);
            $fail = $list->where(4, false)->count();
            $row[3] = $list->count() . ' checks, ' . ($fail ? $fail . ' FAILED' : 'all pass');
            $row[4] = $fixed[$name] ?? '-';
            $sh->fromArray($row, null, 'A' . $r);
            $sh->getStyle('D' . $r)->getFont()->setBold(true)->getColor()->setRGB($fail ? 'B91C1C' : '15803D');
            $r++;
        }
        $sh->getStyle('A1:E1')->getFont()->setBold(true);
        foreach (['A' => 20, 'B' => 70, 'C' => 60, 'D' => 18, 'E' => 80] as $c => $w) $sh->getColumnDimension($c)->setWidth($w);
        $sh->getStyle('A2:E' . $r)->getAlignment()->setWrapText(true)->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
        $sh->freezePane('A2');
        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($book))->save($file);
    }

    /** What was changed per module in this audit (shown in the Modules sheet). */
    protected function fixes(): array
    {
        return [
            'Customer invoice' => "CustomerInvoiceController::store: only a draft can be saved (editing an approved invoice turned it back into a draft and left its ledger entry).\n"
                . "CustomerInvoiceController::updateStatus + settledBy(): an approved invoice cannot leave Approved while approved collections, credit notes or applied customer credit settle it.\n"
                . "Controller::assertLineAccounts (called in store): a line cannot use a cash / bank, 1130, 2110, VAT or group account.",
            'Supplier invoice' => "SupplierFinanceOperation: the old entry is found by the invoice id (deleteSupplierInvoiceFinance), not by the supplier's bill number + type (that number is only unique per supplier, so approving / cancelling one supplier's bill deleted another supplier's entry); a posting error is no longer swallowed.\n"
                . "SupplierInvoiceController::store: only a draft can be saved. updateStatus: cannot leave Approved while approved payments / debit notes settle it. Line accounts checked (assertLineAccounts).",
            'Credit note' => 'CreditNoteController::store: only a draft can be saved; line accounts checked.',
            'Debit note' => 'DebitNoteController::updateStatus: taking an approved note back is refused in a closed period; store: line accounts checked.',
            'Expense' => "ExpenseController::store: Paid From must be a cash / bank posting account (group 1120 was accepted); editing an approved expense (it goes back to Pending) now removes its entry (it stayed in the books) and checks the old date's period; line accounts checked.",
            'Journal voucher' => "JournalVoucherController::setDisapprovalReason: disapproving an approved voucher removes its entry (it stayed in the books) inside a transaction; leaving Approved (draft or disapprove) is refused in a closed period.",
            'Payment' => "PaymentController::updateStatus: Approved → Cancelled now removes the entry and gives the amount back to the bills (both stayed); the catch → rollBack / finally → commit pattern removed (it committed an outer transaction). setDisapprovalReason: refused in a closed period, runs in a transaction.",
            'Collection' => 'CollectionController: same three fixes as Payment (cancel from Approved reverses entry, paid amounts and applied credit; no commit after rollBack; disapprove respects closed periods).',
            'Bank reconciliation' => 'BankReconciliationService::addEntry: the other side must be a posting account (a group account was accepted).',
            'Payroll' => 'PayrollLedger::voucher: salary and loan lines carry the employee (partner_type employee, partner_id) and entry_date.',
            'Employee loan' => "EmployeeLoanController::payInstallment: refused in a closed period. PayrollLedger: repayment lines carry the employee.",
            'Asset' => 'AssetController::store: a new asset gets company_id / created_by (it was saved without its company, so the company-scoped list never showed it).',
            'Period closing' => 'See Journal voucher, Debit note, Payment, Collection, Employee loan (each gap closed).',
        ];
    }

    protected function findings(): array
    {
        return [
            [1, 'VAT return put zero rated / exempt sales under "out of scope"', 'Zero rated / exempt / out of scope lines must carry their exemption reason ("ZERO:VATEX-SA-32"), but VatReturn compared the whole code with "ZERO" / "EXEMPT", so every such line went to out of scope (live data: 28,166.00 of zero rated sales missing from box 3). VAT amounts were not affected.', 'Fixed: VatReturn::compute / details use vatBase() (category before the ":")'],
            [2, 'Approved documents could be edited from the server', 'Customer / supplier invoice and credit note store() accepted an approved document: it became a draft with its old ledger entry still in the books (draft with an entry, later re-approval posted twice). The screens only offer Edit on drafts.', 'Fixed: refused unless draft (422)'],
            [3, 'Supplier bill number used to find the ledger entry', 'Approving or cancelling a supplier invoice deleted the first entry with the same supplier bill number of ANY supplier / company.', 'Fixed: by invoice id'],
            [4, 'Settled invoices could be cancelled / moved to draft', 'An approved customer invoice with a collection or credit note (or a bill with a payment / debit note) could be cancelled: its receivable / payable vanished while the cash stayed.', 'Fixed: refused with the settling document numbers'],
            [5, 'Cancel / disapprove left entries in the books', 'Payment and collection: Approved → Cancelled through the status endpoint kept the entry and the paid amounts. JV disapprove endpoint kept the entry. Editing an approved expense kept the entry on a Pending expense.', 'Fixed'],
            [6, 'Closed period not checked when taking documents back', 'JV to draft / disapprove, debit note cancel, payment / collection disapprove and loan repayments were accepted inside a closed period.', 'Fixed'],
            [7, 'Group / control accounts accepted', 'Expense Paid From, bank reconciliation counter account and every invoice / note / expense line accepted a group account; lines also accepted the bank, receivable, payable and VAT accounts. Live data: 3 approved invoices (IN/26/0006, IN/26/0010, IN/26/0014) credit 36,034.00 of income to 1121 Visa/Mastercard Undeposited, 2 credit notes (CR-26-0003, CR-26-0006) debit 5,200.00 to it, SI26-0003 debits 600.00 to it; description "OCEAN FREIGHT INCLUSIVE OF EXW" has 1121 as its sale account. 6 older lines sit on group 1120.', 'Fixed for new saves (assertLineAccounts, posting-account checks). Existing documents need a decision: move to draft, change the line account, approve again (not done: they are live data)'],
            [8, 'CR-26-0003 settles another customer\'s invoice', 'Credit note of customer #73 against IN/26/0002 of customer #54 (finance:reconcile 8a / 8b). Validation already stops new ones.', 'Open: cancel and re-issue for the right customer (user decision)'],
            [9, 'Employee loan payout posts nothing', 'Giving a loan books no Dr 1180 Employee Loan Receivable / Cr bank (fatoora does the same); salary recoveries and repayments credit 1180, so 1180 runs a credit balance and the cash paid to the employee is not in the books unless a JV is made.', 'Open: needs a Paid From account on the loan form (UI + decision). Workaround: JV Dr 1180 / Cr bank'],
            [10, 'Asset depreciation is never posted', 'The schedule (asset_depreciations, posted flag) is created but no voucher is ever made (Dr depreciation expense / Cr accumulated depreciation); there is no accumulated depreciation account on the asset; initial accumulated / months already depreciated are ignored by the schedule. 0 assets exist.', 'Open: new feature (posting run + accounts), needs a decision'],
            [11, 'Payroll instalment above the loan balance', 'When the salary instalment is larger than what is left on the loan, the loan takes only the remaining amount but the salary voucher credits 1180 with the full instalment.', 'Open (edge case): post the amount actually applied'],
            [12, 'Live data that fails other checks', 'Invoice IN/26/0011 (31-10-2026) carries 455,435,345.00 income / 68,315,301.75 VAT (typing error?). Autocheck tax_summary (report vs ledger output VAT differ by 225.00) and report_parity (P&L 42 differences) fail on the live data; finance:report-parity itself crashes (Undefined array key "summary"). Not caused by this audit (VAT figures identical before / after the fix, P&L code untouched).', 'Open: check IN/26/0011 with the user'],
            [13, 'Test commands were stale', 'finance:saudi-test used zero / exempt codes without a reason, a USD invoice for a SAR customer and the group account 1120 as bank; Autocheck also used 1120 as bank.', 'Fixed: reason codes, temporary USD customer currency (rolled back), bank 1121 (id 58)'],
        ];
    }
}
