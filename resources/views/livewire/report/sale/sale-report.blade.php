@section('js', 'sale_report')
@section('page-title', __('Sales Report'))
@section('hide-topbar', true)
@section('page-subtitle', __('Daily, weekly, and monthly sales transaction summaries'))

<div class="provisional-wrapper min-vh-100 bg-light pb-4">
    <div class="container-fluid px-3">
        <style>
            .rpt-title { display: none; }
            body:not(.has-top-header) .rpt-title { display: block; }
        </style>
        <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap pt-2 pb-2 d-print-none">
            <div class="rpt-title">
                <h4 class="fw-bold text-dark mb-0">@yield('page-title')</h4>
                @hasSection('page-subtitle')<div class="text-muted small mt-1">@yield('page-subtitle')</div>@endif
            </div>
            <div class="btn-group shadow-sm ms-auto position-relative">
                <button class="btn btn-white border border-end-0 py-2" onclick="window.print()">
                    <i class="bi bi-printer me-2"></i>{{ __('Print') }}
                </button>
                <div class="btn-group position-relative" x-data="{ open: false, pos: '', toggle() { this.open = !this.open; if (this.open) { const r = this.$refs.btn.getBoundingClientRect(); this.pos = 'position:fixed;left:auto;bottom:auto;right:' + (window.innerWidth - r.right) + 'px;top:' + (r.bottom + 4) + 'px;'; } } }" @click.outside="open = false" @keydown.escape.window="open = false" @scroll.window="open = false" @resize.window="open = false">
                    <button type="button" class="btn btn-white border dropdown-toggle py-2" x-ref="btn" @click="toggle()" :aria-expanded="open">
                        <i class="bi bi-download me-2"></i>{{ __('Export') }}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow" :class="{ show: open }" x-cloak :style="pos" @click="open = false">
                        <li><a class="dropdown-item py-2" href="#" onclick="reportExportPdf(event, 'sr-print', {orientation: 'landscape'})"><i class="bi bi-file-pdf text-danger me-2"></i>{{ __('PDF Document') }}</a></li>
                        <li><a class="dropdown-item py-2" href="#" wire:click.prevent="exportExcel"><i class="bi bi-file-excel text-success me-2"></i>{{ __('Excel Sheet') }}</a></li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Page Header --}}
                {{-- Filters --}}
        <div class="card border-0 shadow-sm mb-4 d-print-none">
            <div class="card-body p-4">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-2 col-md-4" id="sr-customer-wrap" wire:ignore>
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">{{ __('Customer') }}</label>
                        <x-common.customers wire:model="customerId" id="sr-customer" name="sr-customer"
                                            :value="$customerId ? [(int) $customerId] : null" :new="false"
                                            all-label="{{ __('All Customers') }}"
                                            placeholder="{{ __('All Customers') }}"></x-common.customers>
                    </div>
                    <div class="col-lg-2 col-md-4">
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">{{ __('From Date') }}</label>
                        <input type="hidden" id="sr-start-date-hidden" wire:model="startDate" value="{{ $startDate }}" />
                        <input type="text" id="sr-start-date"
                               class="form-control bg-light border-0 py-2"
                               placeholder="dd-mm-yyyy"
                               value="{{ $startDate }}" />
                    </div>
                    <div class="col-lg-2 col-md-4">
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">{{ __('To Date') }}</label>
                        <input type="hidden" id="sr-end-date-hidden" wire:model="endDate" value="{{ $endDate }}" />
                        <input type="text" id="sr-end-date"
                               class="form-control bg-light border-0 py-2"
                               placeholder="dd-mm-yyyy"
                               value="{{ $endDate }}" />
                    </div>
                    <div class="col-lg-2 col-md-4" id="sr-status-wrap" wire:ignore>
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">{{ __('Status') }}</label>
                        <select class="tom-select" id="sr-status" wire:model="status" data-placeholder="{{ __('All Statuses') }}">
                            <option value="">{{ __('All Statuses') }}</option>
                            <option value="1">{{ __('Draft') }}</option>
                            <option value="3">{{ __('Approved') }}</option>
                            <option value="4">{{ __('Cancelled') }}</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-4">
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">{{ __('Search') }}</label>
                        <input type="text" class="form-control bg-light border-0 py-2"
                               wire:model.debounce.400ms="search"
                               placeholder="{{ __('Invoice no...') }}" />
                    </div>
                    <div class="col-lg-auto col-md-4 ms-lg-auto">
                        <div class="d-flex flex-wrap gap-2 justify-content-end align-items-center">
                            <button type="button" class="btn btn-pr fw-bold py-2 shadow-sm"
                                    wire:click="applyFilter" wire:loading.attr="disabled">
                                <i class="bi bi-filter-left me-2"></i>
                                <span wire:loading.remove>{{ __('Generate') }}</span>
                                <span wire:loading><span class="spinner-border spinner-border-sm me-1"></span>{{ __('Loading...') }}</span>
                            </button>
                            </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- Summary Cards --}}
        @if($summary['total_count'] > 0)
        <div class="row g-3 mb-4 d-print-none">
            <div class="col-lg col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3 text-center">
                        <div class="small text-muted fw-bold text-uppercase mb-1 ls-1">{{ __('Total Invoices') }}</div>
                        <div class="h5 fw-bold text-secondary mb-0 tabular-nums">{{ $summary['total_count'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3 text-center">
                        <div class="small text-muted fw-bold text-uppercase mb-1 ls-1">{{ __('Total Sales') }}</div>
                        <div class="h5 fw-bold text-pr mb-0 tabular-nums">{{ number_format($summary['total_grand'], 2) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3 text-center">
                        <div class="small text-muted fw-bold text-uppercase mb-1 ls-1">{{ __('Approved') }}</div>
                        <div class="h5 fw-bold text-success mb-0 tabular-nums">{{ number_format($summary['approved_grand'], 2) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3 text-center">
                        <div class="small text-muted fw-bold text-uppercase mb-1 ls-1">{{ __('Draft') }}</div>
                        <div class="h5 fw-bold text-warning mb-0 tabular-nums">{{ number_format($summary['draft_grand'], 2) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3 text-center">
                        <div class="small text-muted fw-bold text-uppercase mb-1 ls-1">{{ __('Cancelled') }}</div>
                        <div class="h5 fw-bold text-danger mb-0 tabular-nums">{{ number_format($summary['cancelled_grand'], 2) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3 text-center">
                        <div class="small text-muted fw-bold text-uppercase mb-1 ls-1">{{ __('Total Tax') }}</div>
                        <div class="h5 fw-bold text-secondary mb-0 tabular-nums">{{ number_format($summary['total_tax'], 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Table --}}
        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center d-print-none">
                <h6 class="mb-0 fw-bold flex-grow-1">
                    <i class="bi bi-receipt me-2 text-pr"></i>
                    {{ __('Sales Transactions') }}
                </h6>
                <span class="badge bg-pr-subtle text-pr border border-pr-subtle px-3 py-2 ms-auto flex-shrink-0">
                    {{ $summary['total_count'] }} {{ __(Str::plural('Invoice', $summary['total_count'])) }}
                </span>
            </div>
            <div>
                <livewire:report.sale.sale-report-table/>
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
                var startEl = document.getElementById('sr-start-date');
                var endEl   = document.getElementById('sr-end-date');

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
                            syncHidden('sr-start-date-hidden', dateStr);
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
                            syncHidden('sr-end-date-hidden', dateStr);
                        },
                    });
                }
            }

            initFlatpickr();

            // Customer picker: shared tom-select component, wire:ignore'd so Livewire commits can't wipe it.
            var srStatus = document.getElementById('sr-status');
            if (srStatus && !srStatus.tomselect) { initTomSelectForm($('#sr-status-wrap')); }
            var srCust = document.getElementById('sr-customer');
            if (srCust && !srCust.tomselect) { initTomSelectForm($('#sr-customer-wrap')); }
            $wire.on('sr-filter-reset', function () {
                if (srCust && srCust.tomselect) { srCust.tomselect.clear(true); }
                if (srStatus && srStatus.tomselect) { srStatus.tomselect.setValue('', true); }
                var s = document.getElementById('sr-start-date'), e = document.getElementById('sr-end-date');
                if (s && s._flatpickr) s._flatpickr.setDate($wire.get('startDate'), false);
                if (e && e._flatpickr) e._flatpickr.setDate($wire.get('endDate'), false);
            });

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
            --sr-primary: #0ea5e9;
            --sr-dark:    #0369a1;
            --sr-light:   #f0f9ff;
        }

        .btn-pr { background-color: var(--sr-primary); border-color: var(--sr-primary); color: #fff; }
        .btn-pr:hover { background-color: var(--sr-dark); border-color: var(--sr-dark); color: #fff; }
        .text-pr { color: var(--sr-primary) !important; }
        .bg-pr-subtle { background-color: #e0f2fe !important; }
        .border-pr-subtle { border-color: #bae6fd !important; }

        .btn-outline-pr { color: var(--sr-primary); border-color: var(--sr-primary); }
        .btn-outline-pr:hover, .btn-check:checked + .btn-outline-pr {
            background-color: var(--sr-primary); border-color: var(--sr-primary); color: #fff;
        }

        .ls-1 { letter-spacing: 0.05em; }
        .x-small { font-size: 0.7rem; }
        .tabular-nums { font-variant-numeric: tabular-nums; }

        .card { border-radius: 1rem; }
        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 0.25rem rgba(14, 165, 233, 0.1);
            border-color: var(--sr-primary);
        }

        thead th { vertical-align: bottom; }

        @media print {
            body { background: white !important; }
            .d-print-none { display: none !important; }
            .card { box-shadow: none !important; border: 1px solid #eee !important; }
        }
    </style>
</div>
