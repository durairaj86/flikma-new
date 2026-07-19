@section('js', 'waybill_report')
@section('page-title', 'Waybill Report')

<div class="provisional-wrapper min-vh-100 bg-light py-4">
    <div class="container-fluid px-lg-5">

        {{-- Page Header --}}
        <div class="row align-items-center mb-4 d-print-none">
            <div class="col-md-6">
                <h1 class="h3 fw-bold text-slate-900 mb-1">Waybill Report</h1>
                <p class="text-muted small mb-0">Waybills issued, with delivery and status detail</p>
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
                            <li><a class="dropdown-item py-2" href="#" onclick="reportExportPdf(event, 'wbr-print', {orientation: 'landscape'})"><i class="bi bi-file-pdf text-danger me-2"></i>PDF Document</a></li>
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
                    <div class="col-lg-2 col-md-4">
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">From Date</label>
                        <input type="hidden" id="wbr-start-date-hidden" wire:model="startDate" value="{{ $startDate }}" />
                        <input type="text" id="wbr-start-date"
                               class="form-control bg-light border-0 py-2"
                               placeholder="dd-mm-yyyy"
                               value="{{ $startDate }}" />
                    </div>
                    <div class="col-lg-2 col-md-4">
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">To Date</label>
                        <input type="hidden" id="wbr-end-date-hidden" wire:model="endDate" value="{{ $endDate }}" />
                        <input type="text" id="wbr-end-date"
                               class="form-control bg-light border-0 py-2"
                               placeholder="dd-mm-yyyy"
                               value="{{ $endDate }}" />
                    </div>
                    <div class="col-lg-3 col-md-4">
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">Customer</label>
                        <select class="form-select bg-light border-0 py-2 no-ts" wire:model="customerId">
                            <option value="">All Customers</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer['id'] }}" @selected($customerId == $customer['id'])>
                                    {{ $customer['row_no'] }} — {{ $customer['name_en'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-4">
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">Status</label>
                        <select class="form-select bg-light border-0 py-2 no-ts" wire:model="status">
                            <option value="">All Status</option>
                            <option value="pending" @selected($status=='pending')>Pending</option>
                            <option value="in_transit" @selected($status=='in_transit')>In Transit</option>
                            <option value="delivered" @selected($status=='delivered')>Delivered</option>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-4">
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
        @if($totals['total'] > 0)
        <div class="row g-3 mb-4 d-print-none">
            <div class="col-lg col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3 text-center">
                        <div class="small text-muted fw-bold text-uppercase mb-1 ls-1">Total Waybills</div>
                        <div class="h5 fw-bold text-secondary mb-0 tabular-nums">{{ $totals['total'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3 text-center">
                        <div class="small text-muted fw-bold text-uppercase mb-1 ls-1">Pending</div>
                        <div class="h5 fw-bold text-warning mb-0 tabular-nums">{{ $totals['pending'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3 text-center">
                        <div class="small text-muted fw-bold text-uppercase mb-1 ls-1">In Transit</div>
                        <div class="h5 fw-bold text-primary mb-0 tabular-nums">{{ $totals['in_transit'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3 text-center">
                        <div class="small text-muted fw-bold text-uppercase mb-1 ls-1">Delivered</div>
                        <div class="h5 fw-bold text-success mb-0 tabular-nums">{{ $totals['delivered'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Table --}}
        <div class="card border-0 shadow-sm overflow-hidden d-print-none">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-truck me-2 text-pr"></i>
                    Waybill Detail
                </h6>
                <span class="badge bg-pr-subtle text-pr border border-pr-subtle px-3 py-2">
                    {{ $totals['total'] }} {{ Str::plural('Waybill', $totals['total']) }}
                </span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                    <tr class="bg-light text-muted small text-uppercase fw-bold ls-1">
                        <th class="ps-4 border-0">Waybill No</th>
                        <th class="border-0">Date</th>
                        <th class="border-0">Job</th>
                        <th class="border-0">Customer</th>
                        <th class="border-0">Delivery Date</th>
                        <th class="border-0">Delivery Address</th>
                        <th class="border-0">Contact</th>
                        <th class="text-end pe-4 border-0">Status</th>
                    </tr>
                    </thead>
                    <tbody class="border-top-0">
                    @forelse($waybills as $wb)
                        <tr wire:key="wbr-{{ $wb->id }}">
                            <td class="ps-4">
                                <span class="fw-bold text-dark">{{ $wb->row_no ?? $wb->waybill_no }}</span>
                            </td>
                            <td class="small text-muted">{{ $wb->waybill_date }}</td>
                            <td class="small">{{ $wb->job->row_no ?? '—' }}</td>
                            <td class="small">{{ $wb->customer->name_en ?? '—' }}</td>
                            <td class="small text-muted">{{ $wb->delivery_date ?? '—' }}</td>
                            <td class="small">{{ $wb->delivery_address ?? '—' }}</td>
                            <td class="small">
                                {{ $wb->contact_person ?? '—' }}
                                @if($wb->contact_phone)
                                    <div class="text-muted x-small">{{ $wb->contact_phone }}</div>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                @php
                                    $statusCls = [
                                        'pending' => 'bg-warning-subtle text-warning',
                                        'in_transit' => 'bg-primary-subtle text-primary',
                                        'delivered' => 'bg-success-subtle text-success',
                                    ][$wb->status] ?? 'bg-secondary-subtle text-secondary';
                                @endphp
                                <span class="badge rounded-pill px-2 py-1 {{ $statusCls }}">
                                    {{ ucfirst(str_replace('_', ' ', $wb->status)) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <div class="bg-light rounded-circle p-4 d-inline-block mb-3">
                                    <i class="bi bi-truck h2 text-muted"></i>
                                </div>
                                <div class="small">No waybills found for the selected period.</div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Bank-statement style layout: used for Print and PDF export only --}}
        <div id="wbr-print" class="stmt-print d-none d-print-block"
             data-pdf-filename="WaybillReport-{{ $startDate ?? '' }}-{{ $endDate ?? '' }}.pdf">

            <table class="stmt-meta">
                <tr>
                    <td>
                        <div class="stmt-company">{{ optional(authUserCompany())->name ?? config('app.name') }}</div>
                    </td>
                    <td class="text-end">
                        <div class="stmt-title">WAYBILL REPORT</div>
                        <div class="stmt-sub">Period: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} — {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</div>
                        <div class="stmt-sub">Generated: {{ now()->format('d M Y H:i') }}</div>
                    </td>
                </tr>
            </table>

            <table class="stmt-table">
                <thead>
                <tr>
                    <th>Waybill No</th>
                    <th>Date</th>
                    <th>Job</th>
                    <th>Customer</th>
                    <th>Delivery Date</th>
                    <th>Delivery Address</th>
                    <th>Contact</th>
                    <th class="text-end">Status</th>
                </tr>
                </thead>
                <tbody>
                @forelse($waybills as $wb)
                    <tr>
                        <td>{{ $wb->row_no ?? $wb->waybill_no }}</td>
                        <td>{{ $wb->waybill_date }}</td>
                        <td>{{ $wb->job->row_no ?? '—' }}</td>
                        <td>{{ $wb->customer->name_en ?? '—' }}</td>
                        <td>{{ $wb->delivery_date ?? '—' }}</td>
                        <td>{{ $wb->delivery_address ?? '—' }}</td>
                        <td>{{ $wb->contact_person ?? '—' }} {{ $wb->contact_phone ? '(' . $wb->contact_phone . ')' : '' }}</td>
                        <td class="text-end">{{ ucfirst(str_replace('_', ' ', $wb->status)) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center">No waybills found for the selected period.</td></tr>
                @endforelse
                </tbody>
                <tfoot>
                <tr class="stmt-strong">
                    <td colspan="8">{{ $totals['total'] }} Waybill(s)</td>
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
                var startEl = document.getElementById('wbr-start-date');
                var endEl   = document.getElementById('wbr-end-date');

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
                            syncHidden('wbr-start-date-hidden', dateStr);
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
                            syncHidden('wbr-end-date-hidden', dateStr);
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
            --car-primary: #0ea5e9;
            --car-dark:    #0369a1;
            --car-light:   #f0f9ff;
        }

        .btn-pr { background-color: var(--car-primary); border-color: var(--car-primary); color: #fff; }
        .btn-pr:hover { background-color: var(--car-dark); border-color: var(--car-dark); color: #fff; }
        .text-pr { color: var(--car-primary) !important; }
        .bg-pr-subtle { background-color: #e0f2fe !important; }
        .border-pr-subtle { border-color: #bae6fd !important; }

        .bg-warning-subtle { background-color: #fef3c7 !important; }
        .bg-primary-subtle { background-color: #e0f2fe !important; }
        .bg-success-subtle { background-color: #dcfce7 !important; }
        .bg-secondary-subtle { background-color: #f1f5f9 !important; }

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
