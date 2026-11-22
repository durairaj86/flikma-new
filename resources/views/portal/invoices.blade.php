@extends('portal.layout')
@section('title', __('Invoices'))
@section('content')
    <h4 class="fw-bold mb-3">{{ __('Invoices') }}</h4>
    <div class="p-card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr>
                    <th>{{ __('Invoice') }}</th><th>{{ __('Date') }}</th><th>{{ __('Due Date') }}</th>
                    <th class="text-end">{{ __('Amount') }}</th><th class="text-end">{{ __('Balance') }}</th><th></th>
                </tr></thead>
                <tbody>
                @forelse($invoices as $inv)
                    @php($o = $open[$inv->id] ?? null)
                    <tr>
                        <td class="fw-semibold">{{ $inv->row_no }}</td>
                        <td class="small text-nowrap">{{ \Carbon\Carbon::parse($inv->invoice_date)->translatedFormat('d M Y') }}</td>
                        <td class="small text-nowrap {{ $o && $o['days'] > 0 ? 'text-danger fw-semibold' : '' }}">
                            {{ \Carbon\Carbon::parse($inv->due_at ?? $inv->due_date ?? $inv->invoice_date)->translatedFormat('d M Y') }}
                            @if($o && $o['days'] > 0)<div class="small">{{ __(':n days late', ['n' => $o['days']]) }}</div>@endif
                        </td>
                        <td class="text-end text-nowrap">{{ number_format((float) $inv->grand_total, 2) }} {{ strtoupper((string) $inv->currency) }}</td>
                        <td class="text-end text-nowrap">
                            @if($o)<b>{{ number_format($o['balance'], 2) }}</b>@else<span class="badge bg-success-subtle text-success-emphasis">{{ __('Paid') }}</span>@endif
                        </td>
                        <td class="text-end"><a href="{{ url('/portal/invoices/' . $inv->id . '/print') }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary"><i class="bi bi-printer me-1"></i>{{ __('View') }}</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">{{ __('No invoices yet.') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $invoices->links('pagination::bootstrap-5') }}</div>
@endsection
