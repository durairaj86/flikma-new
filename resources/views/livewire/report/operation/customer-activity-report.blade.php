@section('js', 'customer_activity_report')
@section('page-title', 'Customer Activity Report')

<div class="provisional-wrapper min-vh-100 bg-light py-4">
    <div class="container-fluid px-lg-5">

        {{-- Page Header --}}
        <div class="row align-items-center mb-4 d-print-none">
            <div class="col-md-6">
                <h1 class="h3 fw-bold text-slate-900 mb-1">Customer Activity Report</h1>
                <p class="text-muted small mb-0">Job activity, revenue, and profitability grouped by customer</p>
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
                            <li><a class="dropdown-item py-2" href="#" onclick="reportExportPdf(event, 'car-print', {orientation: 'landscape'})"><i class="bi bi-file-pdf text-danger me-2"></i>PDF Document</a></li>
                            <li><a class="dropdown-item py-2" href="#" wire:click.prevent="exportExcel"><i class="bi bi-file-excel text-success me-2"></i>Excel Sheet</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="card border-0 shadow-sm mb-4 d-print-none">
            <div class="card-body p-4">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-2 col-md-4">
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">From Date</label>
                        <input type="hidden" id="car-start-date-hidden" wire:model="startDate" value="{{ $startDate }}" />
                        <input type="text" id="car-start-date"
                               class="form-control bg-light border-0 py-2"
                               placeholder="dd-mm-yyyy"
                               value="{{ $startDate }}" />
                    </div>
                    <div class="col-lg-2 col-md-4">
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">To Date</label>
                        <input type="hidden" id="car-end-date-hidden" wire:model="endDate" value="{{ $endDate }}" />
                        <input type="text" id="car-end-date"
                               class="form-control bg-light border-0 py-2"
                               placeholder="dd-mm-yyyy"
                               value="{{ $endDate }}" />
                    </div>
                    <div class="col-lg-4 col-md-4">
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">Customer</label>
                        <select class="form-select bg-light border-0 py-2 no-ts" wire:model="customerId">
                            <option value="">All Customers</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer['id'] }}" @selected($customerId == $customer['id'])>
                                    {{ $customer['row_no'] }} — {{ $customer['name_en'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-4 col-md-4">
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
        @if($totals['total_customers'] > 0)
        <div class="row g-3 mb-4 d-print-none">
            <div class="col-lg col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3 text-center">
                        <div class="small text-muted fw-bold text-uppercase mb-1 ls-1">Active Customers</div>
                        <div class="h5 fw-bold text-secondary mb-0 tabular-nums">{{ $totals['total_customers'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3 text-center">
                        <div class="small text-muted fw-bold text-uppercase mb-1 ls-1">Total Jobs</div>
                        <div class="h5 fw-bold text-primary mb-0 tabular-nums">{{ $totals['total_jobs'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3 text-center">
                        <div class="small text-muted fw-bold text-uppercase mb-1 ls-1">Total Revenue</div>
                        <div class="h5 fw-bold text-success mb-0 tabular-nums">{{ number_format($totals['total_revenue'], 2) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3 text-center">
                        <div class="small text-muted fw-bold text-uppercase mb-1 ls-1">Total Cost</div>
                        <div class="h5 fw-bold text-danger mb-0 tabular-nums">{{ number_format($totals['total_cost'], 2) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3 text-center">
                        <div class="small text-muted fw-bold text-uppercase mb-1 ls-1">Net Profit</div>
                        <div class="h5 fw-bold mb-0 tabular-nums {{ $totals['total_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ number_format($totals['total_profit'], 2) }}
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
                    <i class="bi bi-people me-2 text-pr"></i>
                    Customer Activity Breakdown
                </h6>
                <span class="badge bg-pr-subtle text-pr border border-pr-subtle px-3 py-2">
                    {{ $totals['total_customers'] }} {{ Str::plural('Customer', $totals['total_customers']) }}
                </span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                    <tr class="bg-light text-muted small text-uppercase fw-bold ls-1">
                        <th class="ps-4 border-0">Customer</th>
                        <th class="text-center border-0">Jobs</th>
                        <th class="text-center border-0">Active</th>
                        <th class="text-center border-0">Completed</th>
                        <th class="text-end border-0">Revenue</th>
                        <th class="text-end border-0">Cost</th>
                        <th class="text-end border-0">Profit / Loss</th>
                        <th class="text-end pe-4 border-0">Margin</th>
                    </tr>
                    </thead>
                    <tbody class="border-top-0">
                    @forelse($rows as $row)
                        <tr wire:key="car-{{ $row['customer']->id }}">
                            <td class="ps-4">
                                <span class="fw-bold text-dark">{{ $row['customer']->name }}</span>
                                <div class="text-muted" style="font-size:0.7rem;">{{ $row['customer']->name_ar }}</div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border">{{ $row['job_count'] }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ $row['active'] }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-success-subtle text-success border border-success-subtle">{{ $row['completed'] }}</span>
                            </td>
                            <td class="text-end tabular-nums text-success fw-medium">
                                {{ $row['revenue'] > 0 ? number_format($row['revenue'], 2) : '—' }}
                            </td>
                            <td class="text-end tabular-nums text-danger">
                                {{ $row['cost'] > 0 ? number_format($row['cost'], 2) : '—' }}
                            </td>
                            <td class="text-end tabular-nums fw-bold {{ $row['profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ number_format($row['profit'], 2) }}
                            </td>
                            <td class="text-end pe-4">
                                <span class="badge rounded-pill px-2 py-1 {{ $row['margin'] >= 20 ? 'bg-success-subtle text-success' : ($row['margin'] >= 10 ? 'bg-warning-subtle text-warning' : ($row['margin'] > 0 ? 'bg-secondary-subtle text-secondary' : 'bg-danger-subtle text-danger')) }}">
                                    {{ number_format($row['margin'], 1) }}%
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <div class="bg-light rounded-circle p-4 d-inline-block mb-3">
                                    <i class="bi bi-people h2 text-muted"></i>
                                </div>
                                <div class="small">No customer activity found for the selected period.</div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                    @if(count($rows) > 0)
                    <tfoot class="bg-light border-top-2">
                    <tr class="fw-bold">
                        <td class="ps-4 py-3">{{ $totals['total_customers'] }} Customers</td>
                        <td class="text-center text-muted">{{ $totals['total_jobs'] }}</td>
                        <td></td>
                        <td></td>
                        <td class="text-end tabular-nums text-success">{{ number_format($totals['total_revenue'], 2) }}</td>
                        <td class="text-end tabular-nums text-danger">{{ number_format($totals['total_cost'], 2) }}</td>
                        <td class="text-end tabular-nums {{ $totals['total_profit'] >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($totals['total_profit'], 2) }}</td>
                        <td class="text-end pe-4"></td>
                    </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>

        {{-- Bank-statement style layout: used for Print and PDF export only --}}
        <div id="car-print" class="stmt-print d-none d-print-block"
             data-pdf-filename="CustomerActivityReport-{{ $startDate ?? '' }}-{{ $endDate ?? '' }}.pdf">

            <table class="stmt-meta">
                <tr>
                    <td>
                        <div class="stmt-company">{{ optional(authUserCompany())->name ?? config('app.name') }}</div>
                    </td>
                    <td class="text-end">
                        <div class="stmt-title">CUSTOMER ACTIVITY REPORT</div>
                        <div class="stmt-sub">Period: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} — {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</div>
                        <div class="stmt-sub">Generated: {{ now()->format('d M Y H:i') }} &nbsp;|&nbsp; Currency: SAR</div>
                    </td>
                </tr>
            </table>

            <table class="stmt-table">
                <thead>
                <tr>
                    <th>Customer</th>
                    <th class="text-end">Jobs</th>
                    <th class="text-end">Active</th>
                    <th class="text-end">Completed</th>
                    <th class="text-end">Draft</th>
                    <th class="text-end">Revenue</th>
                    <th class="text-end">Cost</th>
                    <th class="text-end">Profit / Loss</th>
                    <th class="text-end">Margin</th>
                </tr>
                </thead>
                <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td>{{ $row['customer']->name_en }} ({{ $row['customer']->row_no }})</td>
                        <td class="text-end">{{ $row['job_count'] }}</td>
                        <td class="text-end">{{ $row['active'] }}</td>
                        <td class="text-end">{{ $row['completed'] }}</td>
                        <td class="text-end">{{ $row['draft'] }}</td>
                        <td class="text-end">{{ number_format($row['revenue'], 2) }}</td>
                        <td class="text-end">{{ number_format($row['cost'], 2) }}</td>
                        <td class="text-end">{{ number_format($row['profit'], 2) }}</td>
                        <td class="text-end">{{ number_format($row['margin'], 1) }}%</td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center">No customer activity found for the selected period.</td></tr>
                @endforelse
                </tbody>
                <tfoot>
                <tr class="stmt-strong">
                    <td>{{ $totals['total_customers'] }} Customers</td>
                    <td class="text-end">{{ $totals['total_jobs'] }}</td>
                    <td></td><td></td><td></td>
                    <td class="text-end">{{ number_format($totals['total_revenue'], 2) }}</td>
                    <td class="text-end">{{ number_format($totals['total_cost'], 2) }}</td>
                    <td class="text-end">{{ number_format($totals['total_profit'], 2) }}</td>
                    <td class="text-end"></td>
                </tr>
                </tfoot>
            </table>

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
                var startEl = document.getElementById('car-start-date');
                var endEl   = document.getElementById('car-end-date');

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
                            syncHidden('car-start-date-hidden', dateStr);
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
                            syncHidden('car-end-date-hidden', dateStr);
                        },
                    });
                }
            }

            initFlatpickr();

            // ref.succeed()'s callback fires before the DOM morph for this
            // commit is actually applied — re-running initFlatpickr() there
            // re-initializes the still-old (correctly formatted) input,
            // which the morph then immediately overwrites back to the
            // server-rendered raw value, visibly flipping the date field's
            // format after every Generate click. requestAnimationFrame
            // defers until the next frame, after that synchronous morph
            // pass has actually finished (see general-ledger.blade.php /
            // customer-statement.blade.php for the same root cause).
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
            --car-primary: #0ea5e9;
            --car-dark:    #0369a1;
            --car-light:   #f0f9ff;
        }

        .btn-pr { background-color: var(--car-primary); border-color: var(--car-primary); color: #fff; }
        .btn-pr:hover { background-color: var(--car-dark); border-color: var(--car-dark); color: #fff; }
        .text-pr { color: var(--car-primary) !important; }
        .bg-pr-subtle { background-color: #e0f2fe !important; }
        .border-pr-subtle { border-color: #bae6fd !important; }

        .btn-outline-pr { color: var(--car-primary); border-color: var(--car-primary); }
        .btn-outline-pr:hover, .btn-check:checked + .btn-outline-pr {
            background-color: var(--car-primary); border-color: var(--car-primary); color: #fff;
        }

        .bg-primary-subtle { background-color: #e0f2fe !important; }
        .bg-success-subtle { background-color: #dcfce7 !important; }
        .bg-warning-subtle { background-color: #fef3c7 !important; }
        .bg-secondary-subtle { background-color: #f1f5f9 !important; }
        .bg-danger-subtle { background-color: #fee2e2 !important; }

        .ls-1 { letter-spacing: 0.05em; }
        .x-small { font-size: 0.7rem; }
        .tabular-nums { font-variant-numeric: tabular-nums; }

        .card { border-radius: 1rem; }
        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 0.25rem rgba(14, 165, 233, 0.1);
            border-color: var(--car-primary);
        }

        thead th { vertical-align: bottom; }

        @media print {
            body { background: white !important; }
            .d-print-none { display: none !important; }
            .card { box-shadow: none !important; border: 1px solid #eee !important; }
        }
    </style>
</div>
