<div>
    @php
        use Illuminate\Support\Str;
    @endphp
    <div class="customer-statement-container font-sans">
        @if(isset($customerStatementData['customer']) && $customerStatementData['customer'])

            <div class="row g-3 mb-3">
                <div class="col-md-5">
                    <div class="card border-0 shadow-sm h-100 bg-white">
                        <div class="card-body d-flex align-items-center py-2">
                            <div class="flex-shrink-0">
                                <div class="bg-soft-primary text-primary rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                    <i class="bi bi-person-badge fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0 fw-bold text-dark text-uppercase letter-spacing-1">{{ $customerStatementData['customer']->name_en }}</h6>
                                <div class="d-flex gap-3 mt-1">
                                    <span class="text-muted x-small"><i class="bi bi-hash"></i> {{ $customerStatementData['customer']->row_no }}</span>
                                    <span class="text-muted x-small"><i class="bi bi-envelope"></i> {{ $customerStatementData['customer']->email }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-7">
                    <div class="card border-0 shadow-sm h-100 bg-white overflow-hidden">
                        <div class="card-body p-0">
                            <div class="row g-0 h-100 text-center">
                                <div class="col border-end py-3">
                                    <div class="text-muted mb-1 ls-sm">OPENING</div>
                                    <div class="fw-bold fs-6 font-mono text-dark">{{ number_format($customerStatementData['openingBalance'], 2) }}</div>
                                </div>
                                <div class="col border-end py-3">
                                    <div class="text-primary-emphasis mb-1 ls-sm">INVOICED</div>
                                    <div class="fw-bold fs-6 font-mono text-primary">{{ number_format($customerStatementData['invoicedAmount'], 2) }}</div>
                                </div>
                                <div class="col border-end py-3">
                                    <div class="text-success-emphasis mb-1 ls-sm">PAID</div>
                                    <div class="fw-bold fs-6 font-mono text-success">{{ number_format($customerStatementData['paidAmount'], 2) }}</div>
                                </div>
                                <div class="col py-3 bg-light border-start border-4 border-danger">
                                    <div class="text-danger mb-1 ls-sm">CLOSING</div>
                                    <div class="fw-bold fs-6 font-mono text-danger">{{ number_format($customerStatementData['closingBalance'], 2) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                    <h6 class="mb-0 fw-bold text-dark ls-sm"><i class="bi bi-journals me-2"></i>STATEMENT LEDGER</h6>
                    <span class="badge bg-secondary-subtle text-secondary border px-3 py-2 rounded-pill font-mono">CURRENCY: {{ $customerStatementData['customer']->currency }}</span>
                </div>
                <div class="table-responsive" style="max-height: 60vh; overflow-y: auto;">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light sticky-top">
                        <tr class="text-muted fw-bold">
                            <th class="ps-3 py-3 ls-sm">DATE</th>
                            <th class="py-3 ls-sm">VOUCHER / REF</th>
                            <th class="py-3 ls-sm text-center">JOB</th>
                            <th class="py-3 ls-sm">DESCRIPTION</th>
                            <th class="py-3 ls-sm text-end">DEBIT</th>
                            <th class="py-3 ls-sm text-end">CREDIT</th>
                            <th class="py-3 ls-sm text-end pe-3">BALANCE</th>
                        </tr>
                        </thead>
                        <tbody class="bg-white">
                        <tr class="table-light-subtle">
                            <td colspan="4" class="ps-3 py-2 fw-bold text-muted small italic">Brought Forward (Opening Balance)</td>
                            <td class="text-end font-mono fw-semibold">{{ $customerStatementData['openingBalance'] > 0 ? number_format($customerStatementData['openingBalance'], 2) : '-' }}</td>
                            <td class="text-end font-mono fw-semibold">{{ $customerStatementData['openingBalance'] < 0 ? number_format(abs($customerStatementData['openingBalance']), 2) : '-' }}</td>
                            <td class="text-end pe-3 font-mono fw-bold">{{ number_format($customerStatementData['openingBalance'], 2) }}</td>
                        </tr>

                        @forelse($customerStatementData['transactions'] as $transaction)
                            @php
                                // Skip transactions with zero debit and credit (no activity)
                                if (abs($transaction->base_debit) < 0.001 && abs($transaction->base_credit) < 0.001) continue;
                            @endphp
                            <tr class="border-bottom-0">
                                <td class="ps-3">
                                    <div class="text-dark font-mono small">{{ \Carbon\Carbon::parse($transaction->reference_date)->format('d/m/Y') }}</div>
                                    <div class="text-muted x-small">{{ \Carbon\Carbon::parse($transaction->reference_date)->format('l') }}</div>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $transaction->voucher_no }}</div>
                                    <div class="text-muted x-small">{{ $transaction->voucher_type }}{{ $transaction->reference_no ? ' | '.$transaction->reference_no : '' }}</div>
                                </td>
                                <td class="text-center">
                                    @if($transaction->job_number)
                                        <div>
                                            <span class="badge bg-light text-muted fw-normal border px-2">{{ $transaction->job_number }}</span>
                                        </div>
                                        <div class="text-muted x-small mt-1">Job Reference</div>
                                    @else
                                        <span class="text-muted x-small">No Job</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-wrap small" style="max-width: 250px;">
                                        <div class="text-dark">{{ Str::limit($transaction->description, 50) }}</div>
                                        @if(strlen($transaction->description) > 50)
                                            <div class="text-muted x-small">{{ Str::substr($transaction->description, 50) }}</div>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-end font-mono text-dark">{{ number_format($transaction->base_debit, 2) }}</td>
                                <td class="text-end font-mono text-dark">{{ number_format($transaction->base_credit, 2) }}</td>
                                <td class="text-end pe-3 font-mono fw-bold text-dark bg-light-subtle">{{ number_format($transaction->balance, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="bi bi-inbox text-light display-4"></i>
                                    <p class="text-muted mt-2 mb-0">No transactions found for this period.</p>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                        <tfoot class="table-dark sticky-bottom border-top-0">
                        <tr>
                            <th colspan="4" class="ps-3 py-3 text-uppercase ls-sm">Statement Period Totals</th>
                            <th class="text-end font-mono py-3 fs-6">{{ number_format($customerStatementData['invoicedAmount'], 2) }}</th>
                            <th class="text-end font-mono py-3 fs-6">{{ number_format($customerStatementData['paidAmount'], 2) }}</th>
                            <th class="text-end pe-3 font-mono py-3 fs-6 text-warning">{{ number_format($customerStatementData['closingBalance'], 2) }}</th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

        @else
            <div class="text-center py-5 bg-white border rounded-3 shadow-sm mt-3">
                <i class="bi bi-search display-1 text-light"></i>
                <h5 class="text-muted mt-3 fw-normal">Ready to view statements</h5>
                <p class="text-muted small">Please select a customer from the dropdown to load financial records.</p>
            </div>
        @endif
    </div>

    <style>
        .x-small { font-size: 0.7rem; font-weight: 500; }
        .ls-sm { font-size: 0.65rem; font-weight: 700; color: #6c757d; }
        .bg-soft-primary { background-color: #e7f1ff; }
        .table-light-subtle { background-color: #fcfcfc; }

        /* Table Styling */
        .table > :not(caption) > * > * {
            padding: 0.75rem 0.5rem;
            border-bottom: 1px solid #f1f1f1;
        }

        /* Sticky fixes */
        .sticky-top { top: 0; z-index: 10; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .sticky-bottom { bottom: 0; z-index: 10; }

        .table-hover tbody tr:hover {
            background-color: #fbfbfb !important;
        }

        /* Custom Scrollbar */
        .table-responsive::-webkit-scrollbar { width: 6px; }
        .table-responsive::-webkit-scrollbar-track { background: #f1f1f1; }
        .table-responsive::-webkit-scrollbar-thumb { background: #ccc; border-radius: 10px; }
    </style>

    <div class="container-fluid py-4">
        <div class="row g-4 mb-4 align-items-stretch">
            <div class="col-12 col-md-6">
                <div class="p-3 rounded-3 bg-white shadow-sm h-100 border-start border-5 border-primary">
                    <span class="text-uppercase text-muted small fw-bold">Customer Account</span>
                    <h3 class="fw-bolder text-dark mb-1">Global Tech Solutions</h3>
                    <p class="text-muted small mb-0"><i class="bi bi-geo-alt me-1"></i> Riyadh, KSA | Account: #CTS-9920</p>
                </div>
            </div>

            <div class="col-12 col-md-6">
                <div class="p-3 rounded-3 bg-dark shadow-sm h-100 text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-uppercase text-white-50 small fw-bold">Current Statement Balance</span>
                            <div class="fs-2 fw-bolder">45,875.50 <span class="fs-6 fw-normal">SAR</span></div>
                        </div>
                        <div class="text-end">
                            <button class="btn btn-outline-light btn-sm"><i class="bi bi-download me-1"></i> PDF</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle" style="border-collapse: separate; border-spacing: 0 10px;">
                <thead>
                <tr class="text-secondary small text-uppercase" style="letter-spacing: 0.5px;">
                    <th class="ps-4 border-0">Date & Ref</th>
                    <th class="border-0 text-center">Mode</th>
                    <th class="border-0">Job / Service</th>
                    <th class="border-0">Due Date</th>
                    <th class="border-0 text-end">Excl. VAT</th>
                    <th class="border-0 text-end">Tax (15%)</th>
                    <th class="border-0 text-end">Balance Due</th>
                    <th class="border-0 text-center">Status</th>
                </tr>
                </thead>
                <tbody>

                <tr class="bg-white shadow-sm transition-hover">
                    <td class="ps-4 py-3 border-start border-5 border-primary rounded-start-3">
                        <div class="fw-bold text-dark">12-Feb-2026</div>
                        <div class="text-muted small">INV-88021</div>
                    </td>
                    <td class="text-center">
                        <div class="bg-primary-subtle rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 35px; height: 35px;" title="Air Export">
                            <i class="bi bi-airplane-engines text-primary"></i>
                        </div>
                    </td>
                    <td>
                        <div class="fw-bold small text-dark">JOB: AE-4421</div>
                        <span class="text-muted" style="font-size: 0.7rem;">Air Freight & Clearance</span>
                    </td>
                    <td>
                        <div class="fw-medium small text-dark">12-Mar-2026</div>
                        <span class="badge bg-light text-dark border x-small" style="font-size: 0.6rem;">28 DAYS LEFT</span>
                    </td>
                    <td class="text-end fw-bold text-dark">12,000.00</td>
                    <td class="text-end text-muted">1,800.00</td>
                    <td class="text-end">
                        <div class="fw-bolder text-primary-emphasis fs-5">13,800.00</div>
                        <span class="text-muted x-small">SAR</span>
                    </td>
                    <td class="text-center rounded-end-3">
                        <span class="badge rounded-pill bg-success-subtle text-success border border-success border-opacity-25 px-3">PAID</span>
                    </td>
                </tr>

                <tr class="bg-white shadow-sm transition-hover">
                    <td class="ps-4 py-3 border-start border-5 border-info rounded-start-3">
                        <div class="fw-bold text-dark">10-Feb-2026</div>
                        <div class="text-muted small">INV-88015</div>
                    </td>
                    <td class="text-center">
                        <div class="bg-info-subtle rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 35px; height: 35px;" title="Air Import">
                            <i class="bi bi-airplane-engines text-info" style="transform: rotate(180deg);"></i>
                        </div>
                    </td>
                    <td>
                        <div class="fw-bold small text-dark">JOB: AI-3301</div>
                        <span class="text-muted" style="font-size: 0.7rem;">Customs Clearance Only</span>
                    </td>
                    <td>
                        <div class="fw-bold text-danger small">10-Feb-2026</div>
                        <span class="badge bg-danger-subtle text-danger border border-danger border-opacity-25 x-small" style="font-size: 0.6rem;">OVERDUE</span>
                    </td>
                    <td class="text-end fw-bold text-dark">2,500.00</td>
                    <td class="text-end text-muted">375.00</td>
                    <td class="text-end">
                        <div class="fw-bolder text-danger fs-5">2,875.00</div>
                        <span class="text-muted x-small">SAR</span>
                    </td>
                    <td class="text-center rounded-end-3">
                        <span class="badge rounded-pill bg-warning-subtle text-warning border border-warning border-opacity-25 px-3">OPEN</span>
                    </td>
                </tr>

                <tr class="bg-white shadow-sm transition-hover">
                    <td class="ps-4 py-3 border-start border-5 border-secondary rounded-start-3">
                        <div class="fw-bold text-dark">05-Feb-2026</div>
                        <div class="text-muted small">INV-87002</div>
                    </td>
                    <td class="text-center">
                        <div class="bg-secondary-subtle rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 35px; height: 35px;" title="Sea Freight">
                            <i class="bi bi-ship text-secondary"></i>
                        </div>
                    </td>
                    <td>
                        <div class="fw-bold small text-dark">JOB: SI-9920</div>
                        <span class="text-muted" style="font-size: 0.7rem;">Sea Freight 2x40' HC</span>
                    </td>
                    <td>
                        <div class="fw-medium small text-dark">05-Mar-2026</div>
                    </td>
                    <td class="text-end fw-bold text-dark">25,400.00</td>
                    <td class="text-end text-muted">3,810.00</td>
                    <td class="text-end">
                        <div class="fw-bolder text-dark fs-5">29,210.00</div>
                        <span class="text-muted x-small">SAR</span>
                    </td>
                    <td class="text-center rounded-end-3">
                        <span class="badge rounded-pill bg-primary-subtle text-primary border border-primary border-opacity-25 px-3">PARTIAL</span>
                    </td>
                </tr>

                </tbody>
            </table>
        </div>
    </div>

    <style>
        /* Clean transition for interactive feel */
        .transition-hover {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            cursor: pointer;
        }
        .transition-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.08) !important;
        }
        .x-small { font-size: 0.65rem; }
    </style>
</div>
