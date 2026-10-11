@section('page-title', $title)
@section('page-subtitle', $subtitle)
@section('hide-topbar', true)
<div class="provisional-wrapper min-vh-100 bg-light pb-4">
    <div class="container-fluid px-3">
        @include('includes.report-print-css', ['orientation' => 'landscape'])
        <style>.rpt-title{display:none}body:not(.has-top-header) .rpt-title{display:block}</style>
        <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap pt-2 pb-2 d-print-none">
            <div class="rpt-title"><h4 class="fw-bold text-dark mb-0">{{ $title }}</h4><div class="text-muted small mt-1">{{ $subtitle }}</div></div>
            <div class="btn-group shadow-sm ms-auto">
                <button class="btn btn-white border py-2" onclick="window.print()"><i class="bi bi-printer me-2"></i>{{ __('Print') }}</button>
                <button class="btn btn-white border py-2" onclick="reportExportPdf(event, 'pk-print', {orientation: 'landscape'})"><i class="bi bi-file-pdf text-danger me-2"></i>{{ __('PDF') }}</button>
                <button class="btn btn-white border py-2" wire:click="exportExcel"><i class="bi bi-file-excel text-success me-2"></i>{{ __('Excel') }}</button>
            </div>
        </div>
        <div class="card border-0 shadow-sm mb-4 d-print-none"><div class="card-body p-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-3"><label class="form-label small fw-bold text-uppercase text-muted">{{ __('From Date') }}</label>
                    <input type="date" class="form-control bg-light border-0 py-2" wire:model.live="startDate"></div>
                <div class="col-md-3"><label class="form-label small fw-bold text-uppercase text-muted">{{ __('To Date') }}</label>
                    <input type="date" class="form-control bg-light border-0 py-2" wire:model.live="endDate"></div>
                <div class="col-md-3"><label class="form-label small fw-bold text-uppercase text-muted">{{ __('Group By') }}</label>
                    <select class="form-select bg-light border-0 py-2" wire:model.live="groupBy">
                        @foreach($groups as $k => $l)<option value="{{ $k }}">{{ $l }}</option>@endforeach
                    </select></div>
            </div>
        </div></div>
        <div class="card border-0 shadow-sm overflow-hidden d-print-none">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="bg-light"><tr class="text-muted text-uppercase small">
                        <th>{{ $groups[$groupBy] ?? '' }}</th>@foreach($cols as $c)<th class="text-end">{{ $c }}</th>@endforeach</tr></thead>
                    <tbody>
                    @forelse($rows as $r)
                        <tr><td class="fw-semibold">{{ $r[0] }}</td>@for($i = 1; $i < count($r); $i++)<td class="text-end">{{ number_format($r[$i], is_float($r[$i]) ? 2 : 0) }}</td>@endfor</tr>
                    @empty
                        <tr><td colspan="{{ count($cols) + 1 }}" class="text-center text-muted py-5">{{ __('No jobs in this period.') }}</td></tr>
                    @endforelse
                    </tbody>
                    @if(count($rows))
                        <tfoot><tr class="fw-bold bg-light"><td>{{ __('Total') }}</td>@for($i = 1; $i < count($totals); $i++)<td class="text-end">{{ number_format($totals[$i], 2) }}</td>@endfor</tr></tfoot>
                    @endif
                </table>
            </div>
        </div>

        <div id="pk-print" class="stmt-print d-none d-print-block" data-pdf-filename="{{ \Illuminate\Support\Str::slug($title) }}">
            <div class="stmt-company">{{ auth()->user()->company->name_en ?? '' }}</div>
            <div class="stmt-title">{{ $title }}</div>
            <div class="stmt-sub">{{ $startDate }} - {{ $endDate }} · {{ $groups[$groupBy] ?? '' }}</div>
            <table class="stmt-table">
                <thead><tr><th>{{ $groups[$groupBy] ?? '' }}</th>@foreach($cols as $c)<th style="text-align:right">{{ $c }}</th>@endforeach</tr></thead>
                <tbody>
                @foreach($rows as $r)
                    <tr><td>{{ $r[0] }}</td>@for($i = 1; $i < count($r); $i++)<td style="text-align:right">{{ number_format($r[$i], is_float($r[$i]) ? 2 : 0) }}</td>@endfor</tr>
                @endforeach
                @if(count($rows))<tr class="stmt-strong"><td>{{ __('Total') }}</td>@for($i = 1; $i < count($totals); $i++)<td style="text-align:right">{{ number_format($totals[$i], 2) }}</td>@endfor</tr>@endif
                </tbody>
            </table>
        </div>
    </div>
</div>
