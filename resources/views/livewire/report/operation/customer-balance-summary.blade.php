@section('js', 'customer_balance_summary')
@section('page-title', 'Customer Balance Summary')

<div class="provisional-wrapper min-vh-100 bg-light py-4">
    <div class="container-fluid px-lg-5">

        {{-- Page Header --}}
        <div class="row align-items-center mb-4 d-print-none">
            <div class="col-md-6">
                <h1 class="h3 fw-bold text-slate-900 mb-1">Customer Balance Summary</h1>
                <p class="text-muted small mb-0">Opening balance, invoiced, received and closing balance for every customer</p>
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
                            <li><a class="dropdown-item py-2" href="#" onclick="reportExportPdf(event, 'cbs-print', {orientation: 'landscape'})"><i class="bi bi-file-pdf text-danger me-2"></i>PDF Document</a></li>
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
                    <div class="col-lg-2 col-md-4" wire:ignore>
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">From Date</label>
                        <input type="hidden" id="cbs-start-date-hidden" wire:model="startDate" value="{{ $startDate }}" />
                        <input type="text" id="cbs-start-date"
                               class="form-control bg-light border-0 py-2"
                               placeholder="dd-mm-yyyy"
                               value="{{ $startDate }}" />
                    </div>
                    <div class="col-lg-2 col-md-4" wire:ignore>
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">To Date</label>
                        <input type="hidden" id="cbs-end-date-hidden" wire:model="endDate" value="{{ $endDate }}" />
                        <input type="text" id="cbs-end-date"
                               class="form-control bg-light border-0 py-2"
                               placeholder="dd-mm-yyyy"
                               value="{{ $endDate }}" />
                    </div>
                    <div class="col-lg-4 col-md-4">
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">Search</label>
                        <input type="text" class="form-control bg-light border-0 py-2"
                               wire:model.debounce.400ms="search"
                               placeholder="Customer name..." />
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
        @if(count($rows) > 0)
        <div class="row g-3 mb-4 d-print-none">
            <div class="col-lg col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3 text-center">
                        <div class="small text-muted fw-bold text-uppercase mb-1 ls-1">Customers</div>
                        <div class="h5 fw-bold text-secondary mb-0 tabular-nums">{{ count($rows) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3 text-center">
                        <div class="small text-muted fw-bold text-uppercase mb-1 ls-1">Opening Balance</div>
                        <div class="h5 fw-bold text-secondary mb-0 tabular-nums">{{ number_format($totals['opening'], 2) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3 text-center">
                        <div class="small text-muted fw-bold text-uppercase mb-1 ls-1">Invoiced</div>
                        <div class="h5 fw-bold text-primary mb-0 tabular-nums">{{ number_format($totals['invoiced'], 2) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3 text-center">
                        <div class="small text-muted fw-bold text-uppercase mb-1 ls-1">Received</div>
                        <div class="h5 fw-bold text-success mb-0 tabular-nums">{{ number_format($totals['received'], 2) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3 text-center">
                        <div class="small text-muted fw-bold text-uppercase mb-1 ls-1">Closing Balance</div>
                        <div class="h5 fw-bold mb-0 tabular-nums {{ $totals['closing'] >= 0 ? 'text-dark' : 'text-danger' }}">
                            {{ number_format($totals['closing'], 2) }}
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
                    <i class="bi bi-wallet2 me-2 text-pr"></i>
                    Customer Balance Breakdown
                </h6>
                <span class="badge bg-pr-subtle text-pr border border-pr-subtle px-3 py-2">
                    {{ count($rows) }} {{ Str::plural('Customer', count($rows)) }}
                </span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                    <tr class="bg-light text-muted small text-uppercase fw-bold ls-1">
                        <th class="ps-4 border-0">Customer</th>
                        <th class="text-end border-0">Opening Balance</th>
                        <th class="text-end border-0">Invoiced</th>
                        <th class="text-end border-0">Received</th>
                        <th class="text-end pe-4 border-0">Closing Balance</th>
                    </tr>
                    </thead>
                    <tbody class="border-top-0">
                    @forelse($rows as $row)
                        <tr wire:key="cbs-{{ $row['customer']->id }}">
                            <td class="ps-4">
                                <span class="fw-bold text-dark">{{ $row['customer']->name_en }}</span>
                                <div class="text-muted" style="font-size:0.7rem;">{{ $row['customer']->row_no }}</div>
                            </td>
                            <td class="text-end tabular-nums">
                                {{ number_format($row['opening'], 2) }}
                            </td>
                            <td class="text-end tabular-nums text-primary">
                                {{ $row['invoiced'] > 0 ? number_format($row['invoiced'], 2) : '—' }}
                            </td>
                            <td class="text-end tabular-nums text-success">
                                {{ $row['received'] > 0 ? number_format($row['received'], 2) : '—' }}
                            </td>
                            <td class="text-end pe-4 tabular-nums fw-bold {{ $row['closing'] >= 0 ? 'text-dark' : 'text-danger' }}">
                                {{ number_format($row['closing'], 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <div class="bg-light rounded-circle p-4 d-inline-block mb-3">
                                    <i class="bi bi-wallet2 h2 text-muted"></i>
                                </div>
                                <div class="small">No customer balances found for the selected period.</div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                    @if(count($rows) > 0)
                    <tfoot class="bg-light border-top-2">
                    <tr class="fw-bold">
                        <td class="ps-4 py-3">{{ count($rows) }} Customers</td>
                        <td class="text-end tabular-nums">{{ number_format($totals['opening'], 2) }}</td>
                        <td class="text-end tabular-nums text-primary">{{ number_format($totals['invoiced'], 2) }}</td>
                        <td class="text-end tabular-nums text-success">{{ number_format($totals['received'], 2) }}</td>
                        <td class="text-end pe-4 tabular-nums {{ $totals['closing'] >= 0 ? 'text-dark' : 'text-danger' }}">{{ number_format($totals['closing'], 2) }}</td>
                    </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>

        {{-- Bank-statement style layout: used for Print and PDF export only --}}
        <div id="cbs-print" class="stmt-print d-none d-print-block"
             data-pdf-filename="CustomerBalanceSummary-{{ $startDate ?? '' }}-{{ $endDate ?? '' }}.pdf">

            <table class="stmt-meta">
                <tr>
                    <td>
                        <div class="stmt-company">{{ optional(authUserCompany())->name ?? config('app.name') }}</div>
                    </td>
                    <td class="text-end">
                        <div class="stmt-title">CUSTOMER BALANCE SUMMARY</div>
                        <div class="stmt-sub">Period: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} — {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</div>
                        <div class="stmt-sub">Generated: {{ now()->format('d M Y H:i') }} &nbsp;|&nbsp; Currency: SAR</div>
                    </td>
                </tr>
            </table>

            <table class="stmt-table">
                <thead>
                <tr>
                    <th>Customer</th>
                    <th class="text-end">Opening Balance</th>
                    <th class="text-end">Invoiced</th>
                    <th class="text-end">Received</th>
                    <th class="text-end">Closing Balance</th>
                </tr>
                </thead>
                <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td>{{ $row['customer']->name_en }} ({{ $row['customer']->row_no }})</td>
                        <td class="text-end">{{ number_format($row['opening'], 2) }}</td>
                        <td class="text-end">{{ number_format($row['invoiced'], 2) }}</td>
                        <td class="text-end">{{ number_format($row['received'], 2) }}</td>
                        <td class="text-end">{{ number_format($row['closing'], 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center">No customer balances found for the selected period.</td></tr>
                @endforelse
                </tbody>
                <tfoot>
                <tr class="stmt-strong">
                    <td>{{ count($rows) }} Customers</td>
                    <td class="text-end">{{ number_format($totals['opening'], 2) }}</td>
                    <td class="text-end">{{ number_format($totals['invoiced'], 2) }}</td>
                    <td class="text-end">{{ number_format($totals['received'], 2) }}</td>
                    <td class="text-end">{{ number_format($totals['closing'], 2) }}</td>
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
                var startEl = document.getElementById('cbs-start-date');
                var endEl   = document.getElementById('cbs-end-date');

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
                            syncHidden('cbs-start-date-hidden', dateStr);
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
                            syncHidden('cbs-end-date-hidden', dateStr);
                        },
                    });
                }
            }

            initFlatpickr();

            Livewire.hook('commit', function (ref) {
                ref.succeed(function () {
                    queueMicrotask(initFlatpickr);
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
