@php($rtl = app()->getLocale() === 'ar')
<!doctype html>
<html lang="{{ $rtl ? 'ar' : 'en' }}" dir="{{ $rtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>@yield('title') · {{ __('Customer Portal') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap{{ $rtl ? '.rtl' : '' }}.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/tom-select/tom-select.bootstrap5.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/flatpickr/flatpickr.min.css') }}" rel="stylesheet">
    <style>
        body { background: #f4f6fa; }
        .portal-nav { background: #fff; border-bottom: 1px solid #e5e7eb; }
        .portal-nav .nav-link { color: #4b5563; font-weight: 600; border-bottom: 3px solid transparent; border-radius: 0; padding: .9rem .9rem .75rem; }
        .portal-nav .nav-link.active { color: #4f46e5; border-bottom-color: #4f46e5; }
        .p-card { background: #fff; border-radius: 12px; box-shadow: 0 1px 3px rgba(16, 24, 40, .08); }
        .p-kpi small { display: block; color: #6b7280; font-weight: 600; text-transform: uppercase; font-size: .72rem; letter-spacing: .04em; }
        .p-kpi b { font-size: 1.4rem; }
        .table th { font-size: .74rem; text-transform: uppercase; color: #6b7280; letter-spacing: .03em; white-space: nowrap; }
        .step { display: flex; gap: 1rem; padding-bottom: 1.1rem; position: relative; }
        .step:not(:last-child)::before { content: ''; position: absolute; inset-inline-start: 11px; top: 24px; bottom: 0; width: 2px; background: #dee2e6; }
        .step.done:not(:last-child)::before { background: #198754; }
        .dot { width: 24px; height: 24px; border-radius: 50%; background: #dee2e6; flex: none; color: #fff; text-align: center; font-size: .8rem; line-height: 24px; }
        .done .dot { background: #198754; }
    </style>
</head>
<body>
@if(isset($customer) && auth('portal')->check())
    <nav class="portal-nav">
        <div class="container d-flex align-items-center justify-content-between flex-wrap gap-2 pt-2">
            <div class="d-flex align-items-center gap-2">
                @if(!empty($company->logo))<img src="{{ asset('storage/' . $company->logo) }}" alt="" style="height:32px">@endif
                <div>
                    <div class="fw-bold lh-1">{{ $rtl ? ($company->name_ar ?: $company->name) : $company->name }}</div>
                    <div class="small text-muted">{{ $customer->name_en }}</div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-3 small">
                <a href="{{ url('/portal/locale/' . ($rtl ? 'en' : 'ar')) }}">{{ $rtl ? 'English' : 'العربية' }}</a>
                <span class="text-muted d-none d-md-inline">{{ $portalUser->name }}</span>
                <form method="POST" action="{{ url('/portal/logout') }}" class="m-0">@csrf
                    <button class="btn btn-sm btn-outline-secondary">{{ __('Sign out') }}</button>
                </form>
            </div>
        </div>
        <div class="container">
            <ul class="nav flex-nowrap overflow-auto">
                @foreach(['' => ['Overview', 'bi-house'], 'shipments' => ['Shipments', 'bi-truck'], 'invoices' => ['Invoices', 'bi-receipt'], 'statement' => ['Statement', 'bi-journal-text'], 'quotations' => ['Quotations', 'bi-file-earmark-text']] as $path => [$label, $icon])
                    @php($active = $path === '' ? request()->is('portal') : request()->is('portal/' . $path . '*'))
                    <li class="nav-item"><a class="nav-link text-nowrap {{ $active ? 'active' : '' }}" href="{{ url('/portal' . ($path ? '/' . $path : '')) }}"><i class="bi {{ $icon }} me-1"></i>{{ __($label) }}</a></li>
                @endforeach
            </ul>
        </div>
    </nav>
@endif
<main class="container py-4">
    @if(session('notice'))<div class="alert alert-info">{{ session('notice') }}</div>@endif
    @yield('content')
</main>
@if(isset($customer) && auth('portal')->check())
    @if(!empty($company->email) || !empty($company->phone))
        <div class="text-center small text-muted pb-4">{{ __('Questions?') }} {{ $company->email }} @if($company->email && $company->phone)·@endif {{ $company->phone }}</div>
    @endif
@endif
<script src="{{ asset('js/tom-select@2.4.3/tom-select.complete.min.js') }}"></script>
<script src="{{ asset('js/flatpickr/flatpickr.js') }}"></script>
@include('includes.library-locale')
<script>
    // same controls as the staff app: input.datepicker (shown d-m-Y, sent Y-m-d) and select.tom-select
    document.querySelectorAll('input.datepicker').forEach(function (el) {
        flatpickr(el, {dateFormat: 'Y-m-d', altInput: true, altFormat: 'd-m-Y', allowInput: true, disableMobile: true});
    });
    document.querySelectorAll('select.tom-select').forEach(function (el) {
        const ts = new TomSelect(el, {allowEmptyOption: true, openOnFocus: true, controlInput: null});
        if (el.dataset.submit !== undefined) ts.on('change', function () { el.form.submit(); });
    });
</script>
</body>
</html>
