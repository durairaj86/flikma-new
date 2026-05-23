@section('js', 'general_ledger')
@section('page-title', 'General Ledger')

<div class="gl-wrapper min-vh-100 bg-light py-4">
    <div class="container-fluid px-lg-5">

        {{-- Page Header --}}
        <div class="row align-items-center mb-4 d-print-none">
            <div class="col-md-6">
                <h1 class="h3 fw-bold mb-1" style="color:#0f172a;">General Ledger</h1>
                <p class="text-muted small mb-0">Complete transaction history per account with running balance</p>
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
                            <li><a class="dropdown-item py-2" href="#"><i class="bi bi-file-pdf text-danger me-2"></i>PDF Document</a></li>
                            <li><a class="dropdown-item py-2" href="#"><i class="bi bi-file-excel text-success me-2"></i>Excel Sheet</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="card border-0 shadow-sm mb-4 d-print-none">
            <div class="card-body p-4">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-2 col-md-4">
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">From Date</label>
                        <input type="hidden" id="gl-start-hidden" wire:model.live="startDate" value="{{ $startDate }}" />
                        <input type="text" id="gl-start-date"
                               class="form-control bg-light border-0 py-2"
                               placeholder="dd-mm-yyyy"
                               value="{{ $startDate }}" />
                    </div>
                    <div class="col-lg-2 col-md-4">
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">To Date</label>
                        <input type="hidden" id="gl-end-hidden" wire:model.live="endDate" value="{{ $endDate }}" />
                        <input type="text" id="gl-end-date"
                               class="form-control bg-light border-0 py-2"
                               placeholder="dd-mm-yyyy"
                               value="{{ $endDate }}" />
                    </div>
                    <div class="col-lg-3 col-md-4">
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">Account</label>
                        <select class="form-select bg-light border-0 py-2 no-ts" wire:model.live="accountId">
                            <option value="all">All Accounts</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account['code'] }}">
                                    {{ $account['code'] }} — {{ $account['name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">Search</label>
                        <input type="text"
                               class="form-control bg-light border-0 py-2"
                               placeholder="Voucher, description…"
                               wire:model.live.debounce.300ms="search" />
                    </div>
                    <div class="col-lg-2 col-md-6 d-flex align-items-end">
                        <div class="d-flex gap-2 w-100">
                            <button type="button" class="btn btn-gl fw-bold py-2 flex-grow-1 shadow-sm"
                                    wire:click="updatedStartDate('{{ $startDate }}')"
                                    wire:loading.attr="disabled">
                                <i class="bi bi-filter-left me-2"></i>
                                <span wire:loading.remove>Generate</span>
                                <span wire:loading><span class="spinner-border spinner-border-sm me-1"></span>Loading…</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Period info bar --}}
        <div class="d-flex align-items-center justify-content-between mb-3 px-1">
            <div class="small text-muted">
                <i class="bi bi-calendar3 me-1"></i>
                Period: <strong class="text-dark">{{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}</strong>
                — <strong class="text-dark">{{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</strong>
                @if($accountId !== 'all')
                    &nbsp;·&nbsp; Account: <strong class="text-dark">{{ $accountId }}</strong>
                @endif
            </div>
            <div class="small text-muted">
                Generated: {{ now()->format('d M Y, H:i') }}
            </div>
        </div>

        {{-- Ledger content --}}
        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-journal-text me-2 text-gl"></i>Account Transaction Ledger
                </h6>
                <span class="badge bg-gl-subtle text-gl border border-gl-subtle px-3 py-2">
                    <i class="bi bi-currency-exchange me-1"></i>Currency: SAR
                </span>
            </div>
            <div>
                <livewire:report.finance.general-ledger-table />
            </div>
        </div>

        {{-- Print footer --}}
        <div class="mt-4 p-3 bg-white border rounded shadow-sm d-none d-print-block">
            <div class="row text-center text-muted x-small">
                <div class="col-md-4">Prepared By: _________________</div>
                <div class="col-md-4">Verified By: _________________</div>
                <div class="col-md-4">Approved By: _________________</div>
            </div>
            <div class="text-center mt-2 x-small text-muted">
                ✦ Computer-generated report — no physical signature required
            </div>
        </div>

    </div>

    @script
    <script>
        (function () {
            function syncHidden(hiddenId, dateStr) {
                var hidden = document.getElementById(hiddenId);
                if (hidden) {
                    hidden.value = dateStr;
                    hidden.dispatchEvent(new Event('input', { bubbles: true }));
                }
            }

            function initFlatpickr() {
                var startEl = document.getElementById('gl-start-date');
                var endEl   = document.getElementById('gl-end-date');

                if (startEl && startEl._flatpickr) { startEl._flatpickr.destroy(); }
                if (endEl   && endEl._flatpickr)   { endEl._flatpickr.destroy(); }

                if (startEl) {
                    flatpickr(startEl, {
                        dateFormat: 'Y-m-d', altInput: true, altFormat: 'd-m-Y',
                        allowInput: true, disableMobile: true,
                        defaultDate: startEl.value || null,
                        onChange: function (selectedDates, dateStr) {
                            syncHidden('gl-start-hidden', dateStr);
                        },
                    });
                }
                if (endEl) {
                    flatpickr(endEl, {
                        dateFormat: 'Y-m-d', altInput: true, altFormat: 'd-m-Y',
                        allowInput: true, disableMobile: true,
                        defaultDate: endEl.value || null,
                        onChange: function (selectedDates, dateStr) {
                            syncHidden('gl-end-hidden', dateStr);
                        },
                    });
                }
            }

            initFlatpickr();
            Livewire.hook('commit', function (ref) {
                ref.succeed(function () { queueMicrotask(initFlatpickr); });
            });
        })();
    </script>
    @endscript

    <style>
        :root {
            --gl-primary: #4f46e5;
            --gl-dark:    #4338ca;
            --gl-light:   #eef2ff;
        }

        .btn-gl { background-color: var(--gl-primary); border-color: var(--gl-primary); color: #fff; }
        .btn-gl:hover { background-color: var(--gl-dark); border-color: var(--gl-dark); color: #fff; }
        .text-gl { color: var(--gl-primary) !important; }
        .bg-gl-subtle { background-color: #e0e7ff !important; }
        .border-gl-subtle { border-color: #a5b4fc !important; }

        .ls-1 { letter-spacing: 0.05em; }
        .x-small { font-size: 0.7rem; }
        .tabular-nums { font-variant-numeric: tabular-nums; }

        .card { border-radius: 1rem; }
        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 0.25rem rgba(79, 70, 229, 0.1);
            border-color: var(--gl-primary);
        }

        @media print {
            body { background: white !important; }
            .d-print-none { display: none !important; }
            .card { box-shadow: none !important; border: 1px solid #eee !important; }
        }
    </style>
</div>
