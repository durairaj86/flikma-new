<div class="alert alert-info d-flex justify-content-between align-items-center">
    <span><i class="bi bi-info-circle me-2"></i>{{ __('Showing recent statement entries.') }}</span>
    <a href="{{ url('/customer/statement?customer=' . $customer->id) }}" class="btn btn-primary btn-sm"><i class="bi bi-file-earmark-text me-1"></i>{{ __('Full Statement') }}</a>
</div>
<div class="cust-card">
    <table class="table cust-tbl align-middle mb-0">
        <thead><tr><th>{{ __('Date') }}</th><th>{{ __('Details') }}</th><th class="text-end">{{ __('Debit') }}</th><th class="text-end">{{ __('Credit') }}</th><th class="text-end">{{ __('Balance') }}</th></tr></thead>
        <tbody>
        @forelse($rows as $r)
            <tr>
                <td>{{ $r['date']->format('d-m-Y') }}</td><td>{{ $r['details'] }}</td>
                <td class="text-end text-danger">{{ $r['debit'] ? number_format($r['debit'], 2) : '' }}</td>
                <td class="text-end text-success">{{ $r['credit'] ? number_format($r['credit'], 2) : '' }}</td>
                <td class="text-end fw-bold">{{ number_format($r['balance'], 2) }}</td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted py-4">{{ __('No entries yet.') }}</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
