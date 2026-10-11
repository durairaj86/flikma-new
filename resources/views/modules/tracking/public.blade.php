<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Shipment {{ $job->row_no }}</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>body{background:#f6f7f9}.step{display:flex;gap:1rem;padding-bottom:1.2rem;position:relative}.step:not(:last-child)::before{content:'';position:absolute;left:11px;top:24px;bottom:0;width:2px;background:#dee2e6}.step.done:not(:last-child)::before{background:#198754}.dot{width:24px;height:24px;border-radius:50%;background:#dee2e6;flex:none;color:#fff;text-align:center;font-size:.8rem;line-height:24px}.done .dot{background:#198754}</style></head>
<body><div class="container py-4" style="max-width:560px">
<div class="card border-0 shadow-sm"><div class="card-body p-4">
<div class="text-muted small">Shipment</div><h4 class="fw-bold">{{ $job->row_no }}</h4>
<div class="mb-3">{{ $job->pol_name ?? $job->origin }} → {{ $job->pod_name ?? $job->destination }}
@if($job->eta)<div class="small text-muted">ETA {{ \App\Services\Job\JobInsights::date($job->eta)?->format('d-M-Y') ?? $job->eta }}</div>@endif</div>
@foreach($steps as $s)
<div class="step {{ $s['actual'] ? 'done' : '' }}"><div class="dot" @if($s['skipped']) style="background:#f59e0b" @endif>@if($s['actual'])&#10003;@endif</div>
<div><div class="fw-semibold">{{ $s['label'] }}</div>
@if($s['actual'])<div class="small text-muted">{{ $s['actual']->format('d-M-Y') }}@if($s['location']) · {{ $s['location'] }}@endif</div>@elseif($s['skipped'])<div class="small" style="color:#c2410c">Not required</div>@else<div class="small text-muted">Pending</div>@endif</div></div>
@endforeach
</div></div></div></body></html>
