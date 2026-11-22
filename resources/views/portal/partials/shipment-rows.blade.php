<div class="table-responsive">
    <table class="table align-middle mb-0">
        <thead><tr>
            <th>{{ __('Shipment') }}</th><th>{{ __('Route') }}</th><th>{{ __('B/L / AWB') }}</th>
            <th>{{ __('ETD') }}</th><th>{{ __('ETA') }}</th><th>{{ __('Status') }}</th>
        </tr></thead>
        <tbody>
        @forelse($jobs as $job)
            <tr>
                <td><a href="{{ url('/portal/shipments/' . $job->id) }}" class="fw-semibold text-decoration-none">{{ $job->row_no ?: '#' . $job->id }}</a></td>
                <td class="small">{{ $job->pol_name ?? $job->pol ?? '—' }} → {{ $job->pod_name ?? $job->pod ?? '—' }}</td>
                <td class="small">{{ $job->hbl_number ?: ($job->awb_number ?: '—') }}</td>
                <td class="small text-nowrap">{{ $job->etd ? \Carbon\Carbon::parse($job->etd)->translatedFormat('d M Y') : '—' }}</td>
                <td class="small text-nowrap">{{ $job->eta ? \Carbon\Carbon::parse($job->eta)->translatedFormat('d M Y') : '—' }}</td>
                <td><span class="badge rounded-pill {{ (int) $job->status === \App\Enums\JobEnum::COMPLETED->value ? 'bg-success-subtle text-success-emphasis' : 'bg-primary-subtle text-primary-emphasis' }}">{{ (int) $job->status === \App\Enums\JobEnum::COMPLETED->value ? __('Delivered') : __('In progress') }}</span></td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted py-4">{{ __('No shipments to show.') }}</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
