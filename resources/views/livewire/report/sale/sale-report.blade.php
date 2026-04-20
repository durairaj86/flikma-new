@section('js','sale_report')
@section('page-title','Sales Report')
<div>
    <main class="gmail-content bg-white py-3 px-3">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center gap-3">
                <a href="#" class="btn btn-light btn-sm rounded-circle border">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0" style="font-size: 0.75rem;">
                            <li class="breadcrumb-item"><a href="#" class="text-decoration-none text-muted">Reports</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Sales Report</li>
                        </ol>
                    </nav>
                    <h4 class="fw-bold text-dark mb-0">Sales Report</h4>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary btn-sm px-3 fw-medium">
                    <i class="bi bi-gear me-1"></i> Customize Report
                </button>
                <div class="vr mx-1"></div>
                <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                    <i class="bi bi-printer"></i>
                </button>
                <div class="dropdown">
                    <button class="btn btn-primary btn-sm px-3 dropdown-toggle fw-medium" type="button" data-bs-toggle="dropdown">
                        Export As
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                        <li><a class="dropdown-item py-2 small" href="#"><i class="bi bi-file-pdf text-danger me-2"></i> PDF</a></li>
                        <li><a class="dropdown-item py-2 small" href="#"><i class="bi bi-file-earmark-excel text-success me-2"></i> Excel (XLSX)</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row g-4 mb-3 align-items-stretch">
            <!-- Overall Summary Card -->
            <div class="col-12 col-md-4">
                <div class="p-3 rounded-3 bg-light shadow-sm h-100">
                    <div class="d-flex flex-column border-bottom border-primary-subtle pb-3 mb-3">
                        <div class="d-flex align-items-center justify-content-between w-100 mb-2">
                            <div class="fw-bold text-primary"><i class="bi bi-file-earmark-bar-graph fs-5 text-primary-emphasis ms-2"></i> Overall Summary</div>
                            <div class="text-end">
                                <div class="fs-3 fw-bolder text-primary-emphasis">
                                    {{ number_format($summary['total_grand'], 2) }} SAR
                                </div>
                                <span class="text-secondary-emphasis text-xsmall fw-medium d-block text-uppercase">Total Sales</span>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-start">
                        <div class="d-flex flex-column align-items-center">
                            <div class="fs-4 fw-bold text-body-emphasis">{{ $summary['total_count'] }}</div>
                            <span class="text-secondary-emphasis text-xxsmall text-uppercase fw-medium mt-1 d-block text-center">Invoices</span>
                        </div>
                        <div class="d-flex flex-column align-items-center">
                            <div class="fs-4 fw-bold text-success">{{ $summary['approved_count'] }}</div>
                            <span class="text-success text-xxsmall text-uppercase fw-medium mt-1 d-block text-center">Approved</span>
                        </div>
                        <div class="d-flex flex-column align-items-center">
                            <div class="fs-4 fw-bold text-warning">{{ $summary['draft_count'] }}</div>
                            <span class="text-warning text-xxsmall text-uppercase fw-medium mt-1 d-block text-center">Draft</span>
                        </div>
                        <div class="d-flex flex-column align-items-center">
                            <div class="fs-4 fw-bold text-danger">{{ $summary['cancelled_count'] }}</div>
                            <span class="text-danger text-xxsmall text-uppercase fw-medium mt-1 d-block text-center">Cancelled</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Approved Sales Card -->
            <div class="col-12 col-md-4">
                <div class="p-3 rounded-3 bg-light shadow-sm h-100">
                    <div class="d-flex flex-column border-bottom border-success-subtle pb-3 mb-3">
                        <div class="d-flex align-items-center justify-content-between w-100 mb-2">
                            <div class="fw-bold text-success-emphasis"><i class="bi bi-check-circle fs-5 text-success ms-2"></i> Approved Sales</div>
                            <div class="text-end">
                                <div class="fs-3 fw-bolder text-success">
                                    {{ number_format($summary['approved_grand'], 2) }} SAR
                                </div>
                                <span class="text-secondary-emphasis text-xsmall fw-medium d-block text-uppercase">Total Approved</span>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-start gap-4">
                        <div>
                            <div class="fs-5 fw-bold text-body-emphasis">
                                {{ number_format($summary['approved_amount'], 2) }}
                            </div>
                            <span class="text-secondary-emphasis text-small text-uppercase fw-medium mt-1 d-block">Excl. Tax</span>
                        </div>
                        <div>
                            <div class="fs-5 fw-bold text-body-emphasis">
                                {{ number_format($summary['approved_tax'], 2) }}
                            </div>
                            <span class="text-secondary-emphasis text-small text-uppercase fw-medium mt-1 d-block">Tax</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Draft Sales Card -->
            <div class="col-12 col-md-4">
                <div class="p-3 rounded-3 bg-light shadow-sm h-100">
                    <div class="d-flex flex-column border-bottom pb-3 mb-3">
                        <div class="d-flex align-items-center justify-content-between w-100 mb-2">
                            <div class="fw-bold"><i class="bi bi-pencil-square fs-5 ms-2"></i> Draft Sales</div>
                            <div class="text-end">
                                <div class="fs-3 fw-bolder">
                                    {{ number_format($summary['draft_grand'], 2) }} SAR
                                </div>
                                <span class="text-xsmall fw-medium d-block text-uppercase">Total Draft</span>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-start gap-4">
                        <div>
                            <div class="fs-5 fw-bold">
                                {{ number_format($summary['draft_amount'], 2) }}
                            </div>
                            <span class="text-small text-uppercase fw-medium mt-1 d-block">Excl. Tax</span>
                        </div>
                        <div>
                            <div class="fs-5 fw-bold">
                                {{ number_format($summary['draft_tax'], 2) }}
                            </div>
                            <span class="text-small text-uppercase fw-medium mt-1 d-block">Tax</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters and Table -->
        <div class="border rounded-2 mb-4 bg-light-subtle p-3">
            <div class="row g-3 align-items-center">
                <div class="col-auto d-flex align-items-center gap-2">
                    <span class="small text-muted fw-medium">Date Range:</span>
                    <input type="date" class="form-control form-control-sm border-light-subtle shadow-sm"
                           wire:model.live="startDate" style="width: 150px;">
                    <span class="small text-muted">to</span>
                    <input type="date" class="form-control form-control-sm border-light-subtle shadow-sm"
                           wire:model.live="endDate" style="width: 150px;">
                </div>

                <div class="col-auto">
                    <div class="d-flex align-items-center gap-2">
                        <span class="small text-muted fw-medium">Customer:</span>
                        <select class="form-select form-select-sm border-light-subtle shadow-sm"
                                wire:model.live="customerId" style="width: 250px;">
                            <option value="">All Customers</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer['id'] }}">{{ $customer['name_en'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-auto">
                    <div class="d-flex align-items-center gap-2">
                        <span class="small text-muted fw-medium">Status:</span>
                        <select class="form-select form-select-sm border-light-subtle shadow-sm"
                                wire:model.live="status" style="width: 150px;">
                            <option value="">All Statuses</option>
                            <option value="1">Draft</option>
                            <option value="3">Approved</option>
                            <option value="4">Cancelled</option>
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

        <div class="shadow bdr-r-10 py-3 flex-grow-1">
            <div class="">
                <livewire:report.sale.sale-report-table/>
            </div>
        </div>
    </main>

    <!-- Date Range Modal -->
    <div class="modal fade" id="dateRangeModal" tabindex="-1" aria-labelledby="dateRangeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="dateRangeModalLabel">Select Date Range</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="startDate" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="startDate" wire:model.live="startDate">
                    </div>
                    <div class="mb-3">
                        <label for="endDate" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="endDate" wire:model.live="endDate">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

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

        /* Input Styling */
        .form-control-sm, .form-select-sm {
            border-radius: 4px;
            border: 1px solid #d1d5db;
        }

        .form-control:focus, .form-select:focus {
            border-color: #008cd1;
            box-shadow: 0 0 0 2px rgba(0, 140, 209, 0.1);
        }

        /* Buttons */
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

        /* Table custom behavior for Zoho feel */
        .report-table-wrapper table {
            border-collapse: collapse;
        }

        /* Text sizes */
        .text-xxsmall {
            font-size: 0.65rem;
        }

        .text-xsmall {
            font-size: 0.75rem;
        }

        @media print {
            .btn, .breadcrumb, .border, .bg-light-subtle {
                display: none !important;
            }
            body { background: white; }
            .container-fluid { padding: 0 !important; }
        }
    </style>
</div>
