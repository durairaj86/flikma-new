@extends('layouts.app')

@section('content')
    <div class="finance-wrapper bg-light-subtle min-vh-100">
        <div class="sticky-top bg-white border-bottom shadow-sm py-3 mb-4 z-index-1020">
            <div class="container-fluid px-5">
                <div class="row align-items-center">
                    <div class="col">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-1">
                                <li class="breadcrumb-item"><a href="#" class="text-muted small">Finance</a></li>
                                <li class="breadcrumb-item active small">Opening Balance</li>
                            </ol>
                        </nav>
                        <h1 class="h4 fw-bold mb-0">Opening Balance Entry</h1>
                    </div>
                    <div class="col-auto d-flex gap-2">
                        <button class="btn btn-white btn-sm border px-3 shadow-sm">
                            <i class="bi bi-x-lg me-1"></i> Cancel
                        </button>
                        <button class="btn btn-primary btn-sm px-4 shadow-sm fw-semibold" id="mainSubmitBtn">
                            <i class="bi bi-check2-circle me-1"></i> Save & Post
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid px-5 pb-5">
            <div class="row">
                <div class="col-lg-9">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom-0 pt-3">
                            <ul class="nav nav-pills custom-pills" id="mainTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="new-entry-tab" data-bs-toggle="pill" data-bs-target="#newEntry" type="button" role="tab">New Entry</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="history-tab" data-bs-toggle="pill" data-bs-target="#history" type="button" role="tab">History</button>
                                </li>
                            </ul>
                        </div>

                        <div class="tab-content card-body p-4 pt-2">
                            <div class="tab-pane fade show active" id="newEntry" role="tabpanel" aria-labelledby="new-entry-tab">
                                @livewire('finance.opening-balance.opening-balance-form')
                            </div>

                            <div class="tab-pane fade" id="history" role="tabpanel" aria-labelledby="history-tab">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead class="table-light text-muted small">
                                        <tr>
                                            <th>Voucher #</th>
                                            <th>Date</th>
                                            <th>Description</th>
                                            <th class="text-end">Total Amount</th>
                                            <th>Created By</th>
                                            <th>Actions</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @forelse($openingBalances as $balance)
                                            <tr>
                                                <td class="fw-bold text-dark">{{ $balance->voucher_no }}</td>
                                                <td>{{ $balance->date ? \Carbon\Carbon::parse($balance->date)->format('d-M-Y') : 'N/A' }}</td>
                                                <td class="text-truncate" style="max-width: 200px;">{{ $balance->description }}</td>
                                                <td class="text-end fw-bold">{{ number_format($balance->total_debit, 2) }}</td>
                                                <td>{{ $balance->user->name ?? 'System' }}</td>
                                                <td>
                                                    <a href="{{ route('finance.opening-balance.view', $balance->id) }}" class="btn btn-sm btn-light border">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-5 text-muted">
                                                    <i class="bi bi-folder-x fs-2 d-block mb-2"></i>
                                                    No entries found in the database.
                                                </td>
                                            </tr>
                                        @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="card border-0 shadow-sm mb-4 sticky-top" style="top: 100px;">
                        <div class="card-body">
                            <h6 class="fw-bold mb-3">Balance Summary</h6>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Total Debit</span>
                                <span class="fw-bold text-primary">0.00</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Total Credit</span>
                                <span class="fw-bold text-success">0.00</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="small text-muted">Difference</span>
                                <span class="badge bg-danger-subtle text-danger rounded-pill">Out of Balance</span>
                            </div>
                        </div>
                        <div class="card-footer bg-light-subtle border-0 py-3">
                            <p class="small text-muted mb-0">
                                <i class="bi bi-info-circle me-1"></i> Ensure debits equal credits to finalize the entry.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* 2026 SaaS Aesthetic Tweaks */
        :root {
            --zoho-blue: #008cd1;
            --soft-bg: #f8fafc;
        }

        body {
            background-color: var(--soft-bg);
            font-size: 0.9rem;
        }

        .custom-pills .nav-link {
            border-radius: 6px;
            padding: 8px 16px;
            color: #64748b;
            font-weight: 500;
            transition: all 0.2s;
        }

        .custom-pills .nav-link.active {
            background-color: var(--zoho-blue);
            box-shadow: 0 4px 6px -1px rgba(0, 140, 209, 0.2);
        }

        /* Modern Form Controls */
        .form-control:focus {
            border-color: var(--zoho-blue);
            box-shadow: 0 0 0 4px rgba(0, 140, 209, 0.1);
        }

        /* Sticky Header Elevation on Scroll */
        .sticky-top {
            backdrop-filter: blur(8px);
            background-color: rgba(255, 255, 255, 0.9) !important;
        }

        .border-dashed { border-style: dashed !important; }
    </style>
@endsection
