@extends('portal.layout')
@section('title', $job->row_no ?: __('Shipment'))
@section('content')
    <a href="{{ url('/portal/shipments') }}" class="small text-decoration-none">&larr; {{ __('Shipments') }}</a>
    <div class="row g-3 mt-1">
        <div class="col-lg-5">
            <div class="p-card p-4">
                <div class="text-muted small">{{ __('Shipment') }}</div>
                <h4 class="fw-bold">{{ $job->row_no ?: '#' . $job->id }}</h4>
                <dl class="row small mb-0">
                    <dt class="col-5 text-muted fw-normal">{{ __('Route') }}</dt><dd class="col-7">{{ $job->pol_name ?? $job->pol ?? '—' }} → {{ $job->pod_name ?? $job->pod ?? '—' }}</dd>
                    <dt class="col-5 text-muted fw-normal">{{ __('Shipper') }}</dt><dd class="col-7">{{ $job->shipper ?: '—' }}</dd>
                    <dt class="col-5 text-muted fw-normal">{{ __('Consignee') }}</dt><dd class="col-7">{{ $job->consignee ?: '—' }}</dd>
                    <dt class="col-5 text-muted fw-normal">{{ __('Carrier') }}</dt><dd class="col-7">{{ $job->carrier ?: '—' }}</dd>
                    <dt class="col-5 text-muted fw-normal">{{ __('B/L / AWB') }}</dt><dd class="col-7">{{ $job->hbl_number ?: ($job->awb_number ?: '—') }}</dd>
                    <dt class="col-5 text-muted fw-normal">{{ __('Container') }}</dt><dd class="col-7">{{ $job->container_no ?: '—' }}</dd>
                    <dt class="col-5 text-muted fw-normal">{{ __('ETD') }}</dt><dd class="col-7">{{ $job->etd ? \Carbon\Carbon::parse($job->etd)->translatedFormat('d M Y') : '—' }}</dd>
                    <dt class="col-5 text-muted fw-normal">{{ __('ETA') }}</dt><dd class="col-7">{{ $job->eta ? \Carbon\Carbon::parse($job->eta)->translatedFormat('d M Y') : '—' }}</dd>
                </dl>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="p-card p-4">
                <b class="d-block mb-3">{{ __('Tracking') }}</b>
                @foreach($steps as $s)
                    <div class="step {{ $s['actual'] ? 'done' : '' }}">
                        <div class="dot" @if($s['skipped']) style="background:#f59e0b" @endif>@if($s['actual'])&#10003;@endif</div>
                        <div>
                            <div class="fw-semibold">{{ __($s['label']) }}</div>
                            @if($s['actual'])
                                <div class="small text-muted">{{ \Carbon\Carbon::parse($s['actual'])->translatedFormat('d M Y') }}@if($s['location']) · {{ $s['location'] }}@endif</div>
                            @elseif($s['skipped'])
                                <div class="small" style="color:#c2410c">{{ __('Not required') }}</div>
                            @else
                                <div class="small text-muted">{{ __('Pending') }}</div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
