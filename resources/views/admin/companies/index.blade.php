@extends('admin.layouts.app')

@section('title', 'Companies')

@section('content')
    @php
        $statusColor = ['active' => 'success', 'suspended' => 'danger', 'expired' => 'warning', 'inactive' => 'secondary'];
        // stat cards double as quick filters
        $cards = [
            ['key' => null, 'label' => 'Total', 'value' => $stats['total'], 'color' => '#111827'],
            ['key' => 'paid', 'label' => 'Paid', 'value' => $stats['paid'], 'color' => '#16a34a'],
            ['key' => 'paid_expired', 'label' => 'Paid — expired', 'value' => $stats['paid_expired'], 'color' => '#dc2626'],
            ['key' => 'trial', 'label' => 'On trial', 'value' => $stats['trial'], 'color' => '#2563eb'],
            ['key' => 'trial_ended', 'label' => 'Trial ended', 'value' => $stats['trial_ended'], 'color' => '#6b7280'],
            ['key' => 'suspended', 'label' => 'Suspended', 'value' => $stats['suspended'], 'color' => '#dc2626'],
        ];
        $activeAccess = $filters['access'] ?? null;
        $activeStatus = $filters['status'] ?? null;
        $hasFilters = collect($filters)->filter(fn ($v) => filled($v))->isNotEmpty();
    @endphp

    <div class="mb-4">
        <h1 class="h3 fw-bold text-dark mb-1">Companies</h1>
        <p class="text-muted mb-0">Every company on Flikma — plan, access and activity at a glance.</p>
    </div>

    <div class="ad-stats mb-4" style="--cols: 6">
        @foreach($cards as $card)
            @php
                $isOn = $card['key'] === 'suspended' ? $activeStatus === 'suspended' : ($card['key'] === null ? !$hasFilters : $activeAccess === $card['key']);
                $href = $card['key'] === null
                    ? route('admin.companies.index')
                    : route('admin.companies.index', $card['key'] === 'suspended' ? ['status' => 'suspended'] : ['access' => $card['key']]);
            @endphp
            <a href="{{ $href }}" class="ad-stat {{ $isOn ? 'is-on' : '' }}">
                <div class="ad-stat-lbl">{{ $card['label'] }}</div>
                <div class="ad-stat-num" style="color: {{ $card['color'] }}">{{ number_format($card['value']) }}</div>
            </a>
        @endforeach
    </div>

    <form method="GET" action="{{ route('admin.companies.index') }}" class="ad-box ad-filters mb-4">
        <div class="row g-3">
            <div class="col-lg-4">
                <label class="form-label small fw-semibold text-secondary" for="cq">Quick search</label>
                <div class="position-relative">
                    <i class="bi bi-search position-absolute top-50 translate-middle-y text-muted" style="left: 12px;"></i>
                    <input type="text" id="cq" name="q" value="{{ $filters['q'] ?? '' }}" class="form-control ps-5" placeholder="Name, email, phone, VAT, city or ID…">
                </div>
            </div>
            <div class="col-6 col-lg-2">
                <label class="form-label small fw-semibold text-secondary">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    @foreach(['active' => 'Active', 'inactive' => 'Inactive', 'expired' => 'Expired', 'suspended' => 'Suspended'] as $val => $lbl)
                        <option value="{{ $val }}" @selected($activeStatus === $val)>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-lg-2">
                <label class="form-label small fw-semibold text-secondary">Access</label>
                <select name="access" class="form-select">
                    <option value="">All</option>
                    @foreach(['paid' => 'Paid', 'paid_expired' => 'Paid — expired', 'trial' => 'On trial', 'trial_ended' => 'Trial ended'] as $val => $lbl)
                        <option value="{{ $val }}" @selected($activeAccess === $val)>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-lg-2">
                <label class="form-label small fw-semibold text-secondary">Plan</label>
                <select name="package" class="form-select">
                    <option value="">All</option>
                    @foreach($packages as $pkg)
                        <option value="{{ $pkg['id'] }}" @selected((string) ($filters['package'] ?? '') === (string) $pkg['id'])>{{ $pkg['name'] }}</option>
                    @endforeach
                    <option value="none" @selected(($filters['package'] ?? '') === 'none')>No plan</option>
                </select>
            </div>
            <div class="col-6 col-lg-2">
                <label class="form-label small fw-semibold text-secondary">Country</label>
                <select name="country" class="form-select">
                    <option value="">All</option>
                    @foreach($countries as $cc)
                        <option value="{{ $cc }}" @selected(($filters['country'] ?? '') === $cc)>{{ $cc }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-3 pt-3 border-top">
            <div class="small text-muted d-flex flex-wrap gap-3">
                <span class="fw-semibold text-uppercase" style="letter-spacing:.05em;font-size:.7rem;">Access key</span>
                <span><span class="badge bg-success-subtle text-success-emphasis">Starter</span> paid, end date ahead</span>
                <span><span class="badge bg-danger-subtle text-danger-emphasis">Starter (Expired)</span> paid, end date passed</span>
                <span><span class="badge bg-primary-subtle text-primary-emphasis">Trial</span> no end date, trial running</span>
                <span><span class="badge bg-light text-secondary border">Trial (Ended)</span> no end date, trial over</span>
            </div>
            <div class="d-flex gap-2 ms-auto">
                <a href="{{ route('admin.companies.index') }}" class="btn btn-light border d-inline-flex align-items-center gap-1"><i class="bi bi-arrow-counterclockwise"></i> Reset</a>
                <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1"><i class="bi bi-funnel"></i> Apply filters</button>
            </div>
        </div>
    </form>

    <div class="text-muted small mb-2">
        Showing <strong class="text-dark">{{ $companies->count() }}</strong> of {{ number_format($companies->total()) }} companies
        @if($companies->lastPage() > 1) · page {{ $companies->currentPage() }} of {{ $companies->lastPage() }} @endif
    </div>

    @forelse($companies as $company)
        @php
            $initials = collect(preg_split('/\s+/', trim((string) $company->name)))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
            [$accessLabel, $accessState] = \App\Http\Controllers\Admin\CompanyController::planBadge($company);
            $accessClass = [
                'trial' => 'bg-primary-subtle text-primary-emphasis',
                'paid' => 'bg-success-subtle text-success-emphasis',
                'lapsed' => 'bg-danger-subtle text-danger-emphasis border border-danger-subtle',
                'trial_ended' => 'bg-light text-secondary border',
            ][$accessState];
            $st = $company->subscription_status ?: 'inactive';
            $isTrialCustomer = in_array($accessState, ['trial', 'trial_ended'], true);
            $endsOn = $isTrialCustomer ? $company->trial_ends_at : $company->subscription_expiry_date;
            $endsPassed = $endsOn && $endsOn->isPast() && !$endsOn->isToday();
        @endphp
        <div class="cl-row">
            <div class="cl-avatar">{{ $initials ?: '?' }}</div>
            <div class="cl-main">
                <div class="cl-name">{{ $company->name }}</div>
                <div class="cl-meta">
                    @if($company->email)<span><i class="bi bi-envelope"></i> <a href="mailto:{{ $company->email }}" class="text-reset text-decoration-none">{{ $company->email }}</a></span>@endif
                    @if($company->phone)<span>{{ $company->phone }}</span>@endif
                </div>
                <div class="cl-meta cl-meta-sm">
                    @if($company->city || $company->country)<span><i class="bi bi-geo-alt"></i> {{ collect([$company->city, $company->country])->filter()->implode(', ') }}</span>@endif
                    @if($company->vat_number)<span><i class="bi bi-upc"></i> VAT {{ $company->vat_number }}</span>@endif
                    <span>#{{ $company->id }}</span>
                </div>
            </div>
            <div class="cl-badges">
                <span class="badge rounded-pill bg-{{ $statusColor[$st] ?? 'secondary' }}-subtle text-{{ $statusColor[$st] ?? 'secondary' }}-emphasis border border-{{ $statusColor[$st] ?? 'secondary' }}-subtle">{{ strtoupper($st) }}</span>
                <span class="badge rounded-pill {{ $accessClass }}"><i class="bi {{ $accessState === 'lapsed' ? 'bi-shield-x' : 'bi-shield-check' }} me-1"></i>{{ $accessLabel }}</span>
            </div>
            <div class="cl-col">
                <div class="cl-col-lbl">Created</div>
                <div class="cl-col-val">{{ $company->created_at?->format('d-m-Y') ?? '—' }}</div>
            </div>
            <div class="cl-col">
                <div class="cl-col-lbl">{{ $isTrialCustomer ? 'Trial ends' : 'Plan ends' }}</div>
                <div class="cl-col-val {{ $endsPassed ? 'text-danger' : '' }}">{{ $endsOn ? $endsOn->format('d-m-Y') : '—' }}</div>
            </div>
            <div class="cl-col">
                <div class="cl-col-lbl">Users</div>
                <div class="cl-col-val">{{ $company->users_count }} <span class="text-muted fw-normal">/ {{ $company->employees_count }} emp</span></div>
            </div>
            <div class="cl-col">
                <div class="cl-col-lbl">Jobs</div>
                <div class="cl-col-val">{{ number_format($company->jobs_count) }}</div>
            </div>
            <div class="cl-col">
                <div class="cl-col-lbl">Invoices</div>
                <div class="cl-col-val">{{ number_format($company->invoices_count) }}</div>
            </div>
            <div class="cl-col">
                <div class="cl-col-lbl">Customers</div>
                <div class="cl-col-val">{{ number_format($company->customers_count) }}</div>
            </div>
            <div class="cl-col cl-col-wide">
                <div class="cl-col-lbl">Last login</div>
                <div class="cl-col-val" title="{{ $company->last_login_at ? \Carbon\Carbon::parse($company->last_login_at)->format('d-m-Y H:i') : '' }}">
                    {{ $company->last_login_at ? \Carbon\Carbon::parse($company->last_login_at)->diffForHumans() : 'Never' }}
                </div>
            </div>
        </div>
    @empty
        <div class="cl-row justify-content-center text-muted py-5">
            <div class="text-center">
                <i class="bi bi-building-exclamation fs-1 d-block mb-2 opacity-50"></i>
                {{ $hasFilters ? 'No companies match these filters.' : 'No companies yet.' }}
            </div>
        </div>
    @endforelse

    @if($companies->hasPages())
        <div class="mt-3">{{ $companies->links('pagination::bootstrap-5') }}</div>
    @endif
@endsection

@push('styles')
<style>
    .cl-row { display: flex; align-items: center; gap: 22px; background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 14px 18px; margin-bottom: 10px; transition: border-color .15s, box-shadow .15s; }
    .cl-row:hover { border-color: #c7d2fe; box-shadow: 0 4px 14px rgba(15,23,42,.05); }
    .cl-avatar { flex: 0 0 44px; height: 44px; border-radius: 10px; background: #eef2ff; color: #4338ca; font-weight: 700; display: flex; align-items: center; justify-content: center; font-size: .95rem; }
    .cl-main { flex: 1 1 260px; min-width: 0; }
    .cl-name { font-weight: 700; color: #111827; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .cl-meta { display: flex; flex-wrap: wrap; gap: 4px 14px; font-size: .82rem; color: #4b5563; margin-top: 2px; }
    .cl-meta-sm { font-size: .76rem; color: #6b7280; }
    .cl-meta i { color: #9ca3af; }
    .cl-badges { flex: 0 0 auto; display: flex; flex-direction: column; align-items: flex-start; gap: 5px; min-width: 110px; }
    .cl-badges .badge { font-size: .68rem; font-weight: 600; }
    .cl-col { flex: 0 0 auto; min-width: 72px; }
    .cl-col-wide { min-width: 110px; }
    .cl-col-lbl { font-size: .66rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: #9ca3af; }
    .cl-col-val { font-size: .85rem; font-weight: 600; color: #1f2937; margin-top: 2px; white-space: nowrap; }
    @media (max-width: 1199.98px) {
        .cl-row { flex-wrap: wrap; gap: 12px 20px; }
        .cl-main { flex-basis: calc(100% - 66px); }
    }
</style>
@endpush
