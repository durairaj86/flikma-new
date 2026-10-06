<?php

namespace App\Http\Controllers\Supplier;

use App\Enums\PaymentEnum;
use App\Enums\SupplierInvoiceEnum;
use App\Http\Controllers\Controller;
use App\Models\Supplier\Supplier;
use App\Models\Finance\SupplierInvoice\SupplierInvoice;
use App\Traits\Finance\AgingAsOf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Split-view supplier page: supplier list on the left, details with
 * General / Transactions / Invoices / Statement / Aging tabs on the right.
 *
 * Money rules (so every tab agrees):
 *  - Balance Due  = Accounts Payable ledger balance for the supplier (same source as the Statement).
 *  - Invoices / Aging = open approved invoices; any gap to the ledger is shown as
 *    "unapplied credits / advances" instead of being hidden.
 */
class SupplierDetailController extends Controller
{
    use AgingAsOf;

    private const TABS = ['general', 'transactions', 'invoices', 'statement', 'aging'];

    public function show($id)
    {
        $supplier = Supplier::findOrFail($id);

        $suppliers = $this->listItems(null);

        return view('modules.supplier.show', compact('supplier', 'suppliers'));
    }

    /** Live search for the left-hand list (server side); returns the rendered list items. */
    public function search(Request $request)
    {
        $suppliers = $this->listItems(trim((string) $request->query('q')));

        return view('modules.supplier.partials.split-items', [
            'suppliers' => $suppliers,
            'activeId' => (int) $request->query('active'),
        ]);
    }

    private function listItems(?string $q)
    {
        $query = Supplier::select('id', 'name_en', 'name_ar', 'email', 'phone', 'row_no', 'vat_number')->orderBy('name_en');
        if ($q !== null && $q !== '') {
            $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $q) . '%';
            $query->where(function ($w) use ($like) {
                foreach (['name_en', 'name_ar', 'email', 'phone', 'row_no', 'vat_number'] as $col) {
                    $w->orWhere($col, 'like', $like);
                }
            });
        }
        $items = $query->limit(200)->get();
        $balances = $this->ledgerBalances();
        foreach ($items as $c) {
            $c->balance = (float) ($balances[$c->id] ?? 0);
        }

        return $items;
    }

    public function tab($id, string $tab)
    {
        abort_unless(in_array($tab, self::TABS, true), 404);
        $supplier = Supplier::findOrFail($id);

        return view('modules.supplier.tabs.' . $tab, array_merge(
            ['supplier' => $supplier],
            $this->{'data' . ucfirst($tab)}($supplier)
        ));
    }

    /** @return array<int,float> supplier id => AR ledger balance */
    private function ledgerBalances(): array
    {
        return DB::table('finance_subs as fs')
            ->join('finance as f', 'fs.finance_id', '=', 'f.id')
            ->where('fs.company_id', companyId())
            ->whereNotNull('fs.supplier_id')
            ->whereIn('fs.account_id', $this->arAccountIds())
            ->where('f.is_approved', 1)
            ->groupBy('fs.supplier_id')
            ->selectRaw('fs.supplier_id, SUM(fs.base_credit - fs.base_debit) as bal')
            ->pluck('bal', 'supplier_id')
            ->map(fn ($v) => round((float) $v, 2))
            ->all();
    }

    private function arAccountIds(): array
    {
        return DB::table('accounts')->where('code', '2110')->pluck('id')->all() ?: [18];
    }

    private function openInvoices(Supplier $supplier): array
    {
        $today = Carbon::today();
        $settled = $this->settledAsOf('supplier', $today);
        $rows = [];
        foreach (SupplierInvoice::where('supplier_id', $supplier->id)
                     ->where('status', SupplierInvoiceEnum::APPROVED->value)
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

    private function balanceDue(Supplier $supplier): float
    {
        return (float) ($this->ledgerBalances()[$supplier->id] ?? 0);
    }

    protected function dataGeneral(Supplier $supplier): array
    {
        $open = $this->openInvoices($supplier);

        return [
            'balanceDue' => $this->balanceDue($supplier),
            'overdue' => round(collect($open)->where('days', '>', 0)->sum('balance'), 2),
        ];
    }

    protected function dataTransactions(Supplier $supplier): array
    {
        $rows = [];
        foreach (DB::table('payments')->where('supplier_id', $supplier->id)->whereNull('deleted_at')
                     ->orderByDesc('payment_date')->limit(50)->get() as $p) {
            $rows[] = [
                'date' => Carbon::parse($p->payment_date),
                'type' => __('Payment'),
                'ref' => $p->row_no,
                'mode' => strtoupper((string) $p->payment_method),
                'amount' => (float) $p->base_grand_total,
                'status' => PaymentEnum::tryFrom((int) $p->status)?->label() ?? '-',
            ];
        }

        return ['rows' => $rows];
    }

    protected function dataInvoices(Supplier $supplier): array
    {
        $open = collect($this->openInvoices($supplier))->keyBy('id');
        $rows = [];
        foreach (SupplierInvoice::where('supplier_id', $supplier->id)
                     ->whereIn('status', [SupplierInvoiceEnum::APPROVED->value])
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

    protected function dataStatement(Supplier $supplier): array
    {
        $entries = DB::table('finance_subs as fs')
            ->join('finance as f', 'fs.finance_id', '=', 'f.id')
            ->where('fs.company_id', companyId())
            ->where('fs.supplier_id', $supplier->id)
            ->whereIn('fs.account_id', $this->arAccountIds())
            ->where('f.is_approved', 1)
            ->orderBy('fs.reference_date')->orderBy('fs.id')
            ->get(['fs.reference_date', 'fs.reference_no', 'fs.voucher_no', 'f.narration', 'fs.base_debit', 'fs.base_credit']);

        $running = 0.0;
        $rows = [];
        foreach ($entries as $e) {
            $running += $e->base_credit - $e->base_debit;
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

    protected function dataAging(Supplier $supplier): array
    {
        $buckets = ['current' => 0.0, 'b1' => 0.0, 'b2' => 0.0, 'b3' => 0.0, 'b4' => 0.0];
        $open = $this->openInvoices($supplier);
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
            'unapplied' => round($this->balanceDue($supplier) - $invoiceTotal, 2),
        ];
    }
}
