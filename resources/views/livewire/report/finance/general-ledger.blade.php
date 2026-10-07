@section('js', 'general_ledger')
@section('page-title', __('Customer Ledger'))
@section('hide-topbar', true)
@section('page-subtitle', __('Complete transaction history per customer with running balance'))

<div class="gl-wrapper min-vh-100 bg-light pt-1 pb-4">
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
                        <li><a class="dropdown-item py-2" href="#" onclick="reportExportPdf(event, 'gl-print', {orientation: 'landscape'})"><i class="bi bi-file-pdf text-danger me-2"></i>{{ __('PDF Document') }}</a></li>
                        <li><a class="dropdown-item py-2" href="#" wire:click.prevent="exportExcel"><i class="bi bi-file-excel text-success me-2"></i>{{ __('Excel Sheet') }}</a></li>
                    </ul>
                </div>
            </div>
        </div>

                {{-- Filters --}}
        <div class="card border-0 shadow-sm mb-4 d-print-none" id="list-filter">
            <div class="card-body p-4">
                <div class="row g-3 align-items-end">
<div class="col-12 col-sm-6 col-lg">
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">{{ __('From Date') }}</label>
                        <input type="hidden" id="gl-start-hidden" wire:model.live="startDate" value="{{ $startDate }}" />
                        <input type="text" id="gl-start-date"
                               class="form-control bg-light border-0 py-2"
                               placeholder="dd-mm-yyyy"
                               value="{{ $startDate }}" />
                    </div>
<div class="col-12 col-sm-6 col-lg">
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">{{ __('To Date') }}</label>
                        <input type="hidden" id="gl-end-hidden" wire:model.live="endDate" value="{{ $endDate }}" />
                        <input type="text" id="gl-end-date"
                               class="form-control bg-light border-0 py-2"
                               placeholder="dd-mm-yyyy"
                               value="{{ $endDate }}" />
                    </div>
<div class="col-12 col-sm-6 col-lg">
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">{{ __('Customer') }} <sup class="text-danger">*</sup></label>
                        <select class="tom-select bg-light border-0 no-ts" wire:model="customerId" required data-live-search="true">
                            @if(count($customers) === 0)
                                <option value="">{{ __('No customers found') }}</option>
                            @else
                                <option value="">{{ __('--Select Customer--') }}</option>
                            @endif
                            @foreach($customers as $customer)
                                <option value="{{ $customer['id'] }}" @selected($customerId == $customer['id'])>
                                    {{ $customer['row_no'] }} — {{ $customer['name_en'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

<div class="col-12 col-sm-6 col-lg">
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">{{ __('Search') }}</label>
                        <input type="text"
                               class="form-control bg-light border-0 py-2"
                               placeholder="{{ __('Voucher, description…') }}"
                               wire:model.live.debounce.300ms="search" />
                    </div>
<div class="col-12 col-lg-auto">
    <div class="d-flex flex-wrap gap-2 justify-content-lg-end align-items-center">
        <button type="button" class="btn btn-gl fw-bold py-2 shadow-sm"
                                            onclick="glApplyFilter()"
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

        {{-- Period info bar --}}
        <div class="d-flex align-items-center justify-content-between mb-3 px-1 d-print-none">
            <div class="small text-muted">
                <i class="bi bi-calendar3 me-1"></i>
                {{ __('Period:') }} <strong class="text-dark">{{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}</strong>
                — <strong class="text-dark">{{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</strong>
                @php $selectedCustomer = collect($customers)->firstWhere('id', $customerId); @endphp
                @if($selectedCustomer)
                    &nbsp;·&nbsp; {{ __('Customer:') }} <strong class="text-dark">{{ $selectedCustomer['name_en'] }}</strong>
                @endif
            </div>
            <div class="small text-muted">
                {{ __('Generated:') }} {{ now()->format('d M Y, H:i') }}
            </div>
        </div>

        {{-- Ledger content --}}
        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center d-print-none">
                <h6 class="mb-0 fw-bold flex-grow-1">
                    <i class="bi bi-journal-text me-2 text-gl"></i>{{ __('Customer Transaction Ledger') }}
                </h6>
                <span class="badge bg-gl-subtle text-gl border border-gl-subtle px-3 py-2 ms-auto flex-shrink-0">
                    <i class="bi bi-currency-exchange me-1"></i>{{ __('Currency:') }} SAR
                </span>
            </div>
            <div>
                <livewire:report.finance.general-ledger-table :customerId="$customerId" />
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
                var startEl = document.getElementById('gl-start-date');
                var endEl   = document.getElementById('gl-end-date');

                if (startEl && startEl._flatpickr) { startEl._flatpickr.destroy(); }
                if (endEl   && endEl._flatpickr)   { endEl._flatpickr.destroy(); }

                if (startEl) {
                    flatpickr(startEl, {
                        dateFormat: 'Y-m-d', altInput: true, altFormat: 'd-m-Y',
                        allowInput: true, disableMobile: true,
                        defaultDate: startEl.value || null,
                        onChange: function (selectedDates, dateStr) {
                            syncHidden('gl-start-hidden', dateStr);
                        },
                    });
                }
                if (endEl) {
                    flatpickr(endEl, {
                        dateFormat: 'Y-m-d', altInput: true, altFormat: 'd-m-Y',
                        allowInput: true, disableMobile: true,
                        defaultDate: endEl.value || null,
                        onChange: function (selectedDates, dateStr) {
                            syncHidden('gl-end-hidden', dateStr);
                        },
                    });
                }
            }

            initFlatpickr();
            // Livewire.hook('commit', ...)'s succeed callback fires before
            // the DOM morph for this commit is actually applied — re-running
            // initFlatpickr() there re-initializes the still-old (correctly
            // formatted) input, which the morph then immediately overwrites
            // back to the server-rendered raw value, visibly flipping the
            // date field's format after every commit. requestAnimationFrame
            // defers until the next frame, after that synchronous morph
            // pass has actually finished (see the customer-statement.blade
            // tomselect fix for the same root cause). The Customer
            // tom-select gets destroyed by the same morph, so it needs the
            // same re-init treatment.
            Livewire.hook('commit', function (ref) {
                ref.succeed(function () {
                    requestAnimationFrame(function () {
                        initFlatpickr();
                        initTomSelectForm($('#list-filter'));
                    });
                });
            });

            // Generate: push whatever is in the pickers, then run the filter
            // in one request. Livewire disallows calling lifecycle hooks
            // like updatedStartDate() directly via wire:click, so this
            // explicit $wire.set + applyFilter() call is the safe way to
            // force a manual refresh (same pattern as customer-statement).
            window.glApplyFilter = function () {
                var s = document.getElementById('gl-start-date');
                var e = document.getElementById('gl-end-date');
                if (s && s.value) $wire.set('startDate', s.value, false);
                if (e && e.value) $wire.set('endDate', e.value, false);
                $wire.call('applyFilter');
            };
        })();
    </script>
    @endscript

    <style>
        :root {
            --gl-primary: #4f46e5;
            --gl-dark:    #4338ca;
            --gl-light:   #eef2ff;
        }

        .btn-gl { background-color: var(--gl-primary); border-color: var(--gl-primary); color: #fff; }
        .btn-gl:hover { background-color: var(--gl-dark); border-color: var(--gl-dark); color: #fff; }
        .text-gl { color: var(--gl-primary) !important; }
        .bg-gl-subtle { background-color: #e0e7ff !important; }
        .border-gl-subtle { border-color: #a5b4fc !important; }

        .ls-1 { letter-spacing: 0.05em; }
        .x-small { font-size: 0.7rem; }
        .tabular-nums { font-variant-numeric: tabular-nums; }

        .card { border-radius: 1rem; }
        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 0.25rem rgba(79, 70, 229, 0.1);
            border-color: var(--gl-primary);
        }

        @media print {
            body { background: white !important; }
            .d-print-none { display: none !important; }
            .card { box-shadow: none !important; border: 1px solid #eee !important; }
        }
    </style>
</div>
