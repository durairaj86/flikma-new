@section('js', 'customer_statement')
@section('page-title', 'Customer Statement')

<div class="bg-white min-vh-100">
    <div class="container-fluid py-3 px-3">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center gap-3">
                <a href="#" class="btn btn-light btn-sm rounded-circle border">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0" style="font-size: 0.75rem;">
                            <li class="breadcrumb-item"><a href="#" class="text-decoration-none text-muted">Reports</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Customer Statement</li>
                        </ol>
                    </nav>
                    <h4 class="fw-bold text-dark mb-0">Customer Statement</h4>
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
                    <button class="btn btn-primary btn-sm px-3 dropdown-toggle fw-medium" type="button"
                            data-bs-toggle="dropdown">
                        Export As
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                        <li><a class="dropdown-item py-2 small" href="#" wire:click.prevent="exportPdf"><i
                                    class="bi bi-file-pdf text-danger me-2"></i> PDF</a></li>
                        <li><a class="dropdown-item py-2 small" href="#" wire:click.prevent="exportExcel"><i
                                    class="bi bi-file-earmark-excel text-success me-2"></i> Excel (XLSX)</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="border rounded-2 mb-4 bg-light-subtle p-3">
            <div class="row g-3 align-items-center" wire:ignore>
                <div class="col-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="small text-muted fw-medium">Customer:</span>
                        <div class="ms-2">
                            <select class="tom-select form-select-sm border-light-subtle shadow-sm"
                                    style="width: 250px;"
                                    wire:model.live="customerId" data-live-search="true"
                                    placeholder="Choose Customer...">
                                <option value="">Choose Customer...</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer['id'] }}">{{ $customer['name_en'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="col-4 d-flex align-items-center gap-2">

                    <div class="small text-muted fw-medium">Date&nbsp;Range:</div>
                    {{--<select class="form-control tom-select" id="presetDateRange">
                        <option value="">Custom</option>
                        <option value="today">Today</option>
                        <option value="yesterday">Yesterday</option>
                        <option value="thisMonth">This Month</option>
                        <option value="lastMonth">Last Month</option>
                        <option value="thisQuarter">This Quarter</option>
                        <option value="lastQuarter">Last Quarter</option>
                        <option value="thisYear">This Year</option>
                        <option value="lastYear">Last Year</option>
                    </select>--}}
                    <input type="date"
                           class="form-control <!--form-control-sm border-light-subtle shadow-sm--> datepicker"
                           wire:model.live="startDate" style="width: 150px;" id="fromDate"
                           value="{{ \Carbon\Carbon::today()->subMonth(3)->startOfMonth()->format('d-m-Y') }}">
                    <span class="small text-muted">to</span>
                    <input type="date"
                           class="form-control <!--form-control-sm border-light-subtle shadow-sm--> datepicker"
                           wire:model.live="endDate" style="width: 150px;" id="toDate"
                           value="{{ \Carbon\Carbon::today()->format('d-m-Y') }}">
                </div>

                <div class="col-4 ms-auto" style="width: 300px;">
                    <x-common.currencies-exchange width="auto" wireModel="currency"></x-common.currencies-exchange>
                </div>

                <div class="col-4 ms-auto" style="width: 300px;">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" class="form-control border-start-0 ps-0"
                               placeholder="Live Search..." wire:model.live.debounce.300ms="search">
                    </div>
                </div>
            </div>
        </div>

        <div class="report-table-wrapper">
            <livewire:report.finance.customer-statement-table/>
        </div>

        <div class="mt-5 pt-4 border-top text-center text-muted">
            <p class="small">** This is a computer-generated report and does not require a physical signature. **</p>
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

        @media print {
            .btn, .breadcrumb, .border, .bg-light-subtle {
                display: none !important;
            }

            body {
                background: white;
            }

            .container-fluid {
                padding: 0 !important;
            }
        }
    </style>
</div>
