@section('js', 'tax_summary')
@section('page-title', 'Tax Summary')

<div class="provisional-wrapper min-vh-100 bg-light py-4">
    <div class="container-fluid px-lg-5">

        {{-- Page Header --}}
        <div class="row align-items-center mb-4 d-print-none">
            <div class="col-md-6">
                <h1 class="h3 fw-bold text-slate-900 mb-1">Tax Summary</h1>
                <p class="text-muted small mb-0">
                    VAT reconciliation for {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} &mdash; {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
                </p>
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
                            <li><a class="dropdown-item py-2" href="#"><i class="bi bi-file-pdf text-danger me-2"></i>PDF Document</a></li>
                            <li><a class="dropdown-item py-2" href="#"><i class="bi bi-file-excel text-success me-2"></i>Excel Sheet</a></li>
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
                        <input type="hidden" id="ts-start-date-hidden" wire:model="startDate" value="{{ $startDate }}" />
                        <input type="text" id="ts-start-date"
                               class="form-control bg-light border-0 py-2"
                               placeholder="dd-mm-yyyy"
                               value="{{ $startDate }}" />
                    </div>
                    <div class="col-lg-2 col-md-4">
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">To Date</label>
                        <input type="hidden" id="ts-end-date-hidden" wire:model="endDate" value="{{ $endDate }}" />
                        <input type="text" id="ts-end-date"
                               class="form-control bg-light border-0 py-2"
                               placeholder="dd-mm-yyyy"
                               value="{{ $endDate }}" />
                    </div>
                    <div class="col-lg-4 col-md-4">
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">Search</label>
                        <input type="text" class="form-control bg-light border-0 py-2"
                               wire:model.debounce.400ms="search"
                               placeholder="Reference no, description..." />
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
        <div class="row g-3 mb-4">
            <div class="col-lg-4 col-md-4">
                <div class="card border-0 shadow-sm h-100 border-start border-4 border-primary">
                    <div class="card-body p-3">
                        <div class="small text-muted fw-bold text-uppercase ls-1 mb-1">Output VAT (Sales)</div>
                        <div class="h4 fw-bold text-primary mb-0 tabular-nums">{{ number_format($summary['total_output_tax'], 2) }}</div>
                        <span class="small text-muted">VAT collected on sales</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-4">
                <div class="card border-0 shadow-sm h-100 border-start border-4 border-warning">
                    <div class="card-body p-3">
                        <div class="small text-muted fw-bold text-uppercase ls-1 mb-1">Input VAT (Purchases)</div>
                        <div class="h4 fw-bold text-warning mb-0 tabular-nums">{{ number_format($summary['total_input_tax'], 2) }}</div>
                        <span class="small text-muted">VAT reclaimable on purchases</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-4">
                <div class="card border-0 shadow-sm h-100 {{ $summary['is_payable'] ? 'bg-danger' : 'bg-success' }} text-white border-0">
                    <div class="card-body p-3">
                        <div class="small fw-bold text-uppercase ls-1 mb-1 opacity-75">
                            Net VAT {{ $summary['is_payable'] ? 'Payable' : 'Refundable' }}
                        </div>
                        <div class="h3 fw-bold mb-0 tabular-nums">{{ number_format(abs($summary['net_tax']), 2) }}</div>
                        <span class="small opacity-75">
                            Output SAR {{ number_format($summary['total_output_tax'], 2) }} &minus; Input SAR {{ number_format($summary['total_input_tax'], 2) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-receipt-cutoff me-2 text-pr"></i>
                    VAT Account Breakdown
                </h6>
                <span class="badge bg-pr-subtle text-pr border border-pr-subtle px-3 py-2">
                    {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} &mdash; {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
                </span>
            </div>
            <div class="p-3">
                <livewire:report.finance.tax-summary-table/>
            </div>
        </div>

        {{-- Disclaimer --}}
        <div class="mt-4 text-center text-muted">
            <p class="small">** This is a computer-generated VAT return summary for internal use. Verify against official ZATCA records. **</p>
        </div>

        {{-- Print footer --}}
        <div class="mt-4 p-3 bg-white border rounded shadow-sm d-none d-print-block">
            <div class="row text-center text-muted x-small">
                <div class="col-md-4">Prepared By: _________________</div>
                <div class="col-md-4">Verified By: _________________</div>
                <div class="col-md-4">Date: {{ now()->format('d M Y') }}</div>
            </div>
        </div>

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
                var startEl = document.getElementById('ts-start-date');
                var endEl   = document.getElementById('ts-end-date');

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
                            syncHidden('ts-start-date-hidden', dateStr);
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
                            syncHidden('ts-end-date-hidden', dateStr);
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
            --ts-primary: #0ea5e9;
            --ts-dark:    #0369a1;
            --ts-light:   #f0f9ff;
        }

        .btn-pr { background-color: var(--ts-primary); border-color: var(--ts-primary); color: #fff; }
        .btn-pr:hover { background-color: var(--ts-dark); border-color: var(--ts-dark); color: #fff; }
        .text-pr { color: var(--ts-primary) !important; }
        .bg-pr-subtle { background-color: #e0f2fe !important; }
        .border-pr-subtle { border-color: #bae6fd !important; }

        .btn-outline-pr { color: var(--ts-primary); border-color: var(--ts-primary); }
        .btn-outline-pr:hover, .btn-check:checked + .btn-outline-pr {
            background-color: var(--ts-primary); border-color: var(--ts-primary); color: #fff;
        }

        .ls-1 { letter-spacing: 0.05em; }
        .x-small { font-size: 0.7rem; }
        .tabular-nums { font-variant-numeric: tabular-nums; }

        .card { border-radius: 1rem; }
        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 0.25rem rgba(14, 165, 233, 0.1);
            border-color: var(--ts-primary);
        }

        thead th { vertical-align: bottom; }

        @media print {
            body { background: white !important; }
            .d-print-none { display: none !important; }
            .card { box-shadow: none !important; border: 1px solid #eee !important; }
            .bg-danger, .bg-success { -webkit-print-color-adjust: exact; }
        }
    </style>
</div>
