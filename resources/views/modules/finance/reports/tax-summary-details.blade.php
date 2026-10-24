<div class="p-3">
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0 small">
            <thead class="table-light">
            <tr>
                <th>{{ __('Date') }}</th>
                <th>{{ __('Document') }}</th>
                <th>{{ __('Type') }}</th>
                <th>{{ __('Party') }}</th>
                <th class="text-end">{{ __('Amount') }}</th>
                <th class="text-end">{{ __('VAT') }}</th>
            </tr>
            </thead>
            <tbody>
            @forelse($entries as $e)
                <tr>
                    <td class="text-nowrap">{{ $e->date }}</td>
                    <td class="text-nowrap">{{ $e->doc_number }}</td>
                    <td>{{ $e->doc_type }}</td>
                    <td>{{ $e->party }}</td>
                    <td class="text-end">{{ number_format($e->net_amount, 2) }}</td>
                    <td class="text-end">{{ number_format($e->tax_amount, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-muted fst-italic">{{ __('No documents') }}</td></tr>
            @endforelse
            </tbody>
            <tfoot class="table-light fw-bold">
            <tr>
                <td colspan="4">{{ __('Total') }}</td>
                <td class="text-end">{{ number_format($totalNet, 2) }}</td>
                <td class="text-end">{{ number_format($totalTax, 2) }}</td>
            </tr>
            </tfoot>
        </table>
    </div>
</div>
