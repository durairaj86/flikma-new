<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The next number of a document series (PAY/26/0003, JOB-26-0010, IN/26/0007 ...).
 *
 * The old code took MAX(unique_row_no) of the documents dated in the current year, plus one. That handed out a number twice when
 *  - unique_row_no is text (payments, jobs, quotations, bills): the database compares text character by character, so '9' is "higher"
 *    than '10' and number 10 was given again and again;
 *  - the document date is not the year in the number (a payment dated last year, saved today as PAY/26/...) so it was not counted;
 *  - the latest document had been deleted (soft delete): its number came back;
 *  - a draft number is replaced by the real number on approval and so left the count.
 *
 * Here the highest number is read from the numbers themselves: every row_no that starts with the series prefix (e.g. 'PAY/26/'),
 * its tail taken as a whole number, in the company, deleted rows included. $alsoColumns are other columns that carry numbers of the
 * same series (draft_no holds the DR-26-0005 an invoice had before it was approved), so those are never reused either.
 *
 * Inside a transaction the read locks the series (FOR UPDATE), so two people saving at the same moment get different numbers.
 */
class DocumentNumber
{
    /**
     * @param  class-string  $model  the document's model (only its table is used; soft-deleted rows count)
     * @param  string  $prefix  everything in row_no before the running number, e.g. 'PAY/26/'
     * @param  string[]  $alsoColumns  other columns holding numbers of this series
     */
    public static function next(string $model, string $prefix, array $alsoColumns = []): int
    {
        $table = (new $model)->getTable();
        $companyScoped = Schema::hasColumn($table, 'company_id') && companyId();
        $like = addcslashes($prefix, '%_\\') . '%';
        $start = mb_strlen($prefix) + 1;

        $max = 0;
        foreach (array_merge(['row_no'], array_filter($alsoColumns, fn ($c) => Schema::hasColumn($table, $c))) as $column) {
            $row = DB::table($table)
                ->when($companyScoped, fn ($q) => $q->where('company_id', companyId()))
                ->where($column, 'like', $like)
                ->lockForUpdate()
                ->selectRaw("MAX(CAST(SUBSTRING(`$column`, ?) AS UNSIGNED)) AS n", [$start])
                ->first();
            $max = max($max, (int) ($row->n ?? 0));
        }

        return $max + 1;
    }
}
