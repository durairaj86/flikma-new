<?php

namespace App\Http\Controllers\Customer;

use App\Enums\CollectionEnum;
use App\Enums\CustomerInvoiceEnum;
use App\Http\Controllers\Controller;
use App\Models\Customer\Customer;
use App\Models\Finance\CustomerInvoice\CustomerInvoice;
use App\Traits\Finance\AgingAsOf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Split-view customer page: customer list on the left, details with
 * General / Transactions / Invoices / Statement / Aging tabs on the right.
 *
 * Money rules (so every tab agrees):
 *  - Balance Due  = Accounts Receivable ledger balance for the customer (same source as the Statement).
 *  - Invoices / Aging = open approved invoices; any gap to the ledger is shown as
 *    "unapplied credits / advances" instead of being hidden.
 */
class CustomerDetailController extends Controller
{
    use AgingAsOf;

    private const TABS = ['general', 'transactions', 'invoices', 'statement', 'aging'];

    public function show($id)
    {
        $customer = Customer::findOrFail($id);

        $customers = Customer::select('id', 'name_en', 'email')
            ->orderBy('name_en')
            ->get();
        $balances = $this->ledgerBalances();
        foreach ($customers as $c) {
            $c->balance = (float) ($balances[$c->id] ?? 0);
        }

        return view('modules.customer.show', compact('customer', 'customers'));
    }

    public function tab($id, string $tab)
    {
        abort_unless(in_array($tab, self::TABS, true), 404);
        $customer = Customer::findOrFail($id);

        return view('modules.customer.tabs.' . $tab, array_merge(
            ['customer' => $customer],
            $this->{'data' . ucfirst($tab)}($customer)
        ));
    }

    /** @return array<int,float> customer id => AR ledger balance */
    private function ledgerBalances(): array
    {
        return DB::table('finance_subs as fs')
            ->join('finance as f', 'fs.finance_id', '=', 'f.id')
            ->where('fs.company_id', companyId())
            ->whereNotNull('fs.customer_id')
            ->whereIn('fs.account_id', $this->arAccountIds())
            ->where('f.is_approved', 1)
            ->groupBy('fs.customer_id')
            ->selectRaw('fs.customer_id, SUM(fs.base_debit - fs.base_credit) as bal')
            ->pluck('bal', 'customer_id')
            ->map(fn ($v) => round((float) $v, 2))
            ->all();
    }

    private function arAccountIds(): array
    {
        return DB::table('accounts')->where('code', '1130')->pluck('id')->all() ?: [5];
    }

    private function openInvoices(Customer $customer): array
    {
        $today = Carbon::today();
        $settled = $this->settledAsOf('customer', $today);
        $rows = [];
        foreach (CustomerInvoice::where('customer_id', $customer->id)
                     ->where('status', CustomerInvoiceEnum::APPROVED->value)
                     ->orderBy('invoice_date')->get() as $inv) {
            $balance = round((float) $inv->grand_total - ($settled[$inv->id] ?? 0.0), 2);
            if ($balance <= 0) {
                continue;
            }
            $due = Carbon::parse($inv->due_at ?? $inv->due_date ?? $inv->invoice_date);
            $rows[] = [
                'id' => $inv->id,
                'no' => $inv->row_no,
                'date' => Carbon::parse($inv->invoice_date),
                'due' => $due,
                'total' => (float) $inv->grand_total,
                'balance' => $balance,
                'days' => (int) $due->diffInDays($today, false),
            ];
        }

        return $rows;
    }

    private function balanceDue(Customer $customer): float
    {
        return (float) ($this->ledgerBalances()[$customer->id] ?? 0);
    }

    protected function dataGeneral(Customer $customer): array
    {
        $open = $this->openInvoices($customer);

        return [
            'balanceDue' => $this->balanceDue($customer),
            'overdue' => round(collect($open)->where('days', '>', 0)->sum('balance'), 2),
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
            ];
        }

        return ['rows' => $rows];
    }

    protected function dataStatement(Customer $customer): array
    {
        $entries = DB::table('finance_subs as fs')
            ->join('finance as f', 'fs.finance_id', '=', 'f.id')
            ->where('fs.company_id', companyId())
            ->where('fs.customer_id', $customer->id)
            ->whereIn('fs.account_id', $this->arAccountIds())
            ->where('f.is_approved', 1)
            ->orderBy('fs.reference_date')->orderBy('fs.id')
            ->get(['fs.reference_date', 'fs.reference_no', 'fs.voucher_no', 'f.narration', 'fs.base_debit', 'fs.base_credit']);

        $running = 0.0;
        $rows = [];
        foreach ($entries as $e) {
            $running += $e->base_debit - $e->base_credit;
            $rows[] = [
                'date' => Carbon::parse($e->reference_date),
                'details' => $e->reference_no ?: ($e->voucher_no ?: $e->narration),
                'debit' => (float) $e->base_debit,
                'credit' => (float) $e->base_credit,
                'balance' => round($running, 2),
            ];
        }

        return ['rows' => array_slice($rows, -15)];
    }

    protected function dataAging(Customer $customer): array
    {
        $buckets = ['current' => 0.0, 'b1' => 0.0, 'b2' => 0.0, 'b3' => 0.0, 'b4' => 0.0];
        $open = $this->openInvoices($customer);
        foreach ($open as $inv) {
            $d = $inv['days'];
            $key = $d <= 0 ? 'current' : ($d <= 30 ? 'b1' : ($d <= 60 ? 'b2' : ($d <= 90 ? 'b3' : 'b4')));
            $buckets[$key] += $inv['balance'];
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
