<?php

namespace App\Models\Admin;

use App\Models\EmailQueue;
use Illuminate\Database\Eloquent\Model;

/** One company a newsletter was queued to; the linked queued email says whether it went out. */
class NewsletterRecipient extends Model
{
    protected $fillable = ['newsletter_id', 'company_id', 'email', 'email_queue_id'];

    public function queuedEmail()
    {
        return $this->belongsTo(EmailQueue::class, 'email_queue_id');
    }
}
