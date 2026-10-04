<div>
    <div class="table-responsive d-print-none">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr class="bg-light text-muted small text-uppercase fw-bold ls-1">
                    <th class="ps-4 border-0">{{ __('Job No') }}</th>
                    <th class="border-0">{{ __('Date') }}</th>
                    <th class="border-0">{{ __('Customer') }}</th>
                    <th class="border-0">{{ __('Department') }}</th>
                    <th class="border-0">{{ __('AWB / MBL') }}</th>
                    <th class="border-0">{{ __('HBL / HAWB') }}</th>
                    <th class="border-0">{{ __('Shipper') }}</th>
                    <th class="border-0">{{ __('Consignee') }}</th>
                    <th class="border-0">{{ __('POL') }}</th>
                    <th class="border-0">{{ __('POD') }}</th>
                    <th class="text-center pe-4 border-0">{{ __('Status') }}</th>
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
                            <td class="small">{{ $job->customer->name ?? __('N/A') }}</td>
                            <td class="small">{{ $job->activity->name ?? __('N/A') }}</td>
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
                            <div class="small">{{ __('No jobs found for the selected period.') }}</div>
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    {{-- Bank-statement style layout: used for Print and PDF export only --}}
    <div id="jr-print" class="stmt-print d-none d-print-block"
         data-pdf-filename="JobReport-{{ $startDate ?? '' }}-{{ $endDate ?? '' }}.pdf">

        <table class="stmt-meta">
            <tr>
                <td>
                    <div class="stmt-company">{{ optional(authUserCompany())->name ?? config('app.name') }}</div>
                </td>
                <td class="text-end">
                    <div class="stmt-title">{{ __('JOB REPORT') }}</div>
                    <div class="stmt-sub">{{ __('Period:') }} {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} — {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</div>
                    <div class="stmt-sub">{{ __('Generated:') }} {{ now()->format('d M Y H:i') }}</div>
                </td>
            </tr>
        </table>

        <table class="stmt-table">
            <thead>
            <tr>
                <th>{{ __('Job No') }}</th>
                <th>{{ __('Date') }}</th>
                <th>{{ __('Customer') }}</th>
                <th>{{ __('Department') }}</th>
                <th>{{ __('AWB/MBL') }}</th>
                <th>{{ __('HBL/HAWB') }}</th>
                <th>{{ __('POL') }}</th>
                <th>{{ __('POD') }}</th>
                <th>{{ __('Status') }}</th>
            </tr>
            </thead>
            <tbody>
            @forelse($jobReportData['jobs'] as $job)
                <tr>
                    <td>{{ $job->row_no }}</td>
                    <td>{{ \Carbon\Carbon::parse($job->posted_at)->format('d M Y') }}</td>
                    <td>{{ $job->customer->name ?? __('N/A') }}</td>
                    <td>{{ $job->activity->name ?? __('N/A') }}</td>
                    <td>{{ $job->awb_no ?? '—' }}</td>
                    <td>{{ $job->hbl_no ?? '—' }}</td>
                    <td>{{ $job->pol ?? '—' }}</td>
                    <td>{{ $job->pod ?? '—' }}</td>
                    <td>{{ ucfirst($job->status ?? '—') }}</td>
                </tr>
            @empty
                <tr><td colspan="9" class="text-center">{{ __('No jobs found for the selected period.') }}</td></tr>
            @endforelse
            </tbody>
        </table>

        <div class="stmt-footnote">{{ __('Total jobs:') }} {{ count($jobReportData['jobs']) }}</div>
    </div>

    @include('includes.report-print-css', ['orientation' => 'landscape'])
</div>
