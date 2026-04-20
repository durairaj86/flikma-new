{{-- Put inside your layout --}}
<div class="container-fluid py-4">
    {{-- Top header: breadcrumb + contract title + created/modified + actions --}}
    <div class="row mb-3 d-none">
        <div class="col-12">
            <nav aria-label="breadcrumb" class="mb-2">
                <ol class="breadcrumb small mb-0">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item"><a href="#">G H Contracts</a></li>
                    <li class="breadcrumb-item active" aria-current="page">G H CONTRACTS</li>
                </ol>
            </nav>

            <div class="d-flex align-items-start justify-content-between bg-white rounded-3 p-3 shadow-sm">
                <div>
                    <div class="mb-2 small text-muted">Created: <strong class="text-dark">{{ $created ?? '29-04-2019' }}</strong></div>
                    <h5 class="mb-0">{{ $contract_code ?? 'CMAIR-20_1918*B' }}</h5>
                    <div class="small text-muted mt-1">Last modified: <strong class="text-dark">{{ $modified ?? 'TOPCON 15-01-2019' }}</strong></div>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-outline-secondary btn-sm">Save</button>
                    <button class="btn btn-primary btn-sm">Save & Next <i class="bi bi-arrow-right ms-1"></i></button>
                </div>
            </div>
        </div>
    </div>

    {{-- Main content area: left tabs + right services --}}
    <div class="row">
        <!-- Left Tabs -->
        <div class="col-md-3 col-lg-2">
            <div class="bg-white rounded-3 shadow-sm h-100 p-2">
                <ul class="nav nav-pills flex-column gap-2" role="tablist" aria-orientation="vertical">
                    <li class="nav-item">
                        <button class="nav-link active d-flex align-items-center" data-bs-toggle="pill" data-bs-target="#tab-general" type="button">
                            <i class="bi bi-check-circle-fill text-success me-2"></i> Basic Info
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link d-flex align-items-center" data-bs-toggle="pill" data-bs-target="#tab-comprehensive" type="button">
                            <i class="bi bi-check-circle-fill text-success me-2"></i> Address
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link d-flex align-items-center" data-bs-toggle="pill" data-bs-target="#tab-services" type="button">
                            <i class="bi bi-check-circle-fill text-success me-2"></i> Contact
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link d-flex align-items-center" data-bs-toggle="pill" data-bs-target="#tab-exception" type="button">
                            <i class="bi bi-circle me-2"></i> Exception Handling
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link d-flex align-items-center" data-bs-toggle="pill" data-bs-target="#tab-cancellation" type="button">
                            <i class="bi bi-circle me-2"></i> Cancellation
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link d-flex align-items-center" data-bs-toggle="pill" data-bs-target="#tab-delay" type="button">
                            <i class="bi bi-circle me-2"></i> Delay
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link d-flex align-items-center" data-bs-toggle="pill" data-bs-target="#tab-other" type="button">
                            <i class="bi bi-circle me-2"></i> Other
                        </button>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Right Content -->
        <div class="col-md-9 col-lg-10">
            <div class="tab-content">

                {{-- Placeholder tabs (not implemented) --}}
                <div class="tab-pane fade" id="tab-general"></div>
                <div class="tab-pane fade" id="tab-comprehensive"></div>

                {{-- Services Tab (active) --}}
                <div class="tab-pane fade show active" id="tab-services" role="tabpanel">
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-body p-3">
                            {{-- Table-like header (inline inputs) --}}
                            <div class="table-responsive mb-3">
                                <table class="table table-borderless align-middle mb-0">
                                    <thead class="small text-muted">
                                    <tr>
                                        <th style="min-width:180px">Aircraft name</th>
                                        <th style="min-width:220px">Service name</th>
                                        <th style="min-width:180px">Service Rate Group</th>
                                        <th style="min-width:140px">Rate type</th>
                                        <th style="min-width:120px">Charges</th>
                                        <th style="width:100px" class="text-center">Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    {{-- example row --}}
                                    <tr class="border-top">
                                        <td class="pe-2">
                                            <input type="text" class="form-control form-control-sm" placeholder="Aircraft name">
                                        </td>
                                        <td class="pe-2">
                                            <input type="text" class="form-control form-control-sm" placeholder="BS - Baggage service">
                                        </td>
                                        <td class="pe-2">
                                            <input type="text" class="form-control form-control-sm" placeholder="Value">
                                        </td>
                                        <td class="pe-2">
                                            <input type="text" class="form-control form-control-sm" placeholder="Value">
                                        </td>
                                        <td class="pe-2">
                                            <input type="text" class="form-control form-control-sm" placeholder="Value">
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-link btn-sm text-secondary p-0" title="Edit"><i class="bi bi-pencil"></i></button>
                                            <button class="btn btn-link btn-sm text-danger p-0 ms-2" title="Delete"><i class="bi bi-trash"></i></button>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>

                            {{-- Form area: two-column rows with compact controls --}}
                            <form>
                                <div class="row g-3">
                                    <div class="col-md-6 col-lg-4">
                                        <label class="form-label small">Service Name</label>
                                        <input type="text" class="form-control form-control-sm" placeholder="Service Name">
                                    </div>
                                    <div class="col-md-6 col-lg-4">
                                        <label class="form-label small">Service Rate Group</label>
                                        <select class="form-select form-select-sm">
                                            <option value="">Service Rate Group</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 col-lg-4">
                                        <label class="form-label small">UMO</label>
                                        <select class="form-select form-select-sm">
                                            <option>UMO</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6 col-lg-4">
                                        <label class="form-label small">Rate types</label>
                                        <select class="form-select form-select-sm">
                                            <option value="">Select</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6 col-lg-4">
                                        <label class="form-label small">Charges</label>
                                        <input type="text" class="form-control form-control-sm" placeholder="Charges">
                                    </div>

                                    <div class="col-md-6 col-lg-4">
                                        <label class="form-label small">Stand By Rate Type</label>
                                        <input type="text" class="form-control form-control-sm" placeholder="Stand By Rate Type">
                                    </div>

                                    <div class="col-md-6 col-lg-4">
                                        <label class="form-label small">Cancellation Rate Type</label>
                                        <input type="text" class="form-control form-control-sm" placeholder="Cancellation Rate Type">
                                    </div>

                                    <div class="col-md-6 col-lg-4">
                                        <label class="form-label small">Night handling Charges</label>
                                        <input type="text" class="form-control form-control-sm" placeholder="Night handling Charges">
                                    </div>

                                    <div class="col-md-6 col-lg-4">
                                        <label class="form-label small">Stand By Rate Charges</label>
                                        <input type="text" class="form-control form-control-sm" placeholder="Stand By Rate Charges">
                                    </div>

                                    {{-- checkboxes row example --}}
                                    <div class="col-12">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" id="optOr" value="or">
                                            <label class="form-check-label small" for="optOr">OR</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" id="optRequired" value="required">
                                            <label class="form-check-label small" for="optRequired">Required</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mt-4">
                                    <div>
                                        <button type="button" class="btn btn-outline-secondary btn-sm">Copy From</button>
                                    </div>
                                    <div>
                                        <button type="submit" class="btn btn-primary btn-sm">Save</button>
                                    </div>
                                </div>
                            </form>

                            {{-- bottom list (summary rows) --}}
                            <div class="mt-4 small">
                                <div class="list-group list-group-flush">
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>Aircraft name <span class="text-muted ms-2">BS - Baggage service</span></div>
                                        <div class="text-muted">Value</div>
                                    </div>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>Aircraft name <span class="text-muted ms-2">BS - Baggage service</span></div>
                                        <div class="text-muted">Value</div>
                                    </div>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>Aircraft name <span class="text-muted ms-2">BS - Baggage service</span></div>
                                        <div class="text-muted">Value</div>
                                    </div>
                                </div>
                            </div>
                        </div> {{-- card-body --}}
                    </div> {{-- card --}}
                </div> {{-- services tab --}}
            </div> {{-- tab-content --}}
        </div> {{-- right col --}}
    </div> {{-- row --}}
</div>

{{-- Small custom CSS to better match screenshot spacing --}}
<style>
    .breadcrumb { background: transparent; padding: 0; margin-bottom: 0; }
    .card-body { padding: 1rem; }
    .nav-pills .nav-link { border-radius: .5rem; }
    .nav-pills .nav-link.active { background: #f1f5f9; color: #0d6efd; }
    /* tighten form-control height for compact look */
    .form-control-sm, .form-select-sm { padding: .35rem .5rem; font-size: .875rem; }
    /* subtle shadow */
    .shadow-sm { box-shadow: 0 1px 8px rgba(15, 23, 42, 0.04) !important; }
</style>
