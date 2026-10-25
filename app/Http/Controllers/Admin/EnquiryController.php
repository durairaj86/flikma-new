<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\ActivityLog;
use App\Models\ContactMessage;
use App\Models\User;
use App\Services\Mail\EmailQueueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/** Website contact-form messages. Flikma's form asks for an "interest", shown here as the topic. */
class EnquiryController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['q', 'status', 'topic', 'from', 'to']);
        $status = $filters['status'] ?? null;

        // inbox = open (not closed) and not archived; the stat cards double as the status filter
        $inbox = fn () => ContactMessage::whereNull('archived_at')->whereNull('closed_at');
        $stats = [
            'inbox' => $inbox()->count(),
            'unread' => $inbox()->where('is_read', false)->count(),
            'unreplied' => $inbox()->whereNull('replied_at')->count(),
            'replied' => ContactMessage::whereNotNull('replied_at')->count(),
            'month' => ContactMessage::whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->count(),
            'closed' => ContactMessage::whereNull('archived_at')->whereNotNull('closed_at')->count(),
            'archived' => ContactMessage::whereNotNull('archived_at')->count(),
        ];

        $enquiries = ContactMessage::query()
            ->with('assignee:id,name')
            ->when($status === 'archived', fn ($q) => $q->whereNotNull('archived_at'), fn ($q) => $q->whereNull('archived_at'))
            // closed enquiries leave the inbox; they still show under Closed, Replied and This month
            ->when(!in_array($status, ['archived', 'closed', 'replied', 'month'], true), fn ($q) => $q->whereNull('closed_at'))
            ->when($status === 'closed', fn ($q) => $q->whereNotNull('closed_at'))
            ->when($status === 'unread', fn ($q) => $q->where('is_read', false))
            ->when($status === 'read', fn ($q) => $q->where('is_read', true))
            ->when($status === 'unreplied', fn ($q) => $q->whereNull('replied_at'))
            ->when($status === 'replied', fn ($q) => $q->whereNotNull('replied_at'))
            ->when($status === 'month', fn ($q) => $q->whereYear('created_at', now()->year)->whereMonth('created_at', now()->month))
            ->when(trim($filters['q'] ?? '') !== '', function ($q) use ($filters) {
                $term = '%' . trim($filters['q']) . '%';
                $q->where(fn ($w) => $w->where('name', 'like', $term)->orWhere('email', 'like', $term)->orWhere('phone', 'like', $term)
                    ->orWhere('company', 'like', $term)->orWhere('interest', 'like', $term)->orWhere('message', 'like', $term));
            })
            ->when($filters['topic'] ?? null, fn ($q, $v) => $q->where('interest', $v))
            ->when($filters['from'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($filters['to'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $topics = ContactMessage::whereNotNull('interest')->where('interest', '!=', '')->distinct()->orderBy('interest')->pluck('interest');

        return view('admin.enquiries.index', compact('enquiries', 'stats', 'filters', 'topics'));
    }

    public function show(ContactMessage $contactMessage)
    {
        if (!$contactMessage->is_read) {
            $contactMessage->update(['is_read' => true, 'read_at' => now()]);
        }
        $staffUsers = User::withoutGlobalScopes()->where('is_admin', true)->orderBy('name')->get(['id', 'name']);

        return view('admin.enquiries.show', compact('contactMessage', 'staffUsers'));
    }

    public function markRead(ContactMessage $contactMessage)
    {
        $contactMessage->update(['is_read' => !$contactMessage->is_read, 'read_at' => $contactMessage->is_read ? null : now()]);

        return back()->with('success', 'Enquiry status updated.');
    }

    /** The reply is queued like every Flikma email; the worker sends it within a minute. */
    public function reply(Request $request, ContactMessage $contactMessage, EmailQueueService $mail)
    {
        $data = $request->validate(['reply_message' => 'required|string|max:10000']);
        $subject = 'Re: ' . ($contactMessage->interest ?: 'Your enquiry to Flikma');

        $mail->enqueue($contactMessage->email, $subject, 'emails.generic', ['title' => $subject, 'body' => $data['reply_message']], [
            'type' => 'enquiry_reply',
            'from_name' => 'Flikma',
        ]);
        $contactMessage->update(['replied_at' => now(), 'is_read' => true, 'read_at' => $contactMessage->read_at ?? now()]);
        ActivityLog::log('replied', 'enquiry', $contactMessage->id, 'Replied to enquiry from ' . $contactMessage->name);

        return back()->with('success', 'Reply queued — it will be sent within a minute.');
    }

    public function assign(Request $request, ContactMessage $contactMessage)
    {
        $request->validate(['assigned_to' => 'nullable|exists:users,id']);
        $contactMessage->update(['assigned_to' => $request->assigned_to ?: null]);
        ActivityLog::log('assigned', 'enquiry', $contactMessage->id, $request->assigned_to ? 'Assigned enquiry to staff' : 'Unassigned enquiry');

        return back()->with('success', 'Enquiry assignment saved.');
    }

    /** Close a handled enquiry with a reason, or reopen a closed one. */
    public function close(Request $request, ContactMessage $contactMessage)
    {
        if ($contactMessage->closed_at) {
            $contactMessage->update(['closed_at' => null, 'close_reason' => null, 'close_note' => null]);
            ActivityLog::log('reopened', 'enquiry', $contactMessage->id, 'Reopened enquiry');

            return back()->with('success', 'Enquiry reopened.');
        }

        $validator = Validator::make($request->all(), [
            'close_reason' => ['required', 'in:' . implode(',', array_keys(ContactMessage::CLOSE_REASONS))],
            'close_note' => ['nullable', 'string', 'max:1000', 'required_if:close_reason,other'],
        ], ['close_reason.required' => 'Choose a reason for closing.', 'close_note.required_if' => 'Type the reason for closing.']);
        if ($validator->fails()) {
            return back()->with('error', $validator->errors()->first());
        }
        $data = $validator->validated();

        $contactMessage->update([
            'closed_at' => now(),
            'close_reason' => $data['close_reason'],
            'close_note' => $data['close_note'] ?? null,
            'is_read' => true,
            'read_at' => $contactMessage->read_at ?? now(),
        ]);
        ActivityLog::log('closed', 'enquiry', $contactMessage->id, 'Closed enquiry: ' . $contactMessage->close_reason_label);

        return back()->with('success', 'Enquiry closed.');
    }

    /** Archive, or move an archived enquiry back to the inbox. */
    public function archive(ContactMessage $contactMessage)
    {
        $archiving = !$contactMessage->archived_at;
        $contactMessage->update(['archived_at' => $archiving ? now() : null]);
        ActivityLog::log($archiving ? 'archived' : 'restored', 'enquiry', $contactMessage->id, $archiving ? 'Archived enquiry' : 'Moved enquiry back to inbox');

        return back()->with('success', $archiving ? 'Enquiry archived.' : 'Enquiry moved back to the inbox.');
    }

    public function destroy(ContactMessage $contactMessage)
    {
        $contactMessage->delete();
        ActivityLog::log('deleted', 'enquiry', $contactMessage->id, 'Deleted enquiry from ' . $contactMessage->name);

        return redirect()->route('admin.enquiries.index')->with('success', 'Enquiry deleted.');
    }
}
