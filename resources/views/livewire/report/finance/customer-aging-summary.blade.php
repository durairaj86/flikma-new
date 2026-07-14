@section('js', 'customer_aging_summary')
@section('page-title', 'Customer Aging Summary')

<div class="bg-white min-vh-100">
    <div class="container-fluid py-3 px-3">
        <div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
            <div class="d-flex align-items-center gap-3">
                <a href="#" class="btn btn-light btn-sm rounded-circle border">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0" style="font-size: 0.75rem;">
                            <li class="breadcrumb-item"><a href="#" class="text-decoration-none text-muted">Reports</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Customer Aging Summary</li>
                        </ol>
                    </nav>
                    <h4 class="fw-bold text-dark mb-0">Customer Aging Summary</h4>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                    <i class="bi bi-printer"></i>
                </button>
                <div class="dropdown">
                    <button class="btn btn-primary btn-sm px-3 dropdown-toggle fw-medium" type="button" data-bs-toggle="dropdown">
                        Export As
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                        <li><a class="dropdown-item py-2 small" href="#" onclick="casExportPdf(event)"><i class="bi bi-file-pdf text-danger me-2"></i> PDF</a></li>
                        <li><a class="dropdown-item py-2 small" href="#" wire:click.prevent="exportExcel"><i class="bi bi-file-earmark-excel text-success me-2"></i> Excel (XLSX)</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="border rounded-2 mb-4 bg-light-subtle p-3 d-print-none">
            <div class="row g-3 align-items-center">
                <div class="col-auto">
                    <div class="d-flex align-items-center gap-2">
                        <span class="small text-muted fw-medium">As of Date:</span>
                        <div wire:ignore>
                            <input type="text" id="cas-as-of-date"
                                   class="form-control form-control-sm border-light-subtle shadow-sm"
                                   placeholder="dd-mm-yyyy" value="{{ $asOfDate }}" style="width: 150px;">
                        </div>
                    </div>
                </div>

                <div class="col-auto">
                    <div class="d-flex align-items-center gap-2">
                        <span class="small text-muted fw-medium">Interval:</span>
                        <select class="form-select form-select-sm border-light-subtle shadow-sm no-ts"
                                wire:model.live="agingInterval" style="width: 110px;">
                            @foreach(\App\Livewire\Report\Finance\CustomerAgingAll::AGING_INTERVALS as $days)
                                <option value="{{ $days }}">{{ $days }} Days</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-auto">
                    <div class="d-flex align-items-center gap-2">
                        <span class="small text-muted fw-medium">Columns:</span>
                        <select class="form-select form-select-sm border-light-subtle shadow-sm no-ts"
                                wire:model.live="agingColumns" style="width: 90px;">
                            @foreach(\App\Livewire\Report\Finance\CustomerAgingAll::AGING_COLUMN_CHOICES as $n)
                                <option value="{{ $n }}">{{ $n }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-auto ms-auto" style="width: 300px;">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" class="form-control border-start-0 ps-0"
                               placeholder="Search..." wire:model.live.debounce.300ms="search">
                    </div>
                </div>
            </div>
        </div>

        <div class="report-table-wrapper d-print-none">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                    <tr>
                        <th colspan="{{ 3 + count($bucketDefs) }}" class="text-center">
                            Customer Aging Summary Report
                            <span class="fw-normal small">(as of {{ \Carbon\Carbon::parse($asOfDate)->format('d M Y') }}, {{ $agingInterval }}-day buckets)</span>
                        </th>
                    </tr>
                    <tr>
                        <th>Customer ID</th>
                        <th>Customer Name</th>
                        @foreach($bucketDefs as $def)
                            <th class="text-end" wire:key="th-{{ $def['key'] }}">{{ $def['label'] }}</th>
                        @endforeach
                        <th class="text-end">Total</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($customers as $cust)
                        <tr wire:key="cust-row-{{ $cust['customer_id'] }}">
                            <td>{{ $cust['customer_code'] }}</td>
                            <td>{{ $cust['customer_name'] }}</td>
                            @foreach($bucketDefs as $def)
                                <td class="text-end" wire:key="cell-{{ $cust['customer_id'] }}-{{ $def['key'] }}">{{ number_format($cust[$def['key']], 2) }}</td>
                            @endforeach
                            <td class="text-end fw-bold">{{ number_format($cust['total'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 3 + count($bucketDefs) }}" class="text-center">No customers with outstanding invoices found</td>
                        </tr>
                    @endforelse
                    </tbody>
                    <tfoot class="table-dark">
                    <tr>
                        <th colspan="2" class="text-end">Total</th>
                        @foreach($bucketDefs as $def)
                            <th class="text-end" wire:key="tf-{{ $def['key'] }}">{{ number_format($totals[$def['key']], 2) }}</th>
                        @endforeach
                        <th class="text-end">{{ number_format($totals['grand_total'], 2) }}</th>
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
                            @if(!empty($company->phone)) Phone: {{ $company->phone }} @endif
                            @if(!empty($company->email)) &nbsp;|&nbsp; {{ $company->email }} @endif
                        </div>
                        @if(!empty($company->vat_number))
                            <div class="stmt-sub">VAT No: {{ $company->vat_number }}</div>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="stmt-title">CUSTOMER AGING SUMMARY</div>
                        <div class="stmt-sub">As of: {{ \Carbon\Carbon::parse($asOfDate)->format('d M Y') }}</div>
                        <div class="stmt-sub">Aging: {{ $agingInterval }}-day buckets &times; {{ $agingColumns }}</div>
                        <div class="stmt-sub">Generated: {{ now()->format('d M Y H:i') }} &nbsp;|&nbsp; Currency: SAR</div>
                    </td>
                </tr>
            </table>

            <table class="stmt-table">
                <thead>
                <tr>
                    <th>Customer ID</th>
                    <th>Customer Name</th>
                    @foreach($bucketDefs as $def)
                        <th class="text-end">{{ $def['label'] }}</th>
                    @endforeach
                    <th class="text-end">Total</th>
                </tr>
                </thead>
                <tbody>
                @forelse($customers as $cust)
                    <tr>
                        <td>{{ $cust['customer_code'] }}</td>
                        <td>{{ $cust['customer_name'] }}</td>
                        @foreach($bucketDefs as $def)
                            <td class="text-end">{{ $cust[$def['key']] > 0 ? number_format($cust[$def['key']], 2) : '' }}</td>
                        @endforeach
                        <td class="text-end stmt-strong">{{ number_format($cust['total'], 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ 3 + count($bucketDefs) }}" class="text-center">No customers with outstanding invoices found</td>
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
                ** This is a computer-generated report and does not require a physical signature. **
            </div>
        </div>

        <div class="mt-5 pt-4 border-top text-center text-muted d-print-none">
            <p class="small">** This is a computer-generated report and does not require a physical signature. **</p>
        </div>
    </div>

    @script
    <script>
        (function () {
            var el = document.getElementById('cas-as-of-date');
            if (el && !el._flatpickr) {
                flatpickr(el, {
                    dateFormat:    'Y-m-d',
                    altInput:      true,
                    altFormat:     'd-m-Y',
                    allowInput:    true,
                    disableMobile: true,
                    defaultDate:   el.value || null,
                    onReady: function (selectedDates, dateStr, instance) {
                        if (instance.altInput) {
                            instance.altInput.style.width = '150px';
                        }
                    },
                    onChange: function (selectedDates, dateStr) {
                        if (dateStr) {
                            $wire.set('asOfDate', dateStr);
                        }
                    },
                });
            }
        })();

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
                jsPDF: { unit: 'in', format: 'a4', orientation: 'landscape' },
                pagebreak: { mode: ['avoid-all', 'css'] }
            };
            html2pdf().set(opt).from(clone).save();
        };
    </script>
    @endscript

    @include('includes.report-print-css')

    <style>
        /* Zoho Aesthetic: Clean, Soft, and Modern */
        body {
            background-color: #ffffff;
            color: #444;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }

        .breadcrumb-item + .breadcrumb-item::before {
            content: ">";
            font-size: 0.65rem;
            color: #999;
        }

        .form-control-sm, .form-select-sm {
            border-radius: 4px;
            border: 1px solid #d1d5db;
        }

        .form-control:focus, .form-select:focus {
            border-color: #008cd1;
            box-shadow: 0 0 0 2px rgba(0, 140, 209, 0.1);
        }

        .btn-primary {
            background-color: #008cd1;
            border-color: #008cd1;
        }

        .btn-primary:hover {
            background-color: #007bb8;
        }

        .btn-outline-secondary {
            border-color: #d1d5db;
            color: #444;
        }

        .btn-outline-secondary:hover {
            background-color: #f9fafb;
            border-color: #c1c5cb;
            color: #222;
        }

        .bg-light-subtle {
            background-color: #f8fafc !important;
        }

        .report-table-wrapper table {
            border-collapse: collapse;
        }

        @media print {
            .d-print-none { display: none !important; }
            body { background: white; }
            .container-fluid { padding: 0 !important; }
        }
    </style>
</div>
