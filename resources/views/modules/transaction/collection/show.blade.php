@section('page-title', __('Collection Details'))
<x-app-layout>
    @php
        $paidIntoAccount = $collection->account ? \App\Models\Finance\Account\Account::find($collection->account) : null;
        $statusMap = [
            1 => ['label' => __('Draft'), 'badge' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle'],
            2 => ['label' => __('Approved'), 'badge' => 'bg-success-subtle text-success-emphasis border border-success-subtle'],
            3 => ['label' => __('Cancelled'), 'badge' => 'bg-danger-subtle text-danger-emphasis border border-danger-subtle'],
        ];
        $status = $statusMap[$collection->status] ?? ['label' => __('Unknown'), 'badge' => 'bg-secondary-subtle text-secondary-emphasis'];
    @endphp

    <div class="min-vh-100 bg-light py-4">
        <div class="container-fluid px-lg-5">

            {{-- Header --}}
            <div class="row align-items-center mb-4 d-print-none">
                <div class="col-md-6">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1" style="font-size: 0.8rem;">
                            <li class="breadcrumb-item"><a href="{{ route('transaction.collections.index') }}" class="text-decoration-none text-muted">{{ __('Collections') }}</a></li>
                            <li class="breadcrumb-item active">{{ $collection->row_no }}</li>
                        </ol>
                    </nav>
                    <div class="d-flex align-items-center gap-2">
                        <h1 class="h3 fw-bold text-slate-900 mb-0">{{ $collection->row_no }}</h1>
                        <span class="badge {{ $status['badge'] }} px-3 py-2">{{ $status['label'] }}</span>
                    </div>
                    <p class="text-muted small mb-0 mt-1">{{ __('Collection from') }} {{ $collection->customer->name_en ?? __('N/A') }}</p>
                </div>
                <div class="col-md-6 text-md-end mt-3 mt-md-0">
                    <div class="btn-group shadow-sm">
                        <button class="btn btn-white border border-end-0" onclick="window.print()">
                            <i class="bi bi-printer me-2"></i>{{ __('Print') }}
                        </button>
                        <div class="btn-group">
                            <button class="btn btn-white border dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="bi bi-download me-2"></i>{{ __('Export') }}
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow">
                                <li><a class="dropdown-item py-2" href="#" onclick="collectionExportPdf(event)"><i class="bi bi-file-pdf text-danger me-2"></i>{{ __('PDF Document') }}</a></li>
                            </ul>
                        </div>
                        @if($collection->status == 1)
                            <a href="{{ route('transaction.collections.edit', $collection->id) }}" class="btn btn-primary">
                                <i class="bi bi-pencil me-2"></i>{{ __('Edit') }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            @if($collection->disapproval_reason)
                <div class="alert alert-danger d-print-none"><strong>{{ __('Disapproval Reason') }}:</strong> {{ $collection->disapproval_reason }}</div>
            @endif

            {{-- Summary Cards --}}
            <div class="row g-3 mb-4 d-print-none">
                <div class="col-md col-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center py-3">
                            <div class="small text-muted fw-bold text-uppercase mb-1">{{ __('Collection Date') }}</div>
                            <div class="fw-bold">{{ $collection->collection_date }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md col-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center py-3">
                            <div class="small text-muted fw-bold text-uppercase mb-1">{{ __('Sub Total') }}</div>
                            <div class="fw-bold tabular-nums">{{ number_format($collection->sub_total, 2) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md col-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center py-3">
                            <div class="small text-muted fw-bold text-uppercase mb-1">{{ __('Tax Total') }}</div>
                            <div class="fw-bold tabular-nums">{{ number_format($collection->tax_total, 2) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md col-6">
                    <div class="card border-0 shadow-sm h-100 border-start border-3 border-customer">
                        <div class="card-body text-center py-3">
                            <div class="small text-muted fw-bold text-uppercase mb-1">{{ __('Grand Total') }}</div>
                            <div class="fw-bold tabular-nums text-customer fs-5">{{ number_format($collection->grand_total, 2) }} {{ strtoupper($collection->currency) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Detail Cards --}}
            <div class="row g-3 mb-4 d-print-none">
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white py-3"><h6 class="mb-0 fw-bold"><i class="bi bi-receipt me-2 text-customer"></i>{{ __('Collection Information') }}</h6></div>
                        <div class="card-body">
                            <table class="table table-borderless table-sm mb-0">
                                <tr><th class="text-muted fw-medium" width="45%">{{ __('Collection Number') }}</th><td class="fw-semibold">{{ $collection->row_no }}</td></tr>
                                <tr><th class="text-muted fw-medium">{{ __('Collection Date') }}</th><td>{{ $collection->collection_date }}</td></tr>
                                <tr><th class="text-muted fw-medium">{{ __('Paid Into') }}</th><td>{{ $paidIntoAccount->name ?? __('N/A') }}</td></tr>
                                <tr><th class="text-muted fw-medium">{{ __('Reference Number') }}</th><td>{{ $collection->reference_no ?? __('N/A') }}</td></tr>
                                <tr><th class="text-muted fw-medium">{{ __('Currency') }}</th><td>{{ strtoupper($collection->currency) }}</td></tr>
                                <tr><th class="text-muted fw-medium">{{ __('Currency Rate') }}</th><td>{{ number_format($collection->currency_rate, 4) }}</td></tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white py-3"><h6 class="mb-0 fw-bold"><i class="bi bi-person me-2 text-customer"></i>{{ __('Customer & Job') }}</h6></div>
                        <div class="card-body">
                            <table class="table table-borderless table-sm mb-0">
                                <tr><th class="text-muted fw-medium" width="45%">{{ __('Customer') }}</th><td class="fw-semibold">{{ $collection->customer->name_en ?? __('N/A') }}</td></tr>
                                <tr><th class="text-muted fw-medium">{{ __('Customer Address') }}</th><td>{{ $collection->customer->address1_en ?? __('N/A') }}</td></tr>
                                <tr><th class="text-muted fw-medium">{{ __('Customer Contact') }}</th><td>{{ $collection->customer->phone ?? __('N/A') }}</td></tr>
                                <tr><th class="text-muted fw-medium">{{ __('Job Number') }}</th><td>{{ $collection->job_no ?? __('N/A') }}</td></tr>
                                @if($collection->bank_charges)
                                    <tr><th class="text-muted fw-medium">{{ __('Bank Charges') }}</th><td>{{ number_format($collection->bank_charges, 2) }}</td></tr>
                                @endif
                                @if($collection->other_charges)
                                    <tr><th class="text-muted fw-medium">{{ __('Other Charges') }}</th><td>{{ number_format($collection->other_charges, 2) }}</td></tr>
                                @endif
                                @if($collection->currency != 'SAR')
                                    <tr><th class="text-muted fw-medium">{{ __('Base Currency Total') }}</th><td>{{ number_format($collection->base_grand_total, 2) }} SAR</td></tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Invoices Collected --}}
            <div class="card border-0 shadow-sm overflow-hidden mb-4 d-print-none">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-table me-2 text-customer"></i>{{ __('Invoices Collected') }}</h6>
                    <span class="badge bg-customer-subtle text-customer border border-customer-subtle px-3 py-2">
                        {{ count($collection->collectionInvoices) }} {{ Str::plural('invoice', count($collection->collectionInvoices)) }}
                    </span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                        <tr class="bg-light text-muted small text-uppercase fw-bold ls-1">
                            <th class="ps-4 border-0">{{ __('Invoice Number') }}</th>
                            <th class="border-0">{{ __('Invoice Date') }}</th>
                            <th class="border-0">{{ __('Due Date') }}</th>
                            <th class="text-end border-0">{{ __('Invoice Total') }}</th>
                            <th class="text-end pe-4 border-0">{{ __('Collection Amount') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($collection->collectionInvoices as $collectionInvoice)
                            <tr>
                                <td class="ps-4 fw-medium">{{ $collectionInvoice->customerInvoice->row_no ?? __('N/A') }}</td>
                                <td class="small text-muted">{{ $collectionInvoice->customerInvoice->invoice_date ?? __('N/A') }}</td>
                                <td class="small text-muted">{{ $collectionInvoice->customerInvoice->due_at ?? __('N/A') }}</td>
                                <td class="text-end tabular-nums">{{ number_format($collectionInvoice->customerInvoice->grand_total ?? 0, 2) }}</td>
                                <td class="text-end pe-4 fw-bold tabular-nums text-customer">{{ number_format($collectionInvoice->amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted small">
                                    <i class="bi bi-inbox h3 d-block mb-2"></i>{{ __('No invoices found.') }}
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                        <tfoot class="bg-light border-top">
                        <tr class="fw-bold">
                            <td colspan="4" class="ps-4 py-3 text-end">{{ __('Total') }}</td>
                            <td class="text-end pe-4 text-customer fs-6 tabular-nums">{{ number_format($collection->grand_total, 2) }} {{ strtoupper($collection->currency) }}</td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            @if($collection->notes)
                <div class="card border-0 shadow-sm mb-4 d-print-none">
                    <div class="card-header bg-white py-3"><h6 class="mb-0 fw-bold"><i class="bi bi-sticky me-2 text-customer"></i>{{ __('Notes') }}</h6></div>
                    <div class="card-body">{{ $collection->notes }}</div>
                </div>
            @endif

            {{-- Audit Info --}}
            <div class="card border-0 shadow-sm mb-4 d-print-none">
                <div class="card-header bg-white py-3"><h6 class="mb-0 fw-bold"><i class="bi bi-clock-history me-2 text-customer"></i>{{ __('Audit Information') }}</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="small text-muted text-uppercase fw-bold mb-1">{{ __('Created By') }}</div>
                            <div class="fw-semibold">{{ $collection->createdBy->name ?? __('N/A') }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="small text-muted text-uppercase fw-bold mb-1">{{ __('Created At') }}</div>
                            <div class="fw-semibold">{{ $collection->created_at ? $collection->created_at->format('d-m-Y H:i:s') : __('N/A') }}</div>
                        </div>
                        @if($collection->status == 2)
                            <div class="col-md-3">
                                <div class="small text-muted text-uppercase fw-bold mb-1">{{ __('Approved By') }}</div>
                                <div class="fw-semibold">{{ $collection->approvedBy->name ?? __('N/A') }}</div>
                            </div>
                            <div class="col-md-3">
                                <div class="small text-muted text-uppercase fw-bold mb-1">{{ __('Approved At') }}</div>
                                <div class="fw-semibold">{{ $collection->approved_at ?? __('N/A') }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="d-print-none">
                <a href="{{ route('transaction.collections.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> {{ __('Back to Collections') }}
                </a>
            </div>

            {{-- Bank-statement style layout: used for Print and PDF export only --}}
            <div id="collection-show-print" class="stmt-print d-none d-print-block"
                 data-pdf-filename="{{ $collection->row_no }}.pdf">

                <table class="stmt-meta">
                    <tr>
                        <td>
                            <div class="stmt-company">{{ authUserCompany()->name ?? config('app.name') }}</div>
                        </td>
                        <td class="text-end">
                            <div class="stmt-title">{{ __('COLLECTION VOUCHER') }}</div>
                            <div class="stmt-sub">{{ $collection->row_no }}</div>
                            <div class="stmt-sub">{{ __('Date') }}: {{ $collection->collection_date }}</div>
                            <div class="stmt-sub">{{ __('Generated') }}: {{ now()->format('d M Y H:i') }} &nbsp;|&nbsp; {{ __('Currency') }}: {{ strtoupper($collection->currency) }}</div>
                        </td>
                    </tr>
                </table>

                <table class="stmt-box" style="width:100%;">
                    <tr>
                        <td style="width:50%;">
                            <strong>{{ __('Customer') }}:</strong> {{ $collection->customer->name_en ?? __('N/A') }}<br>
                            <strong>{{ __('Address') }}:</strong> {{ $collection->customer->address1_en ?? __('N/A') }}<br>
                            <strong>{{ __('Job Number') }}:</strong> {{ $collection->job_no ?? __('N/A') }}
                        </td>
                        <td style="width:50%;">
                            <strong>{{ __('Paid Into') }}:</strong> {{ $paidIntoAccount->name ?? __('N/A') }}<br>
                            <strong>{{ __('Reference No') }}:</strong> {{ $collection->reference_no ?? __('N/A') }}<br>
                            <strong>{{ __('Status') }}:</strong> {{ $status['label'] }}
                        </td>
                    </tr>
                </table>

                <table class="stmt-table">
                    <thead>
                    <tr>
                        <th>{{ __('Invoice Number') }}</th>
                        <th>{{ __('Invoice Date') }}</th>
                        <th>{{ __('Due Date') }}</th>
                        <th class="text-end">{{ __('Invoice Total') }}</th>
                        <th class="text-end">{{ __('Collection Amount') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($collection->collectionInvoices as $collectionInvoice)
                        <tr>
                            <td>{{ $collectionInvoice->customerInvoice->row_no ?? __('N/A') }}</td>
                            <td>{{ $collectionInvoice->customerInvoice->invoice_date ?? __('N/A') }}</td>
                            <td>{{ $collectionInvoice->customerInvoice->due_at ?? __('N/A') }}</td>
                            <td class="text-end">{{ number_format($collectionInvoice->customerInvoice->grand_total ?? 0, 2) }}</td>
                            <td class="text-end">{{ number_format($collectionInvoice->amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center">{{ __('No invoices found') }}</td></tr>
                    @endforelse
                    </tbody>
                    <tfoot>
                    <tr class="stmt-strong">
                        <td colspan="3">&nbsp;</td>
                        <td class="text-end">{{ __('Sub Total') }}:</td>
                        <td class="text-end">{{ number_format($collection->sub_total, 2) }}</td>
                    </tr>
                    <tr class="stmt-strong">
                        <td colspan="3">&nbsp;</td>
                        <td class="text-end">{{ __('Tax Total') }}:</td>
                        <td class="text-end">{{ number_format($collection->tax_total, 2) }}</td>
                    </tr>
                    @if($collection->bank_charges)
                        <tr class="stmt-strong">
                            <td colspan="3">&nbsp;</td>
                            <td class="text-end">{{ __('Bank Charges') }}:</td>
                            <td class="text-end">{{ number_format($collection->bank_charges, 2) }}</td>
                        </tr>
                    @endif
                    @if($collection->other_charges)
                        <tr class="stmt-strong">
                            <td colspan="3">&nbsp;</td>
                            <td class="text-end">{{ __('Other Charges') }}:</td>
                            <td class="text-end">{{ number_format($collection->other_charges, 2) }}</td>
                        </tr>
                    @endif
                    <tr class="stmt-strong">
                        <td colspan="3">&nbsp;</td>
                        <td class="text-end">{{ __('Grand Total') }}:</td>
                        <td class="text-end">{{ number_format($collection->grand_total, 2) }} {{ strtoupper($collection->currency) }}</td>
                    </tr>
                    </tfoot>
                </table>

                @if($collection->notes)
                    <div class="stmt-footnote"><strong>{{ __('Notes') }}:</strong> {{ $collection->notes }}</div>
                @endif

                <table class="stmt-signatures" style="width:100%;">
                    <tr>
                        <td style="width:33%;">{{ __('Prepared By') }}: {{ $collection->createdBy->name ?? __('N/A') }}</td>
                        <td style="width:33%;">{{ __('Approved By') }}: {{ $collection->approvedBy->name ?? __('N/A') }}</td>
                        <td style="width:33%;">{{ __('Received By') }}: ____________________</td>
                    </tr>
                </table>

                <div class="stmt-footnote">{{ __('This is a computer-generated document and does not require a physical signature.') }}</div>
            </div>

        </div>
    </div>

    <script>
        window.collectionExportPdf = function (e) {
            e.preventDefault();
            var area = document.getElementById('collection-show-print');
            if (!area) return;
            var clone = area.cloneNode(true);
            clone.classList.remove('d-none');
            clone.style.padding = '10px';
            var opt = {
                margin: 0.4,
                filename: area.dataset.pdfFilename || 'Collection.pdf',
                html2canvas: { scale: 2, useCORS: true },
                jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' },
                pagebreak: { mode: ['avoid-all', 'css'] }
            };
            html2pdf().set(opt).from(clone).save();
        };
    </script>

    @include('includes.report-print-css')

    <style>
        :root {
            --customer-primary: #4f46e5;
            --customer-dark: #3730a3;
        }
        .text-customer { color: var(--customer-primary) !important; }
        .border-customer { border-color: var(--customer-primary) !important; }
        .bg-customer-subtle { background-color: #eef2ff !important; }
        .border-customer-subtle { border-color: #c7d2fe !important; }
        .ls-1 { letter-spacing: 0.05em; }
        .tabular-nums { font-variant-numeric: tabular-nums; }
        .card { border-radius: 1rem; }
        @media print {
            .d-print-none { display: none !important; }
        }
    </style>
</x-app-layout>
