<?php

namespace App\Livewire\Concerns;

use App\Services\Finance\FinanceSettings;
use App\Services\Mail\EmailQueueService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

/**
 * "Send by email" on the customer / supplier statement. The statement goes out through the email queue with what
 * Settings › Finance says: the PDF (built in the browser from the print layout and uploaded as $mailPdf), the Excel
 * sheet (built here from the same data as the Excel export), or both. The browser calls sendStatementMail() and gets
 * ['ok' => bool, 'message' => string] back.
 */
trait SendsStatementMail
{
    use WithFileUploads;

    /** the statement PDF the browser uploaded for the email */
    public $mailPdf = null;

    /** [export object, file name] of the statement's Excel sheet, or null when no party is chosen */
    abstract protected function statementExcel(): ?array;

    /** the party on the statement: ['name' => ..., 'type' => 'customer_statement' | 'supplier_statement'], or null */
    abstract protected function statementParty(): ?array;

    public function sendStatementMail(string $to, string $cc = '', string $subject = '', string $message = ''): array
    {
        $party = $this->statementParty();
        if (!$party) {
            return ['ok' => false, 'message' => __('Choose a party first.')];
        }
        $attach = FinanceSettings::get('statement_attachment');
        $emails = fn (string $list) => array_values(array_filter(array_map('trim', preg_split('/[,;]+/', $list))));

        $validator = Validator::make(
            ['to' => $emails($to), 'cc' => $emails($cc), 'subject' => trim($subject), 'message' => $message, 'pdf' => $this->mailPdf],
            [
                'to' => 'required|array|min:1|max:10', 'to.*' => 'email:rfc,filter',
                'cc' => 'array|max:10', 'cc.*' => 'email:rfc,filter',
                'subject' => 'required|string|max:191',
                'message' => 'nullable|string|max:5000',
                'pdf' => $attach === 'excel' ? 'nullable' : 'required|file|mimes:pdf|max:10240',
            ],
            ['pdf.required' => __('The statement PDF could not be made. Try again.')],
            ['to' => __('To'), 'to.*' => __('To'), 'cc.*' => __('CC'), 'subject' => __('Subject'), 'message' => __('Message')]
        );
        if ($validator->fails()) {
            return ['ok' => false, 'message' => $validator->errors()->first()];
        }

        $folder = 'statement-mails/' . now()->format('Y/m');
        $stamp = Str::random(8);
        $attachments = [];
        if ($attach !== 'excel') {
            $name = Str::slug($party['name']) . '-statement.pdf';
            $path = $this->mailPdf->storeAs($folder, $stamp . '-' . $name, 'local');
            $attachments[] = ['path' => Storage::disk('local')->path($path), 'name' => $name, 'mime' => 'application/pdf'];
        }
        if ($attach !== 'pdf' && ($excel = $this->statementExcel())) {
            [$export, $name] = $excel;
            $path = $folder . '/' . $stamp . '-' . $name;
            Excel::store($export, $path, 'local');
            $attachments[] = ['path' => Storage::disk('local')->path($path), 'name' => $name, 'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
        }

        $addresses = $emails($to);
        app(EmailQueueService::class)->enqueue(implode(',', $addresses), trim($subject), 'emails.generic',
            ['title' => trim($subject), 'body' => (string) $message],
            ['type' => $party['type'], 'cc' => implode(',', $emails($cc)) ?: null, 'attachments' => $attachments]);
        $this->mailPdf = null;

        return ['ok' => true, 'message' => __('The statement is queued and will be sent to :to.', ['to' => implode(', ', $addresses)])];
    }
}
