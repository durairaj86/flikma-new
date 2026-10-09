<div class="cust-card">
    <table class="table cust-tbl align-middle mb-0">
        <thead><tr><th>{{ __('Invoice #') }}</th><th>{{ __('Date') }}</th><th>{{ __('Due Date') }}</th><th class="text-end">{{ __('Total') }}</th><th class="text-end">{{ __('Balance') }}</th><th class="text-center">{{ __('Status') }}</th></tr></thead>
        <tbody>
        @forelse($rows as $r)
            @php $late = $r['balance'] > 0 && $r['due']->isPast(); @endphp
            <tr>
                <td class="fw-semibold text-primary">{{ $r['no'] }}</td>
                <td>{{ $r['date']->format('d-m-Y') }}</td>
                <td class="{{ $late ? 'text-danger' : '' }}">{{ $r['due']->format('d-m-Y') }}</td>
                <td class="text-end">{{ number_format($r['total'], 2) }}</td>
                <td class="text-end fw-bold {{ $r['balance'] > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($r['balance'], 2) }}</td>
                <td class="text-center"><span class="badge rounded-pill {{ $r['balance'] > 0 ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">{{ $r['balance'] > 0 ? __('Approved') : __('Paid') }}</span></td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted py-4">{{ __('No invoices yet.') }}</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
