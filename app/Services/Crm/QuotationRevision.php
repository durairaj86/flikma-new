<?php

namespace App\Services\Crm;

use App\Enums\QuotationEnum;
use App\Models\Crm\CrmActivity;
use App\Models\Quotation\Quotation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * A revision is a new version of a quotation (new rates, new validity) that replaces the old one:
 * QTN/2026/014 → QTN/2026/014-R1 → QTN/2026/014-R2. The old version keeps its rows as history with the
 * status Revised (never counted as won or lost); its open follow-ups move to the new version.
 */
class QuotationRevision
{
    /** Quotations that can still be revised: waiting for the customer, or expired without an answer. */
    public const REVISABLE = [QuotationEnum::PENDING->value, QuotationEnum::EXPIRED->value];

    public static function create(Quotation $old, string $reason): Quotation
    {
        if (!in_array((int) $old->status, self::REVISABLE, true)) {
            throw ValidationException::withMessages(['status' => __('Only a pending or expired quotation can be revised.')]);
        }

        return DB::transaction(function () use ($old, $reason) {
            $rootId = $old->rootId();
            $root = $rootId === (int) $old->id ? $old : Quotation::findOrFail($rootId);
            $number = (int) Quotation::where(fn($q) => $q->whereKey($rootId)->orWhere('parent_quotation_id', $rootId))
                ->lockForUpdate()->max('revision_count') + 1;

            // the new version gets as many days of validity as the old one had
            $posted = Carbon::createFromFormat('d-m-Y', $old->posted_at ?: now()->format('d-m-Y'));
            $valid = $old->valid_until ? Carbon::createFromFormat('d-m-Y', $old->valid_until) : $posted->copy()->addDays(29);
            $days = max(1, (int) $posted->diffInDays($valid, false));
            $today = CrmActivity::localNowNaive((int) $old->company_id)->startOfDay();

            $new = $old->replicate(['customer_token', 'expiry_reminded_at', 'job_id', 'lost_reason_id', 'lost_note',
                'customer_response_note', 'customer_response_at', 'customer_comments', 'customer_action_at', 'rejection_reason', 'submitted_at']);
            $new->row_no = preg_replace('/-R\d+$/', '', $root->row_no) . '-R' . $number;
            $new->parent_quotation_id = $rootId;
            $new->revision_count = $number;
            $new->revision_notes = $reason;
            $new->status = QuotationEnum::PENDING->value;
            $new->customer_status = Quotation::CUSTOMER_NO_ANSWER;
            $new->posted_at = $today->format('Y-m-d');
            $new->valid_until = $today->copy()->addDays($days)->format('Y-m-d');
            $new->user_id = auth()->id();
            $new->save();

            foreach (['quotation_containers', 'quotation_packages', 'quotation_charges'] as $table) {
                $rows = DB::table($table)->where('quotation_id', $old->id)->get()->map(function ($r) use ($new) {
                    $r = (array) $r;
                    unset($r['id']);
                    $r['quotation_id'] = $new->id;
                    $r['created_at'] = $r['updated_at'] = now();

                    return $r;
                })->all();
                if ($rows) {
                    DB::table($table)->insert($rows);
                }
            }

            $old->status = QuotationEnum::REVISED->value;
            $old->save();

            // what still has to be done carries over to the version the customer will now see
            CrmActivity::where('subject_type', 'quotation')->where('subject_id', $old->id)
                ->openFollowUps()->update(['subject_id' => $new->id]);

            return $new;
        });
    }
}
