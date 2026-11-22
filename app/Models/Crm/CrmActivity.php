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
 * A call, meeting, email, note ... logged on a prospect, customer, enquiry or quotation, with an optional follow-up
 * (date + who has to act). One table for every kind of subject, the same idea as job_links.
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

    public const TYPES = [
        'call' => ['label' => 'Call', 'icon' => 'bi-telephone'],
        'meeting' => ['label' => 'Meeting', 'icon' => 'bi-people'],
        'visit' => ['label' => 'Visit', 'icon' => 'bi-geo-alt'],
        'email' => ['label' => 'Email', 'icon' => 'bi-envelope'],
        'whatsapp' => ['label' => 'WhatsApp', 'icon' => 'bi-whatsapp'],
        'note' => ['label' => 'Note', 'icon' => 'bi-sticky'],
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

    public static function typeLabel(?string $type): string
    {
        return __(self::TYPES[$type]['label'] ?? ucfirst((string) $type));
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
