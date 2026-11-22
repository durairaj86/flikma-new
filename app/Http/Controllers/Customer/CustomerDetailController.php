<?php

namespace App\Http\Controllers\Customer;

use App\Enums\CollectionEnum;
use App\Enums\CustomerInvoiceEnum;
use App\Http\Controllers\Controller;
use App\Models\Customer\Customer;
use App\Models\Finance\CustomerInvoice\CustomerInvoice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Split-view customer page: customer list on the left, details with
 * General / Activity / Transactions / Invoices / Statement / Aging tabs on the right.
 *
 * Money rules (so every tab agrees):
 *  - Balance Due  = Accounts Receivable ledger balance for the customer (same source as the Statement).
 *  - Invoices / Aging = open approved invoices; any gap to the ledger is shown as
 *    "unapplied credits / advances" instead of being hidden.
 */
class CustomerDetailController extends Controller
{
    private const TABS = ['general', 'activity', 'transactions', 'invoices', 'statement', 'aging'];

    /** Customers per page in the left-hand list. */
    private const PER_PAGE = 30;

    public function show($id)
    {
        $customer = Customer::findOrFail($id);

        // open the list on the page that holds this customer
        $before = Customer::where(fn ($w) => $w->where('name_en', '<', $customer->name_en)
            ->orWhere(fn ($x) => $x->where('name_en', $customer->name_en)->where('id', '<', $customer->id)))->count();
        $customers = $this->listItems(null, intdiv($before, self::PER_PAGE) + 1);

        return view('modules.customer.show', compact('customer', 'customers'));
    }

    /** Live search for the left-hand list (server side); returns the rendered list items. */
    public function search(Request $request)
    {
        $customers = $this->listItems(trim((string) $request->query('q')), max(1, (int) $request->query('page', 1)));

        return view('modules.customer.partials.split-items', [
            'customers' => $customers,
            'activeId' => (int) $request->query('active'),
        ]);
    }

    private function listItems(?string $q, int $page = 1)
    {
        $query = Customer::select('id', 'name_en', 'name_ar', 'email', 'phone', 'row_no', 'vat_number')->orderBy('name_en')->orderBy('id');
        if ($q !== null && $q !== '') {
            $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $q) . '%';
            $query->where(function ($w) use ($like) {
                foreach (['name_en', 'name_ar', 'email', 'phone', 'row_no', 'vat_number'] as $col) {
                    $w->orWhere($col, 'like', $like);
                }
            });
        }
        $items = $query->paginate(self::PER_PAGE, ['*'], 'page', $page);
        $balances = $this->account()->ledgerBalances();
        foreach ($items as $c) {
            $c->balance = (float) ($balances[$c->id] ?? 0);
        }

        return $items;
    }

    public function tab($id, string $tab)
    {
        abort_unless(in_array($tab, self::TABS, true), 404);
        $customer = Customer::findOrFail($id);

        return view('modules.customer.tabs.' . $tab, array_merge(
            ['customer' => $customer, 'baseCurrency' => strtoupper(optional(authUserCompany())->base_currency ?: 'SAR')],
            $this->{'data' . ucfirst($tab)}($customer)
        ));
    }

    /** Money figures shared with the customer portal (balance, open invoices, statement). */
    private function account(): \App\Services\Customer\CustomerAccount
    {
        return $this->account ??= new \App\Services\Customer\CustomerAccount();
    }

    private ?\App\Services\Customer\CustomerAccount $account = null;

    private function openInvoices(Customer $customer): array
    {
        return $this->account()->openInvoices($customer);
    }

    private function balanceDue(Customer $customer): float
    {
        return $this->account()->balanceDue($customer);
    }

    protected function dataGeneral(Customer $customer): array
    {
        $open = $this->openInvoices($customer);

        return [
            'balanceDue' => $this->balanceDue($customer),
            'overdue' => round(collect($open)->where('days', '>', 0)->sum('balance'), 2),
            'portalUsers' => \App\Models\Portal\PortalUser::where('company_id', companyId())->where('customer_id', $customer->id)->orderBy('name')->get(),
            'canEditCustomer' => userCan('customers.list', 'edit'),
        ];
    }

    /** Customer 360 (CRM): everything that happened with the customer, newest first; ?before= loads older items. */
    protected function dataActivity(Customer $customer): array
    {
        $timeline = new \App\Services\Crm\CustomerTimeline($customer);
        $before = request()->filled('before') ? Carbon::createFromTimestamp((int) request('before')) : null;
        $limit = 40;
        $items = $timeline->items($before, $limit + 1);

        return [
            'items' => $items->take($limit),
            'hasMore' => $items->count() > $limit,
            'olderThan' => $items->take($limit)->last()['at'] ?? null,
            'summary' => $before ? null : $timeline->summary(),
            'kinds' => \App\Services\Crm\CustomerTimeline::KINDS,
            'onlyItems' => (bool) $before,
            'balanceDue' => $before ? null : $this->balanceDue($customer),
        ];
    }

    protected function dataTransactions(Customer $customer): array
    {
        $rows = [];
        foreach (DB::table('collections')->where('customer_id', $customer->id)->whereNull('deleted_at')
                     ->orderByDesc('collection_date')->limit(50)->get() as $c) {
            $rows[] = [
                'date' => Carbon::parse($c->collection_date),
                'type' => __('Collection'),
                'ref' => $c->row_no ?: $c->collection_no,
                'mode' => strtoupper((string) ($c->collection_method ?: $c->payment_method)),
                'amount' => (float) ($c->base_grand_total ?: $c->base_amount),
                'status' => CollectionEnum::tryFrom((int) $c->status)?->label() ?? '-',
            ];
        }

        return ['rows' => $rows];
    }

    protected function dataInvoices(Customer $customer): array
    {
        $open = collect($this->openInvoices($customer))->keyBy('id');
        $rows = [];
        foreach (CustomerInvoice::where('customer_id', $customer->id)
                     ->whereIn('status', [CustomerInvoiceEnum::APPROVED->value])
                     ->orderByDesc('invoice_date')->limit(50)->get() as $inv) {
            $rows[] = [
                'id' => $inv->id,
                'no' => $inv->row_no,
                'date' => Carbon::parse($inv->invoice_date),
                'due' => Carbon::parse($inv->due_at ?? $inv->due_date ?? $inv->invoice_date),
                'total' => (float) $inv->grand_total,
                'balance' => (float) ($open[$inv->id]['balance'] ?? 0),
                'currency' => strtoupper((string) $inv->currency),
                'total_base' => (float) $inv->base_grand_total,
                'balance_base' => (float) ($open[$inv->id]['balance_base'] ?? 0),
            ];
        }

        return ['rows' => $rows];
    }

    protected function dataStatement(Customer $customer): array
    {
        return ['rows' => array_slice($this->account()->statement($customer)['rows'], -15)];
    }

    protected function dataAging(Customer $customer): array
    {
        $buckets = ['current' => 0.0, 'b1' => 0.0, 'b2' => 0.0, 'b3' => 0.0, 'b4' => 0.0];
        $open = $this->openInvoices($customer);
        foreach ($open as $inv) {
            $d = $inv['days'];
            $key = $d <= 0 ? 'current' : ($d <= 30 ? 'b1' : ($d <= 60 ? 'b2' : ($d <= 90 ? 'b3' : 'b4')));
            $buckets[$key] += $inv['balance_base'];
        }
        $invoiceTotal = round(array_sum($buckets), 2);

        return [
            'buckets' => $buckets,
            'invoiceTotal' => $invoiceTotal,
            // Ledger balance minus open invoices = unapplied advances/credit notes (negative) or other debits.
            'unapplied' => round($this->balanceDue($customer) - $invoiceTotal, 2),
        ];
    }
}
