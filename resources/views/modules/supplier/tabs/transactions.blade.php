<div class="cust-card">
    <table class="table cust-tbl align-middle mb-0">
        <thead><tr><th>{{ __('Date') }}</th><th>{{ __('Type') }}</th><th>{{ __('Reference') }}</th><th>{{ __('Mode') }}</th><th class="text-end">{{ __('Amount') }}</th><th class="text-center">{{ __('Status') }}</th></tr></thead>
        <tbody>
        @forelse($rows as $r)
            <tr>
                <td>{{ $r['date']->format('d-m-Y') }}</td><td>{{ $r['type'] }}</td><td class="text-muted">{{ $r['ref'] }}</td><td>{{ $r['mode'] }}</td>
                <td class="text-end fw-bold">{{ number_format($r['amount'], 2) }}</td>
                <td class="text-center"><span class="badge rounded-pill {{ $r['status'] === 'Approved' ? 'bg-success-subtle text-success' : ($r['status'] === 'Cancelled' ? 'bg-warning-subtle text-warning' : 'bg-secondary-subtle text-secondary') }}">{{ __($r['status']) }}</span></td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted py-4">{{ __('No transactions yet.') }}</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
