@section('js', 'supplier_aging')
@section('page-title', 'Supplier Aging Report')

<div class="aging-wrapper min-vh-100 bg-light py-4" wire:key="supplier-aging-{{ $supplierId }}">
    <div class="container-fluid px-lg-5">

        {{-- Header --}}
        <div class="row align-items-center mb-4 d-print-none">
            <div class="col-md-6">
                <h1 class="h3 fw-bold text-slate-900 mb-1">Supplier Aging Report</h1>
                <p class="text-muted small mb-0">Track outstanding payables by aging period</p>
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

        {{-- Filter Bar --}}
        <div class="card border-0 shadow-sm mb-4 d-print-none">
            <div class="card-body p-4">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-4">
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">Supplier</label>
                        <select class="form-select bg-light border-0 py-2 no-ts" wire:model.live="supplierId">
                            <option value="">Select a supplier...</option>
                            @foreach($suppliers as $sup)
                                <option value="{{ $sup['id'] }}" wire:key="sup-opt-{{ $sup['id'] }}">{{ $sup['name_en'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3">
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">As of Date</label>
                        <div wire:ignore>
                            <input type="text" id="sa-as-of-date"
                                   class="form-control bg-light border-0 py-2"
                                   placeholder="dd-mm-yyyy" value="{{ $asOfDate }}" />
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">Search Invoice</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0 text-muted"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control bg-light border-0 ps-0 py-2"
                                   placeholder="Invoice no..." wire:model.live.debounce.300ms="search" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($supplier)
            {{-- Summary Cards --}}
            <div class="row g-3 mb-4">
                <div class="col-md-2 col-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center py-3">
                            <div class="small text-muted fw-bold text-uppercase mb-1">Current</div>
                            <div class="fw-bold tabular-nums text-success">{{ number_format($summary['current'], 2) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center py-3">
                            <div class="small text-muted fw-bold text-uppercase mb-1">1–30 Days</div>
                            <div class="fw-bold tabular-nums text-warning-emphasis">{{ number_format($summary['days_1_30'], 2) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center py-3">
                            <div class="small text-muted fw-bold text-uppercase mb-1">31–60 Days</div>
                            <div class="fw-bold tabular-nums text-orange">{{ number_format($summary['days_31_60'], 2) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center py-3">
                            <div class="small text-muted fw-bold text-uppercase mb-1">61–90 Days</div>
                            <div class="fw-bold tabular-nums text-danger">{{ number_format($summary['days_61_90'], 2) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center py-3">
                            <div class="small text-muted fw-bold text-uppercase mb-1">91–120 Days</div>
                            <div class="fw-bold tabular-nums text-danger">{{ number_format($summary['days_91_120'], 2) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-6">
                    <div class="card border-0 shadow-sm h-100 border-start border-3 border-supplier">
                        <div class="card-body text-center py-3">
                            <div class="small text-muted fw-bold text-uppercase mb-1">Total Due</div>
                            <div class="fw-bold tabular-nums text-supplier fs-5">{{ number_format($summary['grand_total'], 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                {{-- Supplier Info Panel --}}
                <div class="col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-4">
                            <div class="text-center mb-4">
                                <div class="avatar-ui mx-auto mb-3">{{ substr($supplier->name_en, 0, 1) }}</div>
                                <h5 class="fw-bold mb-0">{{ $supplier->name_en }}</h5>
                                <code class="text-supplier small fw-bold">{{ $supplier->row_no }}</code>
                            </div>

                            @if($supplier->email || $supplier->phone)
                                <div class="mb-3 pb-3 border-bottom border-light">
                                    @if($supplier->email)
                                        <div class="d-flex align-items-center gap-2 small text-muted mb-1">
                                            <i class="bi bi-envelope text-supplier"></i>
                                            <span>{{ $supplier->email }}</span>
                                        </div>
                                    @endif
                                    @if($supplier->phone)
                                        <div class="d-flex align-items-center gap-2 small text-muted">
                                            <i class="bi bi-telephone text-supplier"></i>
                                            <span>{{ $supplier->phone }}</span>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            <div class="mt-3 text-center">
                                <label class="small text-uppercase text-muted d-block mb-1 fw-bold">As of {{ \Carbon\Carbon::parse($asOfDate)->format('d M Y') }}</label>
                                <h3 class="fw-bold text-supplier mb-0 tabular-nums">
                                    <small class="h6">SAR</small> {{ number_format($summary['grand_total'], 2) }}
                                </h3>
                                <span class="small text-muted">Total Outstanding</span>
                            </div>

                            {{-- Aging Bar --}}
                            @if($summary['grand_total'] > 0)
                                <div class="mt-4">
                                    <div class="progress" style="height: 8px; border-radius: 4px;">
                                        @php
                                            $gt = $summary['grand_total'];
                                            $pct = fn($v) => $gt > 0 ? round(($v / $gt) * 100, 1) : 0;
                                        @endphp
                                        @if($summary['current'] > 0)
                                            <div class="progress-bar bg-success" style="width: {{ $pct($summary['current']) }}%"></div>
                                        @endif
                                        @if($summary['days_1_30'] > 0)
                                            <div class="progress-bar bg-warning" style="width: {{ $pct($summary['days_1_30']) }}%"></div>
                                        @endif
                                        @if($summary['days_31_60'] > 0)
                                            <div class="progress-bar" style="width: {{ $pct($summary['days_31_60']) }}%; background:#f97316;"></div>
                                        @endif
                                        @if($summary['days_61_90'] > 0)
                                            <div class="progress-bar bg-danger" style="width: {{ $pct($summary['days_61_90']) }}%"></div>
                                        @endif
                                        @if($summary['days_91_120'] > 0)
                                            <div class="progress-bar bg-danger" style="width: {{ $pct($summary['days_91_120']) }}%; opacity:0.7;"></div>
                                        @endif
                                        @if($summary['days_over_120'] > 0)
                                            <div class="progress-bar" style="width: {{ $pct($summary['days_over_120']) }}%; background:#7f1d1d;"></div>
                                        @endif
                                    </div>
                                    <div class="d-flex justify-content-between mt-2 x-small text-muted">
                                        <span>Current</span><span>Over 120d</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Aging Table --}}
                <div class="col-xl-9">
                    <div class="card border-0 shadow-sm overflow-hidden">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold"><i class="bi bi-table me-2 text-supplier"></i>Aging Detail</h6>
                            <span class="badge bg-supplier-subtle text-supplier border border-supplier-subtle px-3 py-2">
                                {{ count($invoices) }} invoice(s) outstanding
                            </span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                <tr class="bg-light text-muted small text-uppercase fw-bold ls-1">
                                    <th class="ps-4 border-0">Invoice #</th>
                                    <th class="border-0">Date</th>
                                    <th class="border-0">Due Date</th>
                                    <th class="text-end border-0">Current</th>
                                    <th class="text-end border-0">1–30</th>
                                    <th class="text-end border-0">31–60</th>
                                    <th class="text-end border-0">61–90</th>
                                    <th class="text-end border-0">91–120</th>
                                    <th class="text-end border-0">>120</th>
                                    <th class="text-end pe-4 border-0">Total</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($invoices as $inv)
                                    <tr wire:key="inv-{{ $loop->index }}">
                                        <td class="ps-4">
                                            <span class="fw-medium">{{ $inv['invoice_no'] }}</span>
                                        </td>
                                        <td class="small text-muted">{{ $inv['invoice_date'] }}</td>
                                        <td>
                                            <span class="small {{ $inv['days_overdue'] > 0 ? 'text-danger fw-medium' : 'text-muted' }}">
                                                {{ $inv['due_date'] }}
                                            </span>
                                            @if($inv['days_overdue'] > 0)
                                                <br><span class="x-small text-danger">{{ $inv['days_overdue'] }}d overdue</span>
                                            @endif
                                        </td>
                                        <td class="text-end tabular-nums text-success">
                                            {{ $inv['current'] > 0 ? number_format($inv['current'], 2) : '—' }}
                                        </td>
                                        <td class="text-end tabular-nums text-warning-emphasis">
                                            {{ $inv['days_1_30'] > 0 ? number_format($inv['days_1_30'], 2) : '—' }}
                                        </td>
                                        <td class="text-end tabular-nums text-orange">
                                            {{ $inv['days_31_60'] > 0 ? number_format($inv['days_31_60'], 2) : '—' }}
                                        </td>
                                        <td class="text-end tabular-nums text-danger">
                                            {{ $inv['days_61_90'] > 0 ? number_format($inv['days_61_90'], 2) : '—' }}
                                        </td>
                                        <td class="text-end tabular-nums text-danger">
                                            {{ $inv['days_91_120'] > 0 ? number_format($inv['days_91_120'], 2) : '—' }}
                                        </td>
                                        <td class="text-end tabular-nums" style="color:#7f1d1d;">
                                            {{ $inv['days_over_120'] > 0 ? number_format($inv['days_over_120'], 2) : '—' }}
                                        </td>
                                        <td class="text-end pe-4 fw-bold tabular-nums text-supplier">
                                            {{ number_format($inv['total'], 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center py-5 text-muted small">
                                            <i class="bi bi-inbox h3 d-block mb-2"></i>
                                            No outstanding invoices found.
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                                <tfoot class="bg-light border-top">
                                <tr class="fw-bold">
                                    <td colspan="3" class="ps-4 py-3">Total</td>
                                    <td class="text-end tabular-nums text-success">{{ number_format($summary['current'], 2) }}</td>
                                    <td class="text-end tabular-nums text-warning-emphasis">{{ number_format($summary['days_1_30'], 2) }}</td>
                                    <td class="text-end tabular-nums text-orange">{{ number_format($summary['days_31_60'], 2) }}</td>
                                    <td class="text-end tabular-nums text-danger">{{ number_format($summary['days_61_90'], 2) }}</td>
                                    <td class="text-end tabular-nums text-danger">{{ number_format($summary['days_91_120'], 2) }}</td>
                                    <td class="text-end tabular-nums" style="color:#7f1d1d;">{{ number_format($summary['days_over_120'], 2) }}</td>
                                    <td class="text-end pe-4 text-supplier fs-6 tabular-nums">{{ number_format($summary['grand_total'], 2) }}</td>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        @else
            <div class="card border-0 shadow-sm text-center py-5">
                <div class="card-body">
                    <div class="bg-light rounded-circle p-4 d-inline-block mb-3">
                        <i class="bi bi-building h1 text-muted"></i>
                    </div>
                    <h5 class="fw-bold">No Supplier Selected</h5>
                    <p class="text-muted mx-auto" style="max-width: 300px;">Select a supplier and date above to view their outstanding aging report.</p>
                </div>
            </div>
        @endif

    </div>

    @script
    <script>
        (function () {
            var el = document.getElementById('sa-as-of-date');
            if (el && !el._flatpickr) {
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
        })();
    </script>
    @endscript

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
        .text-orange { color: #f97316 !important; }
        .avatar-ui {
            width: 56px; height: 56px; background: #fef3c7; color: var(--supplier-primary);
            display: flex; align-items: center; justify-content: center;
            border-radius: 12px; font-weight: 800; font-size: 1.5rem;
        }
        .ls-1 { letter-spacing: 0.05em; }
        .x-small { font-size: 0.7rem; text-transform: uppercase; }
        .tabular-nums { font-variant-numeric: tabular-nums; }
        .card { border-radius: 1rem; }
        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 0.25rem rgba(217, 119, 6, 0.1);
            border-color: var(--supplier-primary);
        }
        @media print {
            body { background: white !important; }
            .d-print-none { display: none !important; }
            .card { box-shadow: none !important; border: 1px solid #eee !important; }
        }
    </style>
</div>
