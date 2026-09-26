<?php

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactMessageReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ContactMessage $contactMessage)
    {
    }

    public function build(): self
    {
        return $this->subject('New Contact Form Submission - '.$this->contactMessage->name)
            ->replyTo($this->contactMessage->email, $this->contactMessage->name)
            ->view('emails.website.contact-message');
    }
}
