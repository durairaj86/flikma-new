@extends('portal.layout')
@section('title', __('Quotations'))
@section('content')
    <h4 class="fw-bold mb-1">{{ __('Quotations') }}</h4>
    <p class="text-muted small">{{ __('Quotations waiting for your answer. Open one to accept or reject it.') }}</p>
    <div class="p-card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr>
                    <th>{{ __('Quotation') }}</th><th>{{ __('Route') }}</th><th>{{ __('Valid Until') }}</th><th class="text-end">{{ __('Total') }}</th><th></th>
                </tr></thead>
                <tbody>
                @forelse($quotations as $q)
                    <tr>
                        <td class="fw-semibold">{{ $q->row_no }}</td>
                        <td class="small">{{ $q->pol ?: '—' }} → {{ $q->pod ?: '—' }}</td>
                        <td class="small text-nowrap">{{ $q->valid_until }}</td>
                        <td class="text-end text-nowrap">{{ number_format((float) $q->total, 2) }} {{ $currency }}</td>
                        <td class="text-end">
                            @if((int) $q->customer_status === \App\Models\Quotation\Quotation::CUSTOMER_NO_ANSWER)
                                <a href="{{ url('/portal/quotations/' . $q->id) }}" class="btn btn-sm btn-primary">{{ __('View and answer') }}</a>
                            @else
                                <span class="badge bg-light text-muted border">{{ (int) $q->customer_status === \App\Models\Quotation\Quotation::CUSTOMER_ACCEPTED ? __('Customer accepted') : __('Customer rejected') }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">{{ __('No quotations waiting for your answer.') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
