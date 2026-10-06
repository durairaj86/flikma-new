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
}
