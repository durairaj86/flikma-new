<?php

namespace App\Services\Finance;

use App\Enums\CollectionEnum;
use App\Enums\PaymentEnum;
use Illuminate\Support\Facades\DB;

/**
 * Money taken off a customer's / supplier's opening balance by a collection / payment (table opening_settlements).
 * The opening balance itself is the receivable (1130) / payable (2110) line of the OB voucher.
 */
class OpeningSettlement
{
    private static function docTable(string $kind): string { return $kind === 'customer' ? 'collections' : 'payments'; }

    private static function approved(string $kind): int { return $kind === 'customer' ? CollectionEnum::APPROVED->value : PaymentEnum::APPROVED->value; }

    private static function draft(string $kind): int { return $kind === 'customer' ? CollectionEnum::DRAFT->value : PaymentEnum::DRAFT->value; }

    /** What the opening balance voucher says the party owes (customer) / is owed (supplier). */
    public static function total(string $kind, int $partyId): float
    {
        $col = $kind === 'customer' ? 'customer_id' : 'supplier_id';
        $sign = $kind === 'customer' ? 'base_debit - base_credit' : 'base_credit - base_debit';

        return round((float) DB::table('finance_subs as s')->join('finance as f', 'f.id', '=', 's.finance_id')
            ->where('s.voucher_type', 'OB')->where('f.is_approved', 1)->where('s.company_id', companyId())
            ->where('s.account_id', $kind === 'customer' ? 5 : 18)->where("s.$col", $partyId)->sum(DB::raw($sign)), 2);
    }

    /** Settled by approved documents (optionally only up to a date) or held by drafts. */
    public static function amount(string $kind, int $partyId, string $state = 'approved', ?int $exceptDocumentId = null, ?string $upTo = null): float
    {
        $t = self::docTable($kind);
        $dateCol = $kind === 'customer' ? 'collection_date' : 'payment_date';

        return round((float) DB::table('opening_settlements as o')->join("$t as d", 'd.id', '=', 'o.document_id')
            ->where('o.kind', $kind)->where('o.party_id', $partyId)->where('o.company_id', companyId())->whereNull('d.deleted_at')
            ->where('d.status', $state === 'approved' ? self::approved($kind) : self::draft($kind))
            ->when($exceptDocumentId, fn ($q) => $q->where('d.id', '!=', $exceptDocumentId))
            ->when($upTo, fn ($q) => $q->whereDate("d.$dateCol", '<=', $upTo))
            ->sum('o.amount'), 2);
    }

    /** What can still be settled: opening balance less approved settlements less other drafts. */
    public static function outstanding(string $kind, int $partyId, ?int $exceptDocumentId = null): float
    {
        $total = self::total($kind, $partyId);
        if ($total <= 0) {
            return 0.0;
        }

        return max(0.0, round($total - self::amount($kind, $partyId, 'approved', $exceptDocumentId) - self::amount($kind, $partyId, 'draft', $exceptDocumentId), 2));
    }

    /**
     * SQL for the opening balance still owed by the row of $outerTable (customers / suppliers): the OB voucher line less approved
     * settlements, never below 0. For the "Due" column of the customer / supplier lists.
     */
    public static function outstandingSql(string $kind, string $outerTable): string
    {
        $col = $kind === 'customer' ? 'customer_id' : 'supplier_id';
        $sign = $kind === 'customer' ? 's.base_debit - s.base_credit' : 's.base_credit - s.base_debit';
        $acct = $kind === 'customer' ? 5 : 18;
        [$docTable, $approved] = $kind === 'customer' ? ['collections', CollectionEnum::APPROVED->value] : ['payments', PaymentEnum::APPROVED->value];
        $company = (int) companyId();

        return "(SELECT GREATEST(COALESCE(SUM($sign), 0) - COALESCE((SELECT SUM(o.amount) FROM opening_settlements o JOIN $docTable d ON d.id = o.document_id "
            . "WHERE o.kind = '$kind' AND o.party_id = $outerTable.id AND o.company_id = $company AND d.status = $approved AND d.deleted_at IS NULL), 0), 0) "
            . "FROM finance_subs s JOIN finance f ON f.id = s.finance_id WHERE s.voucher_type = 'OB' AND f.is_approved = 1 AND s.company_id = $company "
            . "AND s.account_id = $acct AND s.$col = $outerTable.id)";
    }

    /** Amount a given document takes off the opening balance. */
    public static function ofDocument(string $kind, int $documentId): float
    {
        return round((float) DB::table('opening_settlements')->where('kind', $kind)->where('document_id', $documentId)->sum('amount'), 2);
    }

    /** Replace what a document settles (0 removes it). */
    public static function save(string $kind, int $partyId, int $documentId, float $amount): void
    {
        DB::table('opening_settlements')->where('kind', $kind)->where('document_id', $documentId)->delete();
        if ($amount > 0.004) {
            DB::table('opening_settlements')->insert([
                'company_id' => companyId(), 'kind' => $kind, 'party_id' => $partyId, 'document_id' => $documentId,
                'amount' => round($amount, 2), 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public static function remove(string $kind, int $documentId): void
    {
        DB::table('opening_settlements')->where('kind', $kind)->where('document_id', $documentId)->delete();
    }
}
