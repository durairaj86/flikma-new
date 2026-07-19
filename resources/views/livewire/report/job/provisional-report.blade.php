@section('js', 'provisional_report')
@section('page-title', 'Provisional Report')

<div class="provisional-wrapper min-vh-100 bg-light py-4">
    <div class="container-fluid px-lg-5">

        {{-- Page Header --}}
        <div class="row align-items-center mb-4 d-print-none">
            <div class="col-md-6">
                <h1 class="h3 fw-bold text-slate-900 mb-1">Provisional Report</h1>
                <p class="text-muted small mb-0">Compare provisional vs actual cost &amp; sales — per job or by activity</p>
            </div>
            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                <div class="btn-group shadow-sm">
                    <button class="btn btn-white border border-end-0" onclick="window.print()">
                        <i class="bi bi-printer me-2"></i>Print
                    </button>
                    <div class="btn-group">
                        <button class="btn btn-white border dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="bi bi-download me-2"></i>Export
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow">
                            <li><a class="dropdown-item py-2" href="#" onclick="reportExportPdf(event, 'pr-print', {orientation: 'landscape'})"><i class="bi bi-file-pdf text-danger me-2"></i>PDF Document</a></li>
                            <li><a class="dropdown-item py-2" href="#" wire:click.prevent="exportExcel"><i class="bi bi-file-excel text-success me-2"></i>Excel Sheet</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="card border-0 shadow-sm mb-4 d-print-none">
            <div class="card-body p-4">

                {{-- View Mode Toggle --}}
                <div class="mb-3 pb-3 border-bottom">
                    <label class="form-label small fw-bold text-uppercase text-muted ls-1 me-3">View By</label>
                    <div class="btn-group" role="group">
                        <input type="radio" class="btn-check" name="viewMode" id="vm-job" value="job"
                               wire:model.live="viewMode" autocomplete="off"
                               @checked($viewMode === 'job') />
                        <label class="btn btn-outline-pr btn-sm px-4 fw-bold" for="vm-job">
                            <i class="bi bi-briefcase me-1"></i>Job Based
                        </label>

                        <input type="radio" class="btn-check" name="viewMode" id="vm-activity" value="activity"
                               wire:model.live="viewMode" autocomplete="off"
                               @checked($viewMode === 'activity') />
                        <label class="btn btn-outline-pr btn-sm px-4 fw-bold" for="vm-activity">
                            <i class="bi bi-activity me-1"></i>Activity Based
                        </label>
                    </div>
                    <span class="ms-3 text-muted small">
                        @if($viewMode === 'activity')
                            <i class="bi bi-info-circle me-1"></i>Grouped by shipment mode (Ocean, Air, Land, etc.)
                        @else
                            <i class="bi bi-info-circle me-1"></i>One row per job
                        @endif
                    </span>
                </div>

                <div class="row g-3 align-items-end">
                    <div class="col-lg-2 col-md-4">
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">From Date</label>
                        <input type="hidden" id="pr-start-date-hidden" wire:model="startDate" value="{{ $startDate }}" />
                        <input type="text" id="pr-start-date"
                               class="form-control bg-light border-0 py-2"
                               placeholder="dd-mm-yyyy"
                               value="{{ $startDate }}" />
                    </div>
                    <div class="col-lg-2 col-md-4">
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">To Date</label>
                        <input type="hidden" id="pr-end-date-hidden" wire:model="endDate" value="{{ $endDate }}" />
                        <input type="text" id="pr-end-date"
                               class="form-control bg-light border-0 py-2"
                               placeholder="dd-mm-yyyy"
                               value="{{ $endDate }}" />
                    </div>
                    <div class="col-lg-2 col-md-4">
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">Mode</label>
                        <select class="form-select bg-light border-0 py-2" wire:model="shipmentMode">
                            <option value="">All Modes</option>
                            @foreach($modes as $mode)
                                <option value="{{ $mode }}">{{ ucfirst($mode) }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if($viewMode === 'job')
                    <div class="col-lg-2 col-md-4">
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">Type</label>
                        <select class="form-select bg-light border-0 py-2" wire:model="shipmentType">
                            <option value="">All Types</option>
                            @foreach($types as $type)
                                <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-4">
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">Search</label>
                        <input type="text" class="form-control bg-light border-0 py-2"
                               wire:model.debounce.400ms="search"
                               placeholder="Job no..." />
                    </div>
                    @endif
                    <div class="col-lg-2 col-md-4">
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-pr fw-bold py-2 flex-grow-1 shadow-sm"
                                    wire:click="applyFilter" wire:loading.attr="disabled">
                                <i class="bi bi-filter-left me-2"></i>
                                <span wire:loading.remove>Generate</span>
                                <span wire:loading><span class="spinner-border spinner-border-sm me-1"></span>Loading...</span>
                            </button>
                            <button type="button" class="btn btn-outline-secondary border-0 bg-light py-2 px-3"
                                    wire:click="resetFilter">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Summary Cards --}}
        @if(count($rows) > 0)
        <div class="row g-3 mb-4 d-print-none">
            <div class="col-lg col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3 text-center">
                        <div class="small text-muted fw-bold text-uppercase mb-1 ls-1">Prov. Cost</div>
                        <div class="h5 fw-bold text-secondary mb-0 tabular-nums">{{ number_format($totals['provisional_cost'], 2) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3 text-center">
                        <div class="small text-muted fw-bold text-uppercase mb-1 ls-1">Actual Cost</div>
                        <div class="h5 fw-bold text-danger mb-0 tabular-nums">{{ number_format($totals['actual_cost'], 2) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3 text-center">
                        <div class="small text-muted fw-bold text-uppercase mb-1 ls-1">Prov. Sales</div>
                        <div class="h5 fw-bold text-secondary mb-0 tabular-nums">{{ number_format($totals['provisional_sales'], 2) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3 text-center">
                        <div class="small text-muted fw-bold text-uppercase mb-1 ls-1">Actual Sales</div>
                        <div class="h5 fw-bold text-pr mb-0 tabular-nums">{{ number_format($totals['actual_sales'], 2) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3 text-center">
                        <div class="small text-muted fw-bold text-uppercase mb-1 ls-1">Profit / Loss</div>
                        <div class="h5 fw-bold mb-0 tabular-nums {{ $totals['profit_loss'] >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ number_format($totals['profit_loss'], 2) }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3 text-center">
                        <div class="small text-muted fw-bold text-uppercase mb-1 ls-1">Margin</div>
                        <div class="h5 fw-bold mb-0 tabular-nums {{ $totals['margin'] >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ number_format($totals['margin'], 1) }}%
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Table --}}
        <div class="card border-0 shadow-sm overflow-hidden d-print-none">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">
                    <i class="bi {{ $viewMode === 'activity' ? 'bi-activity' : 'bi-table' }} me-2 text-pr"></i>
                    {{ $viewMode === 'activity' ? 'Activity Summary' : 'Job Comparison' }}
                </h6>
                <span class="badge bg-pr-subtle text-pr border border-pr-subtle px-3 py-2">
                    {{ count($rows) }} {{ $viewMode === 'activity' ? Str::plural('Activity', count($rows)) : Str::plural('Job', count($rows)) }}
                </span>
            </div>
            <div class="table-responsive">

                @if($viewMode === 'activity')
                {{-- Activity-Based Table --}}
                <table class="table table-hover align-middle mb-0">
                    <thead>
                    <tr class="bg-light text-muted small text-uppercase fw-bold ls-1">
                        <th class="ps-4 border-0">Activity (Mode)</th>
                        <th class="text-center border-0">Jobs</th>
                        <th class="text-end border-0">
                            <span class="d-block text-muted" style="font-size:0.65rem;">Provisional</span>Cost
                        </th>
                        <th class="text-end border-0">
                            <span class="d-block text-muted" style="font-size:0.65rem;">Actual</span>Cost
                        </th>
                        <th class="text-end border-0">
                            <span class="d-block text-muted" style="font-size:0.65rem;">Provisional</span>Sales
                        </th>
                        <th class="text-end border-0">
                            <span class="d-block text-muted" style="font-size:0.65rem;">Actual</span>Sales
                        </th>
                        <th class="text-end border-0">Profit / Loss</th>
                        <th class="border-0">Cost vs Budget</th>
                        <th class="text-end pe-4 border-0">Margin</th>
                    </tr>
                    </thead>
                    <tbody class="border-top-0">
                    @forelse($rows as $row)
                        <tr wire:key="pr-act-{{ $loop->index }}">
                            <td class="ps-4">
                                <span class="fw-bold text-dark">
                                    @php
                                        $modeIcons = [
                                            'Ocean' => 'bi-water',
                                            'Sea'   => 'bi-water',
                                            'Air'   => 'bi-airplane',
                                            'Land'  => 'bi-truck',
                                            'Road'  => 'bi-truck',
                                            'Courier' => 'bi-box-seam',
                                            'VAS'   => 'bi-stars',
                                        ];
                                        $icon = $modeIcons[$row['activity']] ?? 'bi-box';
                                    @endphp
                                    <i class="bi {{ $icon }} text-pr me-2"></i>{{ $row['activity'] }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border">{{ $row['job_count'] }}</span>
                            </td>
                            <td class="text-end tabular-nums small text-muted">
                                {{ $row['provisional_cost'] > 0 ? number_format($row['provisional_cost'], 2) : '—' }}
                            </td>
                            <td class="text-end tabular-nums small text-danger">
                                {{ $row['actual_cost'] > 0 ? number_format($row['actual_cost'], 2) : '—' }}
                            </td>
                            <td class="text-end tabular-nums small text-muted">
                                {{ $row['provisional_sales'] > 0 ? number_format($row['provisional_sales'], 2) : '—' }}
                            </td>
                            <td class="text-end tabular-nums small text-pr fw-medium">
                                {{ $row['actual_sales'] > 0 ? number_format($row['actual_sales'], 2) : '—' }}
                            </td>
                            <td class="text-end tabular-nums fw-bold {{ $row['profit_loss'] >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ number_format($row['profit_loss'], 2) }}
                            </td>
                            <td style="min-width:110px;">
                                @php
                                    $baseCost = $row['provisional_cost'] > 0 ? $row['provisional_cost'] : $row['actual_cost'];
                                    $costPct  = $baseCost > 0 ? min(100, ($row['actual_cost'] / $baseCost) * 100) : 0;
                                    $overBudget = $row['actual_cost'] > $row['provisional_cost'] && $row['provisional_cost'] > 0;
                                @endphp
                                <div class="progress pr-variance-bar" style="height:6px;">
                                    <div class="progress-bar {{ $overBudget ? 'bg-danger' : 'bg-pr' }}" style="width: {{ $costPct }}%"></div>
                                </div>
                                <div class="x-small text-muted mt-1">
                                    Cost @if($row['provisional_cost'] > 0)
                                        <span class="{{ $overBudget ? 'text-danger fw-bold' : 'text-success' }}">
                                            {{ $overBudget ? '▲' : '▼' }} {{ number_format(abs((($row['actual_cost'] - $row['provisional_cost']) / $row['provisional_cost']) * 100), 1) }}%
                                        </span>
                                    @else
                                        <span class="text-muted">n/a</span>
                                    @endif
                                </div>
                            </td>
                            <td class="text-end pe-4">
                                <span class="badge rounded-pill px-2 py-1 {{ $row['margin'] >= 20 ? 'bg-success-subtle text-success' : ($row['margin'] >= 10 ? 'bg-warning-subtle text-warning' : ($row['margin'] > 0 ? 'bg-secondary-subtle text-secondary' : 'bg-danger-subtle text-danger')) }}">
                                    {{ number_format($row['margin'], 1) }}%
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <div class="bg-light rounded-circle p-4 d-inline-block mb-3">
                                    <i class="bi bi-bar-chart h2 text-muted"></i>
                                </div>
                                <div class="small">No data found for the selected period.</div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                    @if(count($rows) > 0)
                    <tfoot class="bg-light border-top-2">
                    <tr class="fw-bold">
                        <td class="ps-4 py-3">Totals</td>
                        <td class="text-center text-muted">{{ collect($rows)->sum('job_count') }}</td>
                        <td class="text-end tabular-nums text-muted">{{ number_format($totals['provisional_cost'], 2) }}</td>
                        <td class="text-end tabular-nums text-danger">{{ number_format($totals['actual_cost'], 2) }}</td>
                        <td class="text-end tabular-nums text-muted">{{ number_format($totals['provisional_sales'], 2) }}</td>
                        <td class="text-end tabular-nums text-pr">{{ number_format($totals['actual_sales'], 2) }}</td>
                        <td class="text-end tabular-nums {{ $totals['profit_loss'] >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($totals['profit_loss'], 2) }}</td>
                        <td></td>
                        <td class="text-end pe-4 {{ $totals['margin'] >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($totals['margin'], 1) }}%</td>
                    </tr>
                    </tfoot>
                    @endif
                </table>

                @else
                {{-- Job-Based Table --}}
                <table class="table table-hover align-middle mb-0">
                    <thead>
                    <tr class="bg-light text-muted small text-uppercase fw-bold ls-1">
                        <th class="border-0" style="width: 40px;"></th>
                        <th class="ps-2 border-0">Job No</th>
                        <th class="border-0">Date</th>
                        <th class="border-0">Mode / Type</th>
                        <th class="text-end border-0">
                            <span class="d-block text-muted" style="font-size:0.65rem;">Provisional</span>Cost
                        </th>
                        <th class="text-end border-0">
                            <span class="d-block text-muted" style="font-size:0.65rem;">Actual</span>Cost
                        </th>
                        <th class="text-end border-0">
                            <span class="d-block text-muted" style="font-size:0.65rem;">Provisional</span>Sales
                        </th>
                        <th class="text-end border-0">
                            <span class="d-block text-muted" style="font-size:0.65rem;">Actual</span>Sales
                        </th>
                        <th class="text-end border-0">Profit / Loss</th>
                        <th class="border-0">Cost vs Budget</th>
                        <th class="text-end pe-4 border-0">Margin</th>
                    </tr>
                    </thead>
                    <tbody class="border-top-0">
                    @forelse($rows as $pIndex => $row)
                        @php
                            $job = $row['job'];
                            $prCollapseId = 'pr-details-' . $pIndex;
                        @endphp
                        <tr wire:key="pr-job-{{ $job->id }}" class="pr-summary-row">
                            <td class="text-center">
                                @if(count($row['details']) > 0)
                                    <button type="button" class="btn btn-sm btn-link text-decoration-none p-0 pr-toggle-btn"
                                            data-bs-toggle="collapse" data-bs-target="#{{ $prCollapseId }}"
                                            aria-expanded="false" aria-controls="{{ $prCollapseId }}"
                                            title="Show details">
                                        <i class="bi bi-chevron-right pr-toggle-icon text-muted"></i>
                                    </button>
                                @endif
                            </td>
                            <td class="ps-2">
                                <a href="/jobs/{{ $job->id }}" class="fw-bold text-pr text-decoration-none">
                                    {{ $job->row_no }}
                                </a>
                            </td>
                            <td class="small text-muted">
                                {{ \Carbon\Carbon::parse($job->posted_at)->format('d M Y') }}
                            </td>
                            <td class="small">
                                @if($job->shipment_mode)
                                    <span class="badge bg-light text-dark border me-1">{{ $job->shipment_mode }}</span>
                                @endif
                                @if($job->shipment_type)
                                    <span class="badge bg-light text-dark border">{{ $job->shipment_type }}</span>
                                @endif
                            </td>
                            <td class="text-end tabular-nums small text-muted">
                                {{ $row['provisional_cost'] > 0 ? number_format($row['provisional_cost'], 2) : '—' }}
                            </td>
                            <td class="text-end tabular-nums small text-danger">
                                {{ $row['actual_cost'] > 0 ? number_format($row['actual_cost'], 2) : '—' }}
                            </td>
                            <td class="text-end tabular-nums small text-muted">
                                {{ $row['provisional_sales'] > 0 ? number_format($row['provisional_sales'], 2) : '—' }}
                            </td>
                            <td class="text-end tabular-nums small text-pr fw-medium">
                                {{ $row['actual_sales'] > 0 ? number_format($row['actual_sales'], 2) : '—' }}
                            </td>
                            <td class="text-end tabular-nums fw-bold {{ $row['profit_loss'] >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ number_format($row['profit_loss'], 2) }}
                            </td>
                            <td style="min-width:110px;">
                                @php
                                    $baseCost = $row['provisional_cost'] > 0 ? $row['provisional_cost'] : $row['actual_cost'];
                                    $costPct  = $baseCost > 0 ? min(100, ($row['actual_cost'] / $baseCost) * 100) : 0;
                                    $overBudget = $row['actual_cost'] > $row['provisional_cost'] && $row['provisional_cost'] > 0;
                                @endphp
                                <div class="progress pr-variance-bar" style="height:6px;">
                                    <div class="progress-bar {{ $overBudget ? 'bg-danger' : 'bg-pr' }}" style="width: {{ $costPct }}%"></div>
                                </div>
                                <div class="x-small text-muted mt-1">
                                    @if($row['provisional_cost'] > 0)
                                        <span class="{{ $overBudget ? 'text-danger fw-bold' : 'text-success' }}">
                                            {{ $overBudget ? '▲' : '▼' }} {{ number_format(abs((($row['actual_cost'] - $row['provisional_cost']) / $row['provisional_cost']) * 100), 1) }}%
                                        </span>
                                    @else
                                        <span class="text-muted">n/a</span>
                                    @endif
                                </div>
                            </td>
                            <td class="text-end pe-4">
                                <span class="badge rounded-pill px-2 py-1 {{ $row['margin'] >= 20 ? 'bg-success-subtle text-success' : ($row['margin'] >= 10 ? 'bg-warning-subtle text-warning' : ($row['margin'] > 0 ? 'bg-secondary-subtle text-secondary' : 'bg-danger-subtle text-danger')) }}">
                                    {{ number_format($row['margin'], 1) }}%
                                </span>
                            </td>
                        </tr>
                        @if(count($row['details']) > 0)
                            <tr class="pr-collapse-details-row">
                                <td colspan="11" class="p-0 border-0">
                                    <div class="collapse" id="{{ $prCollapseId }}">
                                        <div class="bg-light-subtle px-4 py-3">
                                            <table class="table table-sm mb-0 bg-transparent">
                                                <thead>
                                                    <tr class="small text-muted text-uppercase">
                                                        <th class="border-0">Type</th>
                                                        <th class="border-0">Row No</th>
                                                        <th class="border-0">Date</th>
                                                        <th class="text-end border-0">Amount</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($row['details'] as $dIndex => $detail)
                                                        @php
                                                            $typeClass = match($detail['type']) {
                                                                'Provisional Sale' => 'bg-light text-muted border',
                                                                'Actual Sale' => 'bg-primary-subtle text-primary border-primary-subtle',
                                                                'Provisional Cost' => 'bg-light text-muted border',
                                                                'Actual Cost' => 'bg-danger-subtle text-danger border-danger-subtle',
                                                                default => 'bg-light text-muted border',
                                                            };
                                                        @endphp
                                                        <tr wire:key="pr-{{ $job->id }}-{{ $dIndex }}">
                                                            <td class="small">
                                                                <span class="badge rounded-pill px-2 py-1 border {{ $typeClass }}">{{ $detail['type'] }}</span>
                                                            </td>
                                                            <td class="small">{{ $detail['row_no'] ?? '—' }}</td>
                                                            <td class="small text-muted">{{ $detail['date'] ? \Carbon\Carbon::parse($detail['date'])->format('d M Y') : '—' }}</td>
                                                            <td class="text-end tabular-nums small">{{ number_format($detail['amount'], 2) }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="11" class="text-center py-5 text-muted">
                                <div class="bg-light rounded-circle p-4 d-inline-block mb-3">
                                    <i class="bi bi-bar-chart h2 text-muted"></i>
                                </div>
                                <div class="small">No jobs found for the selected period.</div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                    @if(count($rows) > 0)
                    <tfoot class="bg-light border-top-2">
                    <tr class="fw-bold">
                        <td colspan="4" class="ps-2 py-3">Totals</td>
                        <td class="text-end tabular-nums text-muted">{{ number_format($totals['provisional_cost'], 2) }}</td>
                        <td class="text-end tabular-nums text-danger">{{ number_format($totals['actual_cost'], 2) }}</td>
                        <td class="text-end tabular-nums text-muted">{{ number_format($totals['provisional_sales'], 2) }}</td>
                        <td class="text-end tabular-nums text-pr">{{ number_format($totals['actual_sales'], 2) }}</td>
                        <td class="text-end tabular-nums {{ $totals['profit_loss'] >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($totals['profit_loss'], 2) }}</td>
                        <td></td>
                        <td class="text-end pe-4 {{ $totals['margin'] >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($totals['margin'], 1) }}%</td>
                    </tr>
                    </tfoot>
                    @endif
                </table>

                <style>
                    .pr-toggle-btn[aria-expanded="true"] .pr-toggle-icon {
                        transform: rotate(90deg);
                    }
                    .pr-toggle-icon {
                        display: inline-block;
                        transition: transform 0.2s ease;
                    }
                    .pr-summary-row:hover {
                        background-color: var(--pr-light, #f0f9ff);
                    }
                </style>
                @endif

            </div>
        </div>

        {{-- Bank-statement style layout: used for Print and PDF export only —
             flattened (no collapse), since collapsed content doesn't print. --}}
        <div id="pr-print" class="stmt-print d-none d-print-block"
             data-pdf-filename="ProvisionalReport-{{ $startDate ?? '' }}-{{ $endDate ?? '' }}.pdf">

            <table class="stmt-meta">
                <tr>
                    <td>
                        <div class="stmt-company">{{ optional(authUserCompany())->name ?? config('app.name') }}</div>
                    </td>
                    <td class="text-end">
                        <div class="stmt-title">PROVISIONAL REPORT{{ $viewMode === 'activity' ? ' (BY ACTIVITY)' : ' (BY JOB)' }}</div>
                        <div class="stmt-sub">Period: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} — {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</div>
                        <div class="stmt-sub">Generated: {{ now()->format('d M Y H:i') }} &nbsp;|&nbsp; Currency: SAR</div>
                    </td>
                </tr>
            </table>

            @if($viewMode === 'activity')
                <table class="stmt-table">
                    <thead>
                    <tr>
                        <th>Activity</th>
                        <th class="text-end">Jobs</th>
                        <th class="text-end">Provisional Cost</th>
                        <th class="text-end">Actual Cost</th>
                        <th class="text-end">Provisional Sales</th>
                        <th class="text-end">Actual Sales</th>
                        <th class="text-end">Profit / Loss</th>
                        <th class="text-end">Margin</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td>{{ $row['activity'] }}</td>
                            <td class="text-end">{{ $row['job_count'] }}</td>
                            <td class="text-end">{{ number_format($row['provisional_cost'], 2) }}</td>
                            <td class="text-end">{{ number_format($row['actual_cost'], 2) }}</td>
                            <td class="text-end">{{ number_format($row['provisional_sales'], 2) }}</td>
                            <td class="text-end">{{ number_format($row['actual_sales'], 2) }}</td>
                            <td class="text-end">{{ number_format($row['profit_loss'], 2) }}</td>
                            <td class="text-end">{{ number_format($row['margin'], 1) }}%</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center">No data found for the selected period.</td></tr>
                    @endforelse
                    </tbody>
                    <tfoot>
                    <tr class="stmt-strong">
                        <td>Totals</td>
                        <td class="text-end">{{ collect($rows)->sum('job_count') }}</td>
                        <td class="text-end">{{ number_format($totals['provisional_cost'], 2) }}</td>
                        <td class="text-end">{{ number_format($totals['actual_cost'], 2) }}</td>
                        <td class="text-end">{{ number_format($totals['provisional_sales'], 2) }}</td>
                        <td class="text-end">{{ number_format($totals['actual_sales'], 2) }}</td>
                        <td class="text-end">{{ number_format($totals['profit_loss'], 2) }}</td>
                        <td class="text-end">{{ number_format($totals['margin'], 1) }}%</td>
                    </tr>
                    </tfoot>
                </table>
            @else
                <table class="stmt-table">
                    <thead>
                    <tr>
                        <th>Job No</th>
                        <th>Date</th>
                        <th>Mode / Type</th>
                        <th class="text-end">Provisional Cost</th>
                        <th class="text-end">Actual Cost</th>
                        <th class="text-end">Provisional Sales</th>
                        <th class="text-end">Actual Sales</th>
                        <th class="text-end">Profit / Loss</th>
                        <th class="text-end">Margin</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($rows as $row)
                        @php $job = $row['job']; @endphp
                        <tr>
                            <td>{{ $job->row_no }}</td>
                            <td>{{ \Carbon\Carbon::parse($job->posted_at)->format('d M Y') }}</td>
                            <td>{{ $job->shipment_mode }} {{ $job->shipment_type }}</td>
                            <td class="text-end">{{ number_format($row['provisional_cost'], 2) }}</td>
                            <td class="text-end">{{ number_format($row['actual_cost'], 2) }}</td>
                            <td class="text-end">{{ number_format($row['provisional_sales'], 2) }}</td>
                            <td class="text-end">{{ number_format($row['actual_sales'], 2) }}</td>
                            <td class="text-end stmt-strong">{{ number_format($row['profit_loss'], 2) }}</td>
                            <td class="text-end">{{ number_format($row['margin'], 1) }}%</td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center">No jobs found for the selected period.</td></tr>
                    @endforelse
                    </tbody>
                    <tfoot>
                    <tr class="stmt-strong">
                        <td colspan="3">Totals</td>
                        <td class="text-end">{{ number_format($totals['provisional_cost'], 2) }}</td>
                        <td class="text-end">{{ number_format($totals['actual_cost'], 2) }}</td>
                        <td class="text-end">{{ number_format($totals['provisional_sales'], 2) }}</td>
                        <td class="text-end">{{ number_format($totals['actual_sales'], 2) }}</td>
                        <td class="text-end">{{ number_format($totals['profit_loss'], 2) }}</td>
                        <td class="text-end">{{ number_format($totals['margin'], 1) }}%</td>
                    </tr>
                    </tfoot>
                </table>
            @endif

            <div class="stmt-signatures">
                <table class="stmt-meta">
                    <tr>
                        <td>Prepared By: _________________</td>
                        <td>Verified By: _________________</td>
                        <td>Approved By: _________________</td>
                    </tr>
                </table>
            </div>
        </div>

        @include('includes.report-print-css', ['orientation' => 'landscape'])

    </div>

    @script
    <script>
        (function () {
            function syncHidden(hiddenId, dateStr) {
                var hidden = document.getElementById(hiddenId);
                if (hidden) {
                    hidden.value = dateStr;
                    hidden.dispatchEvent(new Event('input', { bubbles: true }));
                }
            }

            function initFlatpickr() {
                var startEl = document.getElementById('pr-start-date');
                var endEl   = document.getElementById('pr-end-date');

                if (startEl && startEl._flatpickr) { startEl._flatpickr.destroy(); }
                if (endEl   && endEl._flatpickr)   { endEl._flatpickr.destroy(); }

                if (startEl) {
                    flatpickr(startEl, {
                        dateFormat:    'Y-m-d',
                        altInput:      true,
                        altFormat:     'd-m-Y',
                        allowInput:    true,
                        disableMobile: true,
                        defaultDate:   startEl.value || null,
                        onChange: function (selectedDates, dateStr) {
                            syncHidden('pr-start-date-hidden', dateStr);
                        },
                    });
                }

                if (endEl) {
                    flatpickr(endEl, {
                        dateFormat:    'Y-m-d',
                        altInput:      true,
                        altFormat:     'd-m-Y',
                        allowInput:    true,
                        disableMobile: true,
                        defaultDate:   endEl.value || null,
                        onChange: function (selectedDates, dateStr) {
                            syncHidden('pr-end-date-hidden', dateStr);
                        },
                    });
                }
            }

            initFlatpickr();

            Livewire.hook('commit', function (ref) {
                ref.succeed(function () {
                    requestAnimationFrame(initFlatpickr);
                });
            });
        })();
    </script>
    @endscript

    <style>
        :root {
            --pr-primary: #0ea5e9;
            --pr-dark:    #0369a1;
            --pr-light:   #f0f9ff;
        }

        .btn-pr { background-color: var(--pr-primary); border-color: var(--pr-primary); color: #fff; }
        .btn-pr:hover { background-color: var(--pr-dark); border-color: var(--pr-dark); color: #fff; }
        .text-pr { color: var(--pr-primary) !important; }
        .bg-pr { background-color: var(--pr-primary) !important; }
        .bg-pr-subtle { background-color: #e0f2fe !important; }
        .border-pr-subtle { border-color: #bae6fd !important; }
        .pr-variance-bar { background-color: #e9ecef; border-radius: 3px; overflow: hidden; }

        .btn-outline-pr { color: var(--pr-primary); border-color: var(--pr-primary); }
        .btn-outline-pr:hover, .btn-check:checked + .btn-outline-pr {
            background-color: var(--pr-primary); border-color: var(--pr-primary); color: #fff;
        }

        .ls-1 { letter-spacing: 0.05em; }
        .x-small { font-size: 0.7rem; }
        .tabular-nums { font-variant-numeric: tabular-nums; }

        .card { border-radius: 1rem; }
        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 0.25rem rgba(14, 165, 233, 0.1);
            border-color: var(--pr-primary);
        }

        thead th { vertical-align: bottom; }

        @media print {
            body { background: white !important; }
            .d-print-none { display: none !important; }
            .card { box-shadow: none !important; border: 1px solid #eee !important; }
        }
    </style>
</div>
