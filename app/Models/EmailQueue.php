<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One email waiting to be sent, or already sent / failed. Written by App\Services\Mail\EmailQueueService. */
class EmailQueue extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'data_json' => 'array',
        'attachments_json' => 'array',
        'sensitive' => 'boolean',
        'available_at' => 'datetime',
        'expires_at' => 'datetime',
        'sent_at' => 'datetime',
        'failed_at' => 'datetime',
    ];
}
