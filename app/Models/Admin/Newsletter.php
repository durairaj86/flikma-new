<?php

namespace App\Models\Admin;

use App\Models\Master\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/** A bulk email to companies. Sending queues one email per company (email_queues); delivery is tracked there. */
class Newsletter extends Model
{
    protected $fillable = ['subject', 'body', 'recipient_type', 'recipient_ids', 'scheduled_at', 'sent_at', 'status', 'created_by'];

    protected $casts = [
        'recipient_ids' => 'array',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public const RECIPIENT_TYPES = ['all' => 'All companies', 'active' => 'Active subscribers', 'expired' => 'Expired subscribers', 'selected' => 'Selected companies'];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function recipients()
    {
        return $this->hasMany(NewsletterRecipient::class);
    }

    /** The companies this newsletter goes to, by the chosen recipient type. */
    public static function companiesFor(string $type, ?array $ids = null): Collection
    {
        $q = Company::query()->whereNotNull('email')->where('email', '!=', '');

        return match ($type) {
            'active' => $q->where('subscription_status', 'active')->whereDate('subscription_expiry_date', '>=', today())->get(['id', 'name', 'email']),
            'expired' => $q->whereNotNull('subscription_expiry_date')->whereDate('subscription_expiry_date', '<', today())->get(['id', 'name', 'email']),
            'selected' => $q->whereIn('id', $ids ?? [])->get(['id', 'name', 'email']),
            default => $q->get(['id', 'name', 'email']),
        };
    }
}
