<?php

namespace App\Services\Job;

use App\Models\Finance\Adjustment\CreditNote;
use App\Models\Finance\Collection\Collection;
use App\Models\Finance\CustomerInvoice\CustomerInvoice;
use App\Models\Finance\Expense\Expense;
use App\Models\Finance\Payment\Payment;
use App\Models\Finance\ProformaInvoice\ProformaInvoice;
use App\Models\Finance\SupplierInvoice\SupplierInvoice;
use App\Models\Job\JobLink;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Writes the job_links table. Every document type that can belong to a job is described once here:
 * what its number, date, party and amount are, and which statuses count as approved / draft / cancelled.
 */
class JobLinks
{
    /** model => [doc_type, number, date, party relation, party type, amount column, paid column, approved, draft, cancelled] */
    public const TYPES = [
        CustomerInvoice::class => ['customer_invoice', 'row_no', 'posted_at', 'customer', 'customer', 'base_grand_total', 'base_paid_amount', [3], [1, 2], [4, 5]],
        SupplierInvoice::class => ['supplier_invoice', 'row_no', 'posted_at', 'supplier', 'supplier', 'base_grand_total', 'base_paid_amount', [3], [1, 2], [4, 5]],
        CreditNote::class => ['credit_note', 'row_no', 'posted_at', 'customer', 'customer', 'base_grand_total', null, [2], [1], [3]],
        Expense::class => ['expense', 'row_no', 'posted_at', 'customer', 'customer', 'amount_including_vat', null, [2], [1], [3]],
        Collection::class => ['collection', 'row_no', 'posted_at', 'customer', 'customer', 'base_grand_total', null, [2], [1], [3]],
        Payment::class => ['payment', 'row_no', 'posted_at', 'supplier', 'supplier', 'base_grand_total', null, [2], [1], [3]],
        ProformaInvoice::class => ['proforma', 'row_no', 'posted_at', 'customer', 'customer', 'grand_total', null, [], [1], []],
    ];

    /** Jobs a document belongs to: its own job, plus (for collections / payments) the jobs of the invoices it settles. */
    private static function jobIds(Model $m): array
    {
        $ids = [$m->job_id];
        if ($m instanceof Collection) {
            $ids = array_merge($ids, DB::table('collection_invoices as p')->join('customer_invoices as i', 'i.id', '=', 'p.customer_invoice_id')->where('p.collection_id', $m->id)->pluck('i.job_id')->all());
        } elseif ($m instanceof Payment) {
            $ids = array_merge($ids, DB::table('payment_invoices as p')->join('supplier_invoices as i', 'i.id', '=', 'p.supplier_invoice_id')->where('p.payment_id', $m->id)->pluck('i.job_id')->all());
        }

        return array_values(array_unique(array_filter(array_map('intval', $ids))));
    }

    /** Create / update the link rows for one document, and drop rows for jobs it no longer belongs to. */
    public static function sync(Model $m): array
    {
        $cfg = self::TYPES[get_class($m)] ?? null;
        if (!$cfg) {
            return [];
        }
        [$docType, $noCol, $dateCol, $partyRel, $partyType, $amountCol, $paidCol, $approved, $draft, $cancelled] = $cfg;
        $status = $m->status instanceof \BackedEnum ? (int) $m->status->value : (int) $m->status;
        $state = in_array($status, $approved, true) ? 'approved' : (in_array($status, $cancelled, true) ? 'cancelled' : 'draft');
        $party = method_exists($m, $partyRel) ? $m->$partyRel : null;
        $jobs = self::jobIds($m);
        $type = $m->getMorphClass();

        foreach ($jobs as $jobId) {
            JobLink::withoutGlobalScopes()->updateOrCreate(
                ['job_id' => $jobId, 'linkable_type' => $type, 'linkable_id' => $m->id],
                [
                    'company_id' => $m->company_id, 'doc_type' => $docType, 'doc_no' => $m->$noCol,
                    'doc_date' => $m->$dateCol ? \Illuminate\Support\Carbon::parse($m->$dateCol)->toDateString() : null,
                    'party_type' => $party ? $partyType : null, 'party_id' => $party?->id, 'party_name' => $party?->name_en,
                    'status' => $status, 'state' => $state, 'currency' => $m->currency ?? null,
                    'amount' => (float) ($m->$amountCol ?? 0), 'paid' => $paidCol ? (float) ($m->$paidCol ?? 0) : null,
                ]
            );
        }
        // Moved to another job, or the job was cleared: remove the stale rows.
        $stale = JobLink::withoutGlobalScopes()->where('linkable_type', $type)->where('linkable_id', $m->id)
            ->when($jobs, fn($q) => $q->whereNotIn('job_id', $jobs))->get();
        $affected = array_merge($jobs, $stale->pluck('job_id')->all());
        JobLink::withoutGlobalScopes()->whereIn('id', $stale->pluck('id'))->delete();

        return array_values(array_unique($affected));
    }

    /** Remove every link row of a document (deleted). Returns the jobs that were affected. */
    public static function remove(Model $m): array
    {
        $rows = JobLink::withoutGlobalScopes()->where('linkable_type', $m->getMorphClass())->where('linkable_id', $m->id);
        $jobs = $rows->pluck('job_id')->all();
        $rows->delete();

        return array_values(array_unique($jobs));
    }

    /** Rebuild the whole table from the documents (first fill, or repair). */
    public static function rebuildAll(): int
    {
        $n = 0;
        foreach (array_keys(self::TYPES) as $class) {
            $q = $class::withoutGlobalScopes();
            if (in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive($class))) {
                $q = $q->withoutTrashed();
            }
            $q->chunkById(200, function ($rows) use (&$n) {
                foreach ($rows as $m) {
                    $n += count(self::sync($m));
                }
            });
        }

        return $n;
    }
}
