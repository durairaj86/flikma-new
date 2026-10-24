<?php

namespace App\Services\Finance;

use App\Enums\CreditNoteEnum;
use App\Enums\CustomerInvoiceEnum;
use App\Enums\DebitNoteEnum;
use App\Enums\ExpenseEnum;
use App\Enums\SupplierInvoiceEnum;
use Illuminate\Support\Facades\DB;

/**
 * The period laid out like the ZATCA VAT return form (boxes 1-16), in company currency.
 *
 * Amount = approved invoices / bills / expenses; Adjustment = credit notes (sales) and debit notes (purchases),
 * always negative; VAT = net of both. Out-of-scope lines are not part of the return. Boxes 8, 9, 14 and 15
 * (imports, reverse charge, prior-period corrections, carried-forward credit) are not tracked here.
 */
class VatReturn
{
    /** tax_code => [English, Arabic] — the VAT category breakdown under the return */
    public const CATEGORIES = [
        'STANDARD' => ['Standard rated (15%)', 'الخاضعة للنسبة الأساسية'],
        'ZERO' => ['Zero rated', 'الخاضعة للنسبة الصفرية'],
        'EXEMPT' => ['Exempt', 'المعفاة'],
        'OUTOFSCOPE' => ['Out of scope', 'خارج النطاق'],
    ];

    /**
     * Every VAT line of the period: [source, tax_code, is_adjustment, net, tax], amounts in company currency.
     * $code limits it to one tax code (drill-down).
     */
    private static function lines(string $from, string $to): \Illuminate\Support\Collection
    {
        $companyId = companyId();
        $rate = fn (string $col) => "COALESCE(NULLIF($col, 0), 1)";

        $sales = DB::table('customer_invoice_subs as s')
            ->join('customer_invoices as h', 'h.id', '=', 's.customer_invoice_id')
            ->whereNull('s.deleted_at')->whereNull('h.deleted_at')
            ->where('h.company_id', $companyId)->where('h.status', CustomerInvoiceEnum::APPROVED->value)
            ->whereBetween('h.invoice_date', [$from, $to])
            ->get(['s.tax_code', DB::raw('0 as adj'), DB::raw('s.base_total as net'), DB::raw('s.base_tax_amount as tax'),
                DB::raw("'sales' as side"), DB::raw("'invoice' as doc_type"), 'h.id as doc_id', 'h.invoice_date as date',
                DB::raw('COALESCE(h.invoice_number, h.row_no) as doc_no'), 'h.customer_id as party_id']);

        $credits = DB::table('credit_note_subs as s')
            ->join('credit_notes as h', 'h.id', '=', 's.credit_note_id')
            ->whereNull('s.deleted_at')->whereNull('h.deleted_at')
            ->where('h.company_id', $companyId)->where('h.status', CreditNoteEnum::APPROVED->value)
            ->whereBetween('h.posted_at', [$from, $to])
            ->get(['s.tax_code', DB::raw('1 as adj'), DB::raw('s.total * ' . $rate('h.currency_rate') . ' as net'), DB::raw('s.tax_amount * ' . $rate('h.currency_rate') . ' as tax'),
                DB::raw("'sales' as side"), DB::raw("'credit_note' as doc_type"), 'h.id as doc_id', 'h.posted_at as date',
                'h.row_no as doc_no', 'h.customer_id as party_id']);

        $bills = DB::table('supplier_invoice_subs as s')
            ->join('supplier_invoices as h', 'h.id', '=', 's.supplier_invoice_id')
            ->where('h.company_id', $companyId)->whereNull('h.deleted_at')
            ->where('h.status', SupplierInvoiceEnum::APPROVED->value)
            ->whereBetween('h.invoice_date', [$from, $to])
            ->get(['s.tax_code', DB::raw('0 as adj'), DB::raw('s.base_total as net'), DB::raw('s.base_tax_amount as tax'),
                DB::raw("'purchases' as side"), DB::raw("'supplier_invoice' as doc_type"), 'h.id as doc_id', 'h.invoice_date as date',
                DB::raw('COALESCE(h.invoice_number, h.row_no) as doc_no'), 'h.supplier_id as party_id']);

        $debits = DB::table('debit_note_subs as s')
            ->join('debit_notes as h', 'h.id', '=', 's.debit_note_id')
            ->whereNull('s.deleted_at')->whereNull('h.deleted_at')
            ->where('h.company_id', $companyId)->where('h.status', DebitNoteEnum::APPROVED->value)
            ->whereBetween('h.posted_at', [$from, $to])
            ->get(['s.tax_code', DB::raw('1 as adj'), DB::raw('s.total * ' . $rate('h.currency_rate') . ' as net'), DB::raw('s.tax_amount * ' . $rate('h.currency_rate') . ' as tax'),
                DB::raw("'purchases' as side"), DB::raw("'debit_note' as doc_type"), 'h.id as doc_id', 'h.posted_at as date',
                'h.row_no as doc_no', 'h.supplier_id as party_id']);

        // expense lines carry no VAT amount of their own: it is the gap between the line with and without tax
        $expenses = DB::table('expense_subs as s')
            ->join('expenses as h', 'h.id', '=', 's.expense_id')
            ->whereNull('h.deleted_at')
            ->where('h.company_id', $companyId)->where('h.status', ExpenseEnum::APPROVED->value)
            ->whereBetween('h.posted_at', [$from, $to])
            ->whereNotNull('s.tax_code')
            ->get(['s.tax_code', DB::raw('0 as adj'), DB::raw('s.total * ' . $rate('h.currency_rate') . ' as net'),
                DB::raw('(s.total_with_tax - s.total) * ' . $rate('h.currency_rate') . ' as tax'),
                DB::raw("'purchases' as side"), DB::raw("'expense' as doc_type"), 'h.id as doc_id', 'h.posted_at as date',
                'h.row_no as doc_no', 'h.vendor_id as party_id']);

        return $sales->concat($credits)->concat($bills)->concat($debits)->concat($expenses);
    }

    public static function compute(string $from, string $to): array
    {
        $box = fn () => ['amount' => 0.0, 'adjustment' => 0.0, 'vat' => 0.0];
        $sales = ['standard' => $box(), 'citizens' => $box(), 'zero' => $box(), 'exports' => $box(), 'exempt' => $box()];
        $purchases = ['standard' => $box(), 'zero' => $box(), 'exempt' => $box()];
        $outOfScope = 0.0;
        $categories = ['sales' => [], 'purchases' => []];

        foreach (self::lines($from, $to) as $l) {
            $code = strtoupper((string) $l->tax_code) ?: (abs((float) $l->tax) > 0.001 ? 'STANDARD' : 'OUTOFSCOPE');
            $sign = $l->adj ? -1 : 1;
            $net = (float) $l->net * $sign;
            $tax = (float) $l->tax * $sign;

            $c = &$categories[$l->side][$code];
            $c = ($c ?? ['net' => 0.0, 'tax' => 0.0]);
            $c['net'] += $net;
            $c['tax'] += $tax;
            unset($c);

            $key = match ($code) { 'STANDARD' => 'standard', 'ZERO' => 'zero', 'EXEMPT' => 'exempt', default => null };
            if ($key === null) {
                $outOfScope += $net;
                continue;
            }
            if ($l->side === 'sales') {
                $sales[$key][$l->adj ? 'adjustment' : 'amount'] += $net;
                $sales[$key]['vat'] += $tax;
            } else {
                $purchases[$key][$l->adj ? 'adjustment' : 'amount'] += $net;
                $purchases[$key]['vat'] += $tax;
            }
        }

        $sum = fn (array $boxes, string $col) => array_sum(array_column($boxes, $col));
        $salesVat = $sum($sales, 'vat');
        $purchaseVat = $sum($purchases, 'vat');

        $rows = function (array $cats) {
            $out = [];
            foreach (self::CATEGORIES as $code => [$en, $ar]) {
                if ($code === 'OUTOFSCOPE' || ! isset($cats[$code])) {
                    continue;
                }
                if (abs($cats[$code]['net']) < 0.001 && abs($cats[$code]['tax']) < 0.001) {
                    continue;
                }
                $out[] = (object) ['code' => $code, 'label_en' => $en, 'label_ar' => $ar, 'net_amount' => $cats[$code]['net'], 'tax_amount' => $cats[$code]['tax']];
            }

            return collect($out);
        };

        return [
            'sales' => $sales,
            'salesTotal' => ['amount' => $sum($sales, 'amount'), 'adjustment' => $sum($sales, 'adjustment'), 'vat' => $salesVat],
            'purchases' => $purchases,
            'purchasesTotal' => ['amount' => $sum($purchases, 'amount'), 'adjustment' => $sum($purchases, 'adjustment'), 'vat' => $purchaseVat],
            'vatDue' => $salesVat - $purchaseVat,   // box 13 (= box 16: 14 and 15 are not tracked)
            'outOfScope' => $outOfScope,
            'salesRows' => $rows($categories['sales']),
            'purchaseRows' => $rows($categories['purchases']),
        ];
    }

    /** Documents behind one VAT category row (drill-down). */
    public static function details(string $side, string $code, string $from, string $to): array
    {
        $labels = ['invoice' => 'Invoice', 'credit_note' => 'Credit Note', 'supplier_invoice' => 'Supplier Invoice', 'debit_note' => 'Debit Note', 'expense' => 'Expense'];
        $lines = self::lines($from, $to)->filter(fn ($l) => $l->side === $side && (strtoupper((string) $l->tax_code) ?: 'STANDARD') === $code);

        $customers = DB::table('customers')->whereIn('id', $lines->where('side', 'sales')->pluck('party_id')->filter())->pluck('name_en', 'id');
        $suppliers = DB::table('suppliers')->whereIn('id', $lines->where('side', 'purchases')->pluck('party_id')->filter())->pluck('name_en', 'id');

        $entries = $lines->groupBy(fn ($l) => $l->doc_type . ':' . $l->doc_id)->map(function ($g) use ($labels, $customers, $suppliers) {
            $f = $g->first();
            $sign = $f->adj ? -1 : 1;

            return (object) [
                'date' => \Carbon\Carbon::parse($f->date)->format('d-m-Y'),
                'sort' => $f->date,
                'doc_number' => $f->doc_no,
                'doc_type' => __($labels[$f->doc_type] ?? $f->doc_type),
                'party' => ($f->side === 'sales' ? $customers[$f->party_id] ?? null : $suppliers[$f->party_id] ?? null) ?: '—',
                'net_amount' => $g->sum('net') * $sign,
                'tax_amount' => $g->sum('tax') * $sign,
            ];
        })->sortBy('sort')->values();

        return ['entries' => $entries, 'totalNet' => $entries->sum('net_amount'), 'totalTax' => $entries->sum('tax_amount')];
    }
}
