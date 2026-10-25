<aside class="sidebar" id="adminSidebar" aria-label="Admin navigation">
    <div class="logo">
        <a href="{{ route('admin.dashboard') }}" class="d-flex align-items-center gap-2 text-decoration-none">
            <img src="{{ asset('img/logo.png') }}" alt="Flikma">
            <span class="admin-chip">Admin</span>
        </a>
    </div>

    <div class="nav-section">Main</div>
    <a href="{{ route('admin.dashboard') }}" class="nav-item nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
        <i class="bi bi-grid"></i> <span>Dashboard</span>
    </a>

    <div class="nav-section">Management</div>
    <a href="{{ route('admin.companies.index') }}" class="nav-item nav-link {{ request()->routeIs('admin.companies.*') ? 'active' : '' }}">
        <i class="bi bi-building"></i> <span>Companies</span>
    </a>

    <div class="nav-section">Communications</div>
    <a href="{{ route('admin.newsletters.index') }}" class="nav-item nav-link {{ request()->routeIs('admin.newsletters.*') ? 'active' : '' }}">
        <i class="bi bi-envelope-paper"></i> <span>Newsletters</span>
    </a>
    <a href="{{ route('admin.enquiries.index') }}" class="nav-item nav-link {{ request()->routeIs('admin.enquiries.*') ? 'active' : '' }}">
        <i class="bi bi-chat-dots"></i> <span>Enquiries</span>
        @php
            $unreadEnquiries = \App\Models\ContactMessage::where('is_read', false)->whereNull('archived_at')->count();
            // emails that failed every attempt and are no longer retried
            $failedEmails = \App\Models\EmailQueue::where('status', 'failed')->where('attempts', '>=', \App\Services\Mail\EmailQueueService::MAX_ATTEMPTS)->count();
        @endphp
        @if($unreadEnquiries > 0)
            <span class="badge bg-danger rounded-pill">{{ $unreadEnquiries }}</span>
        @endif
    </a>
    <a href="{{ route('admin.email-queue.index') }}" class="nav-item nav-link {{ request()->routeIs('admin.email-queue.*') ? 'active' : '' }}">
        <i class="bi bi-envelope-check"></i> <span>Email Queue</span>
        @if($failedEmails > 0)
            <span class="badge bg-danger rounded-pill" title="Failed emails">{{ $failedEmails }}</span>
        @endif
    </a>

    <div class="nav-section">System</div>
    <a href="{{ route('admin.cache.index') }}" class="nav-item nav-link {{ request()->routeIs('admin.cache.*') ? 'active' : '' }}">
        <i class="bi bi-arrow-repeat"></i> <span>Cache</span>
    </a>
    <a href="{{ url('/log-viewer') }}" class="nav-item nav-link" target="_blank" rel="noopener">
        <i class="bi bi-file-earmark-code"></i> <span>System Logs</span> <i class="bi bi-box-arrow-up-right ms-auto small"></i>
    </a>
    <a href="{{ route('admin.activity-logs.index') }}" class="nav-item nav-link {{ request()->routeIs('admin.activity-logs.*') ? 'active' : '' }}">
        <i class="bi bi-journal-text"></i> <span>Activity Logs</span>
    </a>
    <a href="{{ route('admin.settings.index') }}" class="nav-item nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
        <i class="bi bi-gear"></i> <span>Settings</span>
    </a>

    <div style="height: 2rem;"></div>
</aside>
