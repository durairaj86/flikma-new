@extends('portal.layout')
@section('title', __('Statement'))
@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <h4 class="fw-bold mb-0">{{ __('Statement') }}</h4>
        <form class="d-flex gap-2 align-items-center" method="GET">
            <input type="text" name="from" value="{{ $from->format('Y-m-d') }}" class="form-control form-control-sm datepicker" style="width:130px" aria-label="{{ __('From') }}">
            <span class="text-muted">–</span>
            <input type="text" name="to" value="{{ $to->format('Y-m-d') }}" class="form-control form-control-sm datepicker" style="width:130px" aria-label="{{ __('To') }}">
            <button class="btn btn-sm btn-primary">{{ __('Show') }}</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()"><i class="bi bi-printer"></i></button>
        </form>
    </div>
    <div class="p-card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr>
                    <th>{{ __('Date') }}</th><th>{{ __('Details') }}</th>
                    <th class="text-end">{{ __('Debit') }}</th><th class="text-end">{{ __('Credit') }}</th><th class="text-end">{{ __('Balance') }} ({{ $currency }})</th>
                </tr></thead>
                <tbody>
                <tr class="table-light"><td colspan="4" class="fw-semibold">{{ __('Opening balance') }} · {{ $from->translatedFormat('d M Y') }}</td><td class="text-end fw-semibold">{{ number_format($statement['opening'], 2) }}</td></tr>
                @forelse($statement['rows'] as $r)
                    <tr>
                        <td class="small text-nowrap">{{ $r['date']->translatedFormat('d M Y') }}</td>
                        <td class="small">{{ $r['details'] }}</td>
                        <td class="text-end">{{ $r['debit'] ? number_format($r['debit'], 2) : '' }}</td>
                        <td class="text-end">{{ $r['credit'] ? number_format($r['credit'], 2) : '' }}</td>
                        <td class="text-end">{{ number_format($r['balance'], 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-3">{{ __('No entries in this period.') }}</td></tr>
                @endforelse
                <tr class="table-light"><td colspan="4" class="fw-bold">{{ __('Closing balance') }} · {{ $to->translatedFormat('d M Y') }}</td><td class="text-end fw-bold">{{ number_format($statement['closing'], 2) }}</td></tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection
