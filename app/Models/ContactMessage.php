<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A message from the website contact form; handled in /admin/enquiries (read, reply, assign, close, archive). */
class ContactMessage extends Model
{
    /** Ready-made reasons offered when closing an enquiry; 'other' needs a typed note. */
    public const CLOSE_REASONS = [
        'replied' => 'Replied / resolved',
        'converted' => 'Signed up / converted',
        'not_interested' => 'Customer not interested',
        'not_a_fit' => 'Not what our software does',
        'no_response' => 'No response from customer',
        'duplicate' => 'Duplicate enquiry',
        'spam' => 'Spam',
        'other' => 'Other',
    ];

    protected $fillable = [
        'name', 'email', 'company', 'phone', 'interest', 'message',
        'is_read', 'read_at', 'assigned_to', 'replied_at', 'closed_at', 'close_reason', 'close_note', 'archived_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'replied_at' => 'datetime',
        'closed_at' => 'datetime',
        'archived_at' => 'datetime',
    ];

    /** Admin the enquiry is assigned to. */
    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /** Label for the close reason, with the typed note when the reason is "Other". */
    public function getCloseReasonLabelAttribute(): ?string
    {
        if (!$this->close_reason) {
            return null;
        }

        return $this->close_reason === 'other' && $this->close_note
            ? $this->close_note
            : (self::CLOSE_REASONS[$this->close_reason] ?? $this->close_reason);
    }
}
