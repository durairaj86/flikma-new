<?php

namespace App\Services\Finance;

use App\Enums\CustomerInvoiceEnum;
use Illuminate\Support\Facades\DB;

/**
 * Keeps the link between a customer invoice line and the supplier invoice line it was billed from.
 * The supplier line carries it in invoice_json; a supplier invoice that is linked cannot be edited,
 * deleted or moved back to draft until the customer invoice is deleted (or cancelled).
 */
class SupplierInvoiceLinks
{
    /** Point every supplier line used by this customer invoice back at the invoice's current lines. */
    public function sync(int $customerInvoiceId): void
    {
        $this->release($customerInvoiceId);

        $invoice = DB::table('customer_invoices')->where('id', $customerInvoiceId)->first(['id', 'row_no']);
        $lines = DB::table('customer_invoice_subs')
            ->where('customer_invoice_id', $customerInvoiceId)->whereNull('deleted_at')
            ->where('source_type', 'supplier_invoice')->whereNotNull('source_sub_id')
            ->get(['id', 'source_sub_id']);

        foreach ($lines->groupBy('source_sub_id') as $supplierSubId => $group) {
            $current = $this->read($supplierSubId);
            foreach ($group as $line) {
                $current[] = ['customer_invoice_id' => $customerInvoiceId, 'customer_invoice_sub_id' => $line->id, 'row_no' => $invoice->row_no ?? null];
            }
            $this->write($supplierSubId, $current);
        }
    }

    /** Drop this customer invoice from every supplier line (invoice deleted or cancelled). */
    public function release(int $customerInvoiceId): void
    {
        $rows = DB::table('supplier_invoice_subs')->whereNotNull('invoice_json')->get(['id', 'invoice_json']);
        foreach ($rows as $row) {
            $entries = json_decode($row->invoice_json, true) ?: [];
            $kept = array_values(array_filter($entries, fn ($e) => (int) ($e['customer_invoice_id'] ?? 0) !== $customerInvoiceId));
            if (count($kept) !== count($entries)) {
                $this->write($row->id, $kept);
            }
        }
    }

    /** Numbers of the live customer invoices that bill this supplier invoice (empty = free to edit / delete). */
    public function blockers(int $supplierInvoiceId, ?int $exceptInvoiceId = null): array
    {
        return DB::table('customer_invoice_subs as s')
            ->join('customer_invoices as i', 'i.id', '=', 's.customer_invoice_id')
            ->where('s.source_type', 'supplier_invoice')->where('s.source_id', $supplierInvoiceId)
            ->whereNull('s.deleted_at')->whereNull('i.deleted_at')
            ->where('i.status', '!=', CustomerInvoiceEnum::CANCELLED->value)
            ->when($exceptInvoiceId, fn ($q) => $q->where('i.id', '!=', $exceptInvoiceId))
            ->orderBy('i.id')->pluck('i.row_no')->unique()->values()->all();
    }

    /** JSON error response when the supplier invoice is linked, else null. */
    public function refusal(int $supplierInvoiceId): ?\Illuminate\Http\JsonResponse
    {
        $linked = $this->blockers($supplierInvoiceId);
        if (!$linked) {
            return null;
        }

        return response()->json([
            'status' => 'error',
            'message' => __('This supplier invoice is billed in customer invoice :no. Delete that customer invoice first.', ['no' => implode(', ', $linked)]),
        ], 422);
    }

    private function read($supplierSubId): array
    {
        return json_decode((string) DB::table('supplier_invoice_subs')->where('id', $supplierSubId)->value('invoice_json'), true) ?: [];
    }

    private function write($supplierSubId, array $entries): void
    {
        DB::table('supplier_invoice_subs')->where('id', $supplierSubId)->update(['invoice_json' => $entries ? json_encode($entries) : null]);
    }
}
