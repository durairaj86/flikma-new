<div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr class="bg-light text-muted small text-uppercase fw-bold ls-1">
                    <th class="ps-4 border-0">Job No</th>
                    <th class="border-0">Date</th>
                    <th class="border-0">Customer</th>
                    <th class="border-0">Activity</th>
                    <th class="border-0">AWB / MBL</th>
                    <th class="border-0">HBL / HAWB</th>
                    <th class="border-0">Shipper</th>
                    <th class="border-0">Consignee</th>
                    <th class="border-0">POL</th>
                    <th class="border-0">POD</th>
                    <th class="text-center pe-4 border-0">Status</th>
                </tr>
            </thead>
            <tbody class="border-top-0">
                @if(isset($jobReportData['jobs']) && count($jobReportData['jobs']) > 0)
                    @foreach($jobReportData['jobs'] as $job)
                        <tr wire:key="jr-{{ $job->id }}">
                            <td class="ps-4">
                                <a href="/jobs/{{ $job->id }}" class="fw-bold text-pr text-decoration-none">{{ $job->row_no }}</a>
                            </td>
                            <td class="small text-muted">{{ \Carbon\Carbon::parse($job->posted_at)->format('d M Y') }}</td>
                            <td class="small">{{ $job->customer->name ?? 'N/A' }}</td>
                            <td class="small">{{ $job->activity->name ?? 'N/A' }}</td>
                            <td class="small text-muted">{{ $job->awb_no ?? '—' }}</td>
                            <td class="small text-muted">{{ $job->hbl_no ?? '—' }}</td>
                            <td class="small">{{ $job->shipper ?? '—' }}</td>
                            <td class="small">{{ $job->consignee ?? '—' }}</td>
                            <td class="small text-muted">{{ $job->pol ?? '—' }}</td>
                            <td class="small text-muted">{{ $job->pod ?? '—' }}</td>
                            <td class="text-center pe-4">
                                @php
                                    $s = $job->status ?? '';
                                    $badgeClass = match(true) {
                                        $s == 'draft'     => 'bg-secondary-subtle text-secondary border-secondary-subtle',
                                        $s == 'active'    => 'bg-primary-subtle text-primary border-primary-subtle',
                                        $s == 'completed' => 'bg-success-subtle text-success border-success-subtle',
                                        $s == 'cancelled' => 'bg-danger-subtle text-danger border-danger-subtle',
                                        default           => 'bg-light text-muted border',
                                    };
                                @endphp
                                <span class="badge rounded-pill px-2 py-1 border {{ $badgeClass }}">{{ ucfirst($s ?: '—') }}</span>
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="11" class="text-center py-5 text-muted">
                            <div class="bg-light rounded-circle p-4 d-inline-block mb-3">
                                <i class="bi bi-briefcase h2 text-muted"></i>
                            </div>
                            <div class="small">No jobs found for the selected period.</div>
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
