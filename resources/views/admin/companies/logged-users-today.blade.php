@extends('admin.layouts.app')

@section('title', 'Logged Users Today')

@section('content')
    @php
        $cards = [
            ['label' => 'Active today', 'value' => $stats['active'], 'color' => '#111827'],
            ['label' => 'Logged in today', 'value' => $stats['logged_in'], 'color' => '#16a34a'],
            ['label' => 'Remembered sessions', 'value' => $stats['remembered'], 'color' => '#4f46e5'],
            ['label' => 'Companies', 'value' => $stats['companies'], 'color' => '#0ea5e9'],
        ];
    @endphp

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Logged Users Today</h1>
            <p class="text-muted mb-0">Everyone who used the app on {{ now()->format('l, d-m-Y') }} — including users kept signed in with "Remember me".</p>
        </div>
        <a href="{{ route('admin.companies.index') }}" class="btn btn-light border d-inline-flex align-items-center gap-1">
            <i class="bi bi-arrow-left"></i> Back to Companies
        </a>
    </div>

    <div class="row g-3 mb-4">
        @foreach($cards as $card)
            <div class="col-6 col-xl-3">
                <div class="lu-stat">
                    <div class="lu-stat-lbl">{{ $card['label'] }}</div>
                    <div class="lu-stat-num" style="color: {{ $card['color'] }}">{{ number_format($card['value']) }}</div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="lu-card">
        <div class="table-responsive">
            <table class="table lu-table align-middle mb-0">
                <thead>
                <tr><th>User</th><th>Company</th><th>Session</th><th>Last active</th><th class="text-end"></th></tr>
                </thead>
                <tbody>
                @forelse($loggedUsersToday as $row)
                    @php
                        $lastActive = $row->last_active_at ? \Illuminate\Support\Carbon::parse($row->last_active_at) : null;
                    @endphp
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                @if($row->photo_path)
                                    <img src="{{ asset('storage/' . $row->photo_path) }}" class="rounded-circle" style="width: 36px; height: 36px; object-fit: cover;" alt="{{ $row->name }}">
                                @else
                                    <div class="avatar bg-primary-subtle text-primary">{{ mb_strtoupper(mb_substr($row->name, 0, 1)) }}</div>
                                @endif
                                <div class="min-w-0">
                                    <div class="fw-semibold text-dark">{{ $row->name }}</div>
                                    <div class="text-muted small">{{ $row->email }} · #{{ $row->user_id }}</div>
                                </div>
                            </div>
                        </td>
                        <td><a href="{{ route('admin.companies.show', $row->company_id) }}" class="fw-medium text-decoration-none">{{ $row->company_name }}</a></td>
                        <td>
                            @if($row->logged_in_at)
                                <span class="lu-badge" style="color:#15803d;background:#f0fdf4;border-color:#bbf7d0"><i class="bi bi-box-arrow-in-right"></i> Logged in {{ \Illuminate\Support\Carbon::parse($row->logged_in_at)->format('H:i') }}</span>
                                @if($row->login_count > 1)<span class="small text-muted ms-1">{{ $row->login_count }} logins</span>@endif
                            @else
                                <span class="lu-badge" style="color:#4338ca;background:#eef2ff;border-color:#c7d2fe"><i class="bi bi-arrow-repeat"></i> Remembered</span>
                                @if($row->last_login)<div class="small text-muted mt-1">Last login {{ \Illuminate\Support\Carbon::parse($row->last_login)->format('d-m-Y') }}</div>@endif
                            @endif
                        </td>
                        <td class="text-nowrap">
                            @if($lastActive)
                                <div class="fw-medium text-dark">{{ $lastActive->format('H:i') }}</div>
                                <div class="small text-muted">{{ $lastActive->diffForHumans() }}</div>
                            @else
                                —
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.companies.show', $row->company_id) }}" class="btn btn-sm btn-light border d-inline-flex align-items-center gap-1"><i class="bi bi-eye text-primary"></i> View company</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center py-5 text-muted">
                        <i class="bi bi-person-slash fs-1 d-block mb-2 opacity-50"></i>
                        No users active today yet.
                    </td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($loggedUsersToday->hasPages())
        <div class="mt-3">{{ $loggedUsersToday->links('pagination::bootstrap-5') }}</div>
    @endif
@endsection

@push('styles')
<style>
    .lu-stat { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 16px 18px; }
    .lu-stat-lbl { font-size: .72rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: #6b7280; }
    .lu-stat-num { font-size: 1.6rem; font-weight: 700; margin-top: 4px; font-variant-numeric: tabular-nums; }
    .lu-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; }
    .lu-table thead th { background: #f9fafb; color: #6b7280; font-size: .72rem; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; padding: 10px 16px; border-bottom: 1px solid #e5e7eb; white-space: nowrap; }
    .lu-table tbody td { padding: 12px 16px; font-size: .875rem; color: #374151; border-color: #f1f3f5; }
    .lu-table tbody tr:last-child td { border-bottom: 0; }
    .lu-badge { display: inline-flex; align-items: center; gap: 5px; font-size: .75rem; font-weight: 600; border: 1px solid; border-radius: 6px; padding: 2px 8px; }
</style>
@endpush
