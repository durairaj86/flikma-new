<?php

namespace App\Console\Commands;

use App\Enums\CollectionEnum;
use App\Enums\CustomerInvoiceEnum;
use App\Enums\ExpenseEnum;
use App\Enums\PaymentEnum;
use App\Enums\ProformaInvoiceEnum;
use App\Enums\SupplierInvoiceEnum;
use App\Http\Controllers\Customer\CustomerController;
use App\Http\Controllers\Finance\Expense\ExpenseController;
use App\Http\Controllers\Finance\Invoice\CustomerInvoiceController;
use App\Http\Controllers\Finance\Invoice\ProformaInvoiceController;
use App\Http\Controllers\Finance\Invoice\SupplierInvoiceController;
use App\Http\Controllers\Finance\OpeningBalance\OpeningBalanceController;
use App\Http\Controllers\Master\PeriodClosingController;
use App\Http\Controllers\Supplier\SupplierController;
use App\Http\Controllers\Transaction\CollectionController;
use App\Http\Controllers\Transaction\PaymentController;
use App\Models\Customer\Customer;
use App\Models\Finance\Collection\Collection;
use App\Models\Finance\CustomerInvoice\CustomerInvoice;
use App\Models\Finance\Expense\Expense;
use App\Models\Finance\Payment\Payment;
use App\Models\Finance\ProformaInvoice\ProformaInvoice;
use App\Models\Finance\SupplierInvoice\SupplierInvoice;
use App\Models\Master\PeriodClosing;
use App\Models\Supplier\Supplier;
use App\Services\Finance\LedgerReports;
use App\Services\Finance\VatReturn;
use Illuminate\Support\Facades\DB;

/**
 * Second test pass on top of the chain test's documents: multi-currency (USD customer, EUR supplier, settled at a
 * different rate), period closing (save / approve / cancel / expense in a closed period, reopen), opening balances,
 * proforma invoices, every printed document (all invoice themes) and every report's Excel export.
 * One transaction, rolled back. php artisan finance:extended-test [--out=path.xlsx]
 */
class FinanceExtendedTest extends FinanceChainTest
{
    protected $signature = 'finance:extended-test {--out= : report file}';
    protected $description = 'Multi-currency, period closing, opening balances, proforma, prints and exports, on top of the chain test';

    protected string $reportPrefix = 'Finance-Extended-Test-Report';
    protected string $reportTitle = 'Finance extended test: multi-currency, period closing, opening balances, proforma invoices, printed documents, Excel exports';

    private const OWNER_CAPITAL = 28;

    public function handle(): int
    {
        auth()->loginUsingId(1);
        session(['company_id' => 1]);

        DB::beginTransaction();
        try {
            // documents to print / export, the same chain as finance:chain-test
            $this->step('Set-up (chain documents)', fn () => $this->customerChain());
            $this->step('Set-up (chain documents)', fn () => $this->supplierChain());
            $this->step('Set-up (chain documents)', fn () => $this->expense());
            $this->checks = array_values(array_filter($this->checks, fn ($c) => !$c[4] || str_starts_with($c[0], 'Set-up')));

            $this->step('Opening balance', fn () => $this->openingBalance());
            $this->step('Multi-currency', fn () => $this->multiCurrency());
            $this->step('Proforma', fn () => $this->proforma());
            $this->step('Prints', fn () => $this->prints());
            $this->step('Exports', fn () => $this->exports());
            $this->step('Period closing', fn () => $this->periodClosing());
        } finally {
            DB::rollBack();
            \Illuminate\Support\Facades\Cache::forget('period-closing-lock:1');
        }

        $file = $this->report();
        $fail = collect($this->checks)->where(4, false)->count();
        $this->info(count($this->checks) . ' checks, ' . $fail . ' failed. Report: ' . $file . ' (everything rolled back)');
        foreach (collect($this->checks)->where(4, false) as [$area, $name, $exp, $act]) {
            $this->line("  FAIL [$area] $name — expected $exp, got " . mb_substr((string) $act, 0, 300));
        }

        return $fail ? 1 : 0;
    }

    /** Redirect-style actions report problems through the session's error bag. */
    private function redirectErrors(): string
    {
        $e = session('errors');
        session()->forget(['errors', 'success', '_old_input']);

        return $e ? implode(' ', $e->all()) : '';
    }

    private function arBalance(int $customerId): float
    {
        return (float) DB::table('finance_subs')->where('customer_id', $customerId)->where('account_id', self::AR)->selectRaw('COALESCE(SUM(base_debit - base_credit),0) n')->value('n');
    }

    private function apBalance(int $supplierId): float
    {
        return (float) DB::table('finance_subs')->where('supplier_id', $supplierId)->where('account_id', self::AP)->selectRaw('COALESCE(SUM(base_credit - base_debit),0) n')->value('n');
    }

    // ───────────── opening balances ─────────────

    private function openingBalance(): void
    {
        $cid = $this->ids['customer'];
        $sid = $this->ids['supplier'];
        $tbBefore = LedgerReports::trialBalance('2026-01-01', self::TO);
        app(OpeningBalanceController::class)->store($this->request(['posted_at' => '31-10-2026', 'balances' => [
            ['customer_id' => $cid, 'debit' => 500, 'credit' => 0, 'notes' => self::TAG . ' OB customer'],
            ['supplier_id' => $sid, 'debit' => 0, 'credit' => 300, 'notes' => self::TAG . ' OB supplier'],
            ['account_id' => self::OWNER_CAPITAL, 'debit' => 0, 'credit' => 200, 'notes' => self::TAG . ' OB capital'],
        ]]));
        $err = $this->redirectErrors();
        $n = DB::table('finance_subs')->where('voucher_type', 'OB')->where('description', 'like', self::TAG . ' OB%')->count();
        $this->chk('Opening balance', 'Three opening balance lines saved' . ($err ? " ($err)" : ''), 3, $n, 0);

        // an unbalanced set is refused
        app(OpeningBalanceController::class)->store($this->request(['posted_at' => '31-10-2026', 'balances' => [
            ['account_id' => 57, 'debit' => 100, 'credit' => 0, 'notes' => self::TAG . ' OB unbalanced'],
        ]]));
        $msg = $this->redirectErrors();
        $this->ok('Opening balance', 'An unbalanced opening balance is refused', !DB::table('finance_subs')->where('description', self::TAG . ' OB unbalanced')->exists(), $msg);
        // a second balance for the same customer is refused
        app(OpeningBalanceController::class)->store($this->request(['posted_at' => '31-10-2026', 'balances' => [
            ['customer_id' => $cid, 'debit' => 10, 'credit' => 0, 'notes' => self::TAG . ' OB twice'],
            ['account_id' => self::OWNER_CAPITAL, 'debit' => 0, 'credit' => 10, 'notes' => self::TAG . ' OB twice'],
        ]]));
        $this->redirectErrors();
        $this->ok('Opening balance', 'A second opening balance for the same customer is refused', !DB::table('finance_subs')->where('description', self::TAG . ' OB twice')->exists());

        $tb = LedgerReports::trialBalance('2026-01-01', self::TO);
        $this->chk('Opening balance', 'Trial balance still balances', $tb['total_debit'], $tb['total_credit']);
        $this->chk('Opening balance', 'Trial balance grew by the opening balance (500)', 500.00, $tb['total_debit'] - $tbBefore['total_debit']);
        $this->chk('Opening balance', 'Customer ledger balance = 1,070 + 500 opening', 1570.00, $this->arBalance($cid));
        $this->chk('Opening balance', 'Supplier ledger balance = 570 + 300 opening', 870.00, $this->apBalance($sid));

        $cs = $this->livewire(\App\Livewire\Report\Finance\CustomerStatement::class, ['startDate' => self::FROM, 'endDate' => self::TO, 'customerId' => encodeId($cid)]);
        $this->has('Opening balance', 'Customer statement (November) opens with 500.00', $cs, '500.00');
        $this->has('Opening balance', 'Customer statement closes at 1,570.00', $cs, '1,570.00');
        $ss = $this->livewire(\App\Livewire\Report\Finance\SupplierStatement::class, ['startDate' => self::FROM, 'endDate' => self::TO, 'supplierId' => encodeId($sid)]);
        $this->has('Opening balance', 'Supplier statement closes at 870.00', $ss, '870.00');

        // aging must agree with the statement: the opening balance is money owed too
        $ag = $this->aging(\App\Livewire\Report\Finance\CustomerAging::class, ['asOfDate' => self::AS_OF, 'customerId' => encodeId($cid)]);
        $this->chk('Opening balance', 'Customer aging total = statement balance (1,570.00, includes the 500 opening balance)', 1570.00, $ag['summary']['grand_total'] ?? 0);
        $ag = $this->aging(\App\Livewire\Report\Finance\SupplierAging::class, ['asOfDate' => self::AS_OF, 'supplierId' => encodeId($sid)]);
        $this->chk('Opening balance', 'Supplier aging total = statement balance (870.00, includes the 300 opening balance)', 870.00, $ag['summary']['grand_total'] ?? 0);

        $bs = LedgerReports::balanceSheet(self::AS_OF);
        $this->chk('Opening balance', 'Balance sheet still balances', $bs['total_assets'], $bs['total_liabilities_equity']);
    }

    // ───────────── multi-currency ─────────────

    private function multiCurrency(): void
    {
        // USD customer: invoice 1,000 + 15% at 3.75 (4,312.50 SAR), collected in full at 3.80 (4,370.00 SAR) → 57.50 exchange gain
        $this->send(app(CustomerController::class), 'store', [
            'name_en' => self::TAG . ' USD Customer', 'name_ar' => 'عميل دولار', 'currency' => 'USD', 'business_type' => 'unregistered',
            'country' => 'United States', 'email' => 'chaintest.usd@example.test', 'phone' => '+966500000789',
        ]);
        $usd = Customer::where('name_en', self::TAG . ' USD Customer')->firstOrFail();
        $this->ids['usd_customer'] = $usd->id;
        $this->send(app(CustomerInvoiceController::class), 'store', [
            'job_id' => $this->ids['job'], 'customer' => encodeId($usd->id), 'invoice_date' => '2026-11-14', 'due_date' => '2026-12-14',
            'currency' => 'USD', 'currency_rate' => 3.75, 'terms' => self::TAG . ' USD INV', 'description_id' => ['2'], 'comment' => ['USD freight'],
            'unit_id' => ['1'], 'quantity' => [1], 'unit_price' => ['1000'], 'tax' => ['STANDARD'], 'account' => [self::REVENUE],
        ]);
        $inv = CustomerInvoice::where('terms', self::TAG . ' USD INV')->firstOrFail();
        $this->status(app(CustomerInvoiceController::class), $inv->id, CustomerInvoiceEnum::APPROVED->value);
        $inv->refresh();
        $this->ids['usd_inv'] = $inv->id;
        $this->doc('USD invoice 1,000 + VAT @ 3.75', $inv);
        $this->chk('Multi-currency', 'USD invoice: grand total 1,150.00 USD', 1150.00, (float) $inv->grand_total);
        $this->chk('Multi-currency', 'USD invoice: base grand total 4,312.50 SAR', 4312.50, (float) $inv->base_grand_total);
        $this->chk('Multi-currency', 'Receivable after invoice: 4,312.50 SAR', 4312.50, $this->arBalance($usd->id));

        $this->send(app(CollectionController::class), 'store', [
            'customer' => encodeId($usd->id), 'collection_date' => '2026-11-18', 'account' => [self::BANK], 'reference_no' => self::TAG,
            'currency' => 'USD', 'currency_rate' => 3.80, 'customer_invoice_ids' => [$inv->id], 'invoice_amounts' => [$inv->id => 1150], 'notes' => self::TAG . ' USD COL',
        ]);
        $col = Collection::where('notes', self::TAG . ' USD COL')->firstOrFail();
        $this->status(app(CollectionController::class), $col->id, CollectionEnum::APPROVED->value);
        $col->refresh();
        $this->ids['usd_col'] = $col->id;
        $this->docs[] = ['USD collection 1,150 @ 3.80', $col->row_no, 1150, 0, (float) $col->grand_total, (string) $col->status];

        $this->chk('Multi-currency', 'Invoice fully paid in USD (paid 1,150.00)', 1150.00, (float) $inv->fresh()->paid_amount);
        $this->chk('Multi-currency', 'Customer receivable cleared in SAR (0.00), not left at −57.50', 0.00, $this->arBalance($usd->id));
        $lines = DB::table('finance_subs as fs')->join('finance as f', 'f.id', '=', 'fs.finance_id')->where('f.linked_type', Collection::class)->where('f.linked_id', $col->id)->get(['fs.account_id', 'fs.base_debit', 'fs.base_credit']);
        $this->chk('Multi-currency', 'Bank receives 4,370.00 SAR (1,150 @ 3.80)', 4370.00, $lines->where('account_id', self::BANK)->sum('base_debit'));
        $this->chk('Multi-currency', 'Receivable credited at the invoice rate 4,312.50 SAR', 4312.50, $lines->where('account_id', self::AR)->sum('base_credit'));
        $fx = $lines->whereNotIn('account_id', [self::BANK, self::AR]);
        $this->chk('Multi-currency', 'Exchange gain 57.50 SAR posted', 57.50, $fx->sum('base_credit') - $fx->sum('base_debit'));
        $this->chk('Multi-currency', 'Collection voucher balances', $lines->sum('base_debit'), $lines->sum('base_credit'));
        $ag = $this->aging(\App\Livewire\Report\Finance\CustomerAging::class, ['asOfDate' => self::AS_OF, 'customerId' => encodeId($usd->id)]);
        $this->chk('Multi-currency', 'USD customer aging: nothing open', 0.00, $ag['summary']['grand_total'] ?? 0);

        // EUR supplier: bill 1,000 + 15% at 4.00 (4,600 SAR), paid in full at 4.10 (4,715 SAR) → 115.00 exchange loss
        $this->send(app(SupplierController::class), 'store', [
            'name_en' => self::TAG . ' EUR Supplier', 'name_ar' => 'مورد يورو', 'currency' => 'EUR', 'business_type' => 'unregistered',
            'country' => 'Germany', 'email' => 'chaintest.eur@example.test', 'phone' => '+966500000987',
        ]);
        $eur = Supplier::where('name_en', self::TAG . ' EUR Supplier')->firstOrFail();
        $this->ids['eur_supplier'] = $eur->id;
        $this->send(app(SupplierInvoiceController::class), 'store', [
            'job_id' => $this->ids['job'], 'supplier' => encodeId($eur->id), 'invoice_date' => '2026-11-14', 'invoice_number' => self::TAG . '-EUR',
            'due_date' => '2026-12-14', 'currency' => 'EUR', 'currency_rate' => 4.00, 'terms' => self::TAG . ' EUR SI', 'description_id' => ['2'],
            'comment' => ['EUR'], 'unit_id' => ['1'], 'quantity' => [1], 'unit_price' => ['1000'], 'tax' => ['STANDARD'], 'account' => [self::COST],
        ]);
        $si = SupplierInvoice::where('invoice_number', self::TAG . '-EUR')->firstOrFail();
        $this->status(app(SupplierInvoiceController::class), $si->id, SupplierInvoiceEnum::APPROVED->value);
        $si->refresh();
        $this->doc('EUR supplier invoice 1,000 + VAT @ 4.00', $si);
        $this->chk('Multi-currency', 'EUR bill: base grand total 4,600.00 SAR', 4600.00, (float) $si->base_grand_total);
        $this->send(app(PaymentController::class), 'store', [
            'supplier' => encodeId($eur->id), 'payment_date' => '2026-11-18', 'account' => [self::BANK], 'reference_no' => self::TAG,
            'currency' => 'EUR', 'currency_rate' => 4.10, 'supplier_invoice_ids' => [$si->id], 'invoice_amounts' => [$si->id => 1150], 'notes' => self::TAG . ' EUR PAY',
        ]);
        $pay = Payment::where('notes', self::TAG . ' EUR PAY')->firstOrFail();
        $this->status(app(PaymentController::class), $pay->id, PaymentEnum::APPROVED->value);
        $this->docs[] = ['EUR payment 1,150 @ 4.10', $pay->row_no, 1150, 0, (float) $pay->fresh()->grand_total, (string) $pay->fresh()->status];
        $this->chk('Multi-currency', 'Supplier payable cleared in SAR (0.00)', 0.00, $this->apBalance($eur->id));
        $lines = DB::table('finance_subs as fs')->join('finance as f', 'f.id', '=', 'fs.finance_id')->where('f.linked_type', Payment::class)->where('f.linked_id', $pay->id)->get(['fs.account_id', 'fs.base_debit', 'fs.base_credit']);
        $fx = $lines->whereNotIn('account_id', [self::BANK, self::AP]);
        $this->chk('Multi-currency', 'Exchange loss 115.00 SAR posted', 115.00, $fx->sum('base_debit') - $fx->sum('base_credit'));
        $this->chk('Multi-currency', 'Payment voucher balances', $lines->sum('base_debit'), $lines->sum('base_credit'));

        // the VAT return converts at the invoice rate
        $vr = VatReturn::compute('2026-11-14', '2026-11-14');
        $this->chk('Multi-currency', 'VAT return: USD sale counted as 3,750.00 SAR', 3750.00, $vr['sales']['standard']['amount']);
        $this->chk('Multi-currency', 'VAT return: USD sale VAT 562.50 SAR', 562.50, $vr['sales']['standard']['vat']);
        $this->chk('Multi-currency', 'VAT return: EUR purchase VAT 600.00 SAR', 600.00, $vr['purchases']['standard']['vat']);
        $tb = LedgerReports::trialBalance(self::FROM, self::TO);
        $this->chk('Multi-currency', 'Trial balance still balances', $tb['total_debit'], $tb['total_credit']);
        $cs = $this->livewire(\App\Livewire\Report\Finance\CustomerStatement::class, ['startDate' => self::FROM, 'endDate' => self::TO, 'customerId' => encodeId($usd->id)]);
        $this->has('Multi-currency', 'USD customer statement shows the invoice in USD (1,150.00)', $cs, '1,150.00');
    }

    // ───────────── proforma ─────────────

    private function proforma(): void
    {
        $before = DB::table('finance_subs')->count();
        $vatBefore = VatReturn::compute(self::FROM, self::TO)['vatDue'];
        $arBefore = $this->arBalance($this->ids['customer']);
        $this->send(app(ProformaInvoiceController::class), 'store', [
            'job_id' => $this->ids['job'], 'invoice_date' => '2026-11-19', 'currency' => 'SAR', 'currency_rate' => 1, 'terms' => self::TAG . ' PFI',
            'description_id' => ['2'], 'comment' => ['advance request'], 'unit_id' => ['1'], 'quantity' => [1], 'unit_price' => ['5000'], 'tax' => ['STANDARD'],
        ]);
        $pf = ProformaInvoice::where('terms', self::TAG . ' PFI')->firstOrFail();
        $this->ids['proforma'] = $pf->id;
        $this->chk('Proforma', 'Proforma belongs to the job\'s customer', $this->ids['customer'], (int) $pf->customer_id, 0);
        $this->chk('Proforma', 'Proforma total 5,750.00 (5,000 + VAT)', 5750.00, (float) $pf->grand_total);
        $this->status(app(ProformaInvoiceController::class), $pf->id, ProformaInvoiceEnum::APPROVED->value);
        $this->doc('Proforma invoice 5,000 + VAT', $pf->fresh());
        $this->chk('Proforma', 'An approved proforma posts nothing to the ledger', $before, DB::table('finance_subs')->count(), 0);
        $this->chk('Proforma', 'Proforma is not in the VAT return', $vatBefore, VatReturn::compute(self::FROM, self::TO)['vatDue']);
        $this->chk('Proforma', 'Proforma does not change what the customer owes', $arBefore, $this->arBalance($this->ids['customer']));
        $ag = $this->aging(\App\Livewire\Report\Finance\CustomerAging::class, ['asOfDate' => self::AS_OF, 'customerId' => encodeId($this->ids['customer'])]);
        $this->ok('Proforma', 'Proforma is not in customer aging', !str_contains(json_encode($ag['invoices'] ?? []), (string) $pf->row_no));

        $invBefore = CustomerInvoice::where('job_id', $this->ids['job'])->count();
        $this->status(app(ProformaInvoiceController::class), $pf->id, ProformaInvoiceEnum::CONVERTED->value);
        $this->chk('Proforma', '"Convert to invoice" creates a customer invoice', $invBefore + 1, CustomerInvoice::where('job_id', $this->ids['job'])->count(), 0);
    }

    // ───────────── prints ─────────────

    private function prints(): void
    {
        $render = function (string $label, callable $fn, array $needles) {
            try {
                $html = (string) $fn()->render();
                $text = $this->text($html);
                $missing = array_values(array_filter($needles, fn ($n) => !str_contains($text, $n)));
                $this->ok('Prints', $label . ' prints with ' . implode(', ', $needles), !$missing, 'missing ' . implode(', ', $missing));
            } catch (\Throwable $e) {
                $this->ok('Prints', $label . ' prints', false, get_class($e) . ': ' . $e->getMessage() . ' @ ' . basename($e->getFile()) . ':' . $e->getLine());
            }
        };
        $no = fn ($model, $id) => (string) $model::find($id)->row_no;
        $C = fn ($c) => app($c);

        $render('Customer invoice INV2', fn () => $C(CustomerInvoiceController::class)->print($this->ids['inv2']), [$no(CustomerInvoice::class, $this->ids['inv2']), '2,300.00', '300.00']);
        $render('USD invoice', fn () => $C(CustomerInvoiceController::class)->print($this->ids['usd_inv']), ['1,150.00', 'USD']);
        $render('Credit note', fn () => $C(\App\Http\Controllers\Finance\Adjustment\CreditNoteController::class)->print($this->ids['credit_note']), ['230.00']);
        $render('Collection receipt COL2', fn () => $C(CollectionController::class)->print($this->ids['col2']), [$no(Collection::class, $this->ids['col2']), '1,000.00']);
        $render('Supplier invoice', fn () => $C(SupplierInvoiceController::class)->print($this->ids['supplier_invoice']), ['2,300.00']);
        $render('Debit note', fn () => $C(\App\Http\Controllers\Finance\Adjustment\DebitNoteController::class)->print($this->ids['debit_note']), ['230.00']);
        $render('Payment voucher PAY2', fn () => $C(PaymentController::class)->print($this->ids['pay2']), [$no(Payment::class, $this->ids['pay2']), '500.00']);
        $render('Expense', fn () => $C(ExpenseController::class)->print($this->ids['expense']), ['460.00']);
        $render('Proforma invoice', fn () => $C(ProformaInvoiceController::class)->print($this->ids['proforma']), ['5,750.00']);
        $render('Quotation', fn () => $C(\App\Http\Controllers\Quotation\QuotationController::class)->print($this->ids['quotation']), [$no(\App\Models\Quotation\Quotation::class, $this->ids['quotation'])]);
        $render('Enquiry', fn () => $C(\App\Http\Controllers\Enquiry\EnquiryController::class)->print($this->ids['enquiry']), [$no(\App\Models\Enquiry\Enquiry::class, $this->ids['enquiry'])]);
        $render('Job', fn () => $C(\App\Http\Controllers\Job\JobController::class)->print($this->ids['job']), [$no(\App\Models\Job\Job::class, $this->ids['job'])]);

        // every invoice theme
        $inv = CustomerInvoice::find($this->ids['inv2']);
        $build = new \ReflectionMethod(CustomerInvoiceController::class, 'buildPrintViewData');
        $build->setAccessible(true);
        foreach (['standard', 'stylish', 'advance-gst-tally', 'bilingual', 'fastfatoora', 'ai-fatoora', 'fastfatoora-classic'] as $theme) {
            $render('Invoice theme "' . $theme . '"', function () use ($build, $inv, $theme) {
                $settings = \App\Models\Settings\InvoiceSettings::invoiceSettings()->replicate();
                $settings->theme = $theme;
                [$template, $data] = $build->invoke(app(CustomerInvoiceController::class), $inv->fresh(['customerInvoiceSubs', 'customer', 'job.activity', 'jobContainers', 'jobPackages']), $settings);

                return view($template, $data);
            }, [(string) $inv->row_no, '2,300.00']);
        }

        // report print blocks (what Print / PDF use) carry the figures
        $cid = (string) $this->ids['customer'];
        $sid = (string) $this->ids['supplier'];
        $tb = LedgerReports::trialBalance(self::FROM, self::TO);
        foreach ([
            ['Trial balance print block', \App\Livewire\Report\Finance\TrialBalanceTable::class, [], true, [number_format($tb['total_debit'], 2)]],
            ['Customer statement print block', \App\Livewire\Report\Finance\CustomerStatement::class, ['startDate' => self::FROM, 'endDate' => self::TO] + ['customerId' => encodeId($cid)], false, ['1,570.00']],
            ['Supplier statement print block', \App\Livewire\Report\Finance\SupplierStatement::class, ['startDate' => self::FROM, 'endDate' => self::TO] + ['supplierId' => encodeId($sid)], false, ['870.00']],
            ['Customer ledger print block', \App\Livewire\Report\Finance\GeneralLedgerTable::class, ['customerId' => $cid], true, ['1,570.00']],
        ] as [$label, $cls, $props, $period, $needles]) {
            $t = \Livewire\Livewire::test($cls)->set($props);
            if ($period) $t->call('updateDateRange', ['startDate' => self::FROM, 'endDate' => self::TO]);
            $html = $t->html();
            // the print section: class stmt-print (most reports) or id statement-print (customer statement)
            $block = preg_match('/<div[^>]+(class="[^"]*stmt-print[^"]*"|id="statement-print").*$/s', $html, $m) ? $this->text($m[0]) : '';
            $missing = array_values(array_filter($needles, fn ($n) => !str_contains($block, $n)));
            $this->ok('Prints', $label . ' shows ' . implode(', ', $needles), $block !== '' && !$missing, $block === '' ? 'no print block' : 'missing ' . implode(', ', $missing));
        }
    }

    // ───────────── exports ─────────────

    private function exports(): void
    {
        $cid = $this->ids['customer'];
        $sid = $this->ids['supplier'];
        $period = ['startDate' => self::FROM, 'endDate' => self::TO];
        $tb = LedgerReports::trialBalance(self::FROM, self::TO);
        $pl = LedgerReports::profitAndLoss(self::FROM, self::TO);
        $bs = LedgerReports::balanceSheet(self::TO);
        $vr = VatReturn::compute(self::FROM, self::TO);
        $F = '\\App\\Livewire\\Report\\Finance\\';
        $list = [
            ['Trial balance', $F . 'TrialBalance', $period, [$tb['total_debit']]],
            ['Profit & loss', $F . 'ProfitAndLoss', $period, [$pl['net_income']]],
            ['Balance sheet', $F . 'BalanceSheet', ['endDate' => self::TO], [$bs['total_assets']]],
            ['Tax summary', $F . 'TaxSummary', $period, [$vr['salesTotal']['vat']]],
            ['Input tax', $F . 'InputTax', $period, [LedgerReports::taxLines('input', self::FROM, self::TO)['total']]],
            ['Output tax', $F . 'OutputTax', $period, [LedgerReports::taxLines('output', self::FROM, self::TO)['total']]],
            ['Customer statement', $F . 'CustomerStatement', $period + ['customerId' => encodeId($cid)], [1570.00]],
            ['Customer ledger (closing incl. 500 opening)', $F . 'GeneralLedger', $period + ['customerId' => $cid], [1570.00]],
            ['Supplier statement', $F . 'SupplierStatement', $period + ['supplierId' => encodeId($sid)], [870.00]],
            ['Supplier ledger (closing incl. 300 opening)', $F . 'SupplierLedger', $period + ['supplierId' => $sid], [870.00]],
            ['Customer aging', $F . 'CustomerAging', ['asOfDate' => self::AS_OF, 'customerId' => encodeId($cid)], [1570.00]],
            ['Customer aging (all)', $F . 'CustomerAgingAll', ['asOfDate' => self::AS_OF], [1570.00]],
            ['Supplier aging', $F . 'SupplierAging', ['asOfDate' => self::AS_OF, 'supplierId' => encodeId($sid)], [870.00]],
            ['Supplier aging (all)', $F . 'SupplierAgingAll', ['asOfDate' => self::AS_OF], [870.00]],
        ];
        foreach ($list as [$label, $cls, $props, $numbers]) {
            try {
                $c = \Livewire\Livewire::test($cls)->set($props)->instance();
                $res = $c->exportExcel();
                $path = $res instanceof \Symfony\Component\HttpFoundation\BinaryFileResponse ? $res->getFile()->getPathname() : null;
                if (!$path) {
                    $this->ok('Exports', $label . ' Excel file is produced', false, is_object($res) ? get_class($res) : gettype($res));
                    continue;
                }
                $cells = collect(\PhpOffice\PhpSpreadsheet\IOFactory::load($path)->getActiveSheet()->toArray(null, true, false))->flatten()->filter(fn ($v) => $v !== null && $v !== '');
                $nums = $cells->map(fn ($v) => is_numeric($v) ? (float) $v : (is_string($v) && is_numeric(str_replace(',', '', $v)) ? (float) str_replace(',', '', $v) : null))->filter(fn ($v) => $v !== null);
                foreach ($numbers as $n) {
                    // a payable may be written as a negative (credit) balance in Excel
                    $found = $nums->contains(fn ($v) => abs($v - $n) < 0.01 || abs($v + $n) < 0.01);
                    $this->ok('Exports', $label . ' Excel contains ' . number_format($n, 2), $found, $cells->count() . ' cells, numbers: ' . $nums->take(12)->implode(', '));
                }
            } catch (\Throwable $e) {
                $this->ok('Exports', $label . ' Excel export', false, get_class($e) . ': ' . $e->getMessage() . ' @ ' . basename($e->getFile()) . ':' . $e->getLine());
            }
        }
    }

    // ───────────── period closing ─────────────

    private function periodClosing(): void
    {
        $C = fn ($c) => app($c);
        // a draft dated inside the period-to-be, made while it is still open
        $this->send($C(CustomerInvoiceController::class), 'store', [
            'job_id' => $this->ids['job'], 'customer' => encodeId($this->ids['customer']), 'invoice_date' => '2026-11-10', 'due_date' => '2026-12-10',
            'currency' => 'SAR', 'currency_rate' => 1, 'terms' => self::TAG . ' PC draft', 'description_id' => ['2'], 'comment' => ['x'],
            'unit_id' => ['1'], 'quantity' => [1], 'unit_price' => ['100'], 'tax' => ['STANDARD'], 'account' => [self::REVENUE],
        ]);
        $draft = CustomerInvoice::where('terms', self::TAG . ' PC draft')->firstOrFail();

        $res = $this->send($C(PeriodClosingController::class), 'store', ['year' => 2026, 'closing_date' => '15-11-2026', 'notes' => self::TAG]);
        $pc = PeriodClosing::find($res['module_id'] ?? 0);
        if (!$pc) {
            $this->ok('Period closing', 'Period saved', false, json_encode($res));
            return;
        }
        $C(PeriodClosingController::class)->close($pc->id);
        $this->ok('Period closing', 'Period closed through 15-11-2026', PeriodClosing::isLocked('2026-11-15') && !PeriodClosing::isLocked('2026-11-16'));

        $blocked = function (string $label, callable $fn, callable $changed) {
            $msg = '';
            try {
                $r = $fn();
                $msg = is_array($r) ? (string) ($r['message'] ?? json_encode($r)) : '';
            } catch (\Illuminate\Validation\ValidationException $e) {
                $msg = collect($e->errors())->flatten()->implode(' ');
            } catch (\Throwable $e) {
                $msg = get_class($e) . ': ' . $e->getMessage();
            }
            $pass = !$changed();
            $this->deletes[] = ['Closed period: ' . $label, 'refused', $pass ? 'refused' : 'ALLOWED', $msg, $pass];
            $this->ok('Period closing', $label . ' is refused', $pass, $msg);
        };

        $blocked('New invoice dated 10-11 (closed)', fn () => $this->send($C(CustomerInvoiceController::class), 'store', [
            'job_id' => $this->ids['job'], 'customer' => encodeId($this->ids['customer']), 'invoice_date' => '2026-11-10', 'due_date' => '2026-12-10',
            'currency' => 'SAR', 'currency_rate' => 1, 'terms' => self::TAG . ' PC new', 'description_id' => ['2'], 'comment' => ['x'],
            'unit_id' => ['1'], 'quantity' => [1], 'unit_price' => ['100'], 'tax' => ['STANDARD'], 'account' => [self::REVENUE],
        ]), fn () => CustomerInvoice::where('terms', self::TAG . ' PC new')->exists());
        $blocked('Approving a draft invoice dated 10-11', fn () => $this->status($C(CustomerInvoiceController::class), $draft->id, CustomerInvoiceEnum::APPROVED->value),
            fn () => (int) $draft->fresh()->status !== CustomerInvoiceEnum::DRAFT->value);
        $blocked('Cancelling approved INV1 dated 03-11', fn () => $this->status($C(CustomerInvoiceController::class), $this->ids['inv1'], CustomerInvoiceEnum::CANCELLED->value),
            fn () => (int) CustomerInvoice::find($this->ids['inv1'])->status !== CustomerInvoiceEnum::APPROVED->value);
        $blocked('Un-approving collection COL1 dated 05-11', fn () => $this->status($C(CollectionController::class), $this->ids['col1'], CollectionEnum::DRAFT->value),
            fn () => (int) Collection::find($this->ids['col1'])->status !== CollectionEnum::APPROVED->value);
        $blocked('Un-approving payment PAY1 dated 08-11', fn () => $this->status($C(PaymentController::class), $this->ids['pay1'], PaymentEnum::DRAFT->value),
            fn () => (int) Payment::find($this->ids['pay1'])->status !== PaymentEnum::APPROVED->value);
        $blocked('New expense dated 12-11', fn () => $this->send($C(ExpenseController::class), 'store', [
            'posted_at' => '2026-11-12', 'reference_number' => self::TAG . '-PCEXP', 'payment_status' => 'paid', 'main_account' => self::BANK,
            'paid_amount' => 115, 'amount_excluding_vat' => 100, 'amount_including_vat' => 115, 'account' => [self::EXPENSE], 'comment' => ['x'],
            'quantity' => [1], 'unit_price' => ['100'], 'tax' => ['STANDARD'], 'item_id' => [], 'employee_id' => [''],
        ]), fn () => Expense::where('reference_number', self::TAG . '-PCEXP')->exists());
        $blocked('Opening balance dated 31-10', function () use ($C) {
            $C(OpeningBalanceController::class)->store($this->request(['posted_at' => '31-10-2026', 'balances' => [
                ['account_id' => 57, 'debit' => 10, 'credit' => 0, 'notes' => self::TAG . ' PC OB'], ['account_id' => self::OWNER_CAPITAL, 'debit' => 0, 'credit' => 10, 'notes' => self::TAG . ' PC OB'],
            ]]));

            return ['message' => $this->redirectErrors()];
        }, fn () => DB::table('finance_subs')->where('description', self::TAG . ' PC OB')->exists());

        // after the closing date work goes on
        $this->send($C(CustomerInvoiceController::class), 'store', [
            'job_id' => $this->ids['job'], 'customer' => encodeId($this->ids['customer']), 'invoice_date' => '2026-11-20', 'due_date' => '2026-12-20',
            'currency' => 'SAR', 'currency_rate' => 1, 'terms' => self::TAG . ' PC open', 'description_id' => ['2'], 'comment' => ['x'],
            'unit_id' => ['1'], 'quantity' => [1], 'unit_price' => ['100'], 'tax' => ['STANDARD'], 'account' => [self::REVENUE],
        ]);
        $this->ok('Period closing', 'An invoice dated 20-11 (after the closing date) is accepted', CustomerInvoice::where('terms', self::TAG . ' PC open')->exists());
        $this->ok('Period closing', 'A closed period cannot be deleted', (function () use ($C, $pc) {
            $C(PeriodClosingController::class)->destroy($pc->id);

            return PeriodClosing::whereKey($pc->id)->exists();
        })());

        // reopened: the draft can be approved again
        $C(PeriodClosingController::class)->reopen($pc->id);
        $this->status($C(CustomerInvoiceController::class), $draft->id, CustomerInvoiceEnum::APPROVED->value);
        $this->chk('Period closing', 'After reopening, the draft dated 10-11 can be approved', CustomerInvoiceEnum::APPROVED->value, (int) $draft->fresh()->status, 0);
    }

    protected function findings(): array
    {
        return [
            [1, 'Customer Statement page counted drafts and cancelled documents', 'The page (and its Excel / PDF) added up the invoice, collection and credit note tables with no status filter: draft and cancelled documents were included and opening balances were missing. A ledger-based table component existed but no page used it.', 'Fixed: the page reads the receivable (1130) lines of approved vouchers'],
            [2, 'Supplier Statement ignored debit notes', 'It read voucher headers (debit = credit), so a debit note moved nothing; the Invoiced / Paid columns only showed supplier invoices and payments.', 'Fixed: reads the payable (2110) lines; columns are Credit (owed) and Debit (paid / adjusted)'],
            [3, 'No exchange gain / loss on foreign-currency settlements', 'A USD invoice booked at 3.75 and collected at 3.80 left the customer at -57.50 SAR (EUR supplier at -115.00), with no exchange account. The expense code sent exchange differences to account 2131 VAT Clearing.', 'Fixed: new account 4180 Exchange Gain / Loss; receivable / payable cleared at the booking rate, the difference posted there'],
            [4, 'Aging ignored opening balances', 'Customer and supplier aging (single and all) only read invoices, so they disagreed with the statement once an opening balance existed.', 'Fixed: opening balances appear as an "Opening balance" row aged from their date'],
            [5, 'Aging lagged up to a minute after a payment', 'Settlements are synced at most once a minute; a collection approved within that minute did not reduce the invoice in aging yet.', 'Fixed: posting or removing a voucher marks the sync stale'],
            [6, 'Closed period could still be changed', 'Only saving checked the closed period: approving a draft, cancelling / un-approving an approved invoice, collection or payment, and saving an expense all went through inside a closed period.', 'Fixed: status changes that touch the ledger and expense saves are refused in a closed period'],
            [7, 'Proforma "Convert to invoice" did nothing', 'The menu item only showed a browser alert and setting the status created no invoice.', 'Fixed: creates a draft customer invoice with the proforma lines (approve it to post)'],
            [8, 'Opening balances cannot be collected', 'A collection must name an invoice, so an opening balance can only be cleared with a journal voucher.', 'Open: decide whether collections / payments may be taken "on account" against an opening balance'],
            [9, 'Not covered here', 'ZATCA e-invoicing (by request), PDF files (made in the browser, checked on screen).', 'Info'],
        ];
    }
}
