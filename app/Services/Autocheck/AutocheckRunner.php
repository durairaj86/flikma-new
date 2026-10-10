<?php

namespace App\Services\Autocheck;

use App\Enums\CollectionEnum;
use App\Enums\CustomerInvoiceEnum;
use App\Enums\QuotationEnum;
use App\Enums\CreditNoteEnum;
use App\Enums\ExpenseEnum;
use App\Enums\PaymentEnum;
use App\Enums\SupplierInvoiceEnum;
use App\Http\Controllers\Customer\CustomerController;
use App\Http\Controllers\Finance\Adjustment\CreditNoteController;
use App\Http\Controllers\Finance\Expense\ExpenseController;
use App\Http\Controllers\Finance\Invoice\SupplierInvoiceController;
use App\Http\Controllers\Prospect\ProspectController;
use App\Http\Controllers\Supplier\SupplierController;
use App\Http\Controllers\Enquiry\EnquiryController;
use App\Http\Controllers\Finance\Invoice\CustomerInvoiceController;
use App\Http\Controllers\Quotation\QuotationController;
use App\Http\Controllers\Transaction\CollectionController;
use App\Http\Controllers\Transaction\PaymentController;
use App\Models\Customer\Customer;
use App\Models\Enquiry\Enquiry;
use App\Models\Finance\Collection\Collection;
use App\Models\Finance\CustomerInvoice\CustomerInvoice;
use App\Models\Finance\Adjustment\CreditNote;
use App\Models\Finance\Expense\Expense;
use App\Models\Finance\Payment\Payment;
use App\Models\Finance\SupplierInvoice\SupplierInvoice;
use App\Models\Job\Job;
use App\Models\Prospect\Prospect;
use App\Models\Supplier\Supplier;
use App\Models\Quotation\Quotation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * End-to-end business flow used by /autocheck: customer -> enquiry -> quotation -> job ->
 * customer invoice -> collection -> trial balance. Every step goes through the real controllers
 * (same validation and ledger posting as the screens) and returns a list of checks.
 */
class AutocheckRunner
{
    public const STEPS = [
        'prospect' => ['Create prospect', '/prospects'],
        'supplier' => ['Create supplier', '/suppliers'],
        'enquiry' => ['Create enquiry for the prospect', '/sales/enquiries'],
        'confirm_enquiry' => ['Confirm enquiry', '/sales/enquiries'],
        'quotation' => ['Convert enquiry to quotation (prospect becomes customer)', '/sales/quotations'],
        'accept_quotation' => ['Accept quotation', '/sales/quotations'],
        'convert_job' => ['Convert to job', '/operation/jobs'],
        'supplier_invoice' => ['Create supplier invoice', '/invoice/supplier'],
        'approve_supplier_invoice' => ['Approve supplier invoice (posts to ledger)', '/invoice/supplier'],
        'invoice' => ['Create customer invoice', '/invoice/customer'],
        'approve_invoice' => ['Approve invoice (posts to ledger)', '/invoice/customer'],
        'customer_aging' => ['Check customer aging (open invoice)', '/reports/customer-aging'],
        'supplier_aging' => ['Check supplier aging (open bill)', '/reports/supplier-aging'],
        'credit_note' => ['Create credit note', '/adjustment/credit-note'],
        'approve_credit_note' => ['Approve credit note (posts to ledger)', '/adjustment/credit-note'],
        'collection' => ['Record collection (invoice less credit note)', '/collection'],
        'approve_collection' => ['Approve collection (posts to ledger)', '/collection'],
        'expense' => ['Create expense', '/finance/expense'],
        'approve_expense' => ['Approve expense (posts to ledger)', '/finance/expense'],
        'payment' => ['Pay the supplier invoice', '/transaction/payments'],
        'approve_payment' => ['Approve payment (posts to ledger)', '/transaction/payments'],
        'links' => ['Check the links between modules', '/operation/jobs'],
        'customer_statement' => ['Check customer statement', '/reports/customer-statement'],
        'supplier_statement' => ['Check supplier statement', '/reports/supplier-statement'],
        'general_ledger' => ['Check general ledger', '/reports/general-ledger'],
        'trial_balance' => ['Check trial balance', '/reports/trial-balance'],
        'balance_sheet' => ['Check balance sheet', '/reports/balance-sheet'],
        'tax_summary' => ['Check tax summary', '/reports/tax-summary'],
        'tracking' => ['Shipment tracking: steps, job dates and public link', '/operation/tracking'],
        'job_insights' => ['Job list: health, progress and AI insight counts', '/operation/jobs'],
        'demurrage' => ['Demurrage: free days, detention and cost', '/operation/demurrage'],
        'documents' => ['Documents center: upload, expiry tabs, download, delete', '/operation/documents'],
        'fleet' => ['Masters: driver, vehicle, then a trip from start to finish', '/masters/vehicles'],
        'employee' => ['Payroll: employee, shift, salary structure and a punch', '/payroll/attendance'],
        'payroll_run' => ['Payroll: generate, pay and post the salary to the ledger', '/payroll/runs'],
        'employee_loan' => ['Payroll: employee loan', '/employee-loans'],
        'guard_fleet' => ['Delete check: vehicle with a trip is protected', '/masters/vehicles'],
        'guard_expense' => ['Delete check: approved expense is protected', '/finance/expense'],
        'guard_collection' => ['Delete check: approved collection is protected', '/collection'],
        'guard_payment' => ['Delete check: approved payment is protected', '/transaction/payments'],
        'guard_credit_note' => ['Delete check: approved credit note is protected', '/adjustment/credit-note'],
        'guard_customer_invoice' => ['Delete check: approved invoice is protected', '/invoice/customer'],
        'guard_supplier_invoice' => ['Delete check: approved supplier invoice is protected', '/invoice/supplier'],
        'guard_job' => ['Delete check: job with invoices is protected', '/operation/jobs'],
        'guard_quotation' => ['Delete check: converted quotation is protected', '/sales/quotations'],
        'guard_enquiry' => ['Delete check: converted enquiry is protected', '/sales/enquiries'],
        'guard_prospect' => ['Delete check: converted prospect is protected', '/prospects'],
        'guard_customer' => ['Delete check: customer with transactions is protected', '/customers'],
        'guard_supplier' => ['Delete check: supplier with transactions is protected', '/suppliers'],
        'free_deletes' => ['Delete check: records with no links can be deleted', '/customers'],
    ];

    private const AR = 5;       // 1130 Accounts Receivable
    private const VAT_OUT = 20; // 2130 Output VAT
    private const REVENUE = 32; // 4100 Freight Income
    private const BANK = 4;     // 1120 Bank
    private const AP = 18;      // 2110 Accounts Payable
    private const VAT_IN = 7;   // 1150 Input VAT
    private const COST = 42;    // 5110 Freight Purchase (cost of sales)
    private const EXPENSE = 47; // 5160 Documentation Expense

    public function run(string $step, array &$ctx): array
    {
        $method = 'step' . str_replace(' ', '', ucwords(str_replace('_', ' ', $step)));
        $checks = [];
        try {
            $url = $this->$method($ctx, $checks);
            $ok = collect($checks)->every(fn ($c) => $c['ok']);
            return ['ok' => $ok, 'step' => $step, 'title' => self::STEPS[$step][0], 'checks' => $checks, 'url' => $url ?? self::STEPS[$step][1]];
        } catch (ValidationException $e) {
            return $this->fail($step, 'Validation: ' . collect($e->errors())->flatten()->implode(' | '), $checks);
        } catch (\Throwable $e) {
            return $this->fail($step, get_class($e) . ': ' . $e->getMessage() . ' (' . basename($e->getFile()) . ':' . $e->getLine() . ')', $checks);
        }
    }

    private function fail(string $step, string $message, array $checks): array
    {
        $checks[] = ['label' => 'Step completed without error', 'ok' => false, 'value' => $message];
        return ['ok' => false, 'step' => $step, 'title' => self::STEPS[$step][0], 'checks' => $checks, 'url' => self::STEPS[$step][1]];
    }

    /** Call a controller method the way the router would. */
    private function call(object $controller, string $method, array $payload = [], array $args = []): array
    {
        $request = Request::create('/autocheck-internal', 'POST', $payload);
        $request->setLaravelSession(app('session.store'));
        app()->instance('request', $request);
        $result = $controller->$method(...array_merge([$request], $args));
        if (method_exists($result, 'getData')) {
            $data = $result->getData(true);
            return is_array($data) ? $data : [];
        }
        return [];
    }

    private function callNoRequest(object $controller, string $method, array $args = []): array
    {
        $request = Request::create('/autocheck-internal', 'POST');
        $request->setLaravelSession(app('session.store'));
        app()->instance('request', $request);
        $result = $controller->$method(...$args);
        $data = method_exists($result, 'getData') ? $result->getData(true) : [];
        return is_array($data) ? $data : [];
    }

    private function check(array &$checks, string $label, bool $ok, $value = null): void
    {
        $checks[] = ['label' => $label, 'ok' => $ok, 'value' => $value];
    }

    private function st($status): int
    {
        return $status instanceof \BackedEnum ? (int) $status->value : (int) $status;
    }

    private function money($v): string
    {
        return number_format((float) $v, 2);
    }

    // ───────────────────────── steps ─────────────────────────

    private function stepEnquiry(array &$ctx, array &$checks): string
    {
        $this->call(app(EnquiryController::class), 'store', [
            'prospect' => encodeId($ctx['prospect_id']), 'shipment_category' => 'container',
            'pickup_date' => now()->addDays(3)->format('Y-m-d'), 'expiry_date' => now()->addDays(30)->format('Y-m-d'),
            'pol' => 'Jebel Ali', 'pod' => 'Dammam', 'weight' => 1200, 'volume' => 20,
            'remark' => 'AUTOCHECK enquiry', 'activity_id' => '2',
        ]);
        $e = Enquiry::where('prospect_id', $ctx['prospect_id'])->latest('id')->first();
        $this->check($checks, 'Enquiry saved for the prospect', (bool) $e, $e?->row_no);
        $this->check($checks, 'Enquiry is Pending', $e && $this->st($e->status) === 1, $e ? $this->st($e->status) : '-');
        $this->check($checks, 'Prospect is not a customer yet', !DB::table('prospects')->where('id', $ctx['prospect_id'])->whereNotNull('customer_id')->exists(), 'not converted');
        $ctx['enquiry_id'] = $e?->id;
        return '/sales/enquiries';
    }

    private function stepConfirmEnquiry(array &$ctx, array &$checks): string
    {
        $this->callNoRequest(app(EnquiryController::class), 'updateStatus', [$ctx['enquiry_id'], 2]);
        $e = Enquiry::find($ctx['enquiry_id']);
        $this->check($checks, 'Enquiry is Confirmed', $e && $this->st($e->status) === 2, $e ? $this->st($e->status) : '-');
        return '/sales/enquiries';
    }

    private function stepQuotation(array &$ctx, array &$checks): string
    {
        $before = Customer::count();
        $this->call(app(QuotationController::class), 'store', [
            'prospect' => encodeId($ctx['prospect_id']), 'enquiry_id' => $ctx['enquiry_id'],
            'posted_at' => now()->format('Y-m-d'), 'valid_until' => now()->addDays(30)->format('Y-m-d'),
            'activity_id' => '2', 'shipment_category' => 'container', 'pol' => 'Jebel Ali', 'pod' => 'Dammam',
            'terms' => 'AUTOCHECK quotation',
            'chg_description' => ['Sea Freight Charges'], 'chg_unit' => ['1'], 'chg_qty' => [1],
            'chg_currency' => ['SAR'], 'chg_ex_rate' => [1], 'chg_amt_qty' => [2000], 'chg_tax_group' => ['STANDARD'],
        ]);
        $q = Quotation::where('prospect_id', $ctx['prospect_id'])->latest('id')->first();
        $p = Prospect::find($ctx['prospect_id']);
        $c = $p?->customer_id ? Customer::find($p->customer_id) : null;
        $e = Enquiry::find($ctx['enquiry_id']);
        $this->check($checks, 'Quotation saved', (bool) $q, $q?->row_no);
        $this->check($checks, 'Quotation is Pending', $q && $this->st($q->status) === QuotationEnum::PENDING->value, $q ? $this->st($q->status) : '-');
        $this->check($checks, 'Linked to the enquiry', $q && (int) $q->enquiry_id === (int) $ctx['enquiry_id'], $q?->enquiry_id);
        $this->check($checks, 'Prospect became a customer automatically', (bool) $c, $c ? $c->row_no . ' — ' . $c->name_en : 'no customer created');
        $this->check($checks, 'The new customer is Active', $c && $this->st($c->status) === 3, $c ? $this->st($c->status) : '-');
        $this->check($checks, 'Exactly one customer was created', Customer::count() === $before + 1, (Customer::count() - $before) . ' new');
        $this->check($checks, 'Quotation belongs to that customer', $q && $c && (int) $q->customer_id === (int) $c->id, $q?->customer_id);
        $this->check($checks, 'Enquiry now belongs to that customer', $e && $c && (int) $e->customer_id === (int) $c->id, $e?->customer_id);
        $this->check($checks, 'Enquiry marked as converted to quotation', $e && $this->st($e->status) === 3, $e ? $this->st($e->status) : '-');
        $ctx['quotation_id'] = $q?->id;
        $ctx['customer_id'] = $c?->id;
        return '/sales/quotations';
    }

    private function stepAcceptQuotation(array &$ctx, array &$checks): string
    {
        $this->callNoRequest(app(QuotationController::class), 'updateStatus', [$ctx['quotation_id'], QuotationEnum::ACCEPTED->value]);
        $q = Quotation::find($ctx['quotation_id']);
        $this->check($checks, 'Quotation is Accepted', $q && $this->st($q->status) === QuotationEnum::ACCEPTED->value, $q?->status);
        return '/sales/quotations';
    }

    private function stepConvertJob(array &$ctx, array &$checks): string
    {
        $this->callNoRequest(app(QuotationController::class), 'updateStatus', [$ctx['quotation_id'], QuotationEnum::CONVERTED->value]);
        $q = Quotation::find($ctx['quotation_id']);
        $job = $q?->job_id ? Job::find($q->job_id) : null;
        $this->check($checks, 'Quotation is Converted', $q && $this->st($q->status) === QuotationEnum::CONVERTED->value, $q?->status);
        $this->check($checks, 'Job created and linked', (bool) $job, $job?->row_no);
        $this->check($checks, 'Job belongs to the same customer', $job && (int) $job->customer_id === (int) $ctx['customer_id'], $job?->customer_id);
        $this->check($checks, 'No duplicate customer was created', Customer::where('name_en', Prospect::find($ctx['prospect_id'])?->name)->count() === 1, 'one customer');
        $ctx['job_id'] = $job?->id;
        return '/operation/jobs';
    }

    private function stepInvoice(array &$ctx, array &$checks): string
    {
        $this->call(app(CustomerInvoiceController::class), 'store', [
            'job_id' => $ctx['job_id'], 'customer' => encodeId($ctx['customer_id']),
            'invoice_date' => now()->format('Y-m-d'), 'due_date' => now()->addDays(30)->format('Y-m-d'),
            'currency' => 'SAR', 'currency_rate' => 1, 'terms' => 'AUTOCHECK invoice',
            'description_id' => ['2'], 'comment' => ['AUTOCHECK freight'], 'unit_id' => ['1'],
            'quantity' => [1], 'unit_price' => ['2000'], 'tax' => ['STANDARD'], 'account' => [self::REVENUE],
        ]);
        $i = CustomerInvoice::where('customer_id', $ctx['customer_id'])->latest('id')->first();
        $this->check($checks, 'Invoice saved as Draft', $i && $this->st($i->status) === CustomerInvoiceEnum::DRAFT->value, $i?->row_no);
        $this->check($checks, 'Sub total 2,000.00', $i && abs($i->sub_total - 2000) < 0.01, $this->money($i?->sub_total));
        $this->check($checks, 'VAT 15% = 300.00', $i && abs($i->tax_total - 300) < 0.01, $this->money($i?->tax_total));
        $this->check($checks, 'Grand total 2,300.00', $i && abs($i->grand_total - 2300) < 0.01, $this->money($i?->grand_total));
        $ctx['invoice_id'] = $i?->id;
        return '/invoice/customer';
    }

    private function stepApproveInvoice(array &$ctx, array &$checks): string
    {
        $this->callNoRequest(app(CustomerInvoiceController::class), 'updateStatus', [$ctx['invoice_id'], CustomerInvoiceEnum::APPROVED->value]);
        $i = CustomerInvoice::find($ctx['invoice_id']);
        $this->check($checks, 'Invoice is Approved', $i && $this->st($i->status) === CustomerInvoiceEnum::APPROVED->value, $i?->row_no);
        $ar = $this->ledger(self::AR, $ctx['invoice_id'], CustomerInvoice::class);
        $vat = $this->ledger(self::VAT_OUT, $ctx['invoice_id'], CustomerInvoice::class);
        $rev = $this->ledger(self::REVENUE, $ctx['invoice_id'], CustomerInvoice::class);
        $this->check($checks, 'Receivable debited 2,300.00', abs($ar['dr'] - 2300) < 0.01, $this->money($ar['dr']));
        $this->check($checks, 'Output VAT credited 300.00', abs($vat['cr'] - 300) < 0.01, $this->money($vat['cr']));
        $this->check($checks, 'Revenue credited 2,000.00', abs($rev['cr'] - 2000) < 0.01, $this->money($rev['cr']));
        $this->check($checks, 'Entry balances', abs($this->entryImbalance($ctx['invoice_id'], CustomerInvoice::class)) < 0.01, $this->money($this->entryImbalance($ctx['invoice_id'], CustomerInvoice::class)));
        return '/invoice/customer';
    }

    private function stepCollection(array &$ctx, array &$checks): string
    {
        $this->call(app(CollectionController::class), 'store', [
            'customer' => encodeId($ctx['customer_id']), 'collection_date' => now()->format('Y-m-d'),
            'account' => [self::BANK], 'reference_no' => 'AUTOCHECK', 'currency' => 'SAR', 'currency_rate' => 1,
            'customer_invoice_ids' => [$ctx['invoice_id']], 'invoice_amounts' => [$ctx['invoice_id'] => 1725],
            'notes' => 'AUTOCHECK collection',
        ]);
        $c = Collection::where('customer_id', $ctx['customer_id'])->latest('id')->first();
        $this->check($checks, 'Collection saved as Draft', $c && $this->st($c->status) === CollectionEnum::DRAFT->value, $c?->row_no);
        $this->check($checks, 'Amount 1,725.00 (2,300.00 less credit note 575.00)', $c && abs($c->base_grand_total - 1725) < 0.01, $this->money($c?->base_grand_total));
        $ctx['collection_id'] = $c?->id;
        return '/collection';
    }

    private function stepApproveCollection(array &$ctx, array &$checks): string
    {
        $this->callNoRequest(app(CollectionController::class), 'updateStatus', [$ctx['collection_id'], CollectionEnum::APPROVED->value]);
        $c = Collection::find($ctx['collection_id']);
        $i = CustomerInvoice::find($ctx['invoice_id']);
        $this->check($checks, 'Collection is Approved', $c && $this->st($c->status) === CollectionEnum::APPROVED->value, $c?->row_no);
        $this->check($checks, 'Invoice fully settled (collection 1,725.00 + credit note 575.00)', $i && abs($i->paid_amount - $i->grand_total) < 0.01, $this->money($i?->paid_amount) . ' / ' . $this->money($i?->grand_total));
        $bank = $this->ledger(self::BANK, $ctx['collection_id'], Collection::class);
        $ar = $this->ledger(self::AR, $ctx['collection_id'], Collection::class);
        $this->check($checks, 'Bank debited 1,725.00', abs($bank['dr'] - 1725) < 0.01, $this->money($bank['dr']));
        $this->check($checks, 'Receivable credited 1,725.00', abs($ar['cr'] - 1725) < 0.01, $this->money($ar['cr']));
        $arNet = (float) DB::table('finance_subs')->where('customer_id', $ctx['customer_id'])->where('account_id', self::AR)->selectRaw('COALESCE(SUM(base_debit - base_credit),0) n')->value('n');
        $this->check($checks, 'Customer receivable is now 0.00', abs($arNet) < 0.01, $this->money($arNet));
        return '/collection';
    }

    private function stepProspect(array &$ctx, array &$checks): string
    {
        $name = 'AUTOCHECK Prospect ' . now()->format('Hi');
        $this->call(app(ProspectController::class), 'quickStore', [
            'quick_prospect_name' => $name, 'quick_prospect_email' => 'autocheck.prospect@example.com',
            'quick_prospect_phone' => '+966500008888', 'quick_prospect_address' => 'Riyadh',
        ]);
        $p = Prospect::where('name', $name)->latest('id')->first();
        $this->check($checks, 'Prospect saved', (bool) $p, $p?->row_no . ' — ' . $name);
        $ctx['prospect_id'] = $p?->id;
        return '/prospects';
    }

    private function stepSupplier(array &$ctx, array &$checks): string
    {
        $name = 'AUTOCHECK Supplier ' . now()->format('Hi');
        $this->call(app(SupplierController::class), 'store', [
            'name_en' => $name, 'name_ar' => 'مورد الفحص الآلي', 'currency' => 'SAR', 'business_type' => 'unregistered',
            'country' => 'SA', 'phone' => '+966500007777', 'email' => 'autocheck.supplier@example.com',
            'credit_limit' => 50000, 'credit_days' => 30,
        ]);
        $sp = Supplier::where('name_en', $name)->latest('id')->first();
        $this->check($checks, 'Supplier saved', (bool) $sp, $sp?->row_no . ' — ' . $name);
        $this->check($checks, 'Supplier is Active', $sp && $this->st($sp->status) === 1, $sp ? $this->st($sp->status) : '-');
        $ctx['supplier_id'] = $sp?->id;
        return '/suppliers/' . $sp?->id;
    }

    private function stepSupplierInvoice(array &$ctx, array &$checks): string
    {
        $this->call(app(SupplierInvoiceController::class), 'store', [
            'job_id' => $ctx['job_id'], 'supplier' => encodeId($ctx['supplier_id']),
            'invoice_date' => now()->format('Y-m-d'), 'invoice_number' => 'AUTOCHECK-' . now()->format('His'),
            'due_date' => now()->addDays(30)->format('Y-m-d'), 'currency' => 'SAR', 'currency_rate' => 1,
            'terms' => 'AUTOCHECK bill', 'description_id' => ['2'], 'comment' => ['AUTOCHECK freight cost'],
            'unit_id' => ['1'], 'quantity' => [1], 'unit_price' => ['1000'], 'tax' => ['STANDARD'], 'account' => [self::COST],
        ]);
        $b = SupplierInvoice::where('supplier_id', $ctx['supplier_id'])->latest('id')->first();
        $this->check($checks, 'Supplier invoice saved as Draft', $b && $this->st($b->status) === SupplierInvoiceEnum::DRAFT->value, $b?->row_no);
        $this->check($checks, 'Sub total 1,000.00', $b && abs($b->sub_total - 1000) < 0.01, $this->money($b?->sub_total));
        $this->check($checks, 'VAT 15% = 150.00', $b && abs($b->tax_total - 150) < 0.01, $this->money($b?->tax_total));
        $this->check($checks, 'Grand total 1,150.00', $b && abs($b->grand_total - 1150) < 0.01, $this->money($b?->grand_total));
        $ctx['supplier_invoice_id'] = $b?->id;
        return '/invoice/supplier';
    }

    private function stepApproveSupplierInvoice(array &$ctx, array &$checks): string
    {
        $this->callNoRequest(app(SupplierInvoiceController::class), 'updateStatus', [$ctx['supplier_invoice_id'], SupplierInvoiceEnum::APPROVED->value]);
        $b = SupplierInvoice::find($ctx['supplier_invoice_id']);
        $this->check($checks, 'Supplier invoice is Approved', $b && $this->st($b->status) === SupplierInvoiceEnum::APPROVED->value, $b?->row_no);
        $t = 'supplier_invoice'; // the supplier posting code stores this plain string, not the model class
        $ap = $this->ledger(self::AP, $ctx['supplier_invoice_id'], $t);
        $vat = $this->ledger(self::VAT_IN, $ctx['supplier_invoice_id'], $t);
        $cost = $this->ledger(self::COST, $ctx['supplier_invoice_id'], $t);
        $this->check($checks, 'Payable credited 1,150.00', abs($ap['cr'] - 1150) < 0.01, $this->money($ap['cr']));
        $this->check($checks, 'Input VAT debited 150.00', abs($vat['dr'] - 150) < 0.01, $this->money($vat['dr']));
        $this->check($checks, 'Cost debited 1,000.00', abs($cost['dr'] - 1000) < 0.01, $this->money($cost['dr']));
        $imb = $this->entryImbalance($ctx['supplier_invoice_id'], $t);
        $this->check($checks, 'Entry balances', abs($imb) < 0.01, $this->money($imb));
        return '/invoice/supplier';
    }

    private function stepCustomerAging(array &$ctx, array &$checks): string
    {
        $text = $this->reportText(\App\Livewire\Report\Finance\CustomerAging::class, ['customerId' => encodeId($ctx['customer_id'])]);
        $this->check($checks, 'Aging report lists the customer', str_contains($text, 'AUTOCHECK'), 'customer shown');
        $this->check($checks, 'Aging shows outstanding 2,300.00', str_contains($text, '2,300.00'), '2,300.00');
        return '/reports/customer-aging';
    }

    private function stepSupplierAging(array &$ctx, array &$checks): string
    {
        $text = $this->reportText(\App\Livewire\Report\Finance\SupplierAging::class, ['supplierId' => encodeId($ctx['supplier_id'])]);
        $this->check($checks, 'Aging report lists the supplier', str_contains($text, 'AUTOCHECK Supplier'), 'supplier shown');
        $this->check($checks, 'Aging shows outstanding 1,150.00', str_contains($text, '1,150.00'), '1,150.00');
        return '/reports/supplier-aging';
    }

    private function stepCreditNote(array &$ctx, array &$checks): string
    {
        $this->call(app(CreditNoteController::class), 'store', [
            'credit_note_type' => '1', 'invoice_id' => $ctx['invoice_id'], 'customer' => encodeId($ctx['customer_id']),
            'credit_note_date' => now()->format('Y-m-d'), 'job_id' => $ctx['job_id'], 'reason' => 'AUTOCHECK rate correction',
            'terms' => 'AUTOCHECK credit note', 'description_id' => ['2'], 'comment' => ['AUTOCHECK credit'],
            'unit_id' => ['1'], 'quantity' => [1], 'unit_price' => ['500'], 'tax' => ['STANDARD'], 'account' => [self::REVENUE],
        ]);
        $n = CreditNote::where('customer_id', $ctx['customer_id'])->latest('id')->first();
        $this->check($checks, 'Credit note saved as Draft', $n && $this->st($n->status) === CreditNoteEnum::DRAFT->value, $n?->row_no);
        $this->check($checks, 'Sub total 500.00', $n && abs($n->sub_total - 500) < 0.01, $this->money($n?->sub_total));
        $this->check($checks, 'VAT 75.00', $n && abs($n->tax_total - 75) < 0.01, $this->money($n?->tax_total));
        $this->check($checks, 'Grand total 575.00', $n && abs($n->grand_total - 575) < 0.01, $this->money($n?->grand_total));
        $ctx['credit_note_id'] = $n?->id;
        return '/adjustment/credit-note';
    }

    private function stepApproveCreditNote(array &$ctx, array &$checks): string
    {
        $this->callNoRequest(app(CreditNoteController::class), 'updateStatus', [$ctx['credit_note_id'], CreditNoteEnum::APPROVED->value]);
        $n = CreditNote::find($ctx['credit_note_id']);
        $this->check($checks, 'Credit note is Approved', $n && $this->st($n->status) === CreditNoteEnum::APPROVED->value, $n?->row_no);
        $t = CreditNote::class;
        $ar = $this->ledger(self::AR, $ctx['credit_note_id'], $t);
        $vat = $this->ledger(self::VAT_OUT, $ctx['credit_note_id'], $t);
        $rev = $this->ledger(self::REVENUE, $ctx['credit_note_id'], $t);
        $this->check($checks, 'Receivable credited 575.00', abs($ar['cr'] - 575) < 0.01, $this->money($ar['cr']));
        $this->check($checks, 'Output VAT reversed (debit) 75.00', abs($vat['dr'] - 75) < 0.01, $this->money($vat['dr']));
        $this->check($checks, 'Revenue reversed (debit) 500.00', abs($rev['dr'] - 500) < 0.01, $this->money($rev['dr']));
        $imb = $this->entryImbalance($ctx['credit_note_id'], $t);
        $this->check($checks, 'Entry balances', abs($imb) < 0.01, $this->money($imb));
        return '/adjustment/credit-note';
    }

    private function stepExpense(array &$ctx, array &$checks): string
    {
        $this->call(app(ExpenseController::class), 'store', [
            'posted_at' => now()->format('Y-m-d'), 'reference_number' => 'AUTOCHECK-EXP', 'payment_status' => 'paid',
            'main_account' => self::BANK, 'paid_amount' => 230, 'amount_excluding_vat' => 200, 'amount_including_vat' => 230,
            'account' => [self::EXPENSE], 'comment' => ['AUTOCHECK expense'], 'quantity' => [1],
            'unit_price' => ['200'], 'tax' => ['STANDARD'], 'item_id' => [], 'employee_id' => [''],
        ]);
        $e = Expense::where('reference_number', 'AUTOCHECK-EXP')->latest('id')->first();
        $this->check($checks, 'Expense saved as Pending', $e && $this->st($e->status) === ExpenseEnum::PENDING->value, $e?->row_no);
        $this->check($checks, 'Grand total 230.00 (200.00 + 15% VAT)', $e && abs($e->grand_total - 230) < 0.01, $this->money($e?->grand_total));
        $ctx['expense_id'] = $e?->id;
        return '/finance/expense';
    }

    private function stepApproveExpense(array &$ctx, array &$checks): string
    {
        $this->call(app(ExpenseController::class), 'updateStatus', [], [$ctx['expense_id'], ExpenseEnum::APPROVED->value]);
        $e = Expense::find($ctx['expense_id']);
        $this->check($checks, 'Expense is Approved', $e && $this->st($e->status) === ExpenseEnum::APPROVED->value, $e?->row_no);
        $t = Expense::class;
        $exp = $this->ledger(self::EXPENSE, $ctx['expense_id'], $t);
        $vat = $this->ledger(self::VAT_IN, $ctx['expense_id'], $t);
        $bank = $this->ledger(self::BANK, $ctx['expense_id'], $t);
        $this->check($checks, 'Expense debited 200.00', abs($exp['dr'] - 200) < 0.01, $this->money($exp['dr']));
        $this->check($checks, 'Input VAT debited 30.00', abs($vat['dr'] - 30) < 0.01, $this->money($vat['dr']));
        $this->check($checks, 'Bank credited 230.00', abs($bank['cr'] - 230) < 0.01, $this->money($bank['cr']));
        $imb = $this->entryImbalance($ctx['expense_id'], $t);
        $this->check($checks, 'Entry balances', abs($imb) < 0.01, $this->money($imb));
        return '/finance/expense';
    }

    private function stepCustomerStatement(array &$ctx, array &$checks): string
    {
        $text = $this->reportText(\App\Livewire\Report\Finance\CustomerStatement::class, ['customerId' => encodeId($ctx['customer_id'])]);
        $this->check($checks, 'Statement shows the invoice 2,300.00', str_contains($text, '2,300.00'), '2,300.00');
        $this->check($checks, 'Statement shows the credit note 575.00', str_contains($text, '575.00'), '575.00');
        $this->check($checks, 'Statement shows the collection 1,725.00', str_contains($text, '1,725.00'), '1,725.00');
        $this->check($checks, 'Statement closing balance 0.00', preg_match('/Closing Balance\s*0\.00/', $text) === 1 || str_contains($text, 'Closing Balance 0.00'), '0.00');
        return '/reports/customer-statement?customer=' . $ctx['customer_id'];
    }

    private function stepSupplierStatement(array &$ctx, array &$checks): string
    {
        $text = $this->reportText(\App\Livewire\Report\Finance\SupplierStatement::class, ['supplierId' => encodeId($ctx['supplier_id'])]);
        $this->check($checks, 'Statement shows the bill 1,150.00', str_contains($text, '1,150.00'), '1,150.00');
        return '/reports/supplier-statement?supplier=' . $ctx['supplier_id'];
    }

    private function stepGeneralLedger(array &$ctx, array &$checks): string
    {
        $text = $this->reportText(\App\Livewire\Report\Finance\GeneralLedgerTable::class, [], ['customerId' => (string) $ctx['customer_id']]);
        $this->check($checks, 'Ledger shows invoice 2,300.00', str_contains($text, '2,300.00'), '2,300.00');
        $this->check($checks, 'Ledger shows credit note 575.00', str_contains($text, '575.00'), '575.00');
        $this->check($checks, 'Ledger shows collection 1,725.00', str_contains($text, '1,725.00'), '1,725.00');
        return '/reports/general-ledger';
    }

    private function stepTrialBalance(array &$ctx, array &$checks): string
    {
        $all = DB::table('finance_subs')->selectRaw('COALESCE(SUM(base_debit),0) dr, COALESCE(SUM(base_credit),0) cr')->first();
        $this->check($checks, 'Whole ledger balances', abs($all->dr - $all->cr) < 0.01, 'Dr ' . $this->money($all->dr) . ' / Cr ' . $this->money($all->cr));
        $arNet = (float) DB::table('finance_subs')->where('customer_id', $ctx['customer_id'])->where('account_id', self::AR)->selectRaw('COALESCE(SUM(base_debit - base_credit),0) n')->value('n');
        $apNet = (float) DB::table('finance_subs')->where('supplier_id', $ctx['supplier_id'])->where('account_id', self::AP)->selectRaw('COALESCE(SUM(base_credit - base_debit),0) n')->value('n');
        $this->check($checks, 'Customer receivable is 0.00', abs($arNet) < 0.01, $this->money($arNet));
        $this->check($checks, 'Supplier payable is 0.00 (bill paid)', abs($apNet) < 0.01, $this->money($apNet));
        $text = $this->reportText(\App\Livewire\Report\Finance\TrialBalance::class);
        $this->check($checks, 'Trial balance page shows total debit', str_contains($text, number_format($all->dr, 2)), number_format($all->dr, 2));
        $this->check($checks, 'Trial balance page shows total credit', str_contains($text, number_format($all->cr, 2)), number_format($all->cr, 2));
        $this->check($checks, 'Trial balance page says Balanced', stripos($text, 'Balanced') !== false, 'Balanced');
        return '/reports/trial-balance';
    }

    private function stepBalanceSheet(array &$ctx, array &$checks): string
    {
        $text = $this->reportText(\App\Livewire\Report\Finance\BalanceSheet::class);
        preg_match('/TOTAL ASSETS\s*(-?[\d,]+\.\d{2})/', $text, $a);
        preg_match('/TOTAL LIABILITIES & EQUITY\s*(-?[\d,]+\.\d{2})/', $text, $l);
        $assets = isset($a[1]) ? (float) str_replace(',', '', $a[1]) : null;
        $liab = isset($l[1]) ? (float) str_replace(',', '', $l[1]) : null;
        $this->check($checks, 'Balance sheet shows total assets', $assets !== null, $a[1] ?? 'not found');
        $this->check($checks, 'Assets equal liabilities + equity', $assets !== null && $liab !== null && abs($assets - $liab) < 0.01, ($a[1] ?? '?') . ' = ' . ($l[1] ?? '?'));
        $bank = (float) DB::table('finance_subs')->where('account_id', self::BANK)->selectRaw('COALESCE(SUM(base_debit - base_credit),0) n')->value('n');
        $this->check($checks, 'Bank balance on the sheet matches the ledger', str_contains($text, number_format(abs($bank), 2)), number_format($bank, 2));
        return '/reports/balance-sheet';
    }

    private function stepTaxSummary(array &$ctx, array &$checks): string
    {
        $out = (float) DB::table('finance_subs')->where('account_id', self::VAT_OUT)->selectRaw('COALESCE(SUM(base_credit - base_debit),0) n')->value('n');
        $in = (float) DB::table('finance_subs')->where('account_id', self::VAT_IN)->selectRaw('COALESCE(SUM(base_debit - base_credit),0) n')->value('n');
        $text = $this->reportText(\App\Livewire\Report\Finance\TaxSummary::class);
        $this->check($checks, 'Output VAT on the report matches the ledger', str_contains($text, number_format($out, 2)), number_format($out, 2));
        $this->check($checks, 'Input VAT on the report matches the ledger', str_contains($text, number_format($in, 2)), number_format($in, 2));
        $this->check($checks, 'Net VAT payable = output − input', str_contains($text, number_format($out - $in, 2)), number_format($out - $in, 2));
        return '/reports/tax-summary';
    }

    private function stepPayment(array &$ctx, array &$checks): string
    {
        $this->call(app(PaymentController::class), 'store', [
            'supplier' => encodeId($ctx['supplier_id']), 'payment_date' => now()->format('Y-m-d'),
            'account' => [self::BANK], 'reference_no' => 'AUTOCHECK', 'currency' => 'SAR', 'currency_rate' => 1,
            'supplier_invoice_ids' => [$ctx['supplier_invoice_id']], 'invoice_amounts' => [$ctx['supplier_invoice_id'] => 1150],
            'notes' => 'AUTOCHECK payment',
        ]);
        $p = Payment::where('supplier_id', $ctx['supplier_id'])->latest('id')->first();
        $this->check($checks, 'Payment saved as Draft', $p && $this->st($p->status) === PaymentEnum::DRAFT->value, $p?->row_no);
        $this->check($checks, 'Amount 1,150.00', $p && abs($p->base_grand_total - 1150) < 0.01, $this->money($p?->base_grand_total));
        $ctx['payment_id'] = $p?->id;
        return '/transaction/payments';
    }

    private function stepApprovePayment(array &$ctx, array &$checks): string
    {
        $this->callNoRequest(app(PaymentController::class), 'updateStatus', [$ctx['payment_id'], PaymentEnum::APPROVED->value]);
        $p = Payment::find($ctx['payment_id']);
        $b = SupplierInvoice::find($ctx['supplier_invoice_id']);
        $this->check($checks, 'Payment is Approved', $p && $this->st($p->status) === PaymentEnum::APPROVED->value, $p?->row_no);
        $this->check($checks, 'Supplier invoice fully paid', $b && abs($b->paid_amount - $b->grand_total) < 0.01, $this->money($b?->paid_amount) . ' / ' . $this->money($b?->grand_total));
        $ap = $this->ledger(self::AP, $ctx['payment_id'], Payment::class);
        $bank = $this->ledger(self::BANK, $ctx['payment_id'], Payment::class);
        $this->check($checks, 'Payable debited 1,150.00', abs($ap['dr'] - 1150) < 0.01, $this->money($ap['dr']));
        $this->check($checks, 'Bank credited 1,150.00', abs($bank['cr'] - 1150) < 0.01, $this->money($bank['cr']));
        $imb = $this->entryImbalance($ctx['payment_id'], Payment::class);
        $this->check($checks, 'Entry balances', abs($imb) < 0.01, $this->money($imb));
        return '/transaction/payments';
    }

    // ───────────── logistics, fleet and payroll modules ─────────────

    private function stepTracking(array &$ctx, array &$checks): string
    {
        $tc = app(\App\Http\Controllers\Job\TrackingController::class);
        $job = Job::with('clearance')->find($ctx['job_id']);
        $codes = array_keys(\App\Http\Controllers\Job\TrackingController::template($job->shipment_mode));
        $this->check($checks, 'Tracking steps include documents and D/O', in_array('docs_received', $codes) && in_array('do_released', $codes), implode(', ', $codes));

        $dep = now()->subDays(5)->format('d-m-Y');
        $this->call($tc, 'save', ['date' => ['booked' => now()->subDays(8)->format('d-m-Y'), 'docs_received' => now()->subDays(7)->format('d-m-Y'), 'departed' => $dep], 'location' => ['booked' => 'AUTOCHECK']], [$job->id]);
        $job = $job->fresh('clearance');
        $this->check($checks, 'Documents received date written back to the job', filled($job->doc_received), $job->doc_received);
        $this->check($checks, 'ATD written back to the job', filled($job->atd), $job->atd);

        $before = collect(\App\Http\Controllers\Job\TrackingController::steps($job))->whereNotNull('actual')->count();
        $adv = $this->callNoRequest($tc, 'advance', [$job->id]);
        $after = collect(\App\Http\Controllers\Job\TrackingController::steps($job->fresh('clearance')))->whereNotNull('actual')->count();
        $this->check($checks, 'One-click "Done" completes the next step', ($adv['status'] ?? '') === 'success' && $after === $before + 1, "$before → $after");

        $job->clearance?->forceFill(['clearance_status' => 'cleared', 'clearance_date' => today()])->save();
        $cust = collect(\App\Http\Controllers\Job\TrackingController::steps($job->fresh('clearance')))->firstWhere('code', 'customs');
        if ($job->clearance) {
            $this->check($checks, 'Customs step follows the Customs Clearance module', (bool) $cust['actual'], $cust['actual']?->format('d-m-Y'));
        }

        $url = $this->callNoRequest($tc, 'share', [$job->id])['url'] ?? '';
        $this->check($checks, 'Public tracking link created', str_contains($url, '/track/'), $url);
        $html = $tc->publicShow(substr($url, -32))->render();
        $this->check($checks, 'Public page shows the job and no amounts', str_contains($html, (string) $job->row_no) && !str_contains($html, 'grand_total'), $job->row_no);
        return '/operation/tracking';
    }

    private function stepJobInsights(array &$ctx, array &$checks): string
    {
        $job = Job::with('customer:id,name_en', 'invoices:id,job_id,status', 'clearance', 'milestones')->find($ctx['job_id']);
        $h = \App\Services\Job\JobInsights::health($job);
        $p = \App\Services\Job\JobInsights::progress($job);
        $this->check($checks, 'Job has a health state', filled($h['state']), $h['state'] . ' ' . $h['label']);
        $this->check($checks, 'Progress counts the done steps', $p['done'] >= 3 && $p['total'] >= 5, "{$p['done']}/{$p['total']}");
        $this->check($checks, 'Billing state matches the invoices', \App\Services\Job\JobInsights::billing($job) !== 'none', \App\Services\Job\JobInsights::billing($job));
        $snap = \App\Services\Job\JobInsights::snapshot();
        $this->check($checks, 'Insight counts are returned', isset($snap['counts']['delayed'], $snap['counts']['unbilled']) && $snap['active'] >= 1, json_encode($snap['counts']));
        $ctxText = \App\Services\Job\JobInsights::context();
        $this->check($checks, 'AI assistant context lists the job', str_contains($ctxText, (string) $job->row_no), $job->row_no);
        return '/operation/jobs';
    }

    private function stepDemurrage(array &$ctx, array &$checks): string
    {
        $c = \App\Models\Job\JobContainer::create(['job_id' => $ctx['job_id'], 'container_number' => 'AUTO1234567', 'container_size' => '40HC']);
        $dc = app(\App\Http\Controllers\Job\DemurrageController::class);
        $this->call($dc, 'save', ['discharged_at' => now()->subDays(10)->format('d-m-Y'), 'free_days' => 7], [$c->id]);
        $f = \App\Http\Controllers\Job\DemurrageController::figures($c->fresh(), 100);
        $this->check($checks, 'Container is in detention after the free days', $f['state'] === 'detention', $f['state']);
        $this->check($checks, 'Days over and cost are worked out', $f['over'] === 4 && (int) $f['cost'] === 400, "{$f['over']} days, {$f['cost']}");
        $this->call($dc, 'save', ['discharged_at' => now()->subDays(10)->format('d-m-Y'), 'returned_at' => now()->subDays(5)->format('d-m-Y'), 'free_days' => 7], [$c->id]);
        $f = \App\Http\Controllers\Job\DemurrageController::figures($c->fresh(), 100);
        $this->check($checks, 'Returned in time: no charge', $f['state'] === 'returned' && $f['cost'] == 0, $f['state']);
        $this->check($checks, 'Tracker page renders', strlen($dc->index(Request::create('/x', 'GET', ['tab' => 'returned']))->render()) > 1000, 'ok');
        return '/operation/demurrage';
    }

    private function stepDocuments(array &$ctx, array &$checks): string
    {
        $dc = app(\App\Http\Controllers\Documents\DocumentCenterController::class);
        $tmp = tempnam(sys_get_temp_dir(), 'ac') . '.pdf';
        file_put_contents($tmp, "%PDF-1.4\n1 0 obj<<>>endobj\ntrailer<<>>\n%%EOF");
        $file = new \Illuminate\Http\UploadedFile($tmp, 'autocheck-bl.pdf', 'application/pdf', null, true);
        $request = Request::create('/autocheck-internal', 'POST', [
            'title' => 'AUTOCHECK Bill of Lading', 'doc_type' => 'bl', 'owner_type' => 'job', 'owner_id' => $ctx['job_id'],
            'expiry_date' => now()->addDays(10)->format('d-m-Y'),
        ], [], ['file' => $file]);
        $request->setLaravelSession(app('session.store'));
        app()->instance('request', $request);
        $res = $dc->store($request)->getData(true);
        $doc = \App\Models\Documents\Documents::find($res['id'] ?? 0);
        $this->check($checks, 'Document saved against the job', $doc && (int) $doc->documentable_id === (int) $ctx['job_id'], $doc?->title);
        $this->check($checks, 'File is stored', $doc && \Storage::disk('public')->exists($doc->file_path), $doc?->file_path);

        $list = $this->call($dc, 'fetchAllRows', ['tab' => 'expiring', 'draw' => 1, 'start' => 0, 'length' => 50]);
        $this->check($checks, 'Document appears under Expiring Soon', collect($list['data'] ?? [])->contains('id', $doc?->id), json_encode($list['statusCounts'] ?? []));
        $this->check($checks, 'Download works', $doc && $dc->download($doc->id, Request::create('/x'))->getStatusCode() === 200, 'ok');

        if ($doc) {
            $this->callNoRequest($dc, 'delete', [$doc->id]);
            $this->check($checks, 'Document and file are removed on delete', !$this->exists('documents', $doc->id) && !\Storage::disk('public')->exists($doc->file_path), 'deleted');
        }
        @unlink($tmp);
        return '/operation/documents';
    }

    private function stepFleet(array &$ctx, array &$checks): string
    {
        $d = $this->call(app(\App\Http\Controllers\Fleet\DriverController::class), 'store', ['name' => 'AUTOCHECK Driver', 'phone' => '0500000000', 'license_no' => 'LIC-1', 'license_expiry' => now()->addYear()->format('d-m-Y')]);
        $driver = \App\Models\Fleet\Driver::where('name', 'AUTOCHECK Driver')->latest('id')->first();
        $this->check($checks, 'Driver saved with a number', $driver && filled($driver->row_no), $driver?->row_no);
        $ctx['driver_id'] = $driver?->id;
        $this->call(app(\App\Http\Controllers\Fleet\VehicleController::class), 'store', ['plate_no' => 'AUT 1234', 'type' => 'truck', 'capacity_kg' => 20000, 'driver_id' => $driver?->id]);
        $veh = \App\Models\Fleet\Vehicle::where('plate_no', 'AUT 1234')->latest('id')->first();
        $this->check($checks, 'Vehicle saved with a number', $veh && filled($veh->row_no), $veh?->row_no);
        $ctx['vehicle_id'] = $veh?->id;

        $tc = app(\App\Http\Controllers\Fleet\TripController::class);
        $this->call($tc, 'store', ['trip_date' => now()->format('d-m-Y'), 'vehicle_id' => $veh?->id, 'driver_id' => $driver?->id, 'job_id' => $ctx['job_id'],
            'origin' => 'Dammam', 'destination' => 'Riyadh', 'freight_amount' => 1500, 'driver_allowance' => 100, 'fuel_cost' => 300]);
        $trip = \App\Models\Fleet\Trip::where('vehicle_id', $veh?->id)->latest('id')->first();
        $this->check($checks, 'Trip saved and is Planned', $trip && $this->st($trip->status) === 1, $trip?->row_no);
        $ctx['trip_id'] = $trip?->id;
        $this->callNoRequest($tc, 'updateStatus', [$trip->id, 2]);
        $this->check($checks, 'Starting a trip stamps the start time', filled($trip->fresh()->started_at), $trip->fresh()->started_at);
        $this->callNoRequest($tc, 'updateStatus', [$trip->id, 3]);
        $this->check($checks, 'Completing a trip stamps the end time', filled($trip->fresh()->completed_at), $trip->fresh()->completed_at);
        $this->check($checks, 'Trip listed under the job link', \App\Models\Fleet\Trip::where('job_id', $ctx['job_id'])->exists(), 'linked');
        return '/masters/vehicles';
    }

    private function stepGuardFleet(array &$ctx, array &$checks): string
    {
        $this->guard($checks, 'Vehicle with a trip', app(\App\Http\Controllers\Fleet\VehicleController::class), 'delete', 'vehicles', $ctx['vehicle_id'] ?? null);
        $this->guard($checks, 'Driver with a trip', app(\App\Http\Controllers\Fleet\DriverController::class), 'delete', 'drivers', $ctx['driver_id'] ?? null);
        return '/masters/vehicles';
    }

    private function stepEmployee(array &$ctx, array &$checks): string
    {
        $user = \App\Models\User::withoutGlobalScopes()->create([
            'name' => 'AUTOCHECK Employee', 'email' => 'autocheck.employee.' . time() . '@example.com', 'password' => bcrypt(\Illuminate\Support\Str::random(24)),
            'company_id' => companyId(), 'is_employee' => 1, 'employee_code' => 'ACE' . random_int(100, 999), 'login_permission' => 0,
        ]);
        $this->check($checks, 'Employee created (a users row flagged as employee)', (bool) $user->id && (int) $user->is_employee === 1, $user->employee_code);
        $ctx['employee_id'] = $user->id;

        $this->call(app(\App\Http\Controllers\Payroll\ShiftController::class), 'store', ['name' => 'AUTOCHECK Shift', 'type' => 'morning', 'start_time' => '08:00', 'end_time' => '17:00', 'grace_minutes' => 15, 'week_off_days' => [5]]);
        $shift = \App\Models\Payroll\Shift::where('name', 'AUTOCHECK Shift')->latest('id')->first();
        $this->check($checks, 'Shift saved', (bool) $shift, $shift?->name);
        $ctx['shift_id'] = $shift?->id;

        $this->call(app(\App\Http\Controllers\Payroll\SalaryStructureController::class), 'store', ['employee_id' => $user->id, 'basic_salary' => 3000, 'housing_allowance' => 500, 'transportation_allowance' => 200, 'effective_from' => now()->startOfMonth()->format('Y-m-d')]);
        $ss = \App\Models\Payroll\SalaryStructure::where('employee_id', $user->id)->latest('id')->first();
        $this->check($checks, 'Salary structure total = basic + allowances', $ss && (float) $ss->total_salary === 3700.0, $ss?->total_salary);

        $day = now()->startOfMonth()->addDay();
        while ($day->isFriday() || $day->isFuture()) { $day = $day->subDay(); }
        foreach (['08:05' => 'in', '17:10' => 'out'] as $time => $dir) {
            $this->call(app(\App\Http\Controllers\Payroll\PunchEntryController::class), 'store', ['employee_id' => $user->id, 'date' => $day->format('d-m-Y'), 'time' => $time, 'direction' => $dir]);
        }
        $att = \App\Models\Payroll\Attendance::withoutGlobalScopes()->where('employee_id', $user->id)->first();
        $punches = \App\Models\Payroll\AttendancePunch::withoutGlobalScopes()->where('employee_id', $user->id)->count();
        $this->check($checks, 'Two punches recorded', $punches === 2, $punches);
        $this->check($checks, 'Punches build the day attendance (in and out)', $att && filled($att->check_in) && filled($att->check_out), $att ? "{$att->check_in} - {$att->check_out}" : '-');
        return '/payroll/attendance';
    }

    private function stepPayrollRun(array &$ctx, array &$checks): string
    {
        $pc = app(\App\Http\Controllers\Payroll\PayrollRunController::class);
        $this->call($pc, 'store', ['month' => (int) now()->format('n'), 'year' => (int) now()->format('Y'), 'employee_ids' => [$ctx['employee_id']]]);
        $rec = \App\Models\Payroll\PayrollRecord::withoutGlobalScopes()->where('employee_id', $ctx['employee_id'])->latest('id')->first();
        $this->check($checks, 'Payroll record generated as draft', $rec && $rec->status === 'draft', $rec?->payroll_number);
        $this->check($checks, 'Net pay is worked out', $rec && (float) $rec->net_payable > 0 && (float) $rec->net_payable <= (float) $rec->total_earnings, $rec ? $this->money($rec->net_payable) : '-');
        $ctx['payroll_id'] = $rec?->id;

        $cash = \App\Models\Finance\Account\Account::query()->active()->posting()->cashOrBank()->value('id');
        $this->check($checks, 'A cash or bank account exists to pay from', (bool) $cash, $cash);
        $this->call($pc, 'pay', ['bank_account_id' => $cash, 'payment_date' => now()->format('Y-m-d')], [$rec]);
        $rec = $rec->fresh();
        $this->check($checks, 'Record is Paid', $rec->status === 'paid', $rec->status);
        $led = $this->ledger((int) $cash, $rec->id, \App\Models\Payroll\PayrollRecord::class);
        $this->check($checks, 'Bank credited with the net pay', abs($led['cr'] - (float) $rec->net_payable) < 0.01, $this->money($led['cr']));
        $this->check($checks, 'Salary voucher balances', abs($this->entryImbalance($rec->id, \App\Models\Payroll\PayrollRecord::class)) < 0.01, 'balanced');

        $this->callNoRequest($pc, 'disapprove', [$rec]);
        $this->check($checks, 'Un-paying removes the voucher', $this->entryImbalance($rec->id, \App\Models\Payroll\PayrollRecord::class) == 0.0 && $rec->fresh()->status === 'draft', 'draft again');
        return '/payroll/runs';
    }

    private function stepEmployeeLoan(array &$ctx, array &$checks): string
    {
        $this->call(app(\App\Http\Controllers\Payroll\EmployeeLoanController::class), 'store', [
            'employee_id' => $ctx['employee_id'], 'loan_amount' => 1200, 'total_installments' => 4, 'installment_amount' => 300, 'start_date' => now()->format('Y-m-d'),
        ]);
        $loan = \App\Models\Payroll\EmployeeLoan::withoutGlobalScopes()->where('employee_id', $ctx['employee_id'])->latest('id')->first();
        $this->check($checks, 'Loan saved and Active', $loan && $loan->status === 'active', $loan?->status);
        $this->check($checks, 'Remaining equals the loan amount', $loan && (float) $loan->remaining_amount === 1200.0, $loan?->remaining_amount);
        $ctx['loan_id'] = $loan?->id;
        return '/employee-loans';
    }

    private function stepGuardPayment(array &$ctx, array &$checks): string
    {
        $this->guard($checks, 'Approved payment', app(PaymentController::class), 'destroy', 'payments', $ctx['payment_id']);
        return '/transaction/payments';
    }

    private function stepLinks(array &$ctx, array &$checks): string
    {
        $enq = Enquiry::find($ctx['enquiry_id']);
        $quo = Quotation::find($ctx['quotation_id']);
        $job = Job::find($ctx['job_id']);
        $inv = CustomerInvoice::find($ctx['invoice_id']);
        $bill = SupplierInvoice::find($ctx['supplier_invoice_id']);
        $cn = CreditNote::find($ctx['credit_note_id']);
        $col = Collection::find($ctx['collection_id']);
        $exp = Expense::find($ctx['expense_id']);
        $pro = Prospect::find($ctx['prospect_id']);
        $cid = (int) $ctx['customer_id'];
        $this->check($checks, 'Prospect → customer link', $pro && (int) $pro->customer_id === $cid, $pro?->customer_id);
        $this->check($checks, 'Enquiry → quotation (quotation.enquiry_id)', $quo && (int) $quo->enquiry_id === (int) $enq?->id, $quo?->enquiry_id);
        $this->check($checks, 'Quotation → job (both directions)', $quo && $job && (int) $quo->job_id === (int) $job->id && (int) $job->quotation_id === (int) $quo->id, $job?->row_no);
        $this->check($checks, 'Job → customer invoice', $inv && (int) $inv->job_id === (int) $job?->id, $inv?->row_no);
        $this->check($checks, 'Job → supplier invoice', $bill && (int) $bill->job_id === (int) $job?->id, $bill?->row_no);
        $this->check($checks, 'Credit note → the invoice it reduces', $cn && (int) $cn->invoice_id === (int) $inv?->id, $cn?->row_no);
        $this->check($checks, 'Collection → invoice allocation', DB::table('collection_invoices')->where('collection_id', $col?->id)->where('customer_invoice_id', $inv?->id)->exists(), $col?->row_no);
        $this->check($checks, 'Customer on every document is the same', collect([$enq?->customer_id, $quo?->customer_id, $job?->customer_id, $inv?->customer_id, $cn?->customer_id, $col?->customer_id])->every(fn ($v) => (int) $v === $cid), 'customer ' . $cid);
        $this->check($checks, 'Payment → supplier invoice allocation', DB::table('payment_invoices')->where('payment_id', $ctx['payment_id'])->where('supplier_invoice_id', $bill?->id)->exists(), 'allocated');
        $this->check($checks, 'Supplier invoice → supplier', $bill && (int) $bill->supplier_id === (int) $ctx['supplier_id'], $bill?->supplier_id);
        foreach ([[CustomerInvoice::class, $inv?->id, 'Invoice'], [CreditNote::class, $cn?->id, 'Credit note'], [Collection::class, $col?->id, 'Collection'], [Expense::class, $exp?->id, 'Expense'], [Payment::class, $ctx['payment_id'], 'Payment'], ['supplier_invoice', $bill?->id, 'Supplier invoice']] as [$type, $id, $label]) {
            $this->check($checks, $label . ' has its ledger entry', DB::table('finance')->where('linked_type', $type)->where('linked_id', $id)->exists(), 'posted');
        }
        return '/operation/jobs';
    }

    // ───────────────────────── delete checks ─────────────────────────

    /** Try a delete through the real controller. @return array{0:int,1:array} */
    private function attemptDelete(object $controller, string $method, int $id): array
    {
        $request = Request::create('/autocheck-internal', 'DELETE');
        $request->setLaravelSession(app('session.store'));
        app()->instance('request', $request);
        $res = $controller->$method($id);
        $data = method_exists($res, 'getData') ? (array) $res->getData(true) : [];
        $code = method_exists($res, 'getStatusCode') ? $res->getStatusCode() : 200;
        $status = isset($data['status']) ? (string) $data['status'] : (array_key_exists('success', $data) ? ($data['success'] ? 'ok' : 'error') : 'ok');
        $failed = $code >= 400 || in_array($status, ['error', 'warning'], true);

        return [$failed ? 422 : 200, $data];
    }

    private function exists(string $table, ?int $id): bool
    {
        $q = DB::table($table)->where('id', $id);
        if (\Schema::hasColumn($table, 'deleted_at')) {
            $q->whereNull('deleted_at');
        }
        return $q->exists();
    }

    private function guard(array &$checks, string $label, object $controller, string $method, string $table, ?int $id): void
    {
        [$code, $data] = $this->attemptDelete($controller, $method, (int) $id);
        $this->check($checks, $label . ' — delete is refused', $code >= 400, $data['message'] ?? 'no message');
        $this->check($checks, $label . ' — record is still there', $this->exists($table, $id), 'kept');
    }

    private function stepGuardExpense(array &$ctx, array &$checks): string
    {
        $this->guard($checks, 'Approved expense', app(ExpenseController::class), 'destroy', 'expenses', $ctx['expense_id']);
        return '/finance/expense';
    }

    private function stepGuardCollection(array &$ctx, array &$checks): string
    {
        $this->guard($checks, 'Approved collection', app(CollectionController::class), 'destroy', 'collections', $ctx['collection_id']);
        return '/collection';
    }

    private function stepGuardCreditNote(array &$ctx, array &$checks): string
    {
        $this->guard($checks, 'Approved credit note', app(CreditNoteController::class), 'delete', 'credit_notes', $ctx['credit_note_id']);
        return '/adjustment/credit-note';
    }

    private function stepGuardCustomerInvoice(array &$ctx, array &$checks): string
    {
        $this->guard($checks, 'Approved invoice', app(CustomerInvoiceController::class), 'delete', 'customer_invoices', $ctx['invoice_id']);
        return '/invoice/customer';
    }

    private function stepGuardSupplierInvoice(array &$ctx, array &$checks): string
    {
        $this->guard($checks, 'Approved supplier invoice', app(SupplierInvoiceController::class), 'delete', 'supplier_invoices', $ctx['supplier_invoice_id']);
        return '/invoice/supplier';
    }

    private function stepGuardJob(array &$ctx, array &$checks): string
    {
        $this->guard($checks, 'Job with invoices', app(\App\Http\Controllers\Job\JobController::class), 'delete', 'jobs', $ctx['job_id']);
        return '/operation/jobs';
    }

    private function stepGuardQuotation(array &$ctx, array &$checks): string
    {
        $this->guard($checks, 'Quotation converted to a job', app(QuotationController::class), 'delete', 'quotations', $ctx['quotation_id']);
        return '/sales/quotations';
    }

    private function stepGuardEnquiry(array &$ctx, array &$checks): string
    {
        $this->guard($checks, 'Enquiry converted to a quotation', app(EnquiryController::class), 'delete', 'enquiries', $ctx['enquiry_id']);
        return '/sales/enquiries';
    }

    private function stepGuardProspect(array &$ctx, array &$checks): string
    {
        $this->guard($checks, 'Prospect converted to a customer', app(ProspectController::class), 'delete', 'prospects', $ctx['prospect_id']);
        return '/prospects';
    }

    private function stepGuardCustomer(array &$ctx, array &$checks): string
    {
        $this->guard($checks, 'Customer with transactions', app(CustomerController::class), 'delete', 'customers', $ctx['customer_id']);
        return '/customers/' . $ctx['customer_id'];
    }

    private function stepGuardSupplier(array &$ctx, array &$checks): string
    {
        $this->guard($checks, 'Supplier with a bill', app(SupplierController::class), 'delete', 'suppliers', $ctx['supplier_id']);
        return '/suppliers/' . $ctx['supplier_id'];
    }

    private function stepFreeDeletes(array &$ctx, array &$checks): string
    {
        $tag = now()->format('His');
        $made = function (string $label, string $table, ?int $id, object $c, string $m) use (&$checks) {
            $this->freeDelete($checks, $label, $table, $id, $c, $m);
        };

        // Orphan customer / supplier / prospect: nothing points at them, so they can go.
        $this->call(app(CustomerController::class), 'store', ['name_en' => "AUTOCHECK Orphan Customer $tag", 'name_ar' => 'عميل', 'currency' => 'SAR', 'business_type' => 'unregistered', 'email' => 'orphan@example.com', 'phone' => '+966500001234', 'country' => 'SA']);
        $cust = Customer::where('name_en', "AUTOCHECK Orphan Customer $tag")->latest('id')->first();
        $this->call(app(SupplierController::class), 'store', ['name_en' => "AUTOCHECK Orphan Supplier $tag", 'name_ar' => 'مورد', 'currency' => 'SAR', 'business_type' => 'unregistered', 'country' => 'SA', 'phone' => '+966500005678']);
        $sup = Supplier::where('name_en', "AUTOCHECK Orphan Supplier $tag")->latest('id')->first();
        $this->call(app(ProspectController::class), 'quickStore', ['quick_prospect_name' => "AUTOCHECK Orphan Prospect $tag", 'quick_prospect_email' => 'orphanp@example.com', 'quick_prospect_phone' => '+966500009876']);
        $pro = Prospect::where('name', "AUTOCHECK Orphan Prospect $tag")->latest('id')->first();

        // Pending enquiry + pending quotation for the orphan customer.
        $this->call(app(EnquiryController::class), 'store', ['customer' => encodeId($cust->id), 'shipment_category' => 'container', 'pol' => 'Jebel Ali', 'pod' => 'Dammam', 'activity_id' => '2', 'remark' => 'AUTOCHECK orphan']);
        $enq = Enquiry::where('customer_id', $cust->id)->latest('id')->first();
        $this->call(app(QuotationController::class), 'store', ['customer' => encodeId($cust->id), 'posted_at' => now()->format('Y-m-d'), 'valid_until' => now()->addDays(30)->format('Y-m-d'), 'activity_id' => '2', 'pol' => 'Jebel Ali', 'pod' => 'Dammam', 'terms' => 'AUTOCHECK orphan']);
        $quo = Quotation::where('customer_id', $cust->id)->latest('id')->first();

        // The customer is now protected by its enquiry and quotation …
        $this->guard($checks, 'Customer that has an enquiry and a quotation', app(CustomerController::class), 'delete', 'customers', $cust?->id);
        // … so delete from the top down.
        $made('Pending quotation', 'quotations', $quo?->id, app(QuotationController::class), 'delete');
        $made('Pending enquiry', 'enquiries', $enq?->id, app(EnquiryController::class), 'delete');

        // Draft documents on the job: a draft customer invoice, a draft supplier invoice, a pending expense.
        $this->call(app(CustomerInvoiceController::class), 'store', ['job_id' => $ctx['job_id'], 'customer' => encodeId($ctx['customer_id']), 'invoice_date' => now()->format('Y-m-d'), 'due_date' => now()->addDays(30)->format('Y-m-d'), 'currency' => 'SAR', 'currency_rate' => 1, 'description_id' => ['2'], 'comment' => ['AUTOCHECK draft'], 'unit_id' => ['1'], 'quantity' => [1], 'unit_price' => ['100'], 'tax' => ['STANDARD'], 'account' => [self::REVENUE]]);
        $draftInv = CustomerInvoice::where('customer_id', $ctx['customer_id'])->where('status', 1)->latest('id')->first();
        $this->call(app(SupplierInvoiceController::class), 'store', ['job_id' => $ctx['job_id'], 'supplier' => encodeId($ctx['supplier_id']), 'invoice_date' => now()->format('Y-m-d'), 'invoice_number' => 'AUTOCHECK-D' . $tag, 'due_date' => now()->addDays(30)->format('Y-m-d'), 'currency' => 'SAR', 'currency_rate' => 1, 'description_id' => ['2'], 'comment' => ['AUTOCHECK draft'], 'unit_id' => ['1'], 'quantity' => [1], 'unit_price' => ['100'], 'tax' => ['STANDARD'], 'account' => [self::COST]]);
        $draftBill = SupplierInvoice::where('supplier_id', $ctx['supplier_id'])->where('status', 1)->latest('id')->first();
        $this->call(app(ExpenseController::class), 'store', ['posted_at' => now()->format('Y-m-d'), 'reference_number' => 'AUTOCHECK-DEL', 'payment_status' => 'unpaid', 'main_account' => self::BANK, 'amount_excluding_vat' => 50, 'amount_including_vat' => 57.5, 'account' => [self::EXPENSE], 'comment' => ['AUTOCHECK'], 'quantity' => [1], 'unit_price' => ['50'], 'tax' => ['STANDARD'], 'item_id' => [], 'employee_id' => ['']]);
        $draftExp = Expense::where('reference_number', 'AUTOCHECK-DEL')->latest('id')->first();
        $made('Draft customer invoice', 'customer_invoices', $draftInv?->id, app(CustomerInvoiceController::class), 'delete');
        $made('Draft supplier invoice', 'supplier_invoices', $draftBill?->id, app(SupplierInvoiceController::class), 'delete');
        $made('Pending expense', 'expenses', $draftExp?->id, app(ExpenseController::class), 'destroy');

        // Finally the master records that nothing points at any more.
        $made('Customer with no transactions', 'customers', $cust?->id, app(CustomerController::class), 'delete');
        $made('Supplier with no transactions', 'suppliers', $sup?->id, app(SupplierController::class), 'delete');
        $made('Prospect not converted', 'prospects', $pro?->id, app(ProspectController::class), 'delete');
        return '/customers';
    }

    private function freeDelete(array &$checks, string $label, string $table, ?int $id, object $controller, string $method): void
    {
        $this->check($checks, $label . ' — created for the test', (bool) $id && $this->exists($table, $id), $id);
        if (!$id) {
            return;
        }
        [$code, $data] = $this->attemptDelete($controller, $method, $id);
        $this->check($checks, $label . ' — delete is allowed', $code < 400, $data['message'] ?? '');
        $this->check($checks, $label . ' — record is gone', !$this->exists($table, $id), 'deleted');
    }

    // ───────────────────────── helpers ─────────────────────────

    /** Render a report page component and return its visible text. */
    private function reportText(string $class, array $props = [], array $mount = []): string
    {
        $html = \Livewire\Livewire::test($class, $mount)->set($props)->html();
        $html = preg_replace('/<(script|style)\b.*?<\/\1>/s', '', $html);
        return trim(html_entity_decode(preg_replace('/\s+/', ' ', strip_tags($html)), ENT_QUOTES));
    }

    private function ledger(int $account, ?int $linkedId, string $type): array
    {
        $row = DB::table('finance_subs as fs')->join('finance as f', 'fs.finance_id', '=', 'f.id')
            ->where('f.linked_type', $type)->where('f.linked_id', $linkedId)->where('fs.account_id', $account)
            ->selectRaw('COALESCE(SUM(fs.base_debit),0) dr, COALESCE(SUM(fs.base_credit),0) cr')->first();
        return ['dr' => (float) $row->dr, 'cr' => (float) $row->cr];
    }

    private function entryImbalance(?int $linkedId, string $type): float
    {
        $row = DB::table('finance_subs as fs')->join('finance as f', 'fs.finance_id', '=', 'f.id')
            ->where('f.linked_type', $type)->where('f.linked_id', $linkedId)
            ->selectRaw('COALESCE(SUM(fs.base_debit),0) - COALESCE(SUM(fs.base_credit),0) d')->first();
        return (float) $row->d;
    }

    /** Remove everything a run created (head mode keeps data so you can look at it). */
    public function cleanup(array $ctx): array
    {
        $removed = [];
        DB::transaction(function () use ($ctx, &$removed) {
            // Ledger entries first (they point at the documents).
            $links = [
                [Collection::class, 'collection_id'], [CreditNote::class, 'credit_note_id'], [CustomerInvoice::class, 'invoice_id'],
                [Expense::class, 'expense_id'], [Payment::class, 'payment_id'], ['supplier_invoice', 'supplier_invoice_id'],
            ];
            foreach ($links as [$type, $key]) {
                if (empty($ctx[$key])) continue;
                $fin = DB::table('finance')->where('linked_type', $type)->where('linked_id', $ctx[$key])->pluck('id');
                DB::table('finance_subs')->whereIn('finance_id', $fin)->delete();
                DB::table('finance')->whereIn('id', $fin)->delete();
            }
            // Payroll, fleet and logistics extras created by the newer steps.
            if (!empty($ctx['payroll_id'])) {
                $fin = DB::table('finance')->where('linked_type', \App\Models\Payroll\PayrollRecord::class)->where('linked_id', $ctx['payroll_id'])->pluck('id');
                DB::table('finance_subs')->whereIn('finance_id', $fin)->delete();
                DB::table('finance')->whereIn('id', $fin)->delete();
                DB::table('payroll_records')->where('id', $ctx['payroll_id'])->delete();
            }
            if (!empty($ctx['employee_id'])) {
                foreach (['loan_installments' => 'employee_id', 'attendance_punches' => 'employee_id', 'attendance' => 'employee_id', 'salary_structures' => 'employee_id', 'payroll_records' => 'employee_id'] as $t => $col) {
                    if (\Schema::hasTable($t) && \Schema::hasColumn($t, $col)) DB::table($t)->where($col, $ctx['employee_id'])->delete();
                }
                if (\Schema::hasTable('loan_installments') && !empty($ctx['loan_id'])) DB::table('loan_installments')->where('loan_id', $ctx['loan_id'])->delete();
                if (!empty($ctx['loan_id'])) DB::table('employee_loans')->where('id', $ctx['loan_id'])->delete();
                DB::table('users')->where('id', $ctx['employee_id'])->delete();
                $removed[] = 'employee';
            }
            if (!empty($ctx['shift_id'])) DB::table('shifts')->where('id', $ctx['shift_id'])->delete();
            foreach (['trip_id' => 'trips', 'vehicle_id' => 'vehicles', 'driver_id' => 'drivers'] as $k => $t) {
                if (!empty($ctx[$k])) { DB::table($t)->where('id', $ctx[$k])->delete(); $removed[] = $t; }
            }
            if (!empty($ctx['job_id'])) {
                DB::table('job_milestones')->where('job_id', $ctx['job_id'])->delete();
                $docIds = DB::table('documents')->where('documentable_id', $ctx['job_id'])->where('documentable_type', \App\Models\Job\Job::class)->get(['id', 'file_path']);
                foreach ($docIds as $d) { if ($d->file_path) \Storage::disk('public')->delete($d->file_path); }
                DB::table('documents')->whereIn('id', $docIds->pluck('id'))->delete();
            }
            $docs = [
                ['collection_id', 'collection_invoices', 'collection_id', 'collections'],
                ['payment_id', 'payment_invoices', 'payment_id', 'payments'],
                ['credit_note_id', 'credit_note_subs', 'credit_note_id', 'credit_notes'],
                ['invoice_id', 'customer_invoice_subs', 'customer_invoice_id', 'customer_invoices'],
                ['expense_id', 'expense_subs', 'expense_id', 'expenses'],
                ['supplier_invoice_id', 'supplier_invoice_subs', 'supplier_invoice_id', 'supplier_invoices'],
                ['quotation_id', null, null, 'quotations'],
                ['job_id', null, null, 'jobs'],
                ['enquiry_id', 'enquiry_subs', 'enquiry_id', 'enquiries'],
                ['prospect_id', null, null, 'prospects'],
                ['supplier_id', null, null, 'suppliers'],
                ['customer_id', null, null, 'customers'],
            ];
            $children = [
                'quotations' => ['quotation_containers', 'quotation_packages', 'quotation_charges'],
                'jobs' => ['job_containers', 'job_packages', 'job_charges'],
            ];
            foreach ($docs as [$key, $subTable, $subKey, $table]) {
                if (empty($ctx[$key])) continue;
                if ($subTable && \Schema::hasTable($subTable)) {
                    DB::table($subTable)->where($subKey, $ctx[$key])->delete();
                }
                foreach ($children[$table] ?? [] as $child) {
                    $fk = $table === 'quotations' ? 'quotation_id' : 'job_id';
                    if (\Schema::hasTable($child)) DB::table($child)->where($fk, $ctx[$key])->delete();
                }
                DB::table($table)->where('id', $ctx[$key])->delete();
                $removed[] = $table;
            }
        });
        return $removed;
    }
}
