<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

/** Any email: a subject, a blade view and the values for it (and optional attachments). Used by the email queue. */
class DynamicMail extends Mailable
{
    public function __construct(
        public string $mailSubject,
        public string $mailView,
        public array $mailData = [],
        public array $mailAttachments = [],
        public ?string $fromAddress = null,
        public ?string $fromName = null,
    ) {
    }

    public function build(): self
    {
        $this->subject($this->mailSubject)->view($this->mailView, $this->mailData);

        // The plain-text version lives next to the HTML one, as <view>-text, when there is one.
        if (view()->exists($this->mailView . '-text')) {
            $this->text($this->mailView . '-text', $this->mailData);
        }
        $this->from($this->fromAddress ?: config('mail.from.address'), $this->fromName ?: 'Flikma');

        foreach ($this->mailAttachments as $a) {
            if (!empty($a['path']) && is_file($a['path'])) {
                $this->attach($a['path'], array_filter(['as' => $a['name'] ?? null, 'mime' => $a['mime'] ?? null]));
            }
        }

        return $this;
    }
}
