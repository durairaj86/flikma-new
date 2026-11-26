<?php

namespace App\Services\Finance;

use App\Enums\CollectionEnum;
use App\Enums\PaymentEnum;
use Illuminate\Support\Facades\DB;

/**
 * Advances (the excess of a collection / payment over its invoices) kept on the party's receivable / payable.
 * Available = advance received / paid (approved documents) - advance used on invoices or refunded (approved) - what other drafts hold.
 * Advances are kept in the company currency.
 */
class PartyAdvance
{
    private static function cfg(string $kind): array
    {
        return $kind === 'customer'
            ? ['collections', 'customer_id', CollectionEnum::APPROVED->value, CollectionEnum::DRAFT->value]
            : ['payments', 'supplier_id', PaymentEnum::APPROVED->value, PaymentEnum::DRAFT->value];
    }

    /** Advance given by approved documents and used (applied / refunded) by approved documents. */
    public static function totals(string $kind, int $partyId): array
    {
        [$t, $col, $approved] = self::cfg($kind);
        $r = DB::table($t)->where($col, $partyId)->where('company_id', companyId())->whereNull('deleted_at')->where('status', $approved)
            ->selectRaw('COALESCE(SUM(advance_amount),0) as given, COALESCE(SUM(advance_used),0) as used')->first();

        return ['given' => round((float) $r->given, 2), 'used' => round((float) $r->used, 2)];
    }

    /** Held by other draft documents (advance to be used / refund). */
    public static function held(string $kind, int $partyId, ?int $exceptId = null): float
    {
        [$t, $col, , $draft] = self::cfg($kind);

        return round((float) DB::table($t)->where($col, $partyId)->where('company_id', companyId())->whereNull('deleted_at')->where('status', $draft)
            ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))->sum('advance_used'), 2);
    }

    /** What can still be used on invoices or refunded. */
    public static function available(string $kind, int $partyId, ?int $exceptId = null): float
    {
        $t = self::totals($kind, $partyId);

        return max(0.0, round($t['given'] - $t['used'] - self::held($kind, $partyId, $exceptId), 2));
    }
}
