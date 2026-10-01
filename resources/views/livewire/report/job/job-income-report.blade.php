@section('js', 'job_income_report')
@section('page-title', __('Job Income Report'))
@section('page-subtitle', __('Income breakdown per job with invoice-level details'))

<div class="provisional-wrapper min-vh-100 bg-light py-4">
    <div class="container-fluid px-lg-5">

        {{-- Page Header --}}
                {{-- Filters --}}
        <div class="card border-0 shadow-sm mb-4 d-print-none">
            <div class="card-body p-4">
                <div class="row g-3 align-items-end">
<div class="col-lg-4 col-md-4 col-xl-2">
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">{{ __('From Date') }}</label>
                        <input type="hidden" id="jir-start-date-hidden" wire:model="startDate" value="{{ $startDate }}" />
                        <input type="text" id="jir-start-date"
                               class="form-control bg-light border-0 py-2"
                               placeholder="dd-mm-yyyy"
                               value="{{ $startDate }}" />
                    </div>
<div class="col-lg-4 col-md-4 col-xl-2">
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">{{ __('To Date') }}</label>
                        <input type="hidden" id="jir-end-date-hidden" wire:model="endDate" value="{{ $endDate }}" />
                        <input type="text" id="jir-end-date"
                               class="form-control bg-light border-0 py-2"
                               placeholder="dd-mm-yyyy"
                               value="{{ $endDate }}" />
                    </div>
<div class="col-lg-4 col-md-4 col-xl-2 col-xxl-3">
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">{{ __('Status') }}</label>
                        <select class="form-select bg-light border-0 py-2" wire:model="status">
                            <option value="">{{ __('All Statuses') }}</option>
                            <option value="draft">{{ __('Draft') }}</option>
                            <option value="active">{{ __('Active') }}</option>
                            <option value="completed">{{ __('Completed') }}</option>
                            <option value="cancelled">{{ __('Cancelled') }}</option>
                        </select>
                    </div>
<div class="col-lg-12 col-xl-6 col-xxl-5">
    <div class="d-flex flex-wrap gap-2 justify-content-end align-items-center">
        <button type="button" class="btn btn-pr fw-bold py-2 shadow-sm"
                                            wire:click="applyFilter" wire:loading.attr="disabled">
                                        <i class="bi bi-filter-left me-2"></i>
                                        <span wire:loading.remove>{{ __('Generate') }}</span>
                                        <span wire:loading><span class="spinner-border spinner-border-sm me-1"></span>{{ __('Loading...') }}</span>
                                    </button>
        <div class="btn-group shadow-sm">
                            <button class="btn btn-white border border-end-0" onclick="window.print()">
                                <i class="bi bi-printer me-2"></i>{{ __('Print') }}
                            </button>
                            <div class="btn-group">
                                <button class="btn btn-white border dropdown-toggle" data-bs-toggle="dropdown">
                                    <i class="bi bi-download me-2"></i>{{ __('Export') }}
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end border-0 shadow">
                                    <li><a class="dropdown-item py-2" href="#" onclick="reportExportPdf(event, 'jir-print')"><i class="bi bi-file-pdf text-danger me-2"></i>{{ __('PDF Document') }}</a></li>
                                    <li><a class="dropdown-item py-2" href="#" wire:click.prevent="exportExcel"><i class="bi bi-file-excel text-success me-2"></i>{{ __('Excel Sheet') }}</a></li>
                                </ul>
                            </div>
                        </div>
        <button type="button" class="btn btn-outline-secondary border-0 bg-light py-2 px-3"
                                            wire:click="resetFilter">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </button>
    </div>
</div>
<div class="col-lg-4 col-md-4 col-xl-2">
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">{{ __('Search') }}</label>
                        <input type="text" class="form-control bg-light border-0 py-2"
                               wire:model.debounce.400ms="search"
                               placeholder="{{ __('Job no, AWB, HBL...') }}" />
                    </div></div>
            </div>
        </div>

        {{-- Summary Cards --}}
        @if($summary['total_jobs'] > 0)
        <div class="row g-3 mb-4 d-print-none">
            <div class="col-lg col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3 text-center">
                        <div class="small text-muted fw-bold text-uppercase mb-1 ls-1">{{ __('Jobs with Income') }}</div>
                        <div class="h5 fw-bold text-secondary mb-0 tabular-nums">{{ $summary['total_jobs'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3 text-center">
                        <div class="small text-muted fw-bold text-uppercase mb-1 ls-1">{{ __('Gross Income') }}</div>
                        <div class="h5 fw-bold text-pr mb-0 tabular-nums">{{ number_format($summary['total_income'], 2) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3 text-center">
                        <div class="small text-muted fw-bold text-uppercase mb-1 ls-1">{{ __('Approved Income') }}</div>
                        <div class="h5 fw-bold text-success mb-0 tabular-nums">{{ number_format($summary['approved_income'], 2) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3 text-center">
                        <div class="small text-muted fw-bold text-uppercase mb-1 ls-1">{{ __('Draft Income') }}</div>
                        <div class="h5 fw-bold text-warning mb-0 tabular-nums">{{ number_format($summary['draft_income'], 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Table --}}
        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center d-print-none">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-cash-stack me-2 text-pr"></i>
                    {{ __('Income Breakdown') }}
                </h6>
                <span class="badge bg-pr-subtle text-pr border border-pr-subtle px-3 py-2">
                    {{ $summary['total_jobs'] }} {{ __(Str::plural('Job', $summary['total_jobs'])) }}
                </span>
            </div>
            <div>
                <livewire:report.job.job-income-report-table/>
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
                var startEl = document.getElementById('jir-start-date');
                var endEl   = document.getElementById('jir-end-date');

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
                            syncHidden('jir-start-date-hidden', dateStr);
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
                            syncHidden('jir-end-date-hidden', dateStr);
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
            --jir-primary: #0ea5e9;
            --jir-dark:    #0369a1;
            --jir-light:   #f0f9ff;
        }

        .btn-pr { background-color: var(--jir-primary); border-color: var(--jir-primary); color: #fff; }
        .btn-pr:hover { background-color: var(--jir-dark); border-color: var(--jir-dark); color: #fff; }
        .text-pr { color: var(--jir-primary) !important; }
        .bg-pr-subtle { background-color: #e0f2fe !important; }
        .border-pr-subtle { border-color: #bae6fd !important; }

        .btn-outline-pr { color: var(--jir-primary); border-color: var(--jir-primary); }
        .btn-outline-pr:hover, .btn-check:checked + .btn-outline-pr {
            background-color: var(--jir-primary); border-color: var(--jir-primary); color: #fff;
        }

        .ls-1 { letter-spacing: 0.05em; }
        .x-small { font-size: 0.7rem; }
        .tabular-nums { font-variant-numeric: tabular-nums; }

        .card { border-radius: 1rem; }
        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 0.25rem rgba(14, 165, 233, 0.1);
            border-color: var(--jir-primary);
        }

        thead th { vertical-align: bottom; }

        @media print {
            body { background: white !important; }
            .d-print-none { display: none !important; }
            .card { box-shadow: none !important; border: 1px solid #eee !important; }
        }
    </style>
</div>
