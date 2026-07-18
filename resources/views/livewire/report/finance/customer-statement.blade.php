@section('js', 'customer_statement')
@section('page-title', 'Customer Statement')

<div class="statement-wrapper min-vh-100 bg-light py-4">
    <div class="container-fluid px-lg-5">
        {{-- Debug info --}}
        @if(isset($debug) && $debug)
            <div class="alert alert-info d-print-none mb-3 py-1 px-2 small">
                ID: {{ $customerId }} | Start: {{ $startDate }} | End: {{ $endDate }} | Customers: {{ count($customers) }}
            </div>
        @endif

        <div class="row align-items-center mb-4 d-print-none">
            <div class="col-md-6">
                <h1 class="h3 fw-bold text-slate-900 mb-1">Customer Statement</h1>
                <p class="text-muted small mb-0">Manage and track account transaction history</p>
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
                            <li><a class="dropdown-item py-2" href="#" onclick="csExportPdf(event)"><i class="bi bi-file-pdf text-danger me-2"></i>PDF Document</a></li>
                            <li><a class="dropdown-item py-2" href="#" wire:click.prevent="exportExcel"><i class="bi bi-file-excel text-success me-2"></i>Excel Sheet</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4 d-print-none" id="list-filter">
            <div class="card-body p-4">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-4">
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">Customer</label>
                        <select class="tom-select bg-light border-0 no-ts" wire:model.live="customerId" data-live-search="true">
                            <option value="">Select a customer...</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer['id'] }}" wire:key="cust-opt-{{ $customer['id'] }}">{{ $customer['name_en'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-4">
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">From Date</label>
                        <div wire:ignore>
                            <input type="text" id="cs-start-date"
                                   class="form-control bg-light border-0 py-2"
                                   placeholder="dd-mm-yyyy"
                                   value="{{ $startDate }}" />
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4">
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">To Date</label>
                        <div wire:ignore>
                            <input type="text" id="cs-end-date"
                                   class="form-control bg-light border-0 py-2"
                                   placeholder="dd-mm-yyyy"
                                   value="{{ $endDate }}" />
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-4">
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-primary fw-bold py-2 flex-grow-1 shadow-sm" onclick="csApplyFilter()" wire:loading.attr="disabled">
                                <i class="bi bi-filter-left me-2"></i>
                                <span wire:loading.remove>Generate</span>
                                <span wire:loading><span class="spinner-border spinner-border-sm me-1"></span>Loading...</span>
                            </button>
                            <button type="button" class="btn btn-outline-secondary border-0 bg-light py-2 px-3" wire:click="resetFilter">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($selectedCustomer)
            <div class="row g-4 d-print-none" id="statement-area">
                <div class="col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-4">
                            <div class="text-center mb-4">
                                <div class="avatar-ui mx-auto mb-3">{{ substr($selectedCustomer->name, 0, 1) }}</div>
                                <h5 class="fw-bold mb-0">{{ $selectedCustomer->name }}</h5>
                                <code class="text-primary small fw-bold">{{ $selectedCustomer->code }}</code>
                            </div>

                            <div class="space-y-3 py-3 border-top border-bottom border-light">
                                <div class="d-flex justify-content-between">
                                    <span class="small text-muted">Opening:</span>
                                    <span class="small fw-bold text-dark">{{ number_format($openingBalance, 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="small text-muted">Debits (+):</span>
                                    <span class="small fw-bold text-danger">{{ number_format($totalDebit, 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="small text-muted">Credits (-):</span>
                                    <span class="small fw-bold text-success">{{ number_format($totalCredit, 2) }}</span>
                                </div>
                            </div>

                            <div class="mt-4 text-center">
                                <label class="small text-uppercase text-muted d-block mb-1 fw-bold">Current Balance</label>
                                <h3 class="fw-bold text-primary mb-0 tabular-nums">
                                    <small class="h6">SAR</small> {{ number_format($closingBalance, 2) }}
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-9">
                    <div class="card border-0 shadow-sm overflow-hidden">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h6 class="mb-0 fw-bold"><i class="bi bi-journal-text me-2 text-primary"></i>Transaction Ledger</h6>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-light text-dark border px-3 py-2">
                                    Period: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} &mdash; {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
                                </span>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2">Currency: SAR</span>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                <tr class="bg-light text-muted small text-uppercase fw-bold ls-1">
                                    <th class="ps-4 border-0">Date</th>
                                    <th class="border-0">Reference</th>
                                    <th class="border-0">Description</th>
                                    <th class="text-end border-0">Debit</th>
                                    <th class="text-end border-0">Credit</th>
                                    <th class="text-end pe-4 border-0">Balance</th>
                                </tr>
                                </thead>
                                <tbody class="border-top-0">
                                <tr class="bg-light-blue fw-bold">
                                    <td class="ps-4 py-3" colspan="3">Balance Brought Forward</td>
                                    <td class="text-end"></td>
                                    <td class="text-end"></td>
                                    <td class="text-end pe-4 tabular-nums">{{ number_format($openingBalance, 2) }}</td>
                                </tr>

                                @php $running = $openingBalance; @endphp
                                @forelse($transactions as $txn)
                                    @php $running += (float)$txn->debit - (float)$txn->credit; @endphp
                                    <tr wire:key="txn-{{ $loop->index }}">
                                        <td class="ps-4 small text-muted">{{ $txn->display_date }}</td>
                                        <td>
                                            <span class="fw-medium d-block">{{ $txn->reference }}</span>
                                            <span class="x-small text-muted uppercase">{{ $txn->type }}</span>
                                        </td>
                                        <td class="small">{{ $txn->description }}</td>
                                        <td class="text-end tabular-nums">{{ (float)$txn->debit > 0 ? number_format((float)$txn->debit, 2) : '—' }}</td>
                                        <td class="text-end tabular-nums text-danger">{{ (float)$txn->credit > 0 ? number_format((float)$txn->credit, 2) : '—' }}</td>
                                        <td class="text-end pe-4 fw-bold tabular-nums">{{ number_format($running, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted small italic">No transactions found for the selected period.</td>
                                    </tr>
                                @endforelse
                                </tbody>
                                <tfoot class="bg-light border-top-2">
                                <tr class="fw-bold">
                                    <td colspan="3" class="ps-4 py-3">Closing Totals</td>
                                    <td class="text-end tabular-nums text-dark">{{ number_format($totalDebit, 2) }}</td>
                                    <td class="text-end tabular-nums text-danger">{{ number_format($totalCredit, 2) }}</td>
                                    <td class="text-end pe-4 text-primary fs-5 tabular-nums">{{ number_format($closingBalance, 2) }}</td>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <div class="mt-4 p-3 bg-white border rounded shadow-sm">
                        <div class="row text-center text-muted x-small">
                            <div class="col-md-4">Prepared By: _________________</div>
                            <div class="col-md-4">Verified By: _________________</div>
                            <div class="col-md-4">Customer Signature: _________________</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bank-statement style layout: used for Print and PDF export only --}}
            <div id="statement-print" class="d-none d-print-block"
                 data-pdf-filename="CustomerStatement-{{ $selectedCustomer->code }}-{{ $startDate }}_{{ $endDate }}.pdf">

                <table class="stmt-meta">
                    <tr>
                        <td>
                            <div class="stmt-company">{{ $company->name ?? config('app.name') }}</div>
                            @if(!empty($company->address) || !empty($company->city))
                                <div class="stmt-sub">{{ trim(($company->address ?? '') . ' ' . ($company->city ?? '')) }}</div>
                            @endif
                            <div class="stmt-sub">
                                @if(!empty($company->phone)) Phone: {{ $company->phone }} @endif
                                @if(!empty($company->email)) &nbsp;|&nbsp; {{ $company->email }} @endif
                            </div>
                            @if(!empty($company->vat_number))
                                <div class="stmt-sub">VAT No: {{ $company->vat_number }}</div>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="stmt-title">STATEMENT OF ACCOUNT</div>
                            <div class="stmt-sub">Period: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</div>
                            <div class="stmt-sub">Generated: {{ now()->format('d M Y H:i') }}</div>
                            <div class="stmt-sub">Currency: SAR</div>
                        </td>
                    </tr>
                </table>

                <table class="stmt-meta stmt-box">
                    <tr>
                        <td>
                            <div class="stmt-sub" style="text-transform: uppercase;">Account Holder</div>
                            <div class="stmt-strong">{{ $selectedCustomer->name }} ({{ $selectedCustomer->code }})</div>
                            @if($selectedCustomer->address !== 'N/A')
                                <div class="stmt-sub">{{ $selectedCustomer->address }}</div>
                            @endif
                            <div class="stmt-sub">
                                @if($selectedCustomer->email) {{ $selectedCustomer->email }} @endif
                                @if($selectedCustomer->phone) &nbsp;|&nbsp; {{ $selectedCustomer->phone }} @endif
                            </div>
                        </td>
                        <td class="text-end">
                            <table class="stmt-summary">
                                <tr><td>Opening Balance</td><td class="text-end">{{ number_format($openingBalance, 2) }}</td></tr>
                                <tr><td>Total Debits</td><td class="text-end">{{ number_format($totalDebit, 2) }}</td></tr>
                                <tr><td>Total Credits</td><td class="text-end">{{ number_format($totalCredit, 2) }}</td></tr>
                                <tr class="stmt-strong"><td>Closing Balance</td><td class="text-end">{{ number_format($closingBalance, 2) }}</td></tr>
                            </table>
                        </td>
                    </tr>
                </table>

                <table class="stmt-table">
                    <thead>
                    <tr>
                        <th style="width: 12%;">Date</th>
                        <th style="width: 15%;">Reference</th>
                        <th>Description</th>
                        <th class="text-end" style="width: 13%;">Debit</th>
                        <th class="text-end" style="width: 13%;">Credit</th>
                        <th class="text-end" style="width: 14%;">Balance</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr class="stmt-strong">
                        <td colspan="5">Balance Brought Forward</td>
                        <td class="text-end">{{ number_format($openingBalance, 2) }}</td>
                    </tr>
                    @php $printRunning = $openingBalance; @endphp
                    @forelse($transactions as $txn)
                        @php $printRunning += (float)$txn->debit - (float)$txn->credit; @endphp
                        <tr>
                            <td>{{ $txn->display_date }}</td>
                            <td>{{ $txn->reference }}</td>
                            <td>{{ $txn->description }}</td>
                            <td class="text-end">{{ (float)$txn->debit > 0 ? number_format((float)$txn->debit, 2) : '' }}</td>
                            <td class="text-end">{{ (float)$txn->credit > 0 ? number_format((float)$txn->credit, 2) : '' }}</td>
                            <td class="text-end">{{ number_format($printRunning, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No transactions found for the selected period.</td>
                        </tr>
                    @endforelse
                    </tbody>
                    <tfoot>
                    <tr class="stmt-strong">
                        <td colspan="3">Closing Totals</td>
                        <td class="text-end">{{ number_format($totalDebit, 2) }}</td>
                        <td class="text-end">{{ number_format($totalCredit, 2) }}</td>
                        <td class="text-end">{{ number_format($closingBalance, 2) }}</td>
                    </tr>
                    </tfoot>
                </table>

                <div class="stmt-footnote">
                    This is a system generated statement. Please report any discrepancy within 15 days of receipt.
                </div>

                <table class="stmt-meta stmt-signatures">
                    <tr>
                        <td>Prepared By: _________________</td>
                        <td class="text-center">Verified By: _________________</td>
                        <td class="text-end">Customer Signature: _________________</td>
                    </tr>
                </table>
            </div>
        @else
            <div class="card border-0 shadow-sm text-center py-5">
                <div class="card-body">
                    <div class="bg-light rounded-circle p-4 d-inline-block mb-3">
                        <i class="bi bi-people h1 text-muted"></i>
                    </div>
                    <h5 class="fw-bold">No Customer Selected</h5>
                    <p class="text-muted mx-auto" style="max-width: 300px;">Please use the filters above to select a customer and date range to view the statement.</p>
                </div>
            </div>
        @endif
    </div>

    @script
    <script>
        (function () {
            function initFlatpickr() {
                [['cs-start-date', 'startDate'], ['cs-end-date', 'endDate']].forEach(function (pair) {
                    var el = document.getElementById(pair[0]);
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
                                $wire.set(pair[1], dateStr);
                            }
                        },
                    });
                });
            }

            initFlatpickr();

            // Livewire's wire:model.live morph replaces the <select> markup
            // on every commit (customer change, Generate, Reset), which
            // destroys TomSelect's injected wrapper and reverts it to a
            // plain unstyled select. `Livewire.hook('commit', ...)`'s
            // succeed callback fires too early — before the DOM morph is
            // actually applied — so re-initializing there is a no-op; the
            // morph runs afterward and destroys it anyway. `morph.updated`
            // fires per-element once the patch for that element is really
            // applied, so re-init there instead, scoped to #list-filter.
            Livewire.hook('morph.updated', function (data) {
                if (data.el && data.el.id === 'list-filter') {
                    // Deferred to the next frame: morphdom patches this
                    // element's descendants (including the <select> itself)
                    // synchronously AFTER this hook fires for the container,
                    // so re-initializing immediately here would run before
                    // the <select> is actually replaced and get clobbered a
                    // moment later. requestAnimationFrame runs after that
                    // whole synchronous patch pass completes.
                    requestAnimationFrame(function () {
                        initTomSelectForm($('#list-filter'));
                    });
                }
            });

            // Generate: push whatever is in the pickers, then run the filter in one request.
            window.csApplyFilter = function () {
                var s = document.getElementById('cs-start-date');
                var e = document.getElementById('cs-end-date');
                if (s && s.value) $wire.set('startDate', s.value, false);
                if (e && e.value) $wire.set('endDate', e.value, false);
                $wire.call('applyFilter');
            };

            // Reset: the server chose new dates — reflect them in the pickers.
            $wire.on('statement-dates-reset', function (event) {
                var s = document.getElementById('cs-start-date');
                var e = document.getElementById('cs-end-date');
                if (s && s._flatpickr) s._flatpickr.setDate(event.startDate, false);
                if (e && e._flatpickr) e._flatpickr.setDate(event.endDate, false);
            });

            window.csExportPdf = function (e) {
                e.preventDefault();
                var area = document.getElementById('statement-print');
                if (!area) {
                    alert('Please select a customer first.');
                    return;
                }
                var clone = area.cloneNode(true);
                clone.classList.remove('d-none');
                clone.style.padding = '10px';
                var opt = {
                    margin: 0.4,
                    filename: area.dataset.pdfFilename || 'CustomerStatement.pdf',
                    html2canvas: { scale: 2, useCORS: true },
                    jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' },
                    pagebreak: { mode: ['avoid-all', 'css'] }
                };
                html2pdf().set(opt).from(clone).save();
            };
        })();
    </script>
    @endscript

    <style>
        :root {
            --bs-primary: #4f46e5;
            --bs-primary-hover: #4338ca;
            --slate-900: #0f172a;
        }

        .btn-primary { background-color: #4f46e5; border-color: #4f46e5; }
        .btn-primary:hover { background-color: #4338ca; }
        .text-primary { color: var(--bs-primary) !important; }
        .bg-primary-subtle { background-color: #eef2ff !important; }

        .avatar-ui {
            width: 56px; height: 56px; background: #eef2ff; color: var(--bs-primary);
            display: flex; align-items: center; justify-content: center;
            border-radius: 12px; font-weight: 800; font-size: 1.5rem;
        }

        .ls-1 { letter-spacing: 0.05em; }
        .x-small { font-size: 0.7rem; text-transform: uppercase; }
        .tabular-nums { font-variant-numeric: tabular-nums; }
        .bg-light-blue { background-color: #f8faff; }
        .space-y-3 > * + * { margin-top: 0.75rem; }

        .card { border-radius: 1rem; }
        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 0.25rem rgba(79, 70, 229, 0.1);
            border-color: var(--bs-primary);
        }

        /* Bank-statement layout (print + PDF export). Styles live outside
           @media print so html2pdf can render the same markup. */
        #statement-print {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            line-height: 1.5;
            color: #000;
            background: #fff;
        }
        #statement-print table { width: 100%; border-collapse: collapse; }
        #statement-print .text-end { text-align: right; }
        #statement-print .text-center { text-align: center; }
        #statement-print .stmt-company { font-size: 16px; font-weight: 700; letter-spacing: 0.02em; }
        #statement-print .stmt-title { font-size: 14px; font-weight: 700; letter-spacing: 0.08em; }
        #statement-print .stmt-sub { font-size: 10px; color: #333; }
        #statement-print .stmt-strong { font-weight: 700; }
        #statement-print .stmt-meta td { vertical-align: top; padding: 2px 0; }
        #statement-print .stmt-box {
            margin-top: 10px;
            border: 1px solid #000;
        }
        #statement-print .stmt-box > tbody > tr > td { padding: 8px 10px; }
        #statement-print .stmt-summary { width: auto; margin-left: auto; }
        #statement-print .stmt-summary td { padding: 1px 0 1px 24px; font-size: 11px; }
        #statement-print .stmt-table { margin-top: 12px; }
        #statement-print .stmt-table th {
            border: 1px solid #000;
            background: #efefef;
            padding: 5px 8px;
            font-size: 10px;
            text-transform: uppercase;
            text-align: left;
        }
        #statement-print .stmt-table th.text-end { text-align: right; }
        #statement-print .stmt-table td { border: 1px solid #999; padding: 5px 8px; }
        #statement-print .stmt-table tfoot td { background: #efefef; border: 1px solid #000; }
        #statement-print .stmt-footnote { margin-top: 10px; font-size: 9px; color: #444; font-style: italic; }
        #statement-print .stmt-signatures { margin-top: 36px; font-size: 10px; }

        @media print {
            @page { size: A4 portrait; margin: 12mm; }
            body { background: white !important; }
            body, html { overflow: visible !important; height: auto !important; }
            .d-print-none { display: none !important; }
            .statement-wrapper { padding: 0 !important; background: white !important; }
            .container-fluid { padding: 0 !important; }
            #statement-print .stmt-table thead { display: table-header-group; }
            #statement-print .stmt-table tr { page-break-inside: avoid; }
        }
    </style>
</div>
