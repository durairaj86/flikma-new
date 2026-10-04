{{-- Tall dashboard widget (medium width, double medium height): Recent Transactions. Extra rows scroll vertically. --}}
@php
    $tStatus = [
        1 => [__('Draft'), 'bg-warning text-dark'],
        2 => [__('Sent'), 'bg-info text-dark'],
        3 => [__('Approved'), 'bg-success'],
        4 => [__('Rejected'), 'bg-danger'],
        5 => [__('Cancelled'), 'bg-secondary'],
    ];
@endphp
<div class="kpi kpi-tall" style="--kc: #475569; --kbg: #ffffff;">
    <div class="kpi-head">
        <span class="kpi-icon"><i class="fa-solid fa-receipt"></i></span>
        <span class="kpi-title">{{ __('Recent Transactions') }}</span>
        <a href="{{ route('invoices.customer') }}" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size:.75rem;">{{ __('View All') }}</a>
    </div>
    <div class="kpi-tall-scroll">
        <table class="table table-sm table-hover mb-0 align-middle kpi-table">
            <thead>
            <tr>
                <th>{{ __('Invoice') }}</th>
                <th>{{ __('Customer') }}</th>
                <th class="text-end">{{ __('Amount') }}</th>
                <th>{{ __('Status') }}</th>
            </tr>
            </thead>
            <tbody>
            @forelse($recentTransactions as $transaction)
                @php $st = $tStatus[(int) $transaction->status] ?? [$transaction->status, 'bg-secondary']; @endphp
                <tr>
                    <td>
                        <div class="fw-semibold">{{ $transaction->invoice_number ?? $transaction->row_no ?? '#' . $transaction->id }}</div>
                        <small class="text-muted">{{ $transaction->invoice_date }}</small>
                    </td>
                    <td class="text-truncate" style="max-width:110px;">{{ $transaction->customer->name_en ?? $transaction->customer->name ?? __('N/A') }}</td>
                    <td class="text-end">{{ number_format($transaction->grand_total, 0) }}</td>
                    <td><span class="badge {{ $st[1] }}">{{ $st[0] }}</span></td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted py-4">{{ __('No recent transactions found') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="kpi-tall-foot">{{ __('Showing :shown of :total transactions', ['shown' => count($recentTransactions), 'total' => $totalInvoices]) }}</div>
</div>
