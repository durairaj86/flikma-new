<?php

namespace App\Console\Commands;

use App\Enums\CollectionEnum;
use App\Enums\CreditNoteEnum;
use App\Enums\CustomerInvoiceEnum;
use App\Enums\DebitNoteEnum;
use App\Enums\ExpenseEnum;
use App\Enums\PaymentEnum;
use App\Enums\SupplierInvoiceEnum;
use App\Http\Controllers\Finance\Adjustment\CreditNoteController;
use App\Http\Controllers\Finance\Adjustment\DebitNoteController;
use App\Http\Controllers\Finance\Expense\ExpenseController;
use App\Http\Controllers\Finance\Invoice\CustomerInvoiceController;
use App\Http\Controllers\Finance\JournalVoucher\JournalVoucherController;
use App\Models\Finance\JournalVoucher\JournalVoucher;
use App\Http\Controllers\Finance\Invoice\SupplierInvoiceController;
use App\Http\Controllers\Transaction\CollectionController;
use App\Http\Controllers\Transaction\PaymentController;
use App\Models\Finance\Adjustment\CreditNote;
use App\Models\Finance\Adjustment\DebitNote;
use App\Models\Finance\Collection\Collection;
use App\Models\Finance\CustomerInvoice\CustomerInvoice;
use App\Models\Finance\Expense\Expense;
use App\Models\Finance\Payment\Payment;
use App\Models\Finance\SupplierInvoice\SupplierInvoice;
use App\Services\Finance\LedgerReports;
use App\Services\Finance\VatReturn;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Saudi (ZATCA VAT) accounting test: posts a set of documents through the real controllers, then checks the
 * ledger, vouchers, trial balance, P&L, balance sheet, tax summary, input / output tax, statements and the VAT return
 * against figures worked out by hand. Everything is rolled back unless --keep is given.
 * Writes an Excel report.   php artisan finance:saudi-test [--keep] [--out=path.xlsx]
 */
class FinanceSaudiTest extends Command
{
    protected $signature = 'finance:saudi-test {--keep : commit the test documents instead of rolling back} {--out= : report file}';
    protected $description = 'Post Saudi VAT test documents and check every finance report against hand-calculated figures';

    private const AR = 5, VAT_OUT = 20, REVENUE = 32, BANK = 4, AP = 18, VAT_IN = 7, COST = 42, EXPENSE = 47;
    private const FROM = '2026-12-01', TO = '2026-12-31';

    private array $checks = [];
    private array $docs = [];
    private $round = null;
    private $cancelledId = null;

    public function handle(): int
    {
        auth()->loginUsingId(1);
        session(['company_id' => 1]);

        DB::beginTransaction();
        try {
            $before = $this->accountTotals();
            $stmtBefore = $this->partyBalances(1, 1);
            $this->post();
            $after = $this->accountTotals();
            $this->verify($before, $after, $stmtBefore);
        } catch (\Throwable $e) {
            $this->checks[] = ['Run', 'Test run completed without error', 'no error', get_class($e) . ': ' . $e->getMessage() . ' (' . basename($e->getFile()) . ':' . $e->getLine() . ')', false];
            $this->error($e->getMessage());
        } finally {
            if ($this->option('keep')) {
                DB::commit();
            } else {
                DB::rollBack();
            }
        }

        $file = $this->report();
        $fail = collect($this->checks)->where(4, false)->count();
        $this->info(count($this->checks) . ' checks, ' . $fail . ' failed. Report: ' . $file . ($this->option('keep') ? ' (entries kept)' : ' (entries rolled back)'));

        return $fail ? 1 : 0;
    }

    // ───────────── posting ─────────────

    private function send(object $controller, string $method, array $payload = [], array $args = []): array
    {
        $request = Request::create('/saudi-test', 'POST', $payload);
        $request->setLaravelSession(app('session.store'));
        app()->instance('request', $request);
        $result = $controller->$method(...array_merge([$request], $args));
        $data = method_exists($result, 'getData') ? $result->getData(true) : [];

        return is_array($data) ? $data : [];
    }

    private function status(object $controller, string $method, $id, $status): void
    {
        $request = Request::create('/saudi-test', 'POST');
        $request->setLaravelSession(app('session.store'));
        app()->instance('request', $request);
        $controller->$method($id, $status);
    }

    private function doc(string $label, $model, string $no): void
    {
        $this->docs[] = [$label, $no, (float) ($model->sub_total ?? 0), (float) ($model->tax_total ?? 0), (float) ($model->grand_total ?? 0), (string) ($model->currency ?? 'SAR')];
    }

    private function invoice(string $label, string $date, $price, $tax, string $currency = 'SAR', float $rate = 1, $qty = 1): CustomerInvoice
    {
        $prices = (array) $price;
        $taxes = (array) $tax;
        $n = count($prices);
        $this->send(app(CustomerInvoiceController::class), 'store', [
            'job_id' => 2, 'customer' => encodeId(1), 'invoice_date' => $date, 'due_date' => '2027-01-15',
            'currency' => $currency, 'currency_rate' => $rate, 'terms' => 'SAUDITEST ' . $label,
            'description_id' => array_fill(0, $n, '2'), 'comment' => array_fill(0, $n, 'SAUDITEST ' . $label), 'unit_id' => array_fill(0, $n, '1'),
            'quantity' => array_fill(0, $n, $qty), 'unit_price' => array_map('strval', $prices), 'tax' => $taxes, 'account' => array_fill(0, $n, self::REVENUE),
        ]);
        $i = CustomerInvoice::where('terms', 'SAUDITEST ' . $label)->latest('id')->firstOrFail();
        $this->status(app(CustomerInvoiceController::class), 'updateStatus', $i->id, CustomerInvoiceEnum::APPROVED->value);
        $i->refresh();
        $this->doc($label, $i, $i->row_no);

        return $i;
    }

    private function bill(string $label, string $date, float $price, string $tax): SupplierInvoice
    {
        $this->send(app(SupplierInvoiceController::class), 'store', [
            'job_id' => 2, 'supplier' => encodeId(1), 'invoice_date' => $date, 'invoice_number' => 'SAUDITEST-' . $label,
            'due_date' => '2027-01-15', 'currency' => 'SAR', 'currency_rate' => 1, 'terms' => 'SAUDITEST ' . $label,
            'description_id' => ['2'], 'comment' => ['SAUDITEST ' . $label], 'unit_id' => ['1'],
            'quantity' => [1], 'unit_price' => [(string) $price], 'tax' => [$tax], 'account' => [self::COST],
        ]);
        $b = SupplierInvoice::where('invoice_number', 'SAUDITEST-' . $label)->latest('id')->firstOrFail();
        $this->status(app(SupplierInvoiceController::class), 'updateStatus', $b->id, SupplierInvoiceEnum::APPROVED->value);
        $b->refresh();
        $this->doc($label, $b, $b->row_no);

        return $b;
    }

    private function post(): void
    {
        $ci1 = $this->invoice('CI1 standard 15%', '2026-12-02', 10000, 'STANDARD');
        $this->invoice('CI2 zero rated export', '2026-12-03', 4000, 'ZERO');
        $this->invoice('CI3 exempt', '2026-12-04', 1000, 'EXEMPT');
        $this->invoice('CI4 out of scope', '2026-12-05', 500, 'OUTOFSCOPE');
        $this->invoice('CI5 USD 1000 @3.75', '2026-12-06', 1000, 'STANDARD', 'USD', 3.75);
        $this->invoice('CI6 mixed lines std+zero+exempt', '2026-12-07', [1000, 500, 200], ['STANDARD', 'ZERO', 'EXEMPT']);
        $this->round = $this->invoice('CI8 rounding 3 x 33.33', '2026-12-07', 33.33, 'STANDARD', 'SAR', 1, 3);

        // an invoice approved and then cancelled must leave no trace in the books or the VAT return
        $cancelled = $this->invoice('CI7 approved then cancelled', '2026-12-07', 700, 'STANDARD');
        $this->status(app(CustomerInvoiceController::class), 'updateStatus', $cancelled->id, CustomerInvoiceEnum::CANCELLED->value);
        $this->cancelledId = $cancelled->id;

        $this->send(app(CreditNoteController::class), 'store', [
            'credit_note_type' => '1', 'invoice_id' => $ci1->id, 'customer' => encodeId(1), 'credit_note_date' => '2026-12-08',
            'job_id' => 2, 'reason' => 'SAUDITEST rate correction', 'terms' => 'SAUDITEST CN1', 'description_id' => ['2'],
            'comment' => ['SAUDITEST CN1'], 'unit_id' => ['1'], 'quantity' => [1], 'unit_price' => ['1000'], 'tax' => ['STANDARD'], 'account' => [self::REVENUE],
        ]);
        $cn = CreditNote::where('terms', 'SAUDITEST CN1')->latest('id')->firstOrFail();
        $this->status(app(CreditNoteController::class), 'updateStatus', $cn->id, CreditNoteEnum::APPROVED->value);
        $cn->refresh();
        $this->doc('CN1 credit note on CI1', $cn, $cn->row_no);

        $si1 = $this->bill('SI1 standard 15%', '2026-12-09', 3000, 'STANDARD');
        $this->bill('SI2 zero rated', '2026-12-10', 600, 'ZERO');
        $this->bill('SI3 exempt', '2026-12-11', 300, 'EXEMPT');

        $this->send(app(DebitNoteController::class), 'store', [
            'invoice_id' => $si1->id, 'supplier' => encodeId(1), 'debit_note_date' => '2026-12-12', 'job_id' => 2,
            'reason' => 'SAUDITEST rate correction', 'terms' => 'SAUDITEST DN1', 'description_id' => ['2'], 'comment' => ['SAUDITEST DN1'],
            'unit_id' => ['1'], 'quantity' => [1], 'unit_price' => ['200'], 'tax' => ['STANDARD'], 'account' => [self::COST],
        ]);
        $dn = DebitNote::where('terms', 'SAUDITEST DN1')->latest('id')->firstOrFail();
        $this->status(app(DebitNoteController::class), 'updateStatus', $dn->id, DebitNoteEnum::APPROVED->value);
        $dn->refresh();
        $this->doc('DN1 debit note on SI1', $dn, $dn->row_no);

        foreach ([['EXP1 standard 15%', 400, 'STANDARD', 460, '2026-12-13'], ['EXP2 exempt', 100, 'EXEMPT', 100, '2026-12-14']] as [$label, $price, $tax, $gross, $date]) {
            $this->send(app(ExpenseController::class), 'store', [
                'posted_at' => $date, 'reference_number' => 'SAUDITEST-' . substr($label, 0, 4), 'payment_status' => 'paid', 'main_account' => self::BANK,
                'paid_amount' => $gross, 'amount_excluding_vat' => $price, 'amount_including_vat' => $gross,
                'account' => [self::EXPENSE], 'comment' => ['SAUDITEST ' . $label], 'quantity' => [1], 'unit_price' => [(string) $price],
                'tax' => [$tax], 'item_id' => [], 'employee_id' => [''],
            ]);
            $e = Expense::where('reference_number', 'SAUDITEST-' . substr($label, 0, 4))->latest('id')->firstOrFail();
            $this->send(app(ExpenseController::class), 'updateStatus', [], [$e->id, ExpenseEnum::APPROVED->value]);
            $e->refresh();
            $this->docs[] = [$label, $e->row_no, (float) $price, round($gross - $price, 2), (float) $e->grand_total, 'SAR'];
        }

        $this->send(app(CollectionController::class), 'store', [
            'customer' => encodeId(1), 'collection_date' => '2026-12-15', 'account' => [self::BANK], 'reference_no' => 'SAUDITEST',
            'currency' => 'SAR', 'currency_rate' => 1, 'customer_invoice_ids' => [$ci1->id], 'invoice_amounts' => [$ci1->id => 5000], 'notes' => 'SAUDITEST COL1',
        ]);
        $col = Collection::where('notes', 'SAUDITEST COL1')->latest('id')->firstOrFail();
        $this->status(app(CollectionController::class), 'updateStatus', $col->id, CollectionEnum::APPROVED->value);
        $col->refresh();
        $this->docs[] = ['COL1 part collection of CI1', $col->row_no, 5000.0, 0.0, (float) $col->grand_total, 'SAR'];

        $this->send(app(PaymentController::class), 'store', [
            'supplier' => encodeId(1), 'payment_date' => '2026-12-16', 'account' => [self::BANK], 'reference_no' => 'SAUDITEST',
            'currency' => 'SAR', 'currency_rate' => 1, 'supplier_invoice_ids' => [$si1->id], 'invoice_amounts' => [$si1->id => 3000], 'notes' => 'SAUDITEST PAY1',
        ]);
        $pay = Payment::where('notes', 'SAUDITEST PAY1')->latest('id')->firstOrFail();
        $this->status(app(PaymentController::class), 'updateStatus', $pay->id, PaymentEnum::APPROVED->value);
        $pay->refresh();
        $this->docs[] = ['PAY1 part payment of SI1', $pay->row_no, 3000.0, 0.0, (float) $pay->grand_total, 'SAR'];

        // manual journal voucher: Dr Documentation Expense 115 (of which 15 Input VAT) / Cr Bank 115
        $jvRes = $this->send(app(JournalVoucherController::class), 'store', [
            'voucher_type' => 1, 'job_id' => null, 'voucher_date' => '2026-12-17', 'reference_no' => 'SAUDITEST-JV', 'currency' => 'SAR', 'currency_rate' => 1,
            'notes' => 'SAUDITEST JV1', 'account_ids' => [self::EXPENSE, self::BANK], 'descriptions' => ['SAUDITEST JV expense', 'SAUDITEST JV bank'],
            'debit_amounts' => [115, 0], 'credit_amounts' => [0, 115], 'entity_types' => [null, null], 'entity_ids' => [null, null],
            'tax_ids' => [self::VAT_IN, null], 'tax_amounts' => [15, 0],
        ]);
        if (! JournalVoucher::where('notes', 'SAUDITEST JV1')->exists()) {
            throw new \RuntimeException('Journal voucher was not saved: ' . json_encode($jvRes));
        }
        $jv = JournalVoucher::where('notes', 'SAUDITEST JV1')->latest('id')->firstOrFail();
        $this->status(app(JournalVoucherController::class), 'updateStatus', $jv->id, 2);
        $jv->refresh();
        $this->docs[] = ['JV1 journal voucher (expense 100 + input VAT 15)', $jv->row_no, 100.0, 15.0, (float) $jv->debit_total, 'SAR'];
    }

    // ───────────── checking ─────────────

    private function chk(string $area, string $name, $expected, $actual, float $tol = 0.01): void
    {
        $ok = is_numeric($expected) && is_numeric($actual) ? abs((float) $expected - (float) $actual) <= $tol : $expected === $actual;
        $this->checks[] = [$area, $name, is_numeric($expected) ? round((float) $expected, 2) : $expected, is_numeric($actual) ? round((float) $actual, 2) : $actual, $ok];
    }

    private function has(string $area, string $name, string $text, string $needle): void
    {
        $this->checks[] = [$area, $name, 'page shows ' . $needle, str_contains($text, $needle) ? 'found' : 'NOT FOUND', str_contains($text, $needle)];
    }

    private function accountTotals(): array
    {
        return DB::table('finance_subs')->groupBy('account_id')->selectRaw('account_id, SUM(base_debit) dr, SUM(base_credit) cr')->get()->keyBy('account_id')->map(fn ($r) => [(float) $r->dr, (float) $r->cr])->all();
    }

    private function partyBalances(int $customerId, int $supplierId): array
    {
        return [
            'customer' => (float) DB::table('finance_subs')->where('customer_id', $customerId)->where('account_id', self::AR)->selectRaw('COALESCE(SUM(base_debit - base_credit),0) n')->value('n'),
            'supplier' => (float) DB::table('finance_subs')->where('supplier_id', $supplierId)->where('account_id', self::AP)->selectRaw('COALESCE(SUM(base_credit - base_debit),0) n')->value('n'),
        ];
    }

    private function reportText(string $class, array $props = []): string
    {
        $html = \Livewire\Livewire::test($class)->set($props)->html();
        $html = preg_replace('/<(script|style)\b.*?<\/\1>/s', '', $html);

        return trim(html_entity_decode(preg_replace('/\s+/', ' ', strip_tags($html)), ENT_QUOTES));
    }

    /** A report's table component, with the period set the way the filter bar does it (the parent page only passes it on as an event). */
    private function tableText(string $class, array $props = []): string
    {
        $html = \Livewire\Livewire::test($class)->set($props)->call('updateDateRange', ['startDate' => self::FROM, 'endDate' => self::TO])->html();
        $html = preg_replace('/<(script|style)\b.*?<\/\1>/s', '', $html);

        return trim(html_entity_decode(preg_replace('/\s+/', ' ', strip_tags($html)), ENT_QUOTES));
    }

    private function verify(array $before, array $after, array $stmtBefore): void
    {
        $d = fn (int $id) => [($after[$id][0] ?? 0) - ($before[$id][0] ?? 0), ($after[$id][1] ?? 0) - ($before[$id][1] ?? 0)];

        // 1. documents posted
        $this->chk('Documents', 'Documents created and approved', 18, count($this->docs), 0);
        foreach ($this->docs as [$label, $no, $sub, $tax, $grand, $cur]) {
            $this->checks[] = ['Documents', $label . ' has a document number', 'number', $no ?: 'MISSING', (bool) $no];
        }

        // 2. ledger movement per account, worked out by hand
        $r = $this->round;
        $rSub = (float) $r->sub_total;
        $rTax = (float) $r->tax_total;
        $rGrand = (float) $r->grand_total;
        $this->chk('Rounding', 'CI8 (3 x 33.33 + 15%): VAT is stored with 2 decimals', round($rTax, 2), $rTax, 0.00001);
        $this->chk('Rounding', 'CI8 grand total = sub total + VAT', $rSub + $rTax, $rGrand, 0.00001);
        $exp = [
            self::AR => ['Accounts Receivable 1130', 21312.50 + 1850.00 + $rGrand, 6150.00],
            self::VAT_OUT => ['Output VAT 2130', 150.00, 2062.50 + 150.00 + $rTax],
            self::REVENUE => ['Freight Income 4100', 1000.00, 19250.00 + 1700.00 + $rSub],
            self::AP => ['Accounts Payable 2110', 3230.00, 4350.00],
            self::VAT_IN => ['Input VAT 1150', 525.00, 30.00],
            self::COST => ['Freight Purchase 5110', 3900.00, 200.00],
            self::EXPENSE => ['Documentation Expense 5160', 600.00, 0.00],
            self::BANK => ['Bank 1120', 5000.00, 3675.00],
        ];
        foreach ($exp as $id => [$name, $dr, $cr]) {
            [$aDr, $aCr] = $d($id);
            $this->chk('Ledger', $name . ' debit', $dr, $aDr);
            $this->chk('Ledger', $name . ' credit', $cr, $aCr);
        }
        $totDr = 0;
        $totCr = 0;
        foreach ($after as $id => [$dr, $cr]) {
            $totDr += $dr - ($before[$id][0] ?? 0);
            $totCr += $cr - ($before[$id][1] ?? 0);
        }
        $this->chk('Ledger', 'New entries balance (total debit = total credit)', $totDr, $totCr);
        $all = DB::table('finance_subs')->selectRaw('SUM(base_debit) dr, SUM(base_credit) cr')->first();
        $this->chk('Ledger', 'Whole ledger balances', $all->dr, $all->cr);

        $cancelledLines = DB::table('finance')->where('linked_type', CustomerInvoice::class)->where('linked_id', $this->cancelledId)->count();
        $this->chk('Cancellation', 'Cancelled invoice CI7 has no entries left in the ledger', 0, $cancelledLines, 0);
        $jvRows = DB::table('finance_subs as fs')->join('finance as f', 'f.id', '=', 'fs.finance_id')->where('f.voucher_type', 'JV')->where('f.narration', 'like', '%SAUDITEST JV1%')
            ->selectRaw('fs.account_id, SUM(fs.base_debit) dr, SUM(fs.base_credit) cr')->groupBy('fs.account_id')->get()->keyBy('account_id');
        $this->chk('Journal voucher', 'JV posts Documentation Expense debit 100.00 (115 less VAT 15)', 100.00, $jvRows[self::EXPENSE]->dr ?? 0);
        $this->chk('Journal voucher', 'JV posts Input VAT debit 15.00 (tax part)', 15.00, $jvRows[self::VAT_IN]->dr ?? 0);
        $this->chk('Journal voucher', 'JV posts Bank credit 115.00', 115.00, $jvRows[self::BANK]->cr ?? 0);

        // 3. vouchers
        $vouchers = DB::table('finance')->where('narration', 'like', '%SAUDITEST%')->orWhere('reference_no', 'like', '%SAUDITEST%')->get();
        $linked = DB::table('finance')->whereIn('id', DB::table('finance_subs')->whereIn('finance_id', DB::table('finance')->where('posted_at', '>=', self::FROM)->where('posted_at', '<=', self::TO)->pluck('id'))->pluck('finance_id'))->get();
        $dec = DB::table('finance')->whereBetween('reference_date', [self::FROM, self::TO])->get();
        $this->chk('Vouchers', 'A voucher exists for each posted document, cancelled invoice excluded (December 2026)', 17, $dec->count(), 0);
        $this->chk('Vouchers', 'Every voucher is approved', $dec->count(), $dec->where('is_approved', 1)->count(), 0);
        $this->chk('Vouchers', 'Voucher numbers are unique', $dec->count(), $dec->pluck('voucher_no')->unique()->count(), 0);
        $this->chk('Vouchers', 'Every voucher has a voucher number', $dec->count(), $dec->filter(fn ($v) => $v->voucher_no)->count(), 0);
        foreach ($dec as $v) {
            $s = DB::table('finance_subs')->where('finance_id', $v->id)->selectRaw('SUM(base_debit) dr, SUM(base_credit) cr, COUNT(*) n')->first();
            $this->checks[] = ['Vouchers', 'Voucher ' . $v->voucher_no . ' (' . $v->voucher_type . ') balances', 'Dr = Cr', number_format($s->dr, 2) . ' / ' . number_format($s->cr, 2), abs($s->dr - $s->cr) < 0.01 && $s->n >= 2];
        }
        $this->chk('Vouchers', 'Total of voucher debits (header) = total of lines', $dec->sum('base_total_debit'), DB::table('finance_subs')->whereIn('finance_id', $dec->pluck('id'))->sum('base_debit'));

        // 4. tax summary (ZATCA VAT return boxes)
        $vr = VatReturn::compute(self::FROM, self::TO);
        $this->chk('Tax summary', 'Box 1 standard rated sales: amount', 14750.00 + $rSub, $vr['sales']['standard']['amount']);
        $this->chk('Tax summary', 'Box 1 standard rated sales: adjustment (credit note)', -1000.00, $vr['sales']['standard']['adjustment']);
        $this->chk('Tax summary', 'Box 1 standard rated sales: VAT', 2062.50 + $rTax, $vr['sales']['standard']['vat']);
        $this->chk('Tax summary', 'Box 3 zero rated domestic / export sales', 4500.00, $vr['sales']['zero']['amount'] + $vr['sales']['exports']['amount']);
        $this->chk('Tax summary', 'Box 5 exempt sales', 1200.00, $vr['sales']['exempt']['amount']);
        $this->chk('Tax summary', 'Box 6 total sales: amount', 20450.00 + $rSub, $vr['salesTotal']['amount']);
        $this->chk('Tax summary', 'Box 6 total sales: adjustment', -1000.00, $vr['salesTotal']['adjustment']);
        $this->chk('Tax summary', 'Box 6 total sales: VAT', 2062.50 + $rTax, $vr['salesTotal']['vat']);
        $this->chk('Tax summary', 'Out of scope (500.00) is left out of the return', 500.00, $vr['outOfScope']);
        $this->chk('Tax summary', 'Box 7 standard rated purchases: amount', 3400.00, $vr['purchases']['standard']['amount']);
        $this->chk('Tax summary', 'Box 7 standard rated purchases: adjustment (debit note)', -200.00, $vr['purchases']['standard']['adjustment']);
        $this->chk('Tax summary', 'Box 7 standard rated purchases: VAT', 480.00, $vr['purchases']['standard']['vat']);
        $this->chk('Tax summary', 'Box 10 zero rated purchases', 600.00, $vr['purchases']['zero']['amount']);
        $this->chk('Tax summary', 'Box 11 exempt purchases', 400.00, $vr['purchases']['exempt']['amount']);
        $this->chk('Tax summary', 'Box 12 total purchases: amount', 4400.00, $vr['purchasesTotal']['amount']);
        $this->chk('Tax summary', 'Box 12 total purchases: VAT', 480.00, $vr['purchasesTotal']['vat']);
        $this->chk('Tax summary', 'Box 13 / 16 net VAT due', 1582.50 + $rTax, $vr['vatDue']);
        $ledgerTax = LedgerReports::taxSummary(self::FROM, self::TO);
        $this->chk('Tax summary', 'VAT return output VAT = ledger output VAT (2130)', $ledgerTax['total_output_tax'], $vr['salesTotal']['vat']);
        $this->chk('Tax summary', 'Ledger Input VAT (1150) − VAT return = VAT booked by the journal voucher (15.00, a manual entry the return does not read)', 15.00, $ledgerTax['total_input_tax'] - $vr['purchasesTotal']['vat']);
        $tax = $this->reportText(\App\Livewire\Report\Finance\TaxSummary::class, ['startDate' => self::FROM, 'endDate' => self::TO]);
        $this->has('Tax summary', 'Page: output VAT', $tax, number_format(2062.50 + $rTax, 2));
        $this->has('Tax summary', 'Page: input VAT 480.00', $tax, '480.00');
        $this->has('Tax summary', 'Page: net VAT', $tax, number_format(1582.50 + $rTax, 2));
        $this->has('Tax summary', 'Page: standard rated sales', $tax, number_format(14750.00 + $rSub, 2));

        // 5. input / output tax lines
        $out = LedgerReports::taxLines('output', self::FROM, self::TO);
        $in = LedgerReports::taxLines('input', self::FROM, self::TO);
        $this->chk('Output tax', 'Output tax detail total in SAR (USD invoice at 3.75)', 2062.50 + $rTax, $out['total']);
        $this->chk('Input tax', 'Input tax detail total', 495.00, $in['total']);
        $this->chk('Output tax', 'Output tax lines: 5 (CI1, CI5, CI6, CI8, credit note)', 5, count($out['lines']), 0);
        $this->chk('Input tax', 'Input tax lines: 4 (SI1, debit note, EXP1, JV)', 4, count($in['lines']), 0);

        // 6. trial balance
        $tb = LedgerReports::trialBalance(self::FROM, self::TO);
        $this->chk('Trial balance', 'Total debit = total credit', $tb['total_debit'], $tb['total_credit']);
        $row = fn (string $code) => collect($tb['accounts'])->firstWhere('account_code', $code);
        foreach ([['1150', 'Input VAT'], ['2130', 'Output VAT'], ['4100', 'Freight Income'], ['5110', 'Freight Purchase'], ['5160', 'Documentation Expense']] as [$code, $name]) {
            $this->checks[] = ['Trial balance', $name . ' (' . $code . ') is listed', 'listed', $row($code) ? 'listed' : 'MISSING', (bool) $row($code)];
        }
        $text = $this->tableText(\App\Livewire\Report\Finance\TrialBalanceTable::class);
        $this->has('Trial balance', 'Page: total debit', $text, number_format($tb['total_debit'], 2));
        $this->has('Trial balance', 'Page: total credit', $text, number_format($tb['total_credit'], 2));
        $this->checks[] = ['Trial balance', 'Page says Balanced', 'Balanced', stripos($text, 'Balanced') !== false ? 'Balanced' : 'not shown', stripos($text, 'Balanced') !== false];

        // 7. profit and loss
        $pl = LedgerReports::profitAndLoss(self::FROM, self::TO);
        $this->chk('Profit & loss', 'Revenue (invoices less credit note 1,000)', 18250.00 + 1700.00 + $rSub, $pl['total_revenue']);
        $this->chk('Profit & loss', 'Expenses (cost 3,700 + documentation 600)', 4300.00, $pl['total_expenses']);
        $this->chk('Profit & loss', 'Net income', 18250.00 + 1700.00 + $rSub - 4300.00, $pl['net_income']);
        $this->chk('Profit & loss', 'VAT is not in revenue or expense', 0.00, collect(array_merge($pl['revenue'], $pl['expenses']))->whereIn('account_code', ['1150', '2130'])->sum('balance'));
        $plText = $this->tableText(\App\Livewire\Report\Finance\ProfitAndLossTable::class);
        $this->has('Profit & loss', 'Page: net income', $plText, number_format(18250.00 + 1700.00 + $rSub - 4300.00, 2));
        $this->has('Profit & loss', 'Page: revenue', $plText, number_format(18250.00 + 1700.00 + $rSub, 2));

        // 8. balance sheet
        $bs = LedgerReports::balanceSheet(self::TO);
        $this->chk('Balance sheet', 'Assets = liabilities + equity', $bs['total_assets'], $bs['total_liabilities_equity']);
        $this->chk('Balance sheet', 'Current-year result includes the test (net income to 31-12-2026)', LedgerReports::netIncomeTo(self::TO), $bs['net_income']);
        $bsText = $this->tableText(\App\Livewire\Report\Finance\BalanceSheetTable::class);
        $this->has('Balance sheet', 'Page: total assets', $bsText, number_format($bs['total_assets'], 2));
        $this->has('Balance sheet', 'Page: total liabilities & equity', $bsText, number_format($bs['total_liabilities_equity'], 2));

        // 9. statements
        $after2 = $this->partyBalances(1, 1);
        $this->chk('Customer', 'Customer receivable movement (debits − 6,150.00)', 21312.50 + 1850.00 + $rGrand - 6150.00, $after2['customer'] - $stmtBefore['customer']);
        $this->chk('Supplier', 'Supplier payable movement (4,350 − 3,230)', 1120.00, $after2['supplier'] - $stmtBefore['supplier']);
        $cs = $this->tableText(\App\Livewire\Report\Finance\CustomerStatementTable::class, ['customerId' => '1']);
        foreach (['11,500.00', '4,000.00', '4,312.50', '1,850.00', '1,150.00', '5,000.00'] as $n) {
            $this->has('Customer statement', 'Page shows ' . $n, $cs, $n);
        }
        $ss = $this->tableText(\App\Livewire\Report\Finance\SupplierStatementTable::class, ['supplierId' => '1']);
        foreach (['3,450.00', '600.00', '300.00', '230.00', '3,000.00'] as $n) {
            $this->has('Supplier statement', 'Page shows ' . $n, $ss, $n);
        }

        // 10. USD invoice converted to SAR for the books and the VAT return
        $usd = CustomerInvoice::where('terms', 'SAUDITEST CI5 USD 1000 @3.75')->first();
        $this->chk('Currency', 'USD invoice: base sub total 3,750.00 SAR', 3750.00, $usd->base_sub_total ?? 0);
        $this->chk('Currency', 'USD invoice: base VAT 562.50 SAR', 562.50, $usd->base_tax_total ?? 0);
        $this->chk('Currency', 'USD invoice: foreign grand total 1,150.00 USD', 1150.00, $usd->grand_total ?? 0);

        // 11. settlement
        $ci1 = CustomerInvoice::where('terms', 'SAUDITEST CI1 standard 15%')->first();
        $si1 = SupplierInvoice::where('invoice_number', 'SAUDITEST-SI1 standard 15%')->first();
        $this->chk('Settlement', 'CI1 settled amount (collection 5,000 + credit note 1,150)', 6150.00, $ci1->paid_amount ?? 0);
        $this->chk('Settlement', 'SI1 settled amount (payment 3,000 + debit note 230)', 3230.00, $si1->paid_amount ?? 0);
    }

    // ───────────── report ─────────────

    private function report(): string
    {
        $file = $this->option('out') ?: base_path('Saudi-Finance-Test-Report-' . now()->format('Y-m-d') . '.xlsx');
        $book = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        $pass = collect($this->checks)->where(4, true)->count();
        $fail = collect($this->checks)->where(4, false)->count();
        $s = $book->getActiveSheet()->setTitle('Summary');
        $s->fromArray([
            ['Saudi VAT (ZATCA) finance test'],
            ['Run on', now()->format('d-m-Y H:i')],
            ['Period', self::FROM . ' to ' . self::TO],
            ['Checks', count($this->checks)], ['Passed', $pass], ['Failed', $fail],
            [],
            ['Area', 'Checks', 'Passed', 'Failed'],
        ], null, 'A1');
        $row = 9;
        foreach (collect($this->checks)->groupBy(0) as $area => $list) {
            $s->fromArray([$area, $list->count(), $list->where(4, true)->count(), $list->where(4, false)->count()], null, 'A' . $row++);
        }
        $s->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $s->getStyle('A8:D8')->getFont()->setBold(true);
        $s->getColumnDimension('A')->setWidth(26);

        $c = $book->createSheet()->setTitle('Checks');
        $c->fromArray(['Area', 'Check', 'Expected', 'Actual', 'Result'], null, 'A1');
        $r = 2;
        foreach ($this->checks as [$area, $name, $expected, $actual, $ok]) {
            $c->fromArray([$area, $name, $expected, $actual, $ok ? 'PASS' : 'FAIL'], null, 'A' . $r);
            $c->getStyle('E' . $r)->getFont()->getColor()->setRGB($ok ? '15803D' : 'B91C1C');
            $c->getStyle('E' . $r)->getFont()->setBold(true);
            $r++;
        }
        $c->getStyle('A1:E1')->getFont()->setBold(true);
        foreach (['A' => 20, 'B' => 70, 'C' => 26, 'D' => 26, 'E' => 10] as $col => $w) {
            $c->getColumnDimension($col)->setWidth($w);
        }
        $c->setAutoFilter('A1:E' . ($r - 1));
        $c->freezePane('A2');

        $d = $book->createSheet()->setTitle('Documents');
        $d->fromArray(['Document', 'Number', 'Sub total', 'VAT', 'Grand total', 'Currency'], null, 'A1');
        $d->fromArray($this->docs, null, 'A2');
        $d->getStyle('A1:F1')->getFont()->setBold(true);
        foreach (['A' => 34, 'B' => 22, 'C' => 14, 'D' => 12, 'E' => 14, 'F' => 10] as $col => $w) {
            $d->getColumnDimension($col)->setWidth($w);
        }

        $f = $book->createSheet()->setTitle('Findings');
        $f->fromArray(['#', 'Finding', 'What was wrong', 'Status'], null, 'A1');
        $f->fromArray([
            [1, 'Input / Output Tax reports in foreign currency', 'The detail lines and header totals of Input Tax and Output Tax used the document currency: a USD 1,000 invoice showed VAT 150.00 (USD) instead of 562.50 SAR, so the report no longer matched the ledger or the VAT return.', 'Fixed (LedgerReports::taxLines / taxHeader now use SAR)'],
            [2, 'Journal voucher approval posted nothing', 'Approving a journal voucher only changed its status: nothing reached the ledger, trial balance, P&L or VAT accounts.', 'Fixed (posts voucher type JV on approval, removes it on un-approve / disapprove, refuses an unbalanced voucher)'],
            [3, 'Journal vouchers could not be saved', 'journal_vouchers had no unique_row_no column and row_no was an integer, so every save failed (JV/26/0001 is text).', 'Fixed (migration 2026_10_26_000001, applied to the live DB by loading the file)'],
            [4, 'VAT not rounded per line', 'VAT was stored unrounded (3 x 33.33 at 15% = 14.9985), so documents and ledger carried 4 decimals while the printed / ZATCA invoice shows 15.00; a collection of 114.99 left 0.0015 open.', 'Fixed in customer invoice, supplier invoice, proforma, credit note, debit note and expense (VAT rounded to 2 decimals per line)'],
            [5, 'Manual VAT in a journal voucher is not in the VAT return', 'The Tax Summary (VAT return) reads documents only. VAT booked by a journal voucher (tested: Input VAT 15.00) appears in the ledger and in Input Tax but not in the return, so ledger VAT and return VAT differ by that amount.', 'Open: decide whether JV VAT should appear as an adjustment (e.g. box 14)'],
            [6, 'Settlement checks of finance:reconcile fail (8a, 8b)', 'Two lines look settled for more than their amount and one settlement links different accounts / partners. Not created by this test (it is rolled back); looks like existing data or the credit-application flow.', 'Open: needs a look at finance_allocations'],
            [7, 'Not covered by this test', 'ZATCA e-invoice submission, reverse charge / import VAT (boxes 8-9), prior-period corrections (boxes 14-15), multi-company, payroll / loan postings.', 'Info'],
        ], null, 'A2');
        $f->getStyle('A1:D1')->getFont()->setBold(true);
        foreach (['A' => 5, 'B' => 44, 'C' => 90, 'D' => 60] as $col => $w) {
            $f->getColumnDimension($col)->setWidth($w);
        }
        $f->getStyle('B2:D8')->getAlignment()->setWrapText(true)->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);

        $book->setActiveSheetIndex(0);
        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($book))->save($file);

        return $file;
    }
}
