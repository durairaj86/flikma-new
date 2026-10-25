<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\ActivityLog;
use App\Models\Admin\Newsletter;
use App\Models\Master\Company;
use App\Services\Mail\EmailQueueService;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function index()
    {
        $newsletters = Newsletter::with('creator:id,name')->withCount('recipients')->latest()->paginate(20);

        return view('admin.newsletters.index', compact('newsletters'));
    }

    public function create()
    {
        $counts = collect(array_keys(Newsletter::RECIPIENT_TYPES))->reject(fn ($t) => $t === 'selected')
            ->mapWithKeys(fn ($t) => [$t => Newsletter::companiesFor($t)->count()]);
        $companies = Company::whereNotNull('email')->where('email', '!=', '')->orderBy('name')->get(['id', 'name', 'email']);

        return view('admin.newsletters.create', compact('counts', 'companies'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'recipient_type' => 'required|in:' . implode(',', array_keys(Newsletter::RECIPIENT_TYPES)),
            'recipient_ids' => 'required_if:recipient_type,selected|array',
            'recipient_ids.*' => 'exists:companies,id',
            'scheduled_at' => 'nullable|date|after:now',
        ]);
        $newsletter = Newsletter::create([
            'subject' => $validated['subject'],
            'body' => $validated['body'],
            'recipient_type' => $validated['recipient_type'],
            'recipient_ids' => $validated['recipient_type'] === 'selected' ? array_map('intval', $validated['recipient_ids'] ?? []) : null,
            'scheduled_at' => $validated['scheduled_at'] ?? null,
            'status' => !empty($validated['scheduled_at']) ? 'scheduled' : 'draft',
            'created_by' => auth()->id(),
        ]);
        ActivityLog::log('created', 'newsletter', $newsletter->id, "Created newsletter: {$newsletter->subject}");

        return redirect()->route('admin.newsletters.show', $newsletter)->with('success', $newsletter->status === 'scheduled'
            ? 'Newsletter scheduled for ' . $newsletter->scheduled_at->format('d-m-Y H:i') . '.'
            : 'Newsletter saved as draft. Press Send now when it is ready.');
    }

    public function show(Newsletter $newsletter)
    {
        $newsletter->load(['creator:id,name', 'recipients.queuedEmail']);

        return view('admin.newsletters.show', compact('newsletter'));
    }

    public function destroy(Newsletter $newsletter)
    {
        $newsletter->recipients()->delete();
        $newsletter->delete();
        ActivityLog::log('deleted', 'newsletter', $newsletter->id, "Deleted newsletter: {$newsletter->subject}");

        return redirect()->route('admin.newsletters.index')->with('success', 'Newsletter deleted.');
    }

    public function send(Newsletter $newsletter, EmailQueueService $mail)
    {
        if ($newsletter->status === 'sent') {
            return back()->with('error', 'This newsletter was already sent.');
        }
        $count = self::queue($newsletter, $mail);
        ActivityLog::log('sent', 'newsletter', $newsletter->id, "Queued newsletter to {$count} companies");

        return redirect()->route('admin.newsletters.show', $newsletter)->with('success', "Newsletter queued to {$count} companies — the emails go out within a minute.");
    }

    /** Queue one email per company and mark the newsletter sent. Also used by the scheduler for due newsletters. */
    public static function queue(Newsletter $newsletter, EmailQueueService $mail): int
    {
        $count = 0;
        foreach (Newsletter::companiesFor($newsletter->recipient_type, $newsletter->recipient_ids) as $company) {
            $email = $mail->enqueue($company->email, $newsletter->subject, 'emails.admin.newsletter',
                ['subject' => $newsletter->subject, 'html' => $newsletter->body, 'company' => $company->name],
                ['type' => 'newsletter', 'company_id' => $company->id, 'from_name' => 'Flikma']);
            $newsletter->recipients()->create(['company_id' => $company->id, 'email' => $company->email, 'email_queue_id' => $email->id]);
            $count++;
        }
        $newsletter->update(['status' => 'sent', 'sent_at' => now()]);

        return $count;
    }
}
