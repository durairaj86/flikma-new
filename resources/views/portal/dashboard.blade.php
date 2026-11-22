@extends('portal.layout')
@section('title', __('Overview'))
@section('content')
    <h4 class="fw-bold mb-3">{{ __('Welcome, :name', ['name' => $portalUser->name]) }}</h4>
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3"><a href="{{ url('/portal/shipments') }}" class="text-reset text-decoration-none"><div class="p-card p-3 p-kpi"><small>{{ __('Shipments in progress') }}</small><b>{{ $inProgress }}</b></div></a></div>
        <div class="col-6 col-lg-3"><a href="{{ url('/portal/statement') }}" class="text-reset text-decoration-none"><div class="p-card p-3 p-kpi"><small>{{ __('Balance Due') }}</small><b class="{{ $balanceDue > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($balanceDue, 2) }}</b> <span class="text-muted small">{{ $currency }}</span></div></a></div>
        <div class="col-6 col-lg-3"><a href="{{ url('/portal/invoices') }}" class="text-reset text-decoration-none"><div class="p-card p-3 p-kpi"><small>{{ __('Overdue Amount') }}</small><b class="{{ $overdue > 0 ? 'text-warning' : '' }}">{{ number_format($overdue, 2) }}</b> <span class="text-muted small">{{ $currency }}</span></div></a></div>
        <div class="col-6 col-lg-3"><a href="{{ url('/portal/quotations') }}" class="text-reset text-decoration-none"><div class="p-card p-3 p-kpi"><small>{{ __('Quotations to answer') }}</small><b>{{ $quotations }}</b></div></a></div>
    </div>
    <div class="p-card">
        <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
            <b>{{ __('Latest shipments') }}</b>
            <a href="{{ url('/portal/shipments?show=all') }}" class="small">{{ __('All shipments') }}</a>
        </div>
        @include('portal.partials.shipment-rows', ['jobs' => $recent])
    </div>
@endsection
