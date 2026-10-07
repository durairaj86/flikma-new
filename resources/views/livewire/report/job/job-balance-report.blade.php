@section('js', 'job_balance_report')
@section('page-title', __('Job Balance Report'))
@section('hide-topbar', true)
@section('page-subtitle', __('Income vs expense per job with profit / loss and margin'))

<div class="provisional-wrapper min-vh-100 bg-light pt-1 pb-4">
    <div class="container-fluid px-3">

        <style>
            .rpt-title { display: none; }
            body:not(.has-top-header) .rpt-title { display: block; }
        </style>
        <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap pt-2 pb-3 d-print-none">
            <div class="rpt-title">
                <h4 class="fw-bold text-dark mb-0">@yield('page-title')</h4>
                @hasSection('page-subtitle')<div class="text-muted small mt-1">@yield('page-subtitle')</div>@endif
            </div>
            <div class="btn-group shadow-sm ms-auto position-relative">
                <button class="btn btn-white border border-end-0" onclick="window.print()">
                    <i class="bi bi-printer me-2"></i>{{ __('Print') }}
                </button>
                <div class="btn-group position-relative" x-data="{ open: false, pos: '', toggle() { this.open = !this.open; if (this.open) { const r = this.$refs.btn.getBoundingClientRect(); this.pos = 'position:fixed;left:auto;bottom:auto;right:' + (window.innerWidth - r.right) + 'px;top:' + (r.bottom + 4) + 'px;'; } } }" @click.outside="open = false" @keydown.escape.window="open = false" @scroll.window="open = false" @resize.window="open = false">
                    <button type="button" class="btn btn-white border dropdown-toggle" x-ref="btn" @click="toggle()" :aria-expanded="open">
                        <i class="bi bi-download me-2"></i>{{ __('Export') }}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow" :class="{ show: open }" x-cloak :style="pos" @click="open = false">
                        <li><a class="dropdown-item py-2" href="#" onclick="reportExportPdf(event, 'jbr-print', {orientation: 'landscape'})"><i class="bi bi-file-pdf text-danger me-2"></i>{{ __('PDF Document') }}</a></li>
                        <li><a class="dropdown-item py-2" href="#" wire:click.prevent="exportExcel"><i class="bi bi-file-excel text-success me-2"></i>{{ __('Excel Sheet') }}</a></li>
                    </ul>
                </div>
            </div>
        </div>

                {{-- Filters --}}
        <div class="card border-0 shadow-sm mb-4 d-print-none">
            <div class="card-body p-4">
                <div class="row g-3 align-items-end">
<div class="col-12 col-sm-6 col-lg">
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">{{ __('From Date') }}</label>
                        <input type="hidden" id="jbr-start-date-hidden" wire:model="startDate" value="{{ $startDate }}" />
                        <input type="text" id="jbr-start-date"
                               class="form-control bg-light border-0 py-2"
                               placeholder="dd-mm-yyyy"
                               value="{{ $startDate }}" />
                    </div>
<div class="col-12 col-sm-6 col-lg">
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">{{ __('To Date') }}</label>
                        <input type="hidden" id="jbr-end-date-hidden" wire:model="endDate" value="{{ $endDate }}" />
                        <input type="text" id="jbr-end-date"
                               class="form-control bg-light border-0 py-2"
                               placeholder="dd-mm-yyyy"
                               value="{{ $endDate }}" />
                    </div>
<div class="col-12 col-sm-6 col-lg">
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">{{ __('Status') }}</label>
                        <select class="form-select bg-light border-0 py-2" wire:model="status">
                            <option value="">{{ __('All Statuses') }}</option>
                            <option value="draft">{{ __('Draft') }}</option>
                            <option value="active">{{ __('Active') }}</option>
                            <option value="completed">{{ __('Completed') }}</option>
                            <option value="cancelled">{{ __('Cancelled') }}</option>
                        </select>
                    </div>
<div class="col-12 col-sm-6 col-lg">
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">{{ __('Search') }}</label>
                        <input type="text" class="form-control bg-light border-0 py-2"
                               wire:model.debounce.400ms="search"
                               placeholder="{{ __('Job no, AWB, HBL...') }}" />
                    </div>
<div class="col-12 col-lg-auto">
    <div class="d-flex flex-wrap gap-2 justify-content-lg-end align-items-center">
        <button type="button" class="btn btn-pr fw-bold py-2 shadow-sm"
                                            wire:click="applyFilter" wire:loading.attr="disabled">
                                        <i class="bi bi-filter-left me-2"></i>
                                        <span wire:loading.remove>{{ __('Generate') }}</span>
                                        <span wire:loading><span class="spinner-border spinner-border-sm me-1"></span>{{ __('Loading...') }}</span>
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
        @if($summary['total_jobs'] > 0)
        <div class="row g-3 mb-4 d-print-none">
            <div class="col-lg col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3 text-center">
                        <div class="small text-muted fw-bold text-uppercase mb-1 ls-1">{{ __('Jobs') }}</div>
                        <div class="h5 fw-bold text-secondary mb-0 tabular-nums">{{ $summary['total_jobs'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3 text-center">
                        <div class="small text-muted fw-bold text-uppercase mb-1 ls-1">{{ __('Income') }}</div>
                        <div class="h5 fw-bold text-pr mb-0 tabular-nums">{{ number_format($summary['total_income'], 2) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3 text-center">
                        <div class="small text-muted fw-bold text-uppercase mb-1 ls-1">{{ __('Expense') }}</div>
                        <div class="h5 fw-bold text-danger mb-0 tabular-nums">{{ number_format($summary['total_expense'], 2) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3 text-center">
                        <div class="small text-muted fw-bold text-uppercase mb-1 ls-1">{{ __('Profit / Loss') }}</div>
                        <div class="h5 fw-bold mb-0 tabular-nums {{ $summary['profit_loss'] >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ number_format($summary['profit_loss'], 2) }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3 text-center">
                        <div class="small text-muted fw-bold text-uppercase mb-1 ls-1">{{ __('Margin') }}</div>
                        <div class="h5 fw-bold mb-0 tabular-nums {{ $summary['margin'] >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ number_format($summary['margin'], 1) }}%
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Table --}}
        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center d-print-none">
                <h6 class="mb-0 fw-bold flex-grow-1">
                    <i class="bi bi-balance-scale me-2 text-pr"></i>
                    {{ __('Job Balance') }}
                </h6>
                <span class="badge bg-pr-subtle text-pr border border-pr-subtle px-3 py-2 ms-auto flex-shrink-0">
                    {{ $summary['total_jobs'] }} {{ __(Str::plural('Job', $summary['total_jobs'])) }}
                </span>
            </div>
            <div>
                <livewire:report.job.job-balance-report-table/>
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
                var startEl = document.getElementById('jbr-start-date');
                var endEl   = document.getElementById('jbr-end-date');

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
                            syncHidden('jbr-start-date-hidden', dateStr);
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
                            syncHidden('jbr-end-date-hidden', dateStr);
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
            --jbr-primary: #0ea5e9;
            --jbr-dark:    #0369a1;
            --jbr-light:   #f0f9ff;
        }

        .btn-pr { background-color: var(--jbr-primary); border-color: var(--jbr-primary); color: #fff; }
        .btn-pr:hover { background-color: var(--jbr-dark); border-color: var(--jbr-dark); color: #fff; }
        .text-pr { color: var(--jbr-primary) !important; }
        .bg-pr-subtle { background-color: #e0f2fe !important; }
        .border-pr-subtle { border-color: #bae6fd !important; }

        .btn-outline-pr { color: var(--jbr-primary); border-color: var(--jbr-primary); }
        .btn-outline-pr:hover, .btn-check:checked + .btn-outline-pr {
            background-color: var(--jbr-primary); border-color: var(--jbr-primary); color: #fff;
        }

        .ls-1 { letter-spacing: 0.05em; }
        .x-small { font-size: 0.7rem; }
        .tabular-nums { font-variant-numeric: tabular-nums; }

        .card { border-radius: 1rem; }
        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 0.25rem rgba(14, 165, 233, 0.1);
            border-color: var(--jbr-primary);
        }

        thead th { vertical-align: bottom; }

        @media print {
            body { background: white !important; }
            .d-print-none { display: none !important; }
            .card { box-shadow: none !important; border: 1px solid #eee !important; }
        }
    </style>
</div>
