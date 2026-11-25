<?php

namespace App\Livewire\Report\Finance;

use App\Enums\CustomerInvoiceEnum;
use App\Enums\SupplierInvoiceEnum;
use App\Livewire\Report\Pack\PackReport;
use Illuminate\Support\Facades\DB;

/**
 * Description Wise Report: what each charge description (the Description master used on invoice lines: "Air Freight Charges",
 * "Customs Clearance" ...) sold on approved customer invoices and cost on approved supplier invoices, and the profit between.
 * Amounts are without VAT, in the company's base currency, by invoice date. Group by description, its category, the customer
 * (a supplier invoice's cost counts for its job's customer) or the month.
 */
class DescriptionWise extends PackReport
{
    protected function title(): string { return __('Description Wise Report'); }
    protected function subtitle(): string { return __('Sales, cost and profit per charge description, from customer and supplier invoice lines'); }

    protected function groups(): array
    {
        return ['description' => __('Description'), 'category' => __('Category'), 'customer' => __('Customer'), 'month' => __('Month')];
    }

    protected function columns(): array
    {
        return [__('Sales lines'), __('Qty sold'), __('Sales'), __('Cost lines'), __('Cost'), __('Profit'), __('Margin %')];
    }

    protected function emptyText(): string
    {
        return __('No invoice lines in this period.');
    }

    /** [key => SQL expression for the group, label SQL] for the sales (ci) and the cost (si) side. */
    private function keys(): array
    {
        return match ($this->groupBy) {
            'category' => ["COALESCE(d.category, '')", "COALESCE(d.category, '')"],
            'customer' => ['COALESCE(ci.customer_id, 0)', 'COALESCE(j.customer_id, 0)'],
            'month' => ["DATE_FORMAT(ci.invoice_date, '%Y-%m')", "DATE_FORMAT(si.invoice_date, '%Y-%m')"],
            default => ['COALESCE(cis.description_id, 0)', 'COALESCE(sis.description_id, 0)'],
        };
    }

    protected function rows(): array
    {
        [$saleKey, $costKey] = $this->keys();

        $sales = DB::table('customer_invoice_subs as cis')
            ->join('customer_invoices as ci', 'ci.id', '=', 'cis.customer_invoice_id')
            ->leftJoin('descriptions as d', 'd.id', '=', 'cis.description_id')
            ->where('ci.company_id', companyId())->whereNull('ci.deleted_at')->whereNull('cis.deleted_at')
            ->where('ci.status', CustomerInvoiceEnum::APPROVED->value)
            ->whereBetween('ci.invoice_date', [$this->startDate, $this->endDate])
            ->groupBy(DB::raw($saleKey))
            ->selectRaw("$saleKey AS k, COUNT(*) AS n, SUM(cis.quantity) AS qty, SUM(cis.base_total) AS amount")
            ->get()->keyBy('k');

        $costs = DB::table('supplier_invoice_subs as sis')
            ->join('supplier_invoices as si', 'si.id', '=', 'sis.supplier_invoice_id')
            ->leftJoin('descriptions as d', 'd.id', '=', 'sis.description_id')
            ->leftJoin('jobs as j', 'j.id', '=', 'si.job_id')
            ->where('si.company_id', companyId())->whereNull('si.deleted_at')
            ->where('si.status', SupplierInvoiceEnum::APPROVED->value)
            ->whereBetween('si.invoice_date', [$this->startDate, $this->endDate])
            ->groupBy(DB::raw($costKey))
            ->selectRaw("$costKey AS k, COUNT(*) AS n, SUM(sis.base_total) AS amount")
            ->get()->keyBy('k');

        $keys = $sales->keys()->merge($costs->keys())->unique();
        $labels = $this->labels($keys->all());

        $rows = [];
        foreach ($keys as $k) {
            $sale = (float) ($sales[$k]->amount ?? 0);
            $cost = (float) ($costs[$k]->amount ?? 0);
            $rows[] = [
                $labels[$k] ?? (string) $k,
                (int) ($sales[$k]->n ?? 0),
                (float) ($sales[$k]->qty ?? 0),
                round($sale, 2),
                (int) ($costs[$k]->n ?? 0),
                round($cost, 2),
                round($sale - $cost, 2),
                $sale > 0 ? round(($sale - $cost) * 100 / $sale, 1) : 0.0,
            ];
        }
        // biggest sales first (months in date order)
        usort($rows, fn($a, $b) => $this->groupBy === 'month' ? strcmp($a[0], $b[0]) : $b[3] <=> $a[3]);
        if ($this->groupBy === 'month') {
            foreach ($rows as &$r) {
                $r[0] = \Carbon\Carbon::createFromFormat('Y-m', $r[0])->translatedFormat('F Y');
            }
        }

        return $rows;
    }

    /** Readable name of each group key. */
    private function labels(array $keys): array
    {
        return match ($this->groupBy) {
            // the Arabic name (description_local) when the app is in Arabic and one was entered
            'description' => DB::table('descriptions')->whereIn('id', $keys)->get(['id', 'description', 'description_local'])
                ->mapWithKeys(fn($d) => [$d->id => app()->getLocale() === 'ar' && trim((string) $d->description_local) !== '' ? $d->description_local : (string) $d->description])
                ->all() + [0 => __('(no description)')],
            'customer' => DB::table('customers')->where('company_id', companyId())->whereIn('id', $keys)->pluck('name_en', 'id')->all() + [0 => __('(no customer)')],
            'category' => collect($keys)->mapWithKeys(fn($k) => [$k => $k === '' ? __('(no category)') : $k])->all(),
            default => array_combine($keys, $keys),
        };
    }

    /** Totals add up; quantity is a quantity, the margin is worked out again from the totals. */
    protected function totals(array $rows): array
    {
        $t = parent::totals($rows);
        $t[7] = $t[3] > 0 ? round(($t[3] - $t[5]) * 100 / $t[3], 1) : 0.0;

        return $t;
    }
}
