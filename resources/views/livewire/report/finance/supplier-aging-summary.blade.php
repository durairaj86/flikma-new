@section('js', 'supplier_aging_summary')
@section('page-title', 'Supplier Aging Summary')

<div class="aging-wrapper min-vh-100 bg-light py-4">
    <div class="container-fluid px-lg-5">

        @php
            $bucketColor = function (int $i) use ($bucketDefs) {
                if ($i === 0) return '#16a34a'; // current
                $ramp = ['#b45309', '#f97316', '#dc2626', '#991b1b', '#7f1d1d'];
                return $ramp[min($i - 1, count($ramp) - 1)];
            };
        @endphp

        {{-- Header --}}
        <div class="row align-items-center mb-4 d-print-none">
            <div class="col-md-6">
                <h1 class="h3 fw-bold text-slate-900 mb-1">Supplier Aging Summary</h1>
                <p class="text-muted small mb-0">Outstanding payables across all suppliers, by aging period</p>
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
                            <li><a class="dropdown-item py-2" href="#" onclick="sasExportPdf(event)"><i class="bi bi-file-pdf text-danger me-2"></i>PDF Document</a></li>
                            <li><a class="dropdown-item py-2" href="#" wire:click.prevent="exportExcel"><i class="bi bi-file-excel text-success me-2"></i>Excel Sheet</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filter Bar --}}
        <div class="card border-0 shadow-sm mb-4 d-print-none">
            <div class="card-body p-4">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-2 col-md-4">
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">As of Date</label>
                        <div wire:ignore>
                            <input type="text" id="sas-as-of-date"
                                   class="form-control bg-light border-0 py-2"
                                   placeholder="dd-mm-yyyy" value="{{ $asOfDate }}" />
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4">
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">Interval (Days)</label>
                        <select class="form-select bg-light border-0 py-2 no-ts" wire:model.live="agingInterval">
                            @foreach(\App\Livewire\Report\Finance\SupplierAgingAll::AGING_INTERVALS as $days)
                                <option value="{{ $days }}">{{ $days }} Days</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-4">
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">Columns</label>
                        <select class="form-select bg-light border-0 py-2 no-ts" wire:model.live="agingColumns">
                            @foreach(\App\Livewire\Report\Finance\SupplierAgingAll::AGING_COLUMN_CHOICES as $n)
                                <option value="{{ $n }}">{{ $n }} {{ $n === 1 ? 'Column' : 'Columns' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-6">
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">Search Supplier</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0 text-muted"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control bg-light border-0 ps-0 py-2"
                                   placeholder="Supplier name or code..." wire:model.live.debounce.300ms="search" />
                        </div>
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
                <div class="card border-0 shadow-sm h-100 border-start border-3 border-supplier">
                    <div class="card-body text-center py-3">
                        <div class="small text-muted fw-bold text-uppercase mb-1">Total Due</div>
                        <div class="fw-bold tabular-nums text-supplier fs-5">{{ number_format($totals['grand_total'], 2) }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Aging Table --}}
        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2 d-print-none">
                <h6 class="mb-0 fw-bold"><i class="bi bi-table me-2 text-supplier"></i>Supplier Aging Summary</h6>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-light text-dark border px-3 py-2">{{ $agingInterval }}-day buckets</span>
                    <span class="badge bg-supplier-subtle text-supplier border border-supplier-subtle px-3 py-2">
                        {{ count($suppliers) }} {{ Str::plural('supplier', count($suppliers)) }}
                    </span>
                </div>
            </div>
            <div class="table-responsive d-print-none">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                    <tr class="bg-light text-muted small text-uppercase fw-bold ls-1">
                        <th class="ps-4 border-0">Supplier Code</th>
                        <th class="border-0">Supplier Name</th>
                        @foreach($bucketDefs as $def)
                            <th class="text-end border-0" wire:key="th-{{ $def['key'] }}">{{ $def['short'] }}</th>
                        @endforeach
                        <th class="text-end pe-4 border-0">Total</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($suppliers as $supp)
                        <tr wire:key="supp-row-{{ $supp['supplier_id'] }}">
                            <td class="ps-4">
                                <span class="fw-medium">{{ $supp['supplier_code'] }}</span>
                            </td>
                            <td class="small">{{ $supp['supplier_name'] }}</td>
                            @foreach($bucketDefs as $i => $def)
                                <td class="text-end tabular-nums" style="color: {{ $bucketColor($i) }};" wire:key="cell-{{ $supp['supplier_id'] }}-{{ $def['key'] }}">
                                    {{ $supp[$def['key']] > 0 ? number_format($supp[$def['key']], 2) : '—' }}
                                </td>
                            @endforeach
                            <td class="text-end pe-4 fw-bold tabular-nums text-supplier">
                                {{ number_format($supp['total'], 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 3 + count($bucketDefs) }}" class="text-center py-5 text-muted small">
                                <i class="bi bi-inbox h3 d-block mb-2"></i>
                                No suppliers with outstanding invoices found.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                    <tfoot class="bg-light border-top">
                    <tr class="fw-bold">
                        <td colspan="2" class="ps-4 py-3">Total</td>
                        @foreach($bucketDefs as $i => $def)
                            <td class="text-end tabular-nums" style="color: {{ $bucketColor($i) }};" wire:key="tf-{{ $def['key'] }}">{{ number_format($totals[$def['key']], 2) }}</td>
                        @endforeach
                        <td class="text-end pe-4 text-supplier fs-6 tabular-nums">{{ number_format($totals['grand_total'], 2) }}</td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Bank-statement style layout: used for Print and PDF export only --}}
        <div id="supplier-aging-summary-print" class="stmt-print d-none d-print-block"
             data-pdf-filename="SupplierAgingSummary-{{ $asOfDate }}.pdf">

            <table class="stmt-meta">
                <tr>
                    <td>
                        <div class="stmt-company">{{ $company->name ?? config('app.name') }}</div>
                        <div class="stmt-sub">
                            @if(!empty($company->phone)) Phone: {{ $company->phone }} @endif
                            @if(!empty($company->email)) &nbsp;|&nbsp; {{ $company->email }} @endif
                        </div>
                        @if(!empty($company->vat_number))
                            <div class="stmt-sub">VAT No: {{ $company->vat_number }}</div>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="stmt-title">SUPPLIER AGING SUMMARY</div>
                        <div class="stmt-sub">As of: {{ \Carbon\Carbon::parse($asOfDate)->format('d M Y') }}</div>
                        <div class="stmt-sub">Aging: {{ $agingInterval }}-day buckets &times; {{ $agingColumns }}</div>
                        <div class="stmt-sub">Generated: {{ now()->format('d M Y H:i') }} &nbsp;|&nbsp; Currency: SAR</div>
                    </td>
                </tr>
            </table>

            <table class="stmt-table">
                <thead>
                <tr>
                    <th>Supplier ID</th>
                    <th>Supplier Name</th>
                    @foreach($bucketDefs as $def)
                        <th class="text-end">{{ $def['label'] }}</th>
                    @endforeach
                    <th class="text-end">Total</th>
                </tr>
                </thead>
                <tbody>
                @forelse($suppliers as $supp)
                    <tr>
                        <td>{{ $supp['supplier_code'] }}</td>
                        <td>{{ $supp['supplier_name'] }}</td>
                        @foreach($bucketDefs as $def)
                            <td class="text-end">{{ $supp[$def['key']] > 0 ? number_format($supp[$def['key']], 2) : '' }}</td>
                        @endforeach
                        <td class="text-end stmt-strong">{{ number_format($supp['total'], 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ 3 + count($bucketDefs) }}" class="text-center">No suppliers with outstanding invoices found</td>
                    </tr>
                @endforelse
                </tbody>
                <tfoot>
                <tr class="stmt-strong">
                    <td colspan="2">Total</td>
                    @foreach($bucketDefs as $def)
                        <td class="text-end">{{ number_format($totals[$def['key']], 2) }}</td>
                    @endforeach
                    <td class="text-end">{{ number_format($totals['grand_total'], 2) }}</td>
                </tr>
                </tfoot>
            </table>

            <div class="stmt-footnote">
                This is a system generated report. Aging buckets: {{ $agingInterval }} days &times; {{ $agingColumns }} columns.
            </div>
        </div>

    </div>

    @script
    <script>
        (function () {
            function initFlatpickr() {
                var el = document.getElementById('sas-as-of-date');
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

            window.sasExportPdf = function (e) {
                e.preventDefault();
                var area = document.getElementById('supplier-aging-summary-print');
                if (!area) return;
                var clone = area.cloneNode(true);
                clone.classList.remove('d-none');
                clone.style.padding = '10px';
                var opt = {
                    margin: 0.4,
                    filename: area.dataset.pdfFilename || 'SupplierAgingSummary.pdf',
                    html2canvas: { scale: 2, useCORS: true },
                    jsPDF: { unit: 'in', format: 'a4', orientation: 'landscape' },
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

    @include('includes.report-print-css')

    <style>
        :root {
            --supplier-primary: #d97706;
            --supplier-dark: #92400e;
            --supplier-light: #fffbeb;
        }
        .text-supplier { color: var(--supplier-primary) !important; }
        .border-supplier { border-color: var(--supplier-primary) !important; }
        .bg-supplier-subtle { background-color: #fef3c7 !important; }
        .border-supplier-subtle { border-color: #fde68a !important; }
        .ls-1 { letter-spacing: 0.05em; }
        .x-small { font-size: 0.7rem; text-transform: uppercase; }
        .tabular-nums { font-variant-numeric: tabular-nums; }
        .card { border-radius: 1rem; }
        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 0.25rem rgba(217, 119, 6, 0.1);
            border-color: var(--supplier-primary);
        }
        @media print {
            .d-print-none { display: none !important; }
            .aging-wrapper { padding: 0 !important; background: white !important; }
            .container-fluid { padding: 0 !important; }
        }
    </style>
</div>
