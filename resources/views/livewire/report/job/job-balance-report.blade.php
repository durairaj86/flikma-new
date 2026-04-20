@section('js','job_balance_report')
@section('page-title','Job Balance Report')
<div>
    <main class="gmail-content bg-white px-3">
        <div class="shadow bdr-r-10 py-3 flex-grow-1">
            <div class="d-flex justify-content-between px-3 flex-shrink-0">
                <!-- Date Range Label -->
                <div class="d-inline-flex align-items-center bg-light border rounded-pill px-2 py-1 me-2 mb-2 small"
                     style="font-size: 0.8rem;">
                    <span class="me-2">Date: {{ \Carbon\Carbon::parse($startDate)->format('d-m-Y') }} / {{ \Carbon\Carbon::parse($endDate)->format('d-m-Y') }}</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <!-- Status Filter -->
                    <div class="me-2">
                        <select class="form-select form-select-sm rounded-pill" wire:model.live="status">
                            <option value="">All Statuses</option>
                            <option value="draft">Draft</option>
                            <option value="active">Active</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <!-- Search Box -->
                    <div class="search-box position-relative me-2">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" id="customSearch" class="form-control rounded-pill ps-5"
                               placeholder="Search jobs..." aria-label="Search jobs..."
                               wire:model.live.debounce.300ms="search">
                    </div>
                </div>
            </div>

            <div class="">
                <livewire:report.job.job-balance-report-table/>
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
</div>
