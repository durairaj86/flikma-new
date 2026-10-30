<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Lists document numbers (PAY/26/0003, JOB-26-0010 ...) that more than one document carries in the same company.
 * Read-only: nothing is changed. New duplicates can no longer be created (App\Services\DocumentNumber); the ones already
 * in the database are for a person to decide: an approved document's number is also in the books (finance voucher_no), so it
 * is not renumbered automatically.
 *
 *   php artisan documents:duplicate-numbers
 */
class DuplicateDocumentNumbers extends Command
{
    protected $signature = 'documents:duplicate-numbers';

    protected $description = 'List document numbers that two or more documents share (read-only)';

    private const TABLES = [
        'payments', 'collections', 'customer_invoices', 'supplier_invoices', 'credit_notes', 'debit_notes', 'journal_vouchers',
        'proforma_invoices', 'jobs', 'quotations', 'quotations_new', 'enquiries', 'bookings', 'delivery_orders', 'arrival_notices',
        'waybills', 'seaway_bills', 'airway_bills', 'expenses',
    ];

    public function handle(): int
    {
        $found = 0;
        foreach (self::TABLES as $table) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'row_no')) {
                continue;
            }
            $hasCompany = Schema::hasColumn($table, 'company_id');
            $hasStatus = Schema::hasColumn($table, 'status');
            $hasDeleted = Schema::hasColumn($table, 'deleted_at');
            $dups = DB::table($table)->select($hasCompany ? 'company_id' : DB::raw('NULL as company_id'), 'row_no', DB::raw('COUNT(*) AS n'))
                ->whereNotNull('row_no')->where('row_no', '!=', '')
                ->groupBy($hasCompany ? ['company_id', 'row_no'] : ['row_no'])->having('n', '>', 1)->get();
            foreach ($dups as $d) {
                $found++;
                $docs = DB::table($table)->where('row_no', $d->row_no)->when($hasCompany, fn ($q) => $q->where('company_id', $d->company_id))->orderBy('id')
                    ->get(array_filter(['id', $hasStatus ? 'status' : null, 'created_at', $hasDeleted ? 'deleted_at' : null]));
                $this->line(sprintf('%-18s %-16s company %-4s x%d', $table, $d->row_no, $d->company_id ?? '-', $d->n));
                foreach ($docs as $doc) {
                    $this->line(sprintf('      id %-6s %s%s%s', $doc->id, isset($doc->status) ? 'status ' . $doc->status . '  ' : '', 'created ' . $doc->created_at, !empty($doc->deleted_at) ? '  (deleted)' : ''));
                }
            }
        }
        $this->newLine();
        $this->info($found ? "{$found} duplicated number(s). Nothing was changed." : 'No document number is used twice.');

        return self::SUCCESS;
    }
}
