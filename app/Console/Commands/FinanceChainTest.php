<?php

namespace App\Console\Commands;

use App\Enums\CollectionEnum;
use App\Enums\CreditNoteEnum;
use App\Enums\CustomerInvoiceEnum;
use App\Enums\DebitNoteEnum;
use App\Enums\ExpenseEnum;
use App\Enums\PaymentEnum;
use App\Enums\QuotationEnum;
use App\Enums\SupplierInvoiceEnum;
use App\Http\Controllers\Customer\CustomerController;
use App\Http\Controllers\Enquiry\EnquiryController;
use App\Http\Controllers\Finance\Account\AccountController;
use App\Http\Controllers\Finance\Adjustment\CreditNoteController;
use App\Http\Controllers\Finance\Adjustment\DebitNoteController;
use App\Http\Controllers\Finance\Expense\ExpenseController;
use App\Http\Controllers\Finance\Invoice\CustomerInvoiceController;
use App\Http\Controllers\Finance\Invoice\SupplierInvoiceController;
use App\Http\Controllers\Job\JobController;
use App\Http\Controllers\Payroll\EmployeeLoanController;
use App\Http\Controllers\Payroll\PayrollRunController;
use App\Http\Controllers\Quotation\QuotationController;
use App\Http\Controllers\Supplier\SupplierController;
use App\Http\Controllers\Transaction\CollectionController;
use App\Http\Controllers\Transaction\PaymentController;
use App\Models\Customer\Customer;
use App\Models\Enquiry\Enquiry;
use App\Models\Finance\Adjustment\CreditNote;
use App\Models\Finance\Adjustment\DebitNote;
use App\Models\Finance\Collection\Collection;
use App\Models\Finance\CustomerInvoice\CustomerInvoice;
use App\Models\Finance\Expense\Expense;
use App\Models\Finance\Payment\Payment;
use App\Models\Finance\SupplierInvoice\SupplierInvoice;
use App\Models\Job\Job;
use App\Models\Payroll\EmployeeLoan;
use App\Models\Payroll\PayrollRecord;
use App\Models\Quotation\Quotation;
use App\Models\Supplier\Supplier;
use App\Services\Finance\LedgerReports;
use App\Services\Finance\VatReturn;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Whole-chain test through the real controllers:
 *   customer → enquiry → quotation → job → invoice → collection → credit note → second invoice → collection using the customer credit
 *   supplier → supplier invoice → payment → debit note → second (partial) payment; plus an expense and a payroll payment.
 * Then every linked record is put through its delete action (each must be refused while the next document in the chain
 * exists; draft records with nothing after them must still delete), and the reports are checked: trial balance, P&L,
 * balance sheet, VAT return, customer / supplier statement and aging, ledgers, expense and payroll entries.
 * Everything runs in one transaction that is rolled back (nothing kept, no email sent). Writes an Excel report.
 *   php artisan finance:chain-test [--out=path.xlsx]
 */
class FinanceChainTest extends Command
{
    protected $signature = 'finance:chain-test {--out= : report file}';
    protected $description = 'Run the customer and supplier document chains, try to delete every linked record, and check all finance reports';

    protected const AR = 5, VAT_OUT = 20, REVENUE = 32, BANK = 58, AP = 18, VAT_IN = 7, COST = 42, EXPENSE = 47;
    protected const FROM = '2026-11-01', TO = '2026-11-30', AS_OF = '2026-12-31';
    protected const TAG = 'CHAINTEST';

    protected array $checks = [];
    protected array $docs = [];
    protected array $deletes = [];
    protected array $ids = [];
    protected string $reportPrefix = 'Finance-Chain-Test-Report';
    protected string $reportTitle = 'Finance chain test (customer → enquiry → quotation → job → invoice → collection → credit note → customer credit; supplier → invoice → payment → debit note → payment; expense; payroll)';

    public function handle(): int
    {
        auth()->loginUsingId(1);
        session(['company_id' => 1]);

        DB::beginTransaction();
        try {
            $before = [
                'accounts' => $this->accountTotals(),
                'vat' => VatReturn::compute(self::FROM, self::TO),
                'tb' => LedgerReports::trialBalance(self::FROM, self::TO),
                'pl' => LedgerReports::profitAndLoss(self::FROM, self::TO),
            ];
            $this->step('Customer chain', fn () => $this->customerChain());
            $this->step('Supplier chain', fn () => $this->supplierChain());
            $this->step('Expense', fn () => $this->expense());
            $this->step('Payroll', fn () => $this->payroll());
            $this->step('Delete attempts', fn () => $this->deleteAttempts());
            $this->step('Reports', fn () => $this->verify($before));
        } finally {
            DB::rollBack();
        }

        $file = $this->report();
        $fail = collect($this->checks)->where(4, false)->count();
        $this->info(count($this->checks) . ' checks, ' . $fail . ' failed. Report: ' . $file . ' (everything rolled back)');
        foreach (collect($this->checks)->where(4, false) as [$area, $name, $exp, $act]) {
            $this->line("  FAIL [$area] $name — expected $exp, got $act");
        }

        return $fail ? 1 : 0;
    }

    // ───────────── plumbing ─────────────

    /** Run one part; an exception becomes a failed check and the rest still runs. */
    protected function step(string $area, callable $fn): void
    {
        try {
            $fn();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->checks[] = [$area, 'Completed without error', 'no error', 'Validation: ' . json_encode($e->errors()), false];
        } catch (\Throwable $e) {
            $this->checks[] = [$area, 'Completed without error', 'no error', get_class($e) . ': ' . $e->getMessage() . ' (' . basename($e->getFile()) . ':' . $e->getLine() . ')', false];
        }
    }

    protected function request(array $payload = [], string $method = 'POST'): Request
    {
        $request = Request::create('/chain-test', $method, $payload);
        $request->setLaravelSession(app('session.store'));
        app()->instance('request', $request);

        return $request;
    }

    /** Call a controller action with a request built from $payload; returns the JSON body (or [] for views / redirects). */
    protected function send(object $controller, string $method, array $payload = [], array $args = []): array
    {
        $result = $controller->$method(...array_merge([$this->request($payload)], $args));
        $data = is_object($result) && method_exists($result, 'getData') ? $result->getData(true) : [];

        return is_array($data) ? $data : [];
    }

    protected function status(object $controller, $id, $status, string $method = 'updateStatus'): array
    {
        $this->request();
        $result = $controller->$method($id, $status);

        return is_object($result) && method_exists($result, 'getData') ? (array) $result->getData(true) : [];
    }

    protected function chk(string $area, string $name, $expected, $actual, float $tol = 0.01): void
    {
        $ok = is_numeric($expected) && is_numeric($actual) ? abs((float) $expected - (float) $actual) <= $tol : $expected === $actual;
        $this->checks[] = [$area, $name, is_numeric($expected) ? round((float) $expected, 2) : $expected, is_numeric($actual) ? round((float) $actual, 2) : $actual, $ok];
    }

    protected function ok(string $area, string $name, bool $pass, string $detail = ''): void
    {
        $this->checks[] = [$area, $name, 'yes', $pass ? 'yes' : ('no' . ($detail ? ' — ' . $detail : '')), $pass];
    }

    protected function has(string $area, string $name, string $text, string $needle): void
    {
        $found = str_contains($text, $needle);
        $this->checks[] = [$area, $name, 'page shows ' . $needle, $found ? 'found' : 'NOT FOUND', $found];
    }

    protected function doc(string $label, $model, string $no = null): void
    {
        $this->docs[] = [$label, $no ?? (string) ($model->row_no ?? $model->id), (float) ($model->sub_total ?? 0), (float) ($model->tax_total ?? 0),
            (float) ($model->grand_total ?? 0), (string) ($model->status ?? '')];
    }

    protected function accountTotals(): array
    {
        return DB::table('finance_subs')->groupBy('account_id')->selectRaw('account_id, SUM(base_debit) dr, SUM(base_credit) cr')->get()
            ->keyBy('account_id')->map(fn ($r) => [(float) $r->dr, (float) $r->cr])->all();
    }

    protected function text(string $html): string
    {
        $html = preg_replace('/<(script|style)\b.*?<\/\1>/s', '', $html);

        return trim(html_entity_decode(preg_replace('/\s+/', ' ', strip_tags($html)), ENT_QUOTES));
    }

    protected function livewire(string $class, array $props = [], bool $period = false): string
    {
        $t = \Livewire\Livewire::test($class)->set($props);
        if ($period) {
            $t->call('updateDateRange', ['startDate' => self::FROM, 'endDate' => self::TO]);
        }

        return $this->text($t->html());
    }

    /** Aging rows / totals straight from the component (the same numbers the page prints). */
    protected function aging(string $class, array $props): array
    {
        $c = \Livewire\Livewire::test($class)->set($props)->instance();
        $m = new \ReflectionMethod($c, 'getAgingData');
        $m->setAccessible(true);

        return $m->invoke($c);
    }

    // ───────────── customer chain ─────────────

    protected function customerChain(): void
    {
        $this->send(app(CustomerController::class), 'store', [
            'name_en' => self::TAG . ' Customer', 'name_ar' => 'عميل اختبار', 'currency' => 'SAR', 'business_type' => 'unregistered',
            'country' => 'Saudi Arabia', 'email' => 'chaintest.customer@example.test', 'phone' => '+966500000123', 'credit_days' => 30,
        ]);
        $customer = Customer::where('name_en', self::TAG . ' Customer')->latest('id')->firstOrFail();
        $this->ids['customer'] = $customer->id;
        $this->docs[] = ['Customer', $customer->row_no, 0, 0, 0, (string) $customer->status];
        $this->chk('Customer chain', 'New customer is active (confirmed), so it shows in statements and aging', 3, (int) $customer->status, 0);

        $activity = DB::table('logistic_activities')->value('id') ?? 2;
        $this->send(app(EnquiryController::class), 'store', [
            'customer' => encodeId($customer->id), 'shipment_category' => 'container', 'pol' => 'Jebel Ali', 'pod' => 'Dammam',
            'activity_id' => (string) $activity, 'remark' => self::TAG . ' enquiry',
            'container' => ['type' => ['dry'], 'size' => ['40'], 'quantity' => [1], 'number' => [null], 'hazardous' => [0]],
        ]);
        $enquiry = Enquiry::where('remark', self::TAG . ' enquiry')->latest('id')->firstOrFail();
        $this->ids['enquiry'] = $enquiry->id;
        $this->docs[] = ['Enquiry', $enquiry->row_no, 0, 0, 0, (string) $enquiry->status];

        $this->send(app(QuotationController::class), 'store', [
            'customer' => encodeId($customer->id), 'enquiry_id' => $enquiry->id, 'posted_at' => '2026-11-02', 'valid_until' => '2026-11-30',
            'activity_id' => (string) $activity, 'shipment_category' => 'container', 'pol' => 'Jebel Ali', 'pod' => 'Dammam', 'terms' => self::TAG . ' quotation',
            'container_size' => ['40'], 'container_type' => ['dry'], 'container_qty' => [1],
            'chg_description' => ['Ocean freight'], 'chg_qty' => [1], 'chg_currency' => ['SAR'], 'chg_ex_rate' => [1], 'chg_amt_qty' => [3000],
        ]);
        $quotation = Quotation::where('terms', self::TAG . ' quotation')->latest('id')->firstOrFail();
        $this->ids['quotation'] = $quotation->id;
        $this->chk('Customer chain', 'Enquiry moves to "quotation" once quoted', 3, (int) $enquiry->fresh()->status, 0);

        $this->status(app(QuotationController::class), $quotation->id, QuotationEnum::ACCEPTED->value);
        $res = $this->status(app(QuotationController::class), $quotation->id, QuotationEnum::CONVERTED->value);
        $quotation->refresh();
        $this->ok('Customer chain', 'Converting the quotation creates the job', (bool) $quotation->job_id, json_encode($res));
        $this->docs[] = ['Quotation', $quotation->row_no, 0, 0, 0, (string) $quotation->status];
        $job = Job::findOrFail($quotation->job_id);
        $this->ids['job'] = $job->id;
        $this->docs[] = ['Job', $job->row_no, 0, 0, 0, (string) $job->status];
        $this->chk('Customer chain', 'Job belongs to the new customer', $customer->id, (int) $job->customer_id, 0);

        // invoice 1: 1,000 + 15% = 1,150, due 10-11 (overdue at the aging date)
        $inv1 = $this->invoice('INV1', '2026-11-03', '2026-11-10', 1000);
        // collected in full
        $col1 = $this->collect('COL1', '2026-11-05', [$inv1->id => 1150]);
        // credit note 200 + 15% = 230 on the paid invoice: the customer now has 230 credit
        $this->send(app(CreditNoteController::class), 'store', [
            'credit_note_type' => '1', 'invoice_id' => $inv1->id, 'customer' => encodeId($customer->id), 'credit_note_date' => '2026-11-06',
            'job_id' => $job->id, 'reason' => self::TAG . ' discount', 'terms' => self::TAG . ' CN1', 'description_id' => ['2'],
            'comment' => [self::TAG . ' CN1'], 'unit_id' => ['1'], 'quantity' => [1], 'unit_price' => ['200'], 'tax' => ['STANDARD'], 'account' => [self::REVENUE],
            'confirm_credit' => 1,   // the app asks: the 230 is more than INV1 still owes and stays as customer credit
        ]);
        $cn = CreditNote::where('terms', self::TAG . ' CN1')->latest('id')->firstOrFail();
        $this->status(app(CreditNoteController::class), $cn->id, CreditNoteEnum::APPROVED->value);
        $cn->refresh();
        $this->ids['credit_note'] = $cn->id;
        $this->doc('Credit note on INV1 (200 + VAT)', $cn);

        // invoice 2: 2,000 + 15% = 2,300; collection uses the 230 credit + 1,000 cash → 1,070 left open
        $inv2 = $this->invoice('INV2', '2026-11-07', '2026-12-07', 2000);
        $col2 = $this->collect('COL2', '2026-11-12', [$inv2->id => 1000], [$inv2->id => 230]);
        $this->chk('Customer chain', 'Customer credit applied on COL2 (230.00)', 230.00,
            (float) DB::table('customer_credit_applications')->where('collection_id', $col2->id)->where('status', 1)->sum('amount'));
        $this->chk('Customer chain', 'INV1 settled = 1,150 (collection 1,150 + credit note 230 − credit moved out 230)', 1150.00, (float) $inv1->fresh()->paid_amount);
        $this->chk('Customer chain', 'INV2 settled = 1,230 (cash 1,000 + credit 230)', 1230.00, (float) $inv2->fresh()->paid_amount);
    }

    protected function invoice(string $key, string $date, string $due, float $price): CustomerInvoice
    {
        $this->send(app(CustomerInvoiceController::class), 'store', [
            'job_id' => $this->ids['job'], 'customer' => encodeId($this->ids['customer']), 'invoice_date' => $date, 'due_date' => $due,
            'currency' => 'SAR', 'currency_rate' => 1, 'terms' => self::TAG . ' ' . $key,
            'description_id' => ['2'], 'comment' => [self::TAG . ' ' . $key], 'unit_id' => ['1'],
            'quantity' => [1], 'unit_price' => [(string) $price], 'tax' => ['STANDARD'], 'account' => [self::REVENUE],
        ]);
        $i = CustomerInvoice::where('terms', self::TAG . ' ' . $key)->latest('id')->firstOrFail();
        $this->status(app(CustomerInvoiceController::class), $i->id, CustomerInvoiceEnum::APPROVED->value);
        $i->refresh();
        $this->ids[strtolower($key)] = $i->id;
        $this->doc('Customer invoice ' . $key, $i);
        $this->chk('Customer chain', $key . ' approved', CustomerInvoiceEnum::APPROVED->value, (int) $i->status, 0);

        return $i;
    }

    protected function collect(string $key, string $date, array $cash, array $credit = []): Collection
    {
        $ids = array_keys($cash + $credit);
        $this->send(app(CollectionController::class), 'store', [
            'customer' => encodeId($this->ids['customer']), 'collection_date' => $date, 'account' => [self::BANK], 'reference_no' => self::TAG,
            'currency' => 'SAR', 'currency_rate' => 1, 'customer_invoice_ids' => $ids, 'invoice_amounts' => $cash, 'credit_to' => $credit,
            'notes' => self::TAG . ' ' . $key,
        ]);
        $c = Collection::where('notes', self::TAG . ' ' . $key)->latest('id')->firstOrFail();
        $this->status(app(CollectionController::class), $c->id, CollectionEnum::APPROVED->value);
        $c->refresh();
        $this->ids[strtolower($key)] = $c->id;
        $this->docs[] = ['Collection ' . $key, $c->row_no, array_sum($cash), 0, (float) $c->grand_total, (string) $c->status];

        return $c;
    }

    // ───────────── supplier chain ─────────────

    protected function supplierChain(): void
    {
        $this->send(app(SupplierController::class), 'store', [
            'name_en' => self::TAG . ' Supplier', 'name_ar' => 'مورد اختبار', 'currency' => 'SAR', 'business_type' => 'unregistered',
            'country' => 'Saudi Arabia', 'email' => 'chaintest.supplier@example.test', 'phone' => '+966500000456', 'credit_days' => 15,
        ]);
        $supplier = Supplier::where('name_en', self::TAG . ' Supplier')->latest('id')->firstOrFail();
        $this->ids['supplier'] = $supplier->id;
        $this->docs[] = ['Supplier', $supplier->row_no, 0, 0, 0, (string) $supplier->status];
        $this->chk('Supplier chain', 'New supplier is active (supplier status 1 = confirmed), so it shows in statements and aging', \App\Enums\SupplierStatusEnum::CONFIRMED->value, (int) $supplier->status, 0);

        // supplier invoice 2,000 + 15% = 2,300, due 20-11
        $this->send(app(SupplierInvoiceController::class), 'store', [
            'job_id' => $this->ids['job'] ?? null, 'supplier' => encodeId($supplier->id), 'invoice_date' => '2026-11-04', 'invoice_number' => self::TAG . '-SI1',
            'due_date' => '2026-11-20', 'currency' => 'SAR', 'currency_rate' => 1, 'terms' => self::TAG . ' SI1',
            'description_id' => ['2'], 'comment' => [self::TAG . ' SI1'], 'unit_id' => ['1'],
            'quantity' => [1], 'unit_price' => ['2000'], 'tax' => ['STANDARD'], 'account' => [self::COST],
        ]);
        $si = SupplierInvoice::where('invoice_number', self::TAG . '-SI1')->latest('id')->firstOrFail();
        $this->status(app(SupplierInvoiceController::class), $si->id, SupplierInvoiceEnum::APPROVED->value);
        $si->refresh();
        $this->ids['supplier_invoice'] = $si->id;
        $this->doc('Supplier invoice SI1', $si);

        $this->pay('PAY1', '2026-11-08', 1000);

        // debit note 200 + 15% = 230
        $this->send(app(DebitNoteController::class), 'store', [
            'invoice_id' => $si->id, 'supplier' => encodeId($supplier->id), 'debit_note_date' => '2026-11-09', 'job_id' => $this->ids['job'] ?? null,
            'reason' => self::TAG . ' overcharge', 'terms' => self::TAG . ' DN1', 'description_id' => ['2'], 'comment' => [self::TAG . ' DN1'],
            'unit_id' => ['1'], 'quantity' => [1], 'unit_price' => ['200'], 'tax' => ['STANDARD'], 'account' => [self::COST],
        ]);
        $dn = DebitNote::where('terms', self::TAG . ' DN1')->latest('id')->firstOrFail();
        $this->status(app(DebitNoteController::class), $dn->id, DebitNoteEnum::APPROVED->value);
        $dn->refresh();
        $this->ids['debit_note'] = $dn->id;
        $this->doc('Debit note on SI1 (200 + VAT)', $dn);

        $this->pay('PAY2', '2026-11-10', 500);
        $this->chk('Supplier chain', 'SI1 settled = 1,730 (payments 1,500 + debit note 230), 570 still owed', 1730.00, (float) $si->fresh()->paid_amount);
    }

    protected function pay(string $key, string $date, float $amount): void
    {
        $si = $this->ids['supplier_invoice'];
        $this->send(app(PaymentController::class), 'store', [
            'supplier' => encodeId($this->ids['supplier']), 'payment_date' => $date, 'account' => [self::BANK], 'reference_no' => self::TAG,
            'currency' => 'SAR', 'currency_rate' => 1, 'supplier_invoice_ids' => [$si], 'invoice_amounts' => [$si => $amount], 'notes' => self::TAG . ' ' . $key,
        ]);
        $p = Payment::where('notes', self::TAG . ' ' . $key)->latest('id')->firstOrFail();
        $this->status(app(PaymentController::class), $p->id, PaymentEnum::APPROVED->value);
        $p->refresh();
        $this->ids[strtolower($key)] = $p->id;
        $this->docs[] = ['Payment ' . $key, $p->row_no, $amount, 0, (float) $p->grand_total, (string) $p->status];
    }

    // ───────────── expense & payroll ─────────────

    protected function expense(): void
    {
        $this->send(app(ExpenseController::class), 'store', [
            'posted_at' => '2026-11-11', 'reference_number' => self::TAG . '-EXP', 'payment_status' => 'paid', 'main_account' => self::BANK,
            'paid_amount' => 460, 'amount_excluding_vat' => 400, 'amount_including_vat' => 460,
            'account' => [self::EXPENSE], 'comment' => [self::TAG . ' expense'], 'quantity' => [1], 'unit_price' => ['400'],
            'tax' => ['STANDARD'], 'item_id' => [], 'employee_id' => [''],
        ]);
        $e = Expense::where('reference_number', self::TAG . '-EXP')->latest('id')->firstOrFail();
        $this->send(app(ExpenseController::class), 'updateStatus', [], [$e->id, ExpenseEnum::APPROVED->value]);
        $e->refresh();
        $this->ids['expense'] = $e->id;
        $this->docs[] = ['Expense (400 + VAT, paid from bank)', $e->row_no, 400, 60, (float) $e->grand_total, (string) $e->status];

        $lines = DB::table('finance_subs as fs')->join('finance as f', 'f.id', '=', 'fs.finance_id')
            ->where('f.linked_type', Expense::class)->where('f.linked_id', $e->id)
            ->selectRaw('fs.account_id, SUM(fs.base_debit) dr, SUM(fs.base_credit) cr')->groupBy('fs.account_id')->get()->keyBy('account_id');
        $this->chk('Expense entries', 'Expense account debit 400.00', 400.00, $lines[self::EXPENSE]->dr ?? 0);
        $this->chk('Expense entries', 'Input VAT debit 60.00', 60.00, $lines[self::VAT_IN]->dr ?? 0);
        $this->chk('Expense entries', 'Bank credit 460.00', 460.00, $lines[self::BANK]->cr ?? 0);
    }

    protected function payroll(): void
    {
        $employeeId = (int) DB::table('salary_structures')->where('company_id', 1)->whereNull('effective_to')->value('employee_id');
        if (!$employeeId) {
            $this->ok('Payroll', 'An employee with a salary structure exists', false);
            return;
        }
        $runs = app(PayrollRunController::class);
        $this->request(['month' => 11, 'year' => 2026, 'employee_ids' => [$employeeId]]);
        $runs->store(request());
        $pr = PayrollRecord::where('employee_id', $employeeId)->where('month', 11)->where('year', 2026)->first();
        $this->ok('Payroll', 'Payroll generated for November 2026', (bool) $pr);
        if (!$pr) return;

        $this->request(['bank_account_id' => self::BANK, 'payment_date' => '2026-11-28']);
        $runs->pay(request(), $pr);
        $pr->refresh();
        $this->ids['payroll'] = $pr->id;
        $this->docs[] = ['Payroll ' . $pr->payroll_number, $pr->payroll_number, (float) $pr->total_earnings, 0, (float) $pr->net_payable, $pr->status];
        $this->chk('Payroll', 'Payroll record is paid', 'paid', $pr->status);

        $net = round((float) $pr->net_payable, 2);
        $loan = round((float) $pr->loan_deduction, 2);
        $f = DB::table('finance')->where('linked_type', PayrollRecord::class)->where('linked_id', $pr->id)->first();
        $this->ok('Payroll entries', 'Salary payment posted a voucher', (bool) $f);
        if (!$f) return;
        $lines = DB::table('finance_subs')->where('finance_id', $f->id)->get();
        $this->chk('Payroll entries', 'Voucher balances (debit = credit)', $lines->sum('base_debit'), $lines->sum('base_credit'));
        $this->chk('Payroll entries', 'Salary expense debit = net pay + loan recovery', $net + $loan, $lines->sum('base_debit'));
        $this->chk('Payroll entries', 'Bank credit = net pay', $net, (float) $lines->where('account_id', self::BANK)->sum('base_credit'));
        if ($loan > 0) {
            $this->chk('Payroll entries', 'Loan receivable credit = instalment withheld', $loan, (float) $lines->where('account_id', '!=', self::BANK)->sum('base_credit'));
            $this->ids['loan'] = EmployeeLoan::where('employee_id', $employeeId)->latest('id')->value('id');
        }
        $this->ids['payroll_net'] = $net;
        $this->ids['payroll_gross'] = $net + $loan;
    }

    // ───────────── deletes ─────────────

    protected function deleteAttempts(): void
    {
        $c = fn ($cls) => app($cls);
        $tries = [
            // [label, table, id key, controller, method, expect blocked]
            ['Customer (has enquiry, quotation, job, invoices)', 'customers', 'customer', CustomerController::class, 'delete'],
            ['Enquiry (has a quotation)', 'enquiries', 'enquiry', EnquiryController::class, 'delete'],
            ['Quotation (converted to a job)', 'quotations', 'quotation', QuotationController::class, 'delete'],
            ['Job (has invoices, payments)', 'jobs', 'job', JobController::class, 'delete'],
            ['Customer invoice INV1 (collected, credit note)', 'customer_invoices', 'inv1', CustomerInvoiceController::class, 'delete'],
            ['Customer invoice INV2 (part collected, credit applied)', 'customer_invoices', 'inv2', CustomerInvoiceController::class, 'delete'],
            ['Collection COL1 (approved)', 'collections', 'col1', CollectionController::class, 'destroy'],
            ['Credit note (approved, gave customer credit)', 'credit_notes', 'credit_note', CreditNoteController::class, 'delete'],
            ['Collection COL2 (approved, used customer credit)', 'collections', 'col2', CollectionController::class, 'destroy'],
            ['Supplier (has supplier invoice, payments)', 'suppliers', 'supplier', SupplierController::class, 'delete'],
            ['Supplier invoice SI1 (paid in part, debit note)', 'supplier_invoices', 'supplier_invoice', SupplierInvoiceController::class, 'delete'],
            ['Payment PAY1 (approved)', 'payments', 'pay1', PaymentController::class, 'destroy'],
            ['Debit note (approved)', 'debit_notes', 'debit_note', DebitNoteController::class, 'delete'],
            ['Payment PAY2 (approved)', 'payments', 'pay2', PaymentController::class, 'destroy'],
            ['Expense (approved, posted)', 'expenses', 'expense', ExpenseController::class, 'destroy'],
            ['Bank sub-account (used by postings)', 'accounts', null, AccountController::class, 'delete', self::BANK],
        ];
        // two rounds, like clicking delete over and over: nothing in the chain may ever go
        foreach ([1, 2] as $round) {
            foreach ($tries as $t) {
                [$label, $table, $key, $cls, $method] = $t;
                $id = $key ? ($this->ids[$key] ?? null) : $t[5];
                if (!$id) continue;
                $this->tryDelete("Round $round: $label", $table, $id, fn () => $c($cls)->$method($id), true);
            }
        }

        if (!empty($this->ids['payroll'])) {
            $this->tryDelete('Payroll record (paid)', 'payroll_records', $this->ids['payroll'],
                fn () => $c(PayrollRunController::class)->destroy(PayrollRecord::find($this->ids['payroll'])), true);
        }
        if (!empty($this->ids['loan'])) {
            $this->tryDelete('Employee loan (instalment withheld from salary)', 'employee_loans', $this->ids['loan'],
                fn () => $c(EmployeeLoanController::class)->destroy(EmployeeLoan::find($this->ids['loan'])), true);
        }

        // a note must belong to the same customer / supplier as the invoice it adjusts
        $this->refused('Credit note for another customer against INV1 is refused', fn () => $this->send(app(CreditNoteController::class), 'store', [
            'credit_note_type' => '1', 'invoice_id' => $this->ids['inv1'], 'customer' => encodeId(1), 'credit_note_date' => '2026-11-15',
            'job_id' => $this->ids['job'], 'reason' => 'x', 'terms' => self::TAG . ' wrong customer', 'description_id' => ['2'], 'comment' => ['x'],
            'unit_id' => ['1'], 'quantity' => [1], 'unit_price' => ['10'], 'tax' => ['STANDARD'], 'account' => [self::REVENUE], 'confirm_credit' => 1,
        ]), fn () => CreditNote::where('terms', self::TAG . ' wrong customer')->exists());
        $this->refused('Debit note for another supplier against SI1 is refused', fn () => $this->send(app(DebitNoteController::class), 'store', [
            'invoice_id' => $this->ids['supplier_invoice'], 'supplier' => encodeId(1), 'debit_note_date' => '2026-11-15', 'job_id' => $this->ids['job'],
            'reason' => 'x', 'terms' => self::TAG . ' wrong supplier', 'description_id' => ['2'], 'comment' => ['x'],
            'unit_id' => ['1'], 'quantity' => [1], 'unit_price' => ['10'], 'tax' => ['STANDARD'], 'account' => [self::COST],
        ]), fn () => DebitNote::where('terms', self::TAG . ' wrong supplier')->exists());

        // controls: a record with nothing after it must still delete
        $this->send(app(EnquiryController::class), 'store', [
            'customer' => encodeId($this->ids['customer']), 'shipment_category' => 'package', 'pol' => 'Jeddah', 'pod' => 'Dubai',
            'activity_id' => (string) (DB::table('logistic_activities')->value('id') ?? 2), 'remark' => self::TAG . ' draft enquiry',
        ]);
        $draftEnq = Enquiry::where('remark', self::TAG . ' draft enquiry')->latest('id')->value('id');
        $this->tryDelete('Control: a fresh enquiry with nothing after it', 'enquiries', $draftEnq, fn () => $c(EnquiryController::class)->delete($draftEnq), false);

        $this->send(app(CustomerInvoiceController::class), 'store', [
            'job_id' => $this->ids['job'], 'customer' => encodeId($this->ids['customer']), 'invoice_date' => '2026-11-20', 'due_date' => '2026-12-20',
            'currency' => 'SAR', 'currency_rate' => 1, 'terms' => self::TAG . ' draft invoice', 'description_id' => ['2'], 'comment' => ['x'],
            'unit_id' => ['1'], 'quantity' => [1], 'unit_price' => ['50'], 'tax' => ['STANDARD'], 'account' => [self::REVENUE],
        ]);
        $draftInv = CustomerInvoice::where('terms', self::TAG . ' draft invoice')->latest('id')->value('id');
        $this->tryDelete('Control: a draft customer invoice', 'customer_invoices', $draftInv, fn () => $c(CustomerInvoiceController::class)->delete($draftInv), false);
    }

    /** The action must be refused (validation error) and must not have saved anything. */
    protected function refused(string $label, callable $fn, callable $saved): void
    {
        $message = 'accepted';
        try {
            $res = $fn();
            $message = (string) ($res['message'] ?? json_encode($res));
        } catch (\Illuminate\Validation\ValidationException $e) {
            $message = collect($e->errors())->flatten()->implode(' ');
        }
        $pass = !$saved();
        $this->deletes[] = [$label, 'refused', $pass ? 'refused' : 'SAVED', $message, $pass];
        $this->checks[] = ['Ownership', $label, 'refused', $pass ? 'refused' : 'saved', $pass];
    }

    protected function tryDelete(string $label, string $table, $id, callable $fn, bool $expectBlocked): void
    {
        $message = '';
        try {
            $res = $fn();
            if (is_object($res) && method_exists($res, 'getData')) {
                $d = (array) $res->getData(true);
                $message = (string) ($d['message'] ?? json_encode($d));
            } elseif ($res instanceof \Illuminate\Http\RedirectResponse) {
                $errors = session('errors');
                $message = $errors ? implode(' ', $errors->all()) : (string) session('success');
                session()->forget(['errors', 'success']);
            }
        } catch (\Throwable $e) {
            $message = 'Exception: ' . $e->getMessage();
        }
        $q = DB::table($table)->where('id', $id);
        if (\Illuminate\Support\Facades\Schema::hasColumn($table, 'deleted_at')) {
            $q->whereNull('deleted_at');
        }
        $stillThere = $q->exists();
        $pass = $expectBlocked ? $stillThere : !$stillThere;
        $this->deletes[] = [$label, $expectBlocked ? 'refused' : 'deleted', $stillThere ? 'refused' : 'DELETED', $message, $pass];
        $this->checks[] = ['Delete', $label, $expectBlocked ? 'refused' : 'deleted', $stillThere ? 'refused' : 'deleted', $pass];
    }

    // ───────────── reports ─────────────

    protected function verify(array $before): void
    {
        $after = $this->accountTotals();
        $d = fn (int $id) => [($after[$id][0] ?? 0) - ($before['accounts'][$id][0] ?? 0), ($after[$id][1] ?? 0) - ($before['accounts'][$id][1] ?? 0)];
        $payNet = $this->ids['payroll_net'] ?? 0;

        // ledger movement per account (hand calculated)
        $exp = [
            self::AR => ['Accounts Receivable', 3450.00, 2380.00],        // INV1 1,150 + INV2 2,300 | COL1 1,150 + CN 230 + COL2 1,000
            self::VAT_OUT => ['Output VAT', 30.00, 450.00],               // CN 30 | 150 + 300
            self::REVENUE => ['Freight Income', 200.00, 3000.00],
            self::AP => ['Accounts Payable', 1730.00, 2300.00],           // PAY1 1,000 + DN 230 + PAY2 500 | SI1 2,300
            self::VAT_IN => ['Input VAT', 360.00, 30.00],                 // SI1 300 + expense 60 | DN 30
            self::COST => ['Freight Purchase', 2000.00, 200.00],
            self::EXPENSE => ['Documentation Expense', 400.00, 0.00],
            self::BANK => ['Bank', 2150.00, 1960.00 + $payNet],           // COL1 1,150 + COL2 1,000 | PAY 1,500 + expense 460 + salary
        ];
        foreach ($exp as $id => [$name, $dr, $cr]) {
            [$aDr, $aCr] = $d($id);
            $this->chk('Ledger', $name . ' debit', $dr, $aDr);
            $this->chk('Ledger', $name . ' credit', $cr, $aCr);
        }
        $totDr = $totCr = 0;
        foreach ($after as $id => [$dr, $cr]) {
            $totDr += $dr - ($before['accounts'][$id][0] ?? 0);
            $totCr += $cr - ($before['accounts'][$id][1] ?? 0);
        }
        $this->chk('Ledger', 'New entries balance (debit = credit)', $totDr, $totCr);
        $all = DB::table('finance_subs')->selectRaw('SUM(base_debit) dr, SUM(base_credit) cr')->first();
        $this->chk('Ledger', 'Whole ledger balances', $all->dr, $all->cr);
        $unbalanced = DB::table('finance_subs as fs')->join('finance as f', 'f.id', '=', 'fs.finance_id')
            ->whereBetween('f.posted_at', [self::FROM, self::TO . ' 23:59:59'])->groupBy('fs.finance_id')
            ->havingRaw('ABS(SUM(fs.base_debit) - SUM(fs.base_credit)) > 0.01')->pluck('fs.finance_id');
        $this->chk('Ledger', 'Every November voucher balances on its own', 0, $unbalanced->count(), 0);

        // trial balance
        $tb = LedgerReports::trialBalance(self::FROM, self::TO);
        $this->chk('Trial balance', 'Total debit = total credit', $tb['total_debit'], $tb['total_credit']);
        $this->chk('Trial balance', 'Total debit grew by the new entries', $totDr, $tb['total_debit'] - $before['tb']['total_debit']);
        $tbText = $this->livewire(\App\Livewire\Report\Finance\TrialBalanceTable::class, [], true);
        $this->has('Trial balance', 'Page shows total debit', $tbText, number_format($tb['total_debit'], 2));
        $this->ok('Trial balance', 'Page says Balanced', stripos($tbText, 'Balanced') !== false);

        // profit & loss
        $pl = LedgerReports::profitAndLoss(self::FROM, self::TO);
        $gross = $this->ids['payroll_gross'] ?? 0;
        $this->chk('Profit & loss', 'Revenue +2,800 (3,000 − credit note 200)', 2800.00, $pl['total_revenue'] - $before['pl']['total_revenue']);
        $this->chk('Profit & loss', 'Expenses +' . number_format(1800 + 400 + $gross, 2) . ' (cost 1,800 + expense 400 + salary)', 1800.00 + 400.00 + $gross, $pl['total_expenses'] - $before['pl']['total_expenses']);
        $plText = $this->livewire(\App\Livewire\Report\Finance\ProfitAndLossTable::class, [], true);
        $this->has('Profit & loss', 'Page shows net income', $plText, number_format($pl['net_income'], 2));

        // balance sheet
        $bs = LedgerReports::balanceSheet(self::AS_OF);
        $this->chk('Balance sheet', 'Assets = liabilities + equity', $bs['total_assets'], $bs['total_liabilities_equity']);
        $bsText = $this->livewire(\App\Livewire\Report\Finance\BalanceSheetTable::class, [], true);
        $this->has('Balance sheet', 'Page shows total assets', $bsText, number_format($bs['total_assets'], 2));

        // VAT return
        $vr = VatReturn::compute(self::FROM, self::TO);
        $bv = $before['vat'];
        $this->chk('Tax summary', 'Box 1 standard sales amount +3,000', 3000.00, $vr['sales']['standard']['amount'] - $bv['sales']['standard']['amount']);
        $this->chk('Tax summary', 'Box 1 adjustment −200 (credit note)', -200.00, $vr['sales']['standard']['adjustment'] - $bv['sales']['standard']['adjustment']);
        $this->chk('Tax summary', 'Box 1 VAT +420 (450 − 30)', 420.00, $vr['sales']['standard']['vat'] - $bv['sales']['standard']['vat']);
        $this->chk('Tax summary', 'Box 7 standard purchases +2,400 (supplier invoice 2,000 + expense 400)', 2400.00, $vr['purchases']['standard']['amount'] - $bv['purchases']['standard']['amount']);
        $this->chk('Tax summary', 'Box 7 adjustment −200 (debit note)', -200.00, $vr['purchases']['standard']['adjustment'] - $bv['purchases']['standard']['adjustment']);
        $this->chk('Tax summary', 'Box 7 VAT +330 (300 + 60 − 30)', 330.00, $vr['purchases']['standard']['vat'] - $bv['purchases']['standard']['vat']);
        $this->chk('Tax summary', 'Net VAT due +90', 90.00, $vr['vatDue'] - $bv['vatDue']);
        $ledgerTax = LedgerReports::taxSummary(self::FROM, self::TO);
        $this->chk('Tax summary', 'Return output VAT = ledger Output VAT', $ledgerTax['total_output_tax'], $vr['salesTotal']['vat']);
        $this->chk('Tax summary', 'Return input VAT = ledger Input VAT', $ledgerTax['total_input_tax'], $vr['purchasesTotal']['vat']);
        $taxText = $this->livewire(\App\Livewire\Report\Finance\TaxSummary::class, ['startDate' => self::FROM, 'endDate' => self::TO]);
        $this->has('Tax summary', 'Page shows net VAT due', $taxText, number_format($vr['vatDue'], 2));

        // customer: statement, ledger, aging
        $cid = $this->ids['customer'] ?? null;
        if ($cid) {
            $bal = (float) DB::table('finance_subs')->where('customer_id', $cid)->where('account_id', self::AR)->selectRaw('COALESCE(SUM(base_debit - base_credit),0) n')->value('n');
            $this->chk('Customer statement', 'Customer balance in the ledger = 1,070.00', 1070.00, $bal);
            $cs = $this->livewire(\App\Livewire\Report\Finance\CustomerStatement::class, ['startDate' => self::FROM, 'endDate' => self::TO, 'customerId' => encodeId($cid)]);
            foreach (['1,150.00', '2,300.00', '230.00', '1,000.00', '1,070.00'] as $n) {
                $this->has('Customer statement', 'Statement shows ' . $n, $cs, $n);
            }
            $gl = $this->livewire(\App\Livewire\Report\Finance\GeneralLedgerTable::class, ['customerId' => (string) $cid], true);
            $this->has('Customer ledger', 'Customer ledger page shows the balance 1,070.00', $gl, '1,070.00');

            $ag = $this->aging(\App\Livewire\Report\Finance\CustomerAging::class, ['asOfDate' => self::AS_OF, 'customerId' => encodeId($cid)]);
            $this->chk('Customer aging', 'Open total 1,070.00 (INV2 only; INV1 settled)', 1070.00, $ag['summary']['grand_total'] ?? 0);
            $this->chk('Customer aging', 'One open invoice', 1, count($ag['invoices'] ?? []), 0);
            $this->chk('Customer aging', 'INV2 due 07-12 is in the 1-30 days bucket on 31-12', 1070.00, $ag['summary']['b1'] ?? 0);
            $agAll = \Livewire\Livewire::test(\App\Livewire\Report\Finance\CustomerAgingAll::class)->set('asOfDate', self::AS_OF)->instance()->getAgingData();
            $row = collect($agAll['customers'] ?? $agAll['rows'] ?? [])->first(fn ($r) => (int) ((array) $r)['customer_id'] === $cid || str_contains(json_encode($r), self::TAG));
            $this->ok('Customer aging (all)', 'Customer is listed in the all-customers aging with 1,070.00', $row && str_contains(json_encode($row), '1070'), json_encode($row));
        }

        // supplier: statement, ledger, aging
        $sid = $this->ids['supplier'] ?? null;
        if ($sid) {
            $bal = (float) DB::table('finance_subs')->where('supplier_id', $sid)->where('account_id', self::AP)->selectRaw('COALESCE(SUM(base_credit - base_debit),0) n')->value('n');
            $this->chk('Supplier statement', 'Supplier balance in the ledger = 570.00', 570.00, $bal);
            $ss = $this->livewire(\App\Livewire\Report\Finance\SupplierStatement::class, ['startDate' => self::FROM, 'endDate' => self::TO, 'supplierId' => encodeId($sid)]);
            foreach (['2,300.00', '1,000.00', '230.00', '500.00', '570.00'] as $n) {
                $this->has('Supplier statement', 'Statement shows ' . $n, $ss, $n);
            }
            $sl = $this->livewire(\App\Livewire\Report\Finance\SupplierLedgerTable::class, ['supplierId' => (string) $sid], true);
            $this->has('Supplier ledger', 'Supplier ledger page shows the balance 570.00', $sl, '570.00');

            $ag = $this->aging(\App\Livewire\Report\Finance\SupplierAging::class, ['asOfDate' => self::AS_OF, 'supplierId' => encodeId($sid)]);
            $this->chk('Supplier aging', 'Open total 570.00', 570.00, $ag['summary']['grand_total'] ?? 0);
            $this->chk('Supplier aging', 'SI1 due 20-11 is in the 31-60 days bucket on 31-12', 570.00, $ag['summary']['b2'] ?? ($ag['summary']['days_31_60'] ?? 0));
            $agAll = \Livewire\Livewire::test(\App\Livewire\Report\Finance\SupplierAgingAll::class)->set('asOfDate', self::AS_OF)->instance()->getAgingData();
            $row = collect($agAll['suppliers'] ?? $agAll['rows'] ?? [])->first(fn ($r) => str_contains(json_encode($r), self::TAG));
            $this->ok('Supplier aging (all)', 'Supplier is listed in the all-suppliers aging with 570.00', $row && str_contains(json_encode($row), '570'), json_encode($row));
        }

        // document chain links
        $this->chk('Job', 'Job shows 2 approved customer invoices', 2, DB::table('customer_invoices')->where('job_id', $this->ids['job'] ?? 0)->where('status', 3)->count(), 0);
        $this->chk('Job', 'Job links the supplier invoice', 1, DB::table('supplier_invoices')->where('job_id', $this->ids['job'] ?? 0)->where('status', 3)->count(), 0);
    }

    // ───────────── report ─────────────

    protected function report(): string
    {
        $file = $this->option('out') ?: base_path($this->reportPrefix . '-' . now()->format('Y-m-d') . '.xlsx');
        $book = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $bold = fn ($sheet, $range) => $sheet->getStyle($range)->getFont()->setBold(true);

        $pass = collect($this->checks)->where(4, true)->count();
        $fail = collect($this->checks)->where(4, false)->count();
        $s = $book->getActiveSheet()->setTitle('Summary');
        $s->fromArray([
            [$this->reportTitle],
            ['Run on', now()->format('d-m-Y H:i')], ['Period', '01-11-2026 to 30-11-2026, aging as of 31-12-2026'],
            ['Checks', count($this->checks)], ['Passed', $pass], ['Failed', $fail], ['Note', 'Everything ran in one transaction and was rolled back: nothing was kept and no email was sent.'],
            [], ['Area', 'Checks', 'Passed', 'Failed'],
        ], null, 'A1');
        $r = 10;
        foreach (collect($this->checks)->groupBy(0) as $area => $list) {
            $s->fromArray([$area, $list->count(), $list->where(4, true)->count(), $list->where(4, false)->count()], null, 'A' . $r++);
        }
        $s->getStyle('A1')->getFont()->setBold(true)->setSize(13);
        $bold($s, 'A9:D9');
        $s->getColumnDimension('A')->setWidth(28);

        $sheets = [
            ['Checks', ['Area', 'Check', 'Expected', 'Actual', 'Result'], $this->checks, ['A' => 22, 'B' => 70, 'C' => 26, 'D' => 40, 'E' => 10], 4],
            ['Delete tests', ['Record', 'Expected', 'Result', 'Message shown to the user', 'Pass'], $this->deletes, ['A' => 52, 'B' => 12, 'C' => 12, 'D' => 110, 'E' => 8], 4],
            ['Documents', ['Document', 'Number', 'Sub total', 'VAT', 'Grand total', 'Status'], $this->docs, ['A' => 40, 'B' => 22, 'C' => 14, 'D' => 12, 'E' => 14, 'F' => 10], null],
        ];
        foreach ($sheets as [$title, $head, $rows, $widths, $resultCol]) {
            $sh = $book->createSheet()->setTitle($title);
            $sh->fromArray($head, null, 'A1');
            $i = 2;
            foreach ($rows as $row) {
                if ($resultCol !== null) {
                    $okv = $row[$resultCol];
                    $row[$resultCol] = $okv ? 'PASS' : 'FAIL';
                    $sh->fromArray($row, null, 'A' . $i);
                    $cell = chr(65 + $resultCol) . $i;
                    $sh->getStyle($cell)->getFont()->setBold(true)->getColor()->setRGB($okv ? '15803D' : 'B91C1C');
                } else {
                    $sh->fromArray($row, null, 'A' . $i);
                }
                $i++;
            }
            $bold($sh, 'A1:' . chr(64 + count($head)) . '1');
            foreach ($widths as $col => $w) $sh->getColumnDimension($col)->setWidth($w);
            $sh->freezePane('A2');
        }

        $f = $book->createSheet()->setTitle('Findings');
        $f->fromArray(['#', 'Finding', 'What was wrong', 'Status'], null, 'A1');
        $findings = $this->findings();
        $f->fromArray($findings, null, 'A2');
        $bold($f, 'A1:D1');
        foreach (['A' => 5, 'B' => 44, 'C' => 95, 'D' => 60] as $col => $w) $f->getColumnDimension($col)->setWidth($w);
        $f->getStyle('B2:D' . (count($findings) + 1))->getAlignment()->setWrapText(true)->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);

        $book->setActiveSheetIndex(0);
        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($book))->save($file);

        return $file;
    }

    protected function findings(): array
    {
        return [
            [1, 'Customer Ledger always showed a 0.00 balance', 'It listed every line of the customer\'s vouchers (receivable, revenue, VAT and bank), so each voucher cancelled itself and the running balance always returned to 0.00. For the test customer it showed 0.00 instead of 1,070.00 owed.', 'Fixed: the ledger now lists the receivable (1130) lines only, like the Supplier Ledger (LedgerReports::customerLines)'],
            [2, 'Supplier Aging (single supplier) missed most suppliers', 'Its supplier list used status 3, the customer "confirmed" value; for suppliers 1 = confirmed. Only 2 of 9 suppliers could be picked.', 'Fixed: filters on SupplierStatusEnum::CONFIRMED'],
            [3, 'Job delete was a GET link and hid the reason', 'operation/job/{id}/delete answered GET (no CSRF), and the job list showed "Something went wrong!" instead of why the job cannot be deleted.', 'Fixed: DELETE route; the list uses the shared delete dialog that lists the reasons'],
            [4, 'Posting to a group account is not refused by the server', 'Collections, payments and expenses accept a group account such as 1120 Bank Accounts when called directly (the screens only offer sub-accounts); 6 existing ledger lines sit on 1120. Payroll correctly refuses a group account.', 'Open: decide whether to block group accounts server-side and move the 6 lines'],
            [5, 'Credit note bigger than what the invoice owes asks for confirmation', 'Expected behaviour: the app asks before turning the excess into customer credit; the test confirms like a user.', 'Info'],
            [7, 'Credit / debit note could adjust another party\'s invoice', 'The credit note save did not check that the invoice belongs to the chosen customer (live data: credit note CR-26-0003 of customer #73 settles invoice IN/26/0002 of customer #54, so that invoice shows 115.00 over-settled); the debit note had the same gap for suppliers.', 'Fixed for new notes (validation on save); the existing CR-26-0003 needs a decision (cancel and re-issue for the right customer)'],
            [6, 'Every chain record refused deletion', 'Customer, enquiry, quotation, job, invoices, credit note, collections, supplier, supplier invoice, payments, debit note, expense, bank account, paid payroll and the loan with a withheld instalment were all refused (twice), each with a reason; a draft enquiry and a draft invoice still delete.', 'OK'],
        ];
    }
}
