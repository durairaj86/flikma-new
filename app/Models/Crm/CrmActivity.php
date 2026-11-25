<?php

namespace App\Models\Crm;

use App\Models\Customer\Customer;
use App\Models\Enquiry\Enquiry;
use App\Models\Prospect\Prospect;
use App\Models\Quotation\Quotation;
use App\Models\User;
use App\Traits\CompanyScopeTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

/**
 * One CRM action on one date, on a prospect, customer, enquiry or quotation ("Call Done", "RFQ Received" ...; see CrmAction).
 * An activity with a planned action ("Call Back Due", "Quote Due" ...) is a follow-up: follow_up_at is its due date, assigned_to
 * who has to act, follow_up_done_at when it was ticked off. One table for every kind of subject, the same idea as job_links.
 */
class CrmActivity extends Model
{
    use CompanyScopeTrait, SoftDeletes;

    protected $casts = [
        'activity_at' => 'datetime',
        'follow_up_at' => 'datetime',
        'follow_up_done_at' => 'datetime',
        'reminder_sent_at' => 'datetime',
    ];

    /** subject_type => model; the order is the order of the subject picker. */
    public const SUBJECTS = [
        'prospect' => Prospect::class,
        'customer' => Customer::class,
        'enquiry' => Enquiry::class,
        'quotation' => Quotation::class,
    ];


    /**
     * "Now" on the company's clock. The app runs in UTC, but a salesperson types and reads activity / follow-up times in local time,
     * so those are stored as local wall-clock times and every "today" / "overdue" check uses this.
     */
    public static function localNow(?int $companyId = null): Carbon
    {
        static $zones = [];
        $companyId ??= (int) companyId();
        $zone = $zones[$companyId] ??= (DB::table('companies')->where('id', $companyId)->value('timezone') ?: config('app.timezone'));
        try {
            return Carbon::now($zone);
        } catch (\Throwable) {
            return Carbon::now();
        }
    }

    /** Local "now" as a plain wall-clock value, comparable with the stored times. */
    public static function localNowNaive(?int $companyId = null): Carbon
    {
        return Carbon::parse(self::localNow($companyId)->format('Y-m-d H:i:s'));
    }

    public function action(): BelongsTo
    {
        return $this->belongsTo(CrmAction::class, 'action_id');
    }

    public function isPlanned(): bool
    {
        return $this->follow_up_at !== null;
    }

    /**
     * Put a planned action (a follow-up) on someone's list, e.g. from a command or the customer's online answer.
     * $code is a default action code (CrmAction::DEFAULTS); "Follow-up Due" when the company switched that one off.
     */
    public static function plan(int $companyId, string $subjectType, int $subjectId, string $code, Carbon $dueAt, ?string $note, ?int $assignee): ?self
    {
        $action = CrmAction::byCode($code, $companyId) ?? CrmAction::byCode('follow_up_due', $companyId);
        if (!$action) {
            return null;
        }
        $a = new self();
        $a->company_id = $companyId;
        $a->subject_type = $subjectType;
        $a->subject_id = $subjectId;
        $a->action_id = $action->id;
        $a->notes = $note;
        $a->activity_at = self::localNowNaive($companyId);
        $a->follow_up_at = $dueAt;
        $a->assigned_to = $assignee;
        $a->user_id = auth()->id();
        $a->save();

        return $a;
    }

    /** Log a done action now (no follow-up), e.g. the customer's online answer. */
    public static function record(int $companyId, string $subjectType, int $subjectId, string $code, ?string $note): ?self
    {
        $action = CrmAction::byCode($code, $companyId);
        if (!$action) {
            return null;
        }
        $a = new self();
        $a->company_id = $companyId;
        $a->subject_type = $subjectType;
        $a->subject_id = $subjectId;
        $a->action_id = $action->id;
        $a->notes = $note;
        $a->activity_at = self::localNowNaive($companyId);
        $a->user_id = auth()->id();
        $a->save();

        return $a;
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Follow-ups that are still open. */
    public function scopeOpenFollowUps($query)
    {
        return $query->whereNotNull('follow_up_at')->whereNull('follow_up_done_at');
    }

    /** Name of the activity's action, as the user reads it. */
    public function actionLabel(): string
    {
        return $this->action ? $this->action->label() : __('Activity');
    }

    public static function subjectLabel(string $type): string
    {
        return match ($type) {
            'prospect' => __('Prospect'),
            'customer' => __('Customer'),
            'enquiry' => __('Enquiry'),
            'quotation' => __('Quotation'),
            default => ucfirst($type),
        };
    }

    /**
     * Number + name of each subject, for many activities at once: [type => [id => ['no' => .., 'name' => ..]]].
     * One query per subject type; deleted subjects are simply missing.
     */
    public static function subjectNames(iterable $activities): array
    {
        $ids = [];
        foreach ($activities as $a) {
            $ids[$a->subject_type][] = (int) $a->subject_id;
        }
        $out = [];
        foreach ($ids as $type => $list) {
            $out[$type] = self::describeSubjects($type, array_unique($list));
        }

        return $out;
    }

    /** @return array<int, array{no: ?string, name: ?string}> */
    public static function describeSubjects(string $type, array $ids): array
    {
        if (!$ids) {
            return [];
        }
        $rows = match ($type) {
            'prospect' => Prospect::whereIn('id', $ids)->get(['id', 'row_no', 'name'])
                ->mapWithKeys(fn($r) => [$r->id => ['no' => $r->row_no, 'name' => $r->name]]),
            'customer' => Customer::whereIn('id', $ids)->get(['id', 'row_no', 'name_en'])
                ->mapWithKeys(fn($r) => [$r->id => ['no' => $r->row_no, 'name' => $r->name_en]]),
            'enquiry' => Enquiry::with(['customer:id,name_en', 'prospect:id,name'])->whereIn('id', $ids)->get(['id', 'row_no', 'customer_id', 'prospect_id'])
                ->mapWithKeys(fn($r) => [$r->id => ['no' => $r->row_no, 'name' => $r->customer->name_en ?? $r->prospect->name ?? null]]),
            'quotation' => Quotation::with(['customer:id,name_en', 'prospect:id,name'])->whereIn('id', $ids)->get(['id', 'row_no', 'customer_id', 'prospect_id'])
                ->mapWithKeys(fn($r) => [$r->id => ['no' => $r->row_no, 'name' => $r->customer->name_en ?? $r->prospect->name ?? null]]),
            default => collect(),
        };

        return $rows->all();
    }
}
