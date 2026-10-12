@section('page-title', __('Demurrage & Detention'))
@section('page-subtitle', __('Containers still out, free days left and the cost if they stay'))
@section('js','demurrage')
@section('hide-topbar', true)
<x-app-layout>
    <main class="gmail-content bg-white px-3">
        <style>
            .mod-kpi { border-radius: 12px; padding: .85rem 1.1rem; height: 100%; }
            .mod-kpi .kpi-label { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; opacity: .75; }
            .mod-kpi .kpi-value { font-size: 1.6rem; font-weight: 700; line-height: 1.2; }
            .dm-tabs .btn { border-radius: 50rem; }
        </style>
        @php
            $badge = ['waiting' => ['secondary', 'Not discharged'], 'free' => ['success', 'Within free time'], 'ending' => ['warning', 'Ending soon'], 'detention' => ['danger', 'In detention'], 'returned' => ['dark', 'Returned']];
        @endphp
        <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap pt-2 pb-3">
            <h4 class="fw-bold text-dark mb-0">{{ __('Demurrage & Detention') }}</h4>
            <form class="d-flex gap-2 ms-auto" method="get">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <input type="text" name="q" value="{{ $q }}" class="form-control rounded-pill" placeholder="{{ __('Search container or job...') }}">
                <div class="input-group" style="width:190px"><span class="input-group-text">{{ __('Per day') }}</span>
                    <input type="text" name="rate" value="{{ $rate }}" class="decimal form-control" onchange="this.form.submit()"></div>
            </form>
        </div>
        <div class="row g-3 pb-3">
            <div class="col-6 col-lg-3"><div class="mod-kpi bg-body-tertiary"><div class="kpi-label">{{ __('Containers Out') }}</div><div class="kpi-value">{{ $counts['open'] }}</div></div></div>
            <div class="col-6 col-lg-3"><div class="mod-kpi" style="background:#fef2f2;color:#991b1b;"><div class="kpi-label">{{ __('In Detention') }}</div><div class="kpi-value">{{ $counts['detention'] }}</div></div></div>
            <div class="col-6 col-lg-3"><div class="mod-kpi" style="background:#fff7ed;color:#9a3412;"><div class="kpi-label">{{ __('Charges So Far') }}</div><div class="kpi-value">{{ number_format($exposure, 2) }}</div></div></div>
            <div class="col-6 col-lg-3"><div class="mod-kpi" style="background:#ecfdf5;color:#166534;"><div class="kpi-label">{{ __('Returned') }}</div><div class="kpi-value">{{ $counts['returned'] }}</div></div></div>
        </div>
        <div class="dm-tabs d-flex gap-2 pb-3">
            @foreach(['open' => 'Out now', 'detention' => 'In detention', 'waiting' => 'Not discharged', 'returned' => 'Returned'] as $k => $l)
                <a class="btn btn-sm {{ $tab === $k ? 'btn-primary' : 'btn-light' }}" href="?tab={{ $k }}&rate={{ $rate }}&q={{ $q }}">{{ __($l) }} <span class="ms-1">{{ $counts[$k] }}</span></a>
            @endforeach
        </div>
        <div class="shadow bdr-r-10 pb-2">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead><tr class="text-muted text-uppercase" style="font-size:.7rem;background:#f8f9fa">
                        <th>{{ __('Container') }}</th><th>{{ __('Job') }}</th><th>{{ __('Discharged') }}</th><th>{{ __('Days') }}</th><th>{{ __('Status') }}</th><th class="text-end">{{ __('Cost') }}</th><th></th></tr></thead>
                    <tbody>
                    @forelse($shown as $r)
                        @php $c = $r['c']; $f = $r['f']; [$bc, $bl] = $badge[$f['state']]; @endphp
                        <tr>
                            <td class="fw-semibold">{{ $c->container_no ?: $c->container_number }}<div class="small text-muted fw-normal">{{ $c->container_size }} {{ $c->container_type }}</div></td>
                            <td>{{ $c->job?->row_no }}</td>
                            <td>{{ $c->discharged_at ? \Illuminate\Support\Carbon::parse($c->discharged_at)->format('d M Y') : '—' }}</td>
                            <td>@if($f['state'] !== 'waiting'){{ $f['used'] }} / {{ $f['free'] }}@else — @endif
                                @if($f['state'] === 'detention')<div class="small text-danger">{{ $f['over'] }} {{ __('days over') }}</div>
                                @elseif(in_array($f['state'], ['free','ending']))<div class="small text-muted">{{ $f['left'] }} {{ __('days left') }}</div>@endif</td>
                            <td><span class="badge bg-{{ $bc }}">{{ __($bl) }}</span></td>
                            <td class="text-end fw-semibold {{ $f['cost'] ? 'text-danger' : '' }}">{{ $f['cost'] ? number_format($f['cost'], 2) : '—' }}</td>
                            <td class="text-end"><button class="btn btn-sm btn-outline-primary rounded-pill dm-edit" data-id="{{ $c->id }}" data-title="{{ $c->container_no ?: $c->container_number }}">{{ __('Update') }}</button></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-5">{{ __('Nothing here.') }}</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</x-app-layout>
