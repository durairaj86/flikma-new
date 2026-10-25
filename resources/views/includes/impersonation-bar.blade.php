{{-- Shown while a platform admin uses "Login as company" (admin/companies): who you are signed in as, and the way back. --}}
@if(session('impersonating') && auth()->check())
    <div class="imp-bar" role="status">
        <i class="bi bi-shield-exclamation"></i>
        <span>Signed in as <strong>{{ auth()->user()->name }}</strong> · {{ companyName() }}</span>
        <form action="{{ route('admin.companies.leave-impersonation') }}" method="POST" class="m-0">
            @csrf
            <button type="submit" class="imp-btn"><i class="bi bi-arrow-left me-1"></i>Return to admin</button>
        </form>
    </div>
    <style>
        .imp-bar { position: fixed; z-index: 2000; bottom: 16px; left: 50%; transform: translateX(-50%); display: flex; align-items: center; gap: 10px;
                   background: #fbbf24; color: #111827; padding: 8px 10px 8px 14px; border-radius: 999px; box-shadow: 0 8px 24px rgba(0,0,0,.18); font-size: .85rem; white-space: nowrap; max-width: calc(100vw - 24px); }
        .imp-bar span { overflow: hidden; text-overflow: ellipsis; }
        .imp-btn { background: #111827; color: #fff; border: 0; border-radius: 999px; padding: 5px 12px; font-size: .8rem; font-weight: 600; }
        .imp-btn:hover { background: #374151; }
    </style>
@endif
