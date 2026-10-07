@section('js', 'trial_balance')
@section('page-title', __('Trial Balance'))
@section('hide-topbar', true)
@section('page-subtitle', __('Double-entry verification — total debits must equal total credits'))

<div class="tb-wrapper min-vh-100 bg-light pt-1 pb-4">
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
                        <li><a class="dropdown-item py-2" href="#" onclick="reportExportPdf(event, 'tb-print')"><i class="bi bi-file-pdf text-danger me-2"></i>{{ __('PDF Document') }}</a></li>
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
                        <input type="hidden" id="tb-start-hidden" wire:model.live="startDate" value="{{ $startDate }}" />
                        <input type="text" id="tb-start-date"
                               class="form-control bg-light border-0 py-2"
                               placeholder="dd-mm-yyyy"
                               value="{{ $startDate }}" />
                    </div>
<div class="col-12 col-sm-6 col-lg">
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">{{ __('To Date') }}</label>
                        <input type="hidden" id="tb-end-hidden" wire:model.live="endDate" value="{{ $endDate }}" />
                        <input type="text" id="tb-end-date"
                               class="form-control bg-light border-0 py-2"
                               placeholder="dd-mm-yyyy"
                               value="{{ $endDate }}" />
                    </div>
<div class="col-12 col-sm-6 col-lg">
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">{{ __('Search Accounts') }}</label>
                        <input type="text"
                               class="form-control bg-light border-0 py-2"
                               placeholder="{{ __('Account name or code…') }}"
                               wire:model.live.debounce.300ms="search" />
                    </div>
<div class="col-12 col-lg-auto">
    <div class="d-flex flex-wrap gap-2 justify-content-lg-end align-items-center">
        <button type="button" class="btn btn-tb fw-bold py-2 px-4 shadow-sm"
                                            wire:click="updatedStartDate('{{ $startDate }}')"
                                            wire:loading.attr="disabled">
                                        <i class="bi bi-filter-left me-2"></i>
                                        <span wire:loading.remove>{{ __('Generate') }}</span>
                                        <span wire:loading><span class="spinner-border spinner-border-sm me-1"></span>{{ __('Loading…') }}</span>
                                    </button>
    </div>
</div>
</div>
            </div>
        </div>

        {{-- Report period info bar --}}
        <div class="d-flex align-items-center justify-content-between mb-3 px-1 d-print-none">
            <div class="small text-muted">
                <i class="bi bi-calendar3 me-1"></i>
                {{ __('Period:') }} <strong class="text-dark">{{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}</strong>
                — <strong class="text-dark">{{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</strong>
            </div>
            <div class="small text-muted">
                {{ __('Generated:') }} {{ now()->format('d M Y, H:i') }}
            </div>
        </div>

        {{-- Table card --}}
        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center d-print-none">
                <h6 class="mb-0 fw-bold flex-grow-1">
                    <i class="bi bi-list-columns me-2 text-tb"></i>{{ __('Account Balances') }}
                </h6>
                <span class="badge bg-tb-subtle text-tb border border-tb-subtle px-3 py-2 ms-auto flex-shrink-0">
                    <i class="bi bi-currency-exchange me-1"></i>{{ __('Currency:') }} SAR
                </span>
            </div>
            <div class="table-responsive">
                <livewire:report.finance.trial-balance-table />
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
                var startEl = document.getElementById('tb-start-date');
                var endEl   = document.getElementById('tb-end-date');

                if (startEl && startEl._flatpickr) { startEl._flatpickr.destroy(); }
                if (endEl   && endEl._flatpickr)   { endEl._flatpickr.destroy(); }

                if (startEl) {
                    flatpickr(startEl, {
                        dateFormat: 'Y-m-d', altInput: true, altFormat: 'd-m-Y',
                        allowInput: true, disableMobile: true,
                        defaultDate: startEl.value || null,
                        onChange: function (selectedDates, dateStr) {
                            syncHidden('tb-start-hidden', dateStr);
                        },
                    });
                }
                if (endEl) {
                    flatpickr(endEl, {
                        dateFormat: 'Y-m-d', altInput: true, altFormat: 'd-m-Y',
                        allowInput: true, disableMobile: true,
                        defaultDate: endEl.value || null,
                        onChange: function (selectedDates, dateStr) {
                            syncHidden('tb-end-hidden', dateStr);
                        },
                    });
                }
            }

            initFlatpickr();
            Livewire.hook('commit', function (ref) {
                ref.succeed(function () { requestAnimationFrame(initFlatpickr); });
            });
        })();
    </script>
    @endscript

    <style>
        :root {
            --tb-primary: #1d4ed8;
            --tb-dark:    #1e40af;
            --tb-light:   #eff6ff;
        }

        .btn-tb { background-color: var(--tb-primary); border-color: var(--tb-primary); color: #fff; }
        .btn-tb:hover { background-color: var(--tb-dark); border-color: var(--tb-dark); color: #fff; }
        .text-tb { color: var(--tb-primary) !important; }
        .bg-tb-subtle { background-color: #dbeafe !important; }
        .border-tb-subtle { border-color: #93c5fd !important; }

        .ls-1 { letter-spacing: 0.05em; }
        .x-small { font-size: 0.7rem; }
        .tabular-nums { font-variant-numeric: tabular-nums; }

        .card { border-radius: 1rem; }
        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 0.25rem rgba(29, 78, 216, 0.1);
            border-color: var(--tb-primary);
        }

        @media print {
            body { background: white !important; }
            .d-print-none { display: none !important; }
            .card { box-shadow: none !important; border: 1px solid #eee !important; }
        }
    </style>
</div>
