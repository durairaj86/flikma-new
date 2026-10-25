@extends('admin.layouts.app')
@section('title', 'Enquiry from '.$contactMessage->name)
@section('content')
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('admin.enquiries.index') }}" class="text-decoration-none">Enquiries</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $contactMessage->name }}</li>
            </ol>
        </nav>
        <h4 class="fw-bold text-dark mb-0">Enquiry from {{ $contactMessage->name }}</h4>
    </div>
<div class="row g-4">
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-white border-0 py-3 px-4"><span class="fw-bold text-dark">Details</span></div>
            <div class="card-body px-4 pb-4 pt-0">
                <div class="table-responsive">
                <table class="table table-borderless align-middle mb-0">
                    <tbody>
                    <tr><th class="text-secondary ps-0 w-25">Name</th><td class="fw-semibold text-dark pe-0">{{ $contactMessage->name }}</td></tr>
                    <tr><th class="text-secondary ps-0">Email</th><td class="pe-0"><a href="mailto:{{ $contactMessage->email }}">{{ $contactMessage->email }}</a></td></tr>
                    <tr><th class="text-secondary ps-0">Phone</th><td class="text-dark pe-0">{{ $contactMessage->phone ?? '-' }}</td></tr>
                    <tr><th class="text-secondary ps-0">Company</th><td class="text-dark pe-0">{{ $contactMessage->company ?: '-' }}</td></tr>
                    <tr><th class="text-secondary ps-0">Interest</th><td class="text-dark pe-0">{{ $contactMessage->interest ?: '-' }}</td></tr>
                    <tr><th class="text-secondary ps-0">Status</th><td class="pe-0"><span class="badge rounded-pill bg-{{ $contactMessage->is_read ? 'success' : 'warning' }}-subtle text-{{ $contactMessage->is_read ? 'success' : 'warning' }} px-2 py-1">{{ $contactMessage->is_read ? 'Read' : 'Unread' }}</span>@if($contactMessage->closed_at) <span class="badge rounded-pill bg-info-subtle text-info-emphasis px-2 py-1">Closed</span>@endif</td></tr>
                    @if($contactMessage->closed_at)
                        <tr><th class="text-secondary ps-0">Closed</th><td class="pe-0">{{ $contactMessage->closed_at->format('d-m-Y H:i') }}<div class="fw-semibold">{{ \App\Models\ContactMessage::CLOSE_REASONS[$contactMessage->close_reason] ?? 'No reason given' }}</div>@if($contactMessage->close_note)<div class="text-muted small" style="white-space: pre-line">{{ $contactMessage->close_note }}</div>@endif</td></tr>
                    @endif
                    <tr><th class="text-secondary ps-0">Date</th><td class="text-muted fs-7 pe-0">{{ $contactMessage->created_at->format('d-m-Y H:i') }}</td></tr>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-white border-0 py-3 px-4"><span class="fw-bold text-dark">Actions</span></div>
            <div class="card-body d-flex gap-2 flex-wrap">
                <form action="{{ route('admin.enquiries.mark-read', $contactMessage) }}" method="POST" class="d-inline">@csrf<button type="submit" class="btn btn-{{ $contactMessage->is_read ? 'secondary' : 'primary' }} d-inline-flex align-items-center gap-1"><i class="bi bi-{{ $contactMessage->is_read ? 'eye-slash' : 'eye' }}"></i> {{ $contactMessage->is_read ? 'Mark Unread' : 'Mark Read' }}</button></form>
                @if($contactMessage->closed_at)
                    <form action="{{ route('admin.enquiries.close', $contactMessage) }}" method="POST" class="d-inline">@csrf<button type="submit" class="btn btn-outline-success d-inline-flex align-items-center gap-1"><i class="bi bi-arrow-counterclockwise"></i> Reopen</button></form>
                @else
                    <button type="button" class="btn btn-success d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#closeEnquiryModal" data-close-url="{{ route('admin.enquiries.close', $contactMessage) }}" data-name="{{ $contactMessage->name }} · {{ $contactMessage->interest ?: 'General enquiry' }}"><i class="bi bi-check2-circle"></i> Close</button>
                @endif
                <form action="{{ route('admin.enquiries.archive', $contactMessage) }}" method="POST" class="d-inline">@csrf<button type="submit" class="btn btn-warning d-inline-flex align-items-center gap-1"><i class="bi bi-{{ $contactMessage->archived_at ? 'inbox' : 'archive' }}"></i> {{ $contactMessage->archived_at ? 'Move to Inbox' : 'Archive' }}</button></form>
                <form action="{{ route('admin.enquiries.destroy', $contactMessage) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this enquiry? This cannot be undone.')">@csrf @method('DELETE')<button type="submit" class="btn btn-danger d-inline-flex align-items-center gap-1"><i class="bi bi-trash"></i> Delete</button></form>
            </div>
        </div>
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white border-0 py-3 px-4"><span class="fw-bold text-dark">Assign to Staff</span></div>
            <div class="card-body">
                <form action="{{ route('admin.enquiries.assign', $contactMessage) }}" method="POST" class="d-flex gap-2">@csrf
                    <select name="assigned_to" class="form-select form-select-sm" aria-label="Assign to">
                        <option value="">Unassigned</option>
                        @foreach($staffUsers as $u)
                            <option value="{{ $u->id }}" {{ $contactMessage->assigned_to == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm">Assign</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-white border-0 py-3 px-4"><span class="fw-bold text-dark">Message</span></div>
            <div class="card-body"><p class="mb-0 text-dark" style="white-space: pre-line">{{ $contactMessage->message }}</p></div>
        </div>
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden" id="reply">
            <div class="card-header bg-white border-0 py-3 px-4"><span class="fw-bold text-dark">Reply via Email</span></div>
            <div class="card-body">
                <form action="{{ route('admin.enquiries.reply', $contactMessage) }}" method="POST">@csrf
                    <div class="mb-3">
                        <label class="form-label">Reply Message</label>
                        <textarea name="reply_message" class="form-control" rows="6" required></textarea>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1"><i class="bi bi-send"></i> Send Reply</button>
                        <span class="small text-muted">Sent to {{ $contactMessage->email }} through the email queue.@if($contactMessage->replied_at) Last reply {{ $contactMessage->replied_at->diffForHumans() }}.@endif</span>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
    @include('admin.enquiries._close-modal')
@endsection
