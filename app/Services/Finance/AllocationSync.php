<?php

namespace App\Services\Finance;

use Illuminate\Support\Facades\DB;

/**
 * Builds finance_allocations from the settlement records the current code already keeps (collection_invoices,
 * payment_invoices, credit_notes.invoice_id, debit_notes.invoice_id) and the ledger lines those documents posted.
 * Idempotent: only adds what is missing. Until the posting service (phase 6) writes allocations itself, this keeps the
 * table current (php artisan finance:sync-allocations, scheduled hourly).
 */
class AllocationSync
{
    /** @return array<string,int> rows added per source */
    public function run(): array
    {
        return [
            'collections' => $this->collections(),
            'payments' => $this->payments(),
            'credit_notes' => $this->adjustment('credit_notes', 'credit_note', 'customer_invoice', 'customer', 'credit'),
            'debit_notes' => $this->adjustment('debit_notes', 'debit_note', 'supplier_invoice', 'supplier', 'debit'),
            'credit_applications' => $this->creditApplications(),
        ];
    }

    /** collection_invoices: the receipt's AR credit line settles the invoice's AR debit line. */
    private function collections(): int
    {
        return $this->insert("
            SELECT s.company_id, 'customer' AS partner_type, s.partner_id,
                   inv.id AS settled_line_id, 'customer_invoice' AS settled_doc_type, ci.customer_invoice_id AS settled_doc_id,
                   s.id AS settling_line_id, 'collection' AS settling_doc_type, ci.collection_id AS settling_doc_id,
                   'payment' AS allocation_type, COALESCE(c.collection_date, s.entry_date) AS allocation_date,
                   ci.amount AS amount, s.base_credit AS base_amount, s.currency AS currency,
                   ROUND(s.base_credit - ci.amount * (inv.base_debit / NULLIF(inv.debit, 0)), 2) AS fx_gain_loss
            FROM collection_invoices ci
            JOIN finance_subs s   ON s.linked_type = 'App\\\\Models\\\\Finance\\\\Collection\\\\CollectionInvoice' AND s.linked_id = ci.id
                                  AND s.is_reconcilable = 1 AND s.credit > 0
            JOIN finance f        ON f.id = s.finance_id AND f.is_approved = 1
            JOIN finance_subs inv ON inv.source_type = 'customer_invoice' AND inv.source_id = ci.customer_invoice_id
                                  AND inv.is_reconcilable = 1 AND inv.debit > 0
            LEFT JOIN collections c ON c.id = ci.collection_id
            WHERE ci.amount > 0
        ");
    }

    /** payment_invoices: the payment's AP debit line settles the supplier invoice's AP credit line. */
    private function payments(): int
    {
        return $this->insert("
            SELECT s.company_id, 'supplier' AS partner_type, s.partner_id,
                   inv.id AS settled_line_id, 'supplier_invoice' AS settled_doc_type, pi.supplier_invoice_id AS settled_doc_id,
                   s.id AS settling_line_id, 'payment' AS settling_doc_type, pi.payment_id AS settling_doc_id,
                   'payment' AS allocation_type, COALESCE(p.payment_date, s.entry_date) AS allocation_date,
                   pi.amount AS amount, s.base_debit AS base_amount, s.currency AS currency,
                   ROUND(s.base_debit - pi.amount * (inv.base_credit / NULLIF(inv.credit, 0)), 2) AS fx_gain_loss
            FROM payment_invoices pi
            JOIN finance_subs s   ON s.linked_type = 'App\\\\Models\\\\Finance\\\\Payment\\\\PaymentInvoice' AND s.linked_id = pi.id
                                  AND s.is_reconcilable = 1 AND s.debit > 0
            JOIN finance f        ON f.id = s.finance_id AND f.is_approved = 1
            JOIN finance_subs inv ON inv.source_type = 'supplier_invoice' AND inv.source_id = pi.supplier_invoice_id
                                  AND inv.is_reconcilable = 1 AND inv.credit > 0
            LEFT JOIN payments p ON p.id = pi.payment_id
            WHERE pi.amount > 0
        ");
    }

    /**
     * A credit note (customer) / debit note (supplier) raised against an invoice: its own AR / AP line settles that invoice's line.
     * $side = the side of the note's own line ('credit' for a credit note on AR, 'debit' for a debit note on AP).
     */
    private function adjustment(string $table, string $docType, string $invoiceType, string $partner, string $side): int
    {
        $own = $side;                                   // column on the note's line
        $inv = $side === 'credit' ? 'debit' : 'credit'; // column on the invoice's line
        $amount = "s.$own";
        $base = "s.base_$own";

        return $this->insert("
            SELECT s.company_id, '$partner' AS partner_type, s.partner_id,
                   inv.id AS settled_line_id, '$invoiceType' AS settled_doc_type, n.invoice_id AS settled_doc_id,
                   s.id AS settling_line_id, '$docType' AS settling_doc_type, n.id AS settling_doc_id,
                   '$docType' AS allocation_type, COALESCE(n.posted_at, s.entry_date) AS allocation_date,
                   $amount AS amount, $base AS base_amount, s.currency AS currency,
                   ROUND($base - $amount * (inv.base_$inv / NULLIF(inv.$inv, 0)), 2) AS fx_gain_loss
            FROM $table n
            JOIN finance_subs s   ON s.source_type = '$docType' AND s.source_id = n.id AND s.is_reconcilable = 1 AND s.$own > 0
            JOIN finance f        ON f.id = s.finance_id AND f.is_approved = 1
            JOIN finance_subs inv ON inv.source_type = '$invoiceType' AND inv.source_id = n.invoice_id
                                  AND inv.is_reconcilable = 1 AND inv.$inv > 0
            WHERE n.invoice_id IS NOT NULL AND n.deleted_at IS NULL
        ");
    }

    /**
     * Customer credit moved from one invoice to another (customer_credit_applications, status 1): no new ledger line exists
     * (the credit already sits on the customer's receivable), so the move is recorded as two allocations: the receiving
     * invoice is settled by +amount and the invoice that carried the credit is released by -amount.
     * Reversed applications are marked reversed by CollectionController::revertCredits.
     */
    private function creditApplications(): int
    {
        $added = 0;
        $apps = DB::table('customer_credit_applications as ca')
            ->leftJoin('collections as c', 'c.id', '=', 'ca.collection_id')
            ->where('ca.status', 1)->whereNotNull('ca.to_invoice_id')
            ->whereNotExists(fn ($q) => $q->select(DB::raw(1))->from('finance_allocations as a')
                ->where('a.settling_doc_type', 'credit_application')->where('a.status', 'active')->whereColumn('a.settling_doc_id', 'ca.id'))
            ->get(['ca.*', 'c.approved_at', 'c.collection_date']);
        foreach ($apps as $ca) {
            $line = fn ($invoiceId) => DB::table('finance_subs')->where('source_type', 'customer_invoice')->where('source_id', $invoiceId)
                ->where('is_reconcilable', 1)->where('debit', '>', 0)->first();
            $to = $line($ca->to_invoice_id);
            $from = $line($ca->from_invoice_id);
            if (!$to || !$from) continue;
            $date = substr((string) ($ca->approved_at ?: $ca->collection_date ?: $ca->created_at), 0, 10);
            $mk = fn ($settled, $other, $docId, $amt) => [
                'company_id' => $settled->company_id, 'partner_type' => 'customer', 'partner_id' => $settled->partner_id,
                'settled_line_id' => $settled->id, 'settled_doc_type' => 'customer_invoice', 'settled_doc_id' => $docId,
                'settling_line_id' => $other->id, 'settling_doc_type' => 'credit_application', 'settling_doc_id' => $ca->id,
                'allocation_type' => 'credit_apply', 'allocation_date' => $date,
                'amount' => $amt, 'base_amount' => round($amt * ((float) $settled->base_debit / max(0.0001, (float) $settled->debit)), 2),
                'currency' => $settled->currency, 'fx_gain_loss' => 0, 'status' => 'active', 'created_at' => now(), 'updated_at' => now(),
            ];
            DB::table('finance_allocations')->insert([$mk($to, $from, $ca->to_invoice_id, (float) $ca->amount), $mk($from, $to, $ca->from_invoice_id, -(float) $ca->amount)]);
            $added += 2;
        }
        return $added;
    }

    /** Insert the rows of a source query that are not there yet (same settled line + settling line + type). */
    private function insert(string $select): int
    {
        return DB::affectingStatement("
            INSERT INTO finance_allocations
                (company_id, partner_type, partner_id, settled_line_id, settled_doc_type, settled_doc_id,
                 settling_line_id, settling_doc_type, settling_doc_id, allocation_type, allocation_date,
                 amount, base_amount, currency, fx_gain_loss, status, created_at, updated_at)
            SELECT x.company_id, x.partner_type, x.partner_id, x.settled_line_id, x.settled_doc_type, x.settled_doc_id,
                   x.settling_line_id, x.settling_doc_type, x.settling_doc_id, x.allocation_type, x.allocation_date,
                   x.amount, x.base_amount, x.currency, COALESCE(x.fx_gain_loss, 0), 'active', NOW(), NOW()
            FROM ($select) x
            WHERE NOT EXISTS (
                SELECT 1 FROM finance_allocations a
                WHERE a.settled_line_id = x.settled_line_id AND a.settling_line_id = x.settling_line_id AND a.allocation_type = x.allocation_type
            )
        ");
    }
}
