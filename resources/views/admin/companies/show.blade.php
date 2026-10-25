@extends('admin.layouts.app')

@section('title', $company->name)

@section('content')
    @php
        [$accessLabel, $accessState] = \App\Http\Controllers\Admin\CompanyController::planBadge($company);
        $accessColor = ['trial' => 'primary', 'paid' => 'success', 'lapsed' => 'danger', 'trial_ended' => 'secondary'][$accessState];
        $st = $company->subscription_status ?: 'inactive';
        $stColor = ['active' => 'success', 'suspended' => 'danger', 'expired' => 'warning'][$st] ?? 'secondary';
        $initials = collect(preg_split('/\s+/', trim((string) $company->name)))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
        $roles = [1 => 'Owner', 2 => 'User', 3 => 'Admin', 4 => 'Employee'];
        $invStatus = [1 => ['Draft', 'secondary'], 2 => ['Sent', 'info'], 3 => ['Approved', 'success'], 4 => ['Rejected', 'danger'], 5 => ['Cancelled', 'danger']];
        $address = collect([$company->address, $company->building_number, $company->city_sub_division, $company->city, $company->postal_code, $company->country])->filter()->implode(', ');
    @endphp

    <div class="ad-box p-3 p-lg-4 mb-3">
        <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center">
            <div class="d-flex gap-3 align-items-center flex-grow-1 min-w-0">
                <div class="cs-avatar">{{ $initials ?: '?' }}</div>
                <div class="min-w-0">
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <h4 class="fw-bold text-dark mb-0 text-truncate">{{ $company->name }}</h4>
                        <span class="badge rounded-pill bg-{{ $accessColor }}-subtle text-{{ $accessColor }}-emphasis border border-{{ $accessColor }}-subtle"><i class="bi {{ $accessState === 'lapsed' ? 'bi-shield-x' : 'bi-shield-check' }} me-1"></i>{{ $accessLabel }}</span>
                        <span class="badge rounded-pill bg-{{ $stColor }}-subtle text-{{ $stColor }}-emphasis">{{ ucfirst($st) }}</span>
                    </div>
                    @if($company->name_ar)<div class="text-muted small" dir="rtl">{{ $company->name_ar }}</div>@endif
                    <div class="text-muted small mt-1 d-flex flex-wrap gap-3">
                        @if($company->email)<span><i class="bi bi-envelope me-1"></i><a href="mailto:{{ $company->email }}" class="text-reset">{{ $company->email }}</a></span>@endif
                        @if($company->phone)<span><i class="bi bi-telephone me-1"></i>{{ $company->phone }}</span>@endif
                        @if($company->vat_number)<span><i class="bi bi-upc me-1"></i>VAT {{ $company->vat_number }}</span>@endif
                        <span><i class="bi bi-hash me-1"></i>ID {{ $company->id }}</span>
                    </div>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('admin.companies.impersonate', $company) }}" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1"
                   onclick="return confirm('Open the app as {{ addslashes($company->name) }}? You can come back with Return to admin.')">
                    <i class="bi bi-box-arrow-in-right"></i> Login as company
                </a>
                <a href="{{ route('admin.companies.index') }}" class="btn btn-light border btn-sm d-inline-flex align-items-center gap-1"><i class="bi bi-arrow-left"></i> All companies</a>
            </div>
        </div>
    </div>

    <div class="ad-stats mb-4" style="--cols: 6">
        @foreach(['users' => 'Users', 'employees' => 'Employees', 'jobs' => 'Jobs', 'invoices' => 'Invoices', 'customers' => 'Customers', 'suppliers' => 'Suppliers'] as $k => $label)
            <div class="ad-stat"><div class="ad-stat-lbl">{{ $label }}</div><div class="ad-stat-num">{{ number_format($counts[$k]) }}</div></div>
        @endforeach
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="ad-box mb-4">
                <div class="cs-title"><i class="bi bi-credit-card me-2 text-primary"></i>Plan &amp; access</div>
                <dl class="cs-dl">
                    <dt>Plan</dt><dd>{{ $plan?->label ?? 'No plan' }}</dd>
                    <dt>Status</dt><dd>{{ ucfirst($st) }}</dd>
                    <dt>Plan ends</dt><dd class="{{ $accessState === 'lapsed' ? 'text-danger fw-semibold' : '' }}">{{ $company->subscription_expiry_date?->format('d-m-Y') ?? '—' }}</dd>
                    <dt>Trial</dt><dd>{{ $company->trial_ends_at ? ($accessState === 'trial' ? 'running until ' : 'ended ') . $company->trial_ends_at->format('d-m-Y') : 'none' }}</dd>
                    <dt>AI tokens</dt><dd>{{ number_format((int) $company->ai_tokens_used) }} / {{ $company->ai_token_limit ? number_format($company->ai_token_limit) : 'no limit' }}</dd>
                    <dt>Registered</dt><dd>{{ $company->created_at?->format('d-m-Y') ?? '—' }}</dd>
                </dl>
            </div>
            <div class="ad-box mb-4">
                <div class="cs-title"><i class="bi bi-building me-2 text-primary"></i>Company details</div>
                <dl class="cs-dl">
                    <dt>CR number</dt><dd>{{ $company->cr_number ?: '—' }}</dd>
                    <dt>VAT number</dt><dd>{{ $company->vat_number ?: '—' }}</dd>
                    <dt>ZATCA</dt><dd>{{ $company->zatca_registered ? 'Registered' : 'Not registered' }}</dd>
                    <dt>Address</dt><dd>{{ $address ?: '—' }}</dd>
                    <dt>Currency</dt><dd>{{ $company->base_currency ?: ($company->currency ?: '—') }}</dd>
                    <dt>Time zone</dt><dd>{{ $company->timezone ?: '—' }}</dd>
                </dl>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="ad-box mb-4 overflow-hidden">
                <div class="cs-title"><i class="bi bi-people me-2 text-primary"></i>Users <span class="text-muted fw-normal small">({{ $users->count() }})</span></div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead><tr><th class="ps-3">Name</th><th>Role</th><th>Last login</th><th class="text-end pe-3"></th></tr></thead>
                        <tbody>
                        @forelse($users as $u)
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-semibold text-dark">{{ $u->name }}</div>
                                    <div class="small text-muted">{{ $u->email ?: 'No login' }}@if($u->email && !$u->email_verified_at) <span class="badge bg-warning-subtle text-warning-emphasis">not verified</span>@endif</div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-secondary border">{{ $roles[(int) $u->role] ?? 'User' }}</span>
                                    @if($u->is_employee)<span class="badge bg-info-subtle text-info-emphasis">Employee</span>@endif
                                    @if($u->terminated_at)<span class="badge bg-danger-subtle text-danger-emphasis">Terminated</span>@endif
                                </td>
                                <td class="small text-nowrap" title="{{ $u->last_login ? \Carbon\Carbon::parse($u->last_login)->format('d-m-Y H:i') : '' }}">{{ $u->last_login ? \Carbon\Carbon::parse($u->last_login)->diffForHumans() : 'Never' }}</td>
                                <td class="text-end pe-3">
                                    @if($u->email && !$u->terminated_at)
                                        <a href="{{ route('admin.companies.impersonate', [$company, 'user' => $u->id]) }}" class="btn btn-light border btn-sm" title="Login as this user"
                                           onclick="return confirm('Open the app as {{ addslashes($u->name) }}?')"><i class="bi bi-box-arrow-in-right"></i> Login as</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">No users.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-xl-6">
                    <div class="ad-box overflow-hidden h-100">
                        <div class="cs-title"><i class="bi bi-briefcase me-2 text-primary"></i>Latest jobs</div>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead><tr><th class="ps-3">Job</th><th>Customer</th><th class="text-end pe-3">Date</th></tr></thead>
                                <tbody>
                                @forelse($jobs as $j)
                                    <tr>
                                        <td class="ps-3 fw-semibold">{{ $j->row_no }}</td>
                                        <td class="small">{{ $j->customer ?: '—' }}</td>
                                        <td class="text-end pe-3 small text-muted text-nowrap">{{ $j->posted_at ? \Carbon\Carbon::parse($j->posted_at)->format('d-m-Y') : '—' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-muted py-4">No jobs yet.</td></tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6">
                    <div class="ad-box overflow-hidden h-100">
                        <div class="cs-title"><i class="bi bi-receipt me-2 text-primary"></i>Latest invoices</div>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead><tr><th class="ps-3">Invoice</th><th>Customer</th><th class="text-end pe-3">Total</th></tr></thead>
                                <tbody>
                                @forelse($invoices as $i)
                                    @php [$isLbl, $isCol] = $invStatus[(int) $i->status] ?? ['—', 'secondary']; @endphp
                                    <tr>
                                        <td class="ps-3">
                                            <div class="fw-semibold">{{ (int) $i->status === 1 ? 'Draft' : $i->row_no }}</div>
                                            <div class="small text-muted">{{ $i->invoice_date ? \Carbon\Carbon::parse($i->invoice_date)->format('d-m-Y') : '' }} <span class="badge bg-{{ $isCol }}-subtle text-{{ $isCol }}-emphasis">{{ $isLbl }}</span></div>
                                        </td>
                                        <td class="small">{{ $i->customer ?: '—' }}</td>
                                        <td class="text-end pe-3 text-nowrap">{{ number_format((float) $i->grand_total, 2) }} <span class="small text-muted">{{ $i->currency }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-muted py-4">No invoices yet.</td></tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .cs-avatar { flex: 0 0 52px; height: 52px; border-radius: 12px; background: #eef2ff; color: #4338ca; font-weight: 700; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; }
    .cs-title { padding: 14px 18px; border-bottom: 1px solid #f1f3f5; font-weight: 600; color: #1f2937; }
    .cs-dl { display: grid; grid-template-columns: 110px 1fr; row-gap: 10px; margin: 0; padding: 14px 18px; font-size: .875rem; }
    .cs-dl dt { color: #6b7280; font-weight: 400; } .cs-dl dd { margin: 0; color: #111827; word-break: break-word; }
</style>
@endpush
