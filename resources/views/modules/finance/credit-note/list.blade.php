@section('page-title','Credit Note')
@section('page-sub-title','Manage credit notes and adjustments')
@section('js','credit_note')
<x-app-layout>
    <div class="bg-light py-4">
        <style>
            :root{
                --cn_primary: #0b6aa0;
                --cn_card_bg: #ffffff;
                --cn_radius: 12px;
                --cn_shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
            }
            body { background: #f8fafc; }
            .cn-card {
                background: var(--cn_card_bg);
                border-radius: var(--cn_radius);
                box-shadow: var(--cn_shadow);
                border: 1px solid rgba(0,0,0,0.04);
                padding: 1rem 1.25rem;
            }
            .cn-kpi {
                background: var(--cn_card_bg);
                border-radius: var(--cn_radius);
                box-shadow: var(--cn_shadow);
                border: 1px solid rgba(0,0,0,0.04);
                padding: .9rem 1rem;
                transition: box-shadow .2s;
            }
            .cn-kpi:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
            .cn-kpi .kpi-label { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: #64748b; }
            .cn-kpi .kpi-value { font-size: 1.35rem; font-weight: 700; color: #0f172a; line-height: 1.2; }
            .cn-kpi .kpi-sub { font-size: .72rem; color: #94a3b8; }
            .cn-icon-circle { width: 38px; height: 38px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }
            .cn-table th { font-size: .74rem; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; color: #64748b; background: #f8fafc; border-bottom-width: 1px; }
            .cn-table td { font-size: .82rem; vertical-align: middle; color: #1e293b; }
            .cn-filter-bar {
                background: var(--cn_card_bg);
                border-radius: var(--cn_radius);
                box-shadow: var(--cn_shadow);
                border: 1px solid rgba(0,0,0,0.04);
                padding: .6rem 1rem;
            }

            /* Clean flat table — no floating row cards, just a thin divider
               between rows, matching the redesigned invoice list pages. */
            #dataTable {
                border-collapse: collapse;
            }

            #dataTable thead th {
                background-color: #fff;
                color: #6c757d;
                font-weight: 600;
                border-bottom: 1px solid #e9ecef;
                padding: 0.65rem 1rem;
                white-space: nowrap;
            }

            #dataTable tbody td {
                padding: 0.65rem 1rem;
                border-bottom: 1px solid #f1f3f5;
                vertical-align: middle;
            }

            #dataTable tbody tr:hover td {
                background-color: #fafbfc;
            }

            #dataTable tbody tr:last-child td {
                border-bottom: none;
            }

            /* Two-line cell convention: bold primary line, muted small caption */
            .cell-primary {
                font-weight: 600;
                color: #212529;
                line-height: 1.3;
            }

            .cell-secondary {
                font-size: 0.75rem;
                color: #868e96;
                line-height: 1.3;
            }
        </style>

        <div class="container-fluid px-lg-5">

            {{-- Header --}}
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                <div>
                    <h4 class="fw-bold mb-0" style="color:#0f172a;">Credit Notes</h4>
                    <p class="text-muted mb-0 small">Credit note adjustments and refunds</p>
                </div>
                <div class="d-flex gap-2 mt-2 mt-sm-0">
                    <button class="btn btn-outline-primary btn-sm rounded-pill px-3" id="filter-box">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    <button class="btn btn-primary btn-sm rounded-pill px-3" id="new">
                        <i class="bi bi-plus-lg me-1"></i> New Credit Note
                    </button>
                </div>
            </div>

            {{-- KPI Cards --}}
            <div class="row g-3 mb-3">
                <div class="col-lg-3 col-md-6">
                    <div class="cn-kpi d-flex align-items-center justify-content-between">
                        <div>
                            <div class="kpi-label">Total Credit Notes</div>
                            <div class="kpi-value" id="allCount">0</div>
                            <div class="kpi-sub">All statuses</div>
                        </div>
                        <div class="cn-icon-circle" style="background:rgba(11,106,160,0.1);color:#0b6aa0;">
                            <i class="bi bi-file-earmark-text"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="cn-kpi d-flex align-items-center justify-content-between">
                        <div>
                            <div class="kpi-label">Total Amount</div>
                            <div class="kpi-value" id="overall_sales">0.00</div>
                            <div class="kpi-sub">SAR - Grand total</div>
                        </div>
                        <div class="cn-icon-circle" style="background:rgba(22,163,74,0.1);color:#16a34a;">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4">
                    <div class="cn-kpi text-center">
                        <div class="kpi-label">Draft</div>
                        <div class="kpi-value text-warning" id="draftCount">0</div>
                        <div class="kpi-sub"><span id="draftTotal">0.00</span> SAR</div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4">
                    <div class="cn-kpi text-center">
                        <div class="kpi-label">Approved</div>
                        <div class="kpi-value text-success" id="approvedCount">0</div>
                        <div class="kpi-sub"><span id="approvedTotal">0.00</span> SAR</div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4">
                    <div class="cn-kpi text-center">
                        <div class="kpi-label">Cancelled</div>
                        <div class="kpi-value text-danger" id="cancelledCount">0</div>
                        <div class="kpi-sub"><span id="cancelledTotal">0.00</span> SAR</div>
                    </div>
                </div>
            </div>

            {{-- Filter Panel --}}
            <div id="filterPanel" class="cn-card mb-3 d-none">
                <form id="list-filter" method="post" novalidate="novalidate">
                    @csrf
                    <div class="row g-3 align-items-end">
                        <div class="col-md-2">
                            <label class="form-label fw-medium small">Date Range</label>
                            <select class="form-select form-select-sm" id="presetDateRange">
                                <option value="">Custom</option>
                                <option value="today">Today</option>
                                <option value="yesterday">Yesterday</option>
                                <option value="thisMonth">This Month</option>
                                <option value="lastMonth">Last Month</option>
                                <option value="thisQuarter">This Quarter</option>
                                <option value="lastQuarter">Last Quarter</option>
                                <option value="thisYear">This Year</option>
                                <option value="lastYear">Last Year</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-medium small">From Date</label>
                            <input type="date" class="form-control form-control-sm from-date" id="filter-from-date" name="filter-from-date"
                                   value="{{ \Carbon\Carbon::today()->subMonth(6)->startOfMonth()->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-medium small">To Date</label>
                            <input type="date" class="form-control form-control-sm to-date" id="filter-to-date" name="filter-to-date"
                                   value="{{ \Carbon\Carbon::today()->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-medium small">Customer</label>
                            <x-common.customers multiple></x-common.customers>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-medium small">Invoice</label>
                            <select class="form-select form-select-sm" id="filter-invoice" name="invoice">
                                <option value="">All Invoices</option>
                                @foreach(\App\Models\Finance\CustomerInvoice\CustomerInvoice::where('status', 3)->get() as $invoice)
                                    <option value="{{ encodeId($invoice->id) }}">{{ $invoice->row_no ?? $invoice->id }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="text-center mt-3">
                        <button class="btn btn-primary btn-sm px-4 rounded-pill" type="button" id="apply-filter">
                            <i class="bi bi-search me-1"></i> Search
                        </button>
                    </div>
                </form>
            </div>

            {{-- Status Tabs --}}
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="align-items-center flex-shrink-0">
                    <div class="gap-4">
                        <ul class="nav status-tabs align-items-center border-bottom mb-0" id="listTabs" role="tablist">
                            <li class="nav-item me-2">
                                <button class="nav-link px-3 py-2 d-flex align-items-center justify-content-between status-btn active"
                                        data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="all">
                                    <span><i class="bi bi-collection me-1"></i> All -</span>
                                    <span class="status-count ms-2" id="tabAllCount">0</span>
                                </button>
                            </li>
                            <li class="nav-item me-2">
                                <button class="nav-link py-2 d-flex align-items-center justify-content-between status-btn"
                                        data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="draft">
                                    <span><i class="bi bi-clock me-1"></i> Draft -</span>
                                    <span class="status-count ms-2" id="tabDraftCount">0</span>
                                </button>
                            </li>
                            <li class="nav-item me-2">
                                <button class="nav-link py-2 d-flex align-items-center justify-content-between status-btn"
                                        data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="approved">
                                    <span><i class="bi bi-check-circle me-1"></i> Approved -</span>
                                    <span class="status-count ms-2" id="tabApprovedCount">0</span>
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link py-2 d-flex align-items-center justify-content-between status-btn"
                                        data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="cancelled">
                                    <span><i class="bi bi-x-circle me-1"></i> Cancelled -</span>
                                    <span class="status-count ms-2" id="tabCancelledCount">0</span>
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="pt-2">
                    <div class="search-box position-relative" style="min-width:200px;">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" id="customSearch" class="form-control form-control-sm rounded-pill ps-5" placeholder="Search..." aria-label="Search...">
                    </div>
                </div>
            </div>

            {{-- Table --}}
            <div class="cn-card p-0">
                <table class="table align-middle mb-0 dataTable" id="dataTable">
                    <thead>
                        <tr class="text-muted text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.03em;">
                            <th>Credit Note #</th>
                            <th>Customer</th>
                            <th>Job</th>
                            <th>Invoice</th>
                            <th class="text-end">Excl. VAT</th>
                            <th class="text-end">Tax</th>
                            <th class="text-end">Total</th>
                            <th>Date</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

        </div>
    </div>

    @include('modules.email.send-email')
    @include('modules.finance.credit-note.credit-note-view')

    {{-- Print frame --}}
    <iframe id="print-frame" style="display:none;"></iframe>
</x-app-layout>
