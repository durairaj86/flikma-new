<?php

namespace App\Traits\Finance;

use App\Enums\CollectionEnum;
use App\Enums\PaymentEnum;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Aging must reflect the chosen "as of" date: back-dated invoices count from their
 * own date, and settlements dated after the as-of date do not reduce the balance yet.
 */
trait AgingAsOf
{
    /** @return array<int,float> invoice id => amount settled on or before $asOf */
    protected function settledAsOf(string $kind, Carbon $asOf): array
    {
        // from the ledger allocations: includes credit / debit notes and honours reversals (docs/finance-ledger-design.md)
        if (\App\Services\Finance\LedgerReports::enabled()) {
            return \App\Services\Finance\LedgerReports::settledAsOf($kind, $asOf);
        }

        return $this->settledAsOfFromReceipts($kind, $asOf);
    }

    /** The earlier calculation: receipts / payments only (credit and debit notes were not counted). */
    protected function settledAsOfFromReceipts(string $kind, Carbon $asOf): array
    {
        if ($kind === 'customer') {
            $q = DB::table('collection_invoices as ci')
                ->join('collections as c', 'c.id', '=', 'ci.collection_id')
                ->where('c.status', CollectionEnum::APPROVED->value)
                ->whereDate('c.collection_date', '<=', $asOf)
                ->whereNull('c.deleted_at')
                ->groupBy('ci.customer_invoice_id')
                ->selectRaw('ci.customer_invoice_id as invoice_id, SUM(ci.amount) as paid');
        } else {
            $q = DB::table('payment_invoices as pi')
                ->join('payments as p', 'p.id', '=', 'pi.payment_id')
                ->where('p.status', PaymentEnum::APPROVED->value)
                ->whereDate('p.payment_date', '<=', $asOf)
                ->whereNull('p.deleted_at')
                ->groupBy('pi.supplier_invoice_id')
                ->selectRaw('pi.supplier_invoice_id as invoice_id, SUM(pi.amount) as paid');
        }

        return $q->pluck('paid', 'invoice_id')->map(fn ($v) => (float) $v)->all();
    }

    /**
     * Opening balances still owed on $asOf, per customer / supplier: the receivable (1130) / payable (2110) lines of the
     * opening balance voucher, so aging agrees with the statement. They are aged from the opening balance date.
     * @return array<int, array{amount: float, date: string}>
     */
    protected function openingBalancesAsOf(string $kind, Carbon $asOf, $partyId = null): array
    {
        $col = $kind === 'customer' ? 'customer_id' : 'supplier_id';
        $sign = $kind === 'customer' ? 'base_debit - base_credit' : 'base_credit - base_debit';

        return DB::table('finance_subs as s')->join('finance as f', 'f.id', '=', 's.finance_id')
            ->where('s.voucher_type', 'OB')->where('f.is_approved', 1)->where('s.company_id', companyId())
            ->where('s.account_id', $kind === 'customer' ? 5 : 18)->whereNotNull("s.$col")
            ->whereDate('s.reference_date', '<=', $asOf)
            ->when($partyId, fn ($q) => $q->where("s.$col", $partyId))
            ->groupBy("s.$col")->selectRaw("s.$col as party, SUM($sign) as amount, MIN(s.reference_date) as date")
            ->get()->filter(fn ($r) => abs((float) $r->amount) >= 0.005)
            ->mapWithKeys(fn ($r) => [(int) $r->party => ['amount' => round((float) $r->amount, 2), 'date' => (string) $r->date]])->all();
    }
}
