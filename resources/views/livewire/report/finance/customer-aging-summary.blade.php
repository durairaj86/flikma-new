@section('js', 'customer_aging_summary')
@section('page-title', __('Customer Aging Summary'))
@section('page-subtitle', __('Outstanding receivables across all customers, by aging period'))
@section('hide-topbar', true)

<div class="aging-wrapper min-vh-100 bg-light pb-4">
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
                                <button class="btn btn-white border border-end-0" onclick="window.print()">
                                <i class="bi bi-printer me-2"></i>{{ __('Print') }}
                                </button>
                                <div class="btn-group position-relative" x-data="{ open: false, pos: '', toggle() { this.open = !this.open; if (this.open) { const r = this.$refs.btn.getBoundingClientRect(); this.pos = 'position:fixed;left:auto;bottom:auto;right:' + (window.innerWidth - r.right) + 'px;top:' + (r.bottom + 4) + 'px;'; } } }" @click.outside="open = false" @keydown.escape.window="open = false" @scroll.window="open = false" @resize.window="open = false">
                                <button type="button" class="btn btn-white border dropdown-toggle" x-ref="btn" @click="toggle()" :aria-expanded="open">
                                <i class="bi bi-download me-2"></i>{{ __('Export') }}
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end border-0 shadow" :class="{ show: open }" x-cloak :style="pos" @click="open = false">
                                <li><a class="dropdown-item py-2" href="#" onclick="casExportPdf(event)"><i class="bi bi-file-pdf text-danger me-2"></i>{{ __('PDF Document') }}</a></li>
                                <li><a class="dropdown-item py-2" href="#" wire:click.prevent="exportExcel"><i class="bi bi-file-excel text-success me-2"></i>{{ __('Excel Sheet') }}</a></li>
                                </ul>
                                </div>
                                </div>
        </div>

        @php
            $bucketColor = function (int $i) use ($bucketDefs) {
                if ($i === 0) return '#16a34a'; // current
                $ramp = ['#b45309', '#f97316', '#dc2626', '#991b1b', '#7f1d1d'];
                return $ramp[min($i - 1, count($ramp) - 1)];
            };
        @endphp

        {{-- Header --}}
                {{-- Filter Bar --}}
        <div class="card border-0 shadow-sm mb-4 d-print-none">
            <div class="card-body p-4">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-4 col-xl-3" id="caa-customer-wrap" wire:ignore>
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">{{ __('Customer') }}</label>
                        <x-common.customers wire:model.live="customerId" id="caa-customer" name="caa-customer"
                                            :value="$customerId ? [(int) $customerId] : null" :new="false"
                                            :customers="\App\Models\Customer\Customer::whereIn('status', [3, 4])->orderBy('name_en')->get()"
                                            all-label="{{ __('All Customers') }}"
                                            placeholder="{{ __('All Customers') }}"></x-common.customers>
                    </div>
                    <div class="col-lg-2 col-md-4">
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">{{ __('As of Date') }}</label>
                        <div wire:ignore>
                            <input type="text" id="cas-as-of-date"
                                   class="form-control bg-light border-0 py-2"
                                   placeholder="dd-mm-yyyy" value="{{ $asOfDate }}" />
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4">
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">{{ __('Interval (Days)') }}</label>
                        <select class="form-select bg-light border-0 py-2 no-ts" wire:model.live="agingInterval">
                            @foreach(\App\Livewire\Report\Finance\CustomerAgingAll::AGING_INTERVALS as $days)
                                <option value="{{ $days }}">{{ $days }} {{ __('Days') }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-4">
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">{{ __('Columns') }}</label>
                        <select class="form-select bg-light border-0 py-2 no-ts" wire:model.live="agingColumns">
                            @foreach(\App\Livewire\Report\Finance\CustomerAgingAll::AGING_COLUMN_CHOICES as $n)
                                <option value="{{ $n }}">{{ $n }} {{ __(Str::plural('Column', $n)) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

{{-- Summary Cards --}}
        <div class="row g-3 mb-4 d-print-none">
            @foreach($bucketDefs as $i => $def)
                <div class="col-md col-6" wire:key="sum-card-{{ $def['key'] }}">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center py-3">
                            <div class="small text-muted fw-bold text-uppercase mb-1">{{ $def['label'] }}</div>
                            <div class="fw-bold tabular-nums" style="color: {{ $bucketColor($i) }};">{{ number_format($totals[$def['key']], 2) }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
            <div class="col-md col-6">
                <div class="card border-0 shadow-sm h-100 border-start border-3 border-customer">
                    <div class="card-body text-center py-3">
                        <div class="small text-muted fw-bold text-uppercase mb-1">{{ __('Total Due') }}</div>
                        <div class="fw-bold tabular-nums text-customer fs-5">{{ number_format($totals['grand_total'], 2) }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Aging Table --}}
        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2 d-print-none">
                <h6 class="mb-0 fw-bold flex-grow-1"><i class="bi bi-table me-2 text-customer"></i>{{ __('Customer Aging Summary') }}</h6>
                <div class="d-flex align-items-center gap-2 ms-auto flex-shrink-0">
                    <span class="badge bg-light text-dark border px-3 py-2">{{ $agingInterval }}-{{ __('day buckets') }}</span>
                    <span class="badge bg-customer-subtle text-customer border border-customer-subtle px-3 py-2">
                        {{ count($customers) }} {{ __(Str::plural('customer', count($customers))) }}
                    </span>
                </div>
            </div>
            <div class="table-responsive d-print-none">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                    <tr class="bg-light text-muted small text-uppercase fw-bold ls-1">
                        <th class="ps-4 border-0">{{ __('Customer') }}</th>
                        @foreach($bucketDefs as $def)
                            <th class="text-end border-0" wire:key="th-{{ $def['key'] }}">{{ $def['short'] }}</th>
                        @endforeach
                        <th class="text-end pe-4 border-0">{{ __('Total') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($customers as $cust)
                        <tr wire:key="cust-row-{{ $cust['customer_id'] }}">
                            <td class="ps-4"><div class="fw-medium">{{ $cust['customer_name'] }}</div><div class="small text-muted">{{ $cust['customer_code'] }}</div></td>
                            @foreach($bucketDefs as $i => $def)
                                <td class="text-end tabular-nums" style="color: {{ $bucketColor($i) }};" wire:key="cell-{{ $cust['customer_id'] }}-{{ $def['key'] }}">
                                    {{ $cust[$def['key']] > 0 ? number_format($cust[$def['key']], 2) : '—' }}
                                </td>
                            @endforeach
                            <td class="text-end pe-4 fw-bold tabular-nums text-customer">
                                {{ number_format($cust['total'], 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 2 + count($bucketDefs) }}" class="text-center py-5 text-muted small">
                                <i class="bi bi-inbox h3 d-block mb-2"></i>
                                {{ __('No customers with outstanding invoices found.') }}
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                    <tfoot class="bg-light border-top">
                    <tr class="fw-bold">
                        <td class="ps-4 py-3">{{ __('Total') }}</td>
                        @foreach($bucketDefs as $i => $def)
                            <td class="text-end tabular-nums" style="color: {{ $bucketColor($i) }};" wire:key="tf-{{ $def['key'] }}">{{ number_format($totals[$def['key']], 2) }}</td>
                        @endforeach
                        <td class="text-end pe-4 text-customer fs-6 tabular-nums">{{ number_format($totals['grand_total'], 2) }}</td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Bank-statement style layout: used for Print and PDF export only --}}
        <div id="aging-summary-print" class="stmt-print d-none d-print-block"
             data-pdf-filename="CustomerAgingSummary-{{ $asOfDate }}.pdf">

            <table class="stmt-meta">
                <tr>
                    <td>
                        <div class="stmt-company">{{ $company->name ?? config('app.name') }}</div>
                        <div class="stmt-sub">
                            @if(!empty($company->phone)) {{ __('Phone') }}: {{ $company->phone }} @endif
                            @if(!empty($company->email)) &nbsp;|&nbsp; {{ $company->email }} @endif
                        </div>
                        @if(!empty($company->vat_number))
                            <div class="stmt-sub">{{ __('VAT No') }}: {{ $company->vat_number }}</div>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="stmt-title">{{ __('CUSTOMER AGING SUMMARY') }}</div>
                        <div class="stmt-sub">{{ __('As of') }}: {{ \Carbon\Carbon::parse($asOfDate)->format('d M Y') }}</div>
                        <div class="stmt-sub">{{ __('Aging') }}: {{ $agingInterval }}-{{ __('day buckets') }} &times; {{ $agingColumns }}</div>
                        <div class="stmt-sub">{{ __('Generated') }}: {{ now()->format('d M Y H:i') }} &nbsp;|&nbsp; {{ __('Currency') }}: SAR</div>
                    </td>
                </tr>
            </table>

            <table class="stmt-table">
                <thead>
                <tr>
                    <th>{{ __('Customer') }}</th>
                    @foreach($bucketDefs as $def)
                        <th class="text-end">{{ $def['label'] }}</th>
                    @endforeach
                    <th class="text-end">{{ __('Total') }}</th>
                </tr>
                </thead>
                <tbody>
                @forelse($customers as $cust)
                    <tr>
                        <td>{{ $cust['customer_name'] }}<br><span style="font-size:10px;color:#6b7280;">{{ $cust['customer_code'] }}</span></td>
                        @foreach($bucketDefs as $def)
                            <td class="text-end">{{ $cust[$def['key']] > 0 ? number_format($cust[$def['key']], 2) : '' }}</td>
                        @endforeach
                        <td class="text-end stmt-strong">{{ number_format($cust['total'], 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ 2 + count($bucketDefs) }}" class="text-center">{{ __('No customers with outstanding invoices found') }}</td>
                    </tr>
                @endforelse
                </tbody>
                <tfoot>
                <tr class="stmt-strong">
                    <td>{{ __('Total') }}</td>
                    @foreach($bucketDefs as $def)
                        <td class="text-end">{{ number_format($totals[$def['key']], 2) }}</td>
                    @endforeach
                    <td class="text-end">{{ number_format($totals['grand_total'], 2) }}</td>
                </tr>
                </tfoot>
            </table>

            <div class="stmt-footnote">
                {{ __('This is a system generated report. Aging buckets:') }} {{ $agingInterval }} {{ __('days') }} &times; {{ $agingColumns }} {{ __('columns.') }}
            </div>
        </div>

    </div>

    @script
    <script>
        (function () {
            function initFlatpickr() {
                var el = document.getElementById('cas-as-of-date');
                if (!el || el._flatpickr) return;

                flatpickr(el, {
                    dateFormat:    'Y-m-d',
                    altInput:      true,
                    altFormat:     'd-m-Y',
                    allowInput:    true,
                    disableMobile: true,
                    defaultDate:   el.value || null,
                    onChange: function (selectedDates, dateStr) {
                        if (dateStr) {
                            $wire.set('asOfDate', dateStr);
                        }
                    },
                });
            }

            initFlatpickr();

            window.casExportPdf = function (e) {
                e.preventDefault();
                var area = document.getElementById('aging-summary-print');
                if (!area) return;
                var clone = area.cloneNode(true);
                clone.classList.remove('d-none');
                clone.style.padding = '10px';
                var opt = {
                    margin: 0.4,
                    filename: area.dataset.pdfFilename || 'CustomerAgingSummary.pdf',
                    html2canvas: { scale: 2, useCORS: true },
                    jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' },
                    pagebreak: { mode: ['avoid-all', 'css'] }
                };
                html2pdf().set(opt).from(clone).save();
            };

            Livewire.hook('commit', function (ref) {
                ref.succeed(function () {
                    queueMicrotask(initFlatpickr);
                });
            });
        })();
    </script>
    @endscript

    @include('includes.report-print-css', ['orientation' => 'portrait'])

    <style>
        :root {
            --customer-primary: #0ea5e9;
            --customer-dark: #0369a1;
            --customer-light: #e0f2fe;
        }
        .text-customer { color: var(--customer-primary) !important; }
        .border-customer { border-color: var(--customer-primary) !important; }
        .bg-customer-subtle { background-color: #e0f2fe !important; }
        .border-customer-subtle { border-color: #bae6fd !important; }
        .ls-1 { letter-spacing: 0.05em; }
        .x-small { font-size: 0.7rem; text-transform: uppercase; }
        .tabular-nums { font-variant-numeric: tabular-nums; }
        .card { border-radius: 1rem; }
        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 0.25rem rgba(14, 165, 233, 0.1);
            border-color: var(--customer-primary);
        }
        @media print {
            .d-print-none { display: none !important; }
            .aging-wrapper { padding: 0 !important; background: white !important; }
            .container-fluid { padding: 0 !important; }
        }
    </style>

    @script
    <script>
        // Customer picker: shared tom-select component, wire:ignore'd so Livewire commits can't wipe it.
        (function () {
            var el = document.getElementById('caa-customer');
            if (el && !el.tomselect) { initTomSelectForm($('#caa-customer-wrap')); }
        })();
    </script>
    @endscript
</div>
