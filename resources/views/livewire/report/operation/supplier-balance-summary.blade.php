@section('js', 'supplier_balance_summary')
@section('page-title', __('Supplier Balance Summary'))
@section('page-subtitle', __('Opening balance, invoiced, paid and closing balance for every supplier'))
@section('hide-topbar', true)

<div class="provisional-wrapper min-vh-100 bg-light pb-4">
    <div class="container-fluid px-3">
        @include('includes.inline-page-title')

        {{-- Page Header --}}
                {{-- Filters --}}
        <div class="card border-0 shadow-sm mb-4 d-print-none">
            <div class="card-body p-4">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-4 col-md-4 col-xl-3" id="sbs-supplier-wrap" wire:ignore>
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">{{ __('Supplier') }}</label>
                        <x-common.suppliers wire:model="supplierId" id="sbs-supplier" name="sbs-supplier"
                                            :value="$supplierId ? [(int) $supplierId] : null"
                                            all-label="{{ __('All Suppliers') }}"
                                            placeholder="{{ __('All Suppliers') }}"></x-common.suppliers>
                    </div>
                    <div class="col-lg-2 col-md-4" wire:ignore>
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">{{ __('From Date') }}</label>
                        <input type="hidden" id="sbs-start-date-hidden" wire:model="startDate" value="{{ $startDate }}" />
                        <input type="text" id="sbs-start-date"
                               class="form-control bg-light border-0 py-2"
                               placeholder="dd-mm-yyyy"
                               value="{{ $startDate }}" />
                    </div>
                    <div class="col-lg-2 col-md-4" wire:ignore>
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">{{ __('To Date') }}</label>
                        <input type="hidden" id="sbs-end-date-hidden" wire:model="endDate" value="{{ $endDate }}" />
                        <input type="text" id="sbs-end-date"
                               class="form-control bg-light border-0 py-2"
                               placeholder="dd-mm-yyyy"
                               value="{{ $endDate }}" />
                    </div>
                    <div class="col-lg-12 col-xl-6 col-xxl-5">
                        <div class="d-flex flex-wrap gap-2 justify-content-end align-items-center">
                            <button type="button" class="btn btn-pr fw-bold py-2 shadow-sm"
                                    wire:click="applyFilter" wire:loading.attr="disabled">
                                <i class="bi bi-filter-left me-2"></i>
                                <span wire:loading.remove>{{ __('Generate') }}</span>
                                <span wire:loading><span class="spinner-border spinner-border-sm me-1"></span>{{ __('Loading...') }}</span>
                            </button>
                                                        <div class="btn-group shadow-sm">
                                <button class="btn btn-white border border-end-0" onclick="window.print()">
                                    <i class="bi bi-printer me-2"></i>{{ __('Print') }}
                                </button>
                                <div class="btn-group">
                                    <button class="btn btn-white border dropdown-toggle" data-bs-toggle="dropdown">
                                        <i class="bi bi-download me-2"></i>{{ __('Export') }}
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow">
                                        <li><a class="dropdown-item py-2" href="#" onclick="reportExportPdf(event, 'sbs-print', {orientation: 'portrait'})"><i class="bi bi-file-pdf text-danger me-2"></i>{{ __('PDF Document') }}</a></li>
                                        <li><a class="dropdown-item py-2" href="#" wire:click.prevent="exportExcel"><i class="bi bi-file-excel text-success me-2"></i>{{ __('Excel Sheet') }}</a></li>
                                    </ul>
                                </div>
                            </div>
<button type="button" class="btn btn-outline-secondary border-0 bg-light py-2 px-3"
                                    wire:click="resetFilter">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Summary Cards --}}
        @if(count($rows) > 0)
        <div class="row g-3 mb-4 d-print-none">
            <div class="col-lg col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3 text-center">
                        <div class="small text-muted fw-bold text-uppercase mb-1 ls-1">{{ __('Suppliers') }}</div>
                        <div class="h5 fw-bold text-secondary mb-0 tabular-nums">{{ count($rows) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3 text-center">
                        <div class="small text-muted fw-bold text-uppercase mb-1 ls-1">{{ __('Opening Balance') }}</div>
                        <div class="h5 fw-bold text-secondary mb-0 tabular-nums">{{ number_format($totals['opening'], 2) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3 text-center">
                        <div class="small text-muted fw-bold text-uppercase mb-1 ls-1">{{ __('Invoiced') }}</div>
                        <div class="h5 fw-bold text-primary mb-0 tabular-nums">{{ number_format($totals['invoiced'], 2) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3 text-center">
                        <div class="small text-muted fw-bold text-uppercase mb-1 ls-1">{{ __('Paid') }}</div>
                        <div class="h5 fw-bold text-success mb-0 tabular-nums">{{ number_format($totals['paid'], 2) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3 text-center">
                        <div class="small text-muted fw-bold text-uppercase mb-1 ls-1">{{ __('Closing Balance') }}</div>
                        <div class="h5 fw-bold mb-0 tabular-nums {{ $totals['closing'] >= 0 ? 'text-dark' : 'text-danger' }}">
                            {{ number_format($totals['closing'], 2) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Table --}}
        <div class="card border-0 shadow-sm overflow-hidden d-print-none">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-wallet2 me-2 text-pr"></i>
                    {{ __('Supplier Balance Breakdown') }}
                </h6>
                <span class="badge bg-pr-subtle text-pr border border-pr-subtle px-3 py-2">
                    {{ count($rows) }} {{ __(Str::plural('Supplier', count($rows))) }}
                </span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                    <tr class="bg-light text-muted small text-uppercase fw-bold ls-1">
                        <th class="ps-4 border-0">{{ __('Supplier') }}</th>
                        <th class="text-end border-0">{{ __('Opening Balance') }}</th>
                        <th class="text-end border-0">{{ __('Invoiced') }}</th>
                        <th class="text-end border-0">{{ __('Paid') }}</th>
                        <th class="text-end pe-4 border-0">{{ __('Closing Balance') }}</th>
                    </tr>
                    </thead>
                    <tbody class="border-top-0">
                    @forelse($rows as $row)
                        <tr wire:key="sbs-{{ $row['supplier']->id }}">
                            <td class="ps-4">
                                <span class="fw-bold text-dark">{{ $row['supplier']->name_en }}</span>
                                <div class="text-muted" style="font-size:0.7rem;">{{ $row['supplier']->row_no }}</div>
                            </td>
                            <td class="text-end tabular-nums">
                                {{ number_format($row['opening'], 2) }}
                            </td>
                            <td class="text-end tabular-nums text-primary">
                                {{ $row['invoiced'] > 0 ? number_format($row['invoiced'], 2) : '—' }}
                            </td>
                            <td class="text-end tabular-nums text-success">
                                {{ $row['paid'] > 0 ? number_format($row['paid'], 2) : '—' }}
                            </td>
                            <td class="text-end pe-4 tabular-nums fw-bold {{ $row['closing'] >= 0 ? 'text-dark' : 'text-danger' }}">
                                {{ number_format($row['closing'], 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <div class="bg-light rounded-circle p-4 d-inline-block mb-3">
                                    <i class="bi bi-wallet2 h2 text-muted"></i>
                                </div>
                                <div class="small">{{ __('No supplier balances found for the selected period.') }}</div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                    @if(count($rows) > 0)
                    <tfoot class="bg-light border-top-2">
                    <tr class="fw-bold">
                        <td class="ps-4 py-3">{{ count($rows) }} {{ __('Suppliers') }}</td>
                        <td class="text-end tabular-nums">{{ number_format($totals['opening'], 2) }}</td>
                        <td class="text-end tabular-nums text-primary">{{ number_format($totals['invoiced'], 2) }}</td>
                        <td class="text-end tabular-nums text-success">{{ number_format($totals['paid'], 2) }}</td>
                        <td class="text-end pe-4 tabular-nums {{ $totals['closing'] >= 0 ? 'text-dark' : 'text-danger' }}">{{ number_format($totals['closing'], 2) }}</td>
                    </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>

        {{-- Bank-statement style layout: used for Print and PDF export only --}}
        <div id="sbs-print" class="stmt-print d-none d-print-block"
             data-pdf-filename="SupplierBalanceSummary-{{ $startDate ?? '' }}-{{ $endDate ?? '' }}.pdf">

            <div class="stmt-head">
                <div class="stmt-title">{{ __('SUPPLIER BALANCE SUMMARY') }}</div>
                <div class="stmt-period">{{ __('Period:') }} {{ \Carbon\Carbon::parse($startDate)->format('d-m-Y') }} {{ __('to') }} {{ \Carbon\Carbon::parse($endDate)->format('d-m-Y') }}</div>
            </div>

            <div class="stmt-band">
                <div class="stmt-band-left">
                    <div class="stmt-company">{{ optional(authUserCompany())->name ?? config('app.name') }}</div>
                </div>
                <div class="stmt-band-right">
                    <div class="stmt-sub">{{ __('Currency:') }} {{ optional(authUserCompany())->base_currency ?? 'SAR' }}</div>
                    <div class="stmt-sub">{{ __('Generated:') }} {{ now()->format('d-m-Y H:i') }}</div>
                </div>
            </div>

            <div class="stmt-cards">
                <div class="stmt-card"><div class="stmt-card-label">{{ __('Opening Balance') }}</div><div class="stmt-card-value">{{ number_format($totals['opening'], 2) }}</div></div>
                <div class="stmt-card"><div class="stmt-card-label">{{ __('Invoiced') }}</div><div class="stmt-card-value">{{ number_format($totals['invoiced'], 2) }}</div></div>
                <div class="stmt-card"><div class="stmt-card-label">{{ __('Paid') }}</div><div class="stmt-card-value">{{ number_format($totals['paid'], 2) }}</div></div>
                <div class="stmt-card"><div class="stmt-card-label">{{ __('Closing Balance') }}</div><div class="stmt-card-value">{{ number_format($totals['closing'], 2) }}</div></div>
            </div>

            <table class="stmt-table">
                <thead>
                <tr>
                    <th>{{ __('Supplier') }}</th>
                    <th class="text-end">{{ __('Opening Balance') }}</th>
                    <th class="text-end">{{ __('Invoiced') }}</th>
                    <th class="text-end">{{ __('Paid') }}</th>
                    <th class="text-end">{{ __('Closing Balance') }}</th>
                </tr>
                </thead>
                <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td>{{ $row['supplier']->name_en }} ({{ $row['supplier']->row_no }})</td>
                        <td class="text-end">{{ number_format($row['opening'], 2) }}</td>
                        <td class="text-end">{{ number_format($row['invoiced'], 2) }}</td>
                        <td class="text-end">{{ number_format($row['paid'], 2) }}</td>
                        <td class="text-end">{{ number_format($row['closing'], 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center">{{ __('No supplier balances found for the selected period.') }}</td></tr>
                @endforelse
                </tbody>
                <tfoot>
                <tr class="stmt-strong">
                    <td>{{ count($rows) }} {{ __('Suppliers') }}</td>
                    <td class="text-end">{{ number_format($totals['opening'], 2) }}</td>
                    <td class="text-end">{{ number_format($totals['invoiced'], 2) }}</td>
                    <td class="text-end">{{ number_format($totals['paid'], 2) }}</td>
                    <td class="text-end">{{ number_format($totals['closing'], 2) }}</td>
                </tr>
                </tfoot>
            </table>

            <div class="stmt-signatures">
                <table class="stmt-meta">
                    <tr>
                        <td>{{ __('Prepared By:') }} _________________</td>
                        <td>{{ __('Verified By:') }} _________________</td>
                        <td>{{ __('Approved By:') }} _________________</td>
                    </tr>
                </table>
            </div>
        </div>

        @include('includes.report-print-css', ['orientation' => 'portrait'])
        @include('includes.statement-print-css', ['id' => 'sbs-print'])

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
                var startEl = document.getElementById('sbs-start-date');
                var endEl   = document.getElementById('sbs-end-date');

                if (startEl && startEl._flatpickr) { startEl._flatpickr.destroy(); }
                if (endEl   && endEl._flatpickr)   { endEl._flatpickr.destroy(); }

                if (startEl) {
                    flatpickr(startEl, {
                        dateFormat:    'Y-m-d',
                        altInput:      true,
                        altFormat:     'd-m-Y',
                        allowInput:    true,
                        disableMobile: true,
                        defaultDate:   startEl.value || null,
                        onChange: function (selectedDates, dateStr) {
                            syncHidden('sbs-start-date-hidden', dateStr);
                        },
                    });
                }

                if (endEl) {
                    flatpickr(endEl, {
                        dateFormat:    'Y-m-d',
                        altInput:      true,
                        altFormat:     'd-m-Y',
                        allowInput:    true,
                        disableMobile: true,
                        defaultDate:   endEl.value || null,
                        onChange: function (selectedDates, dateStr) {
                            syncHidden('sbs-end-date-hidden', dateStr);
                        },
                    });
                }
            }

            initFlatpickr();

            // Supplier picker: shared tom-select component, wire:ignore'd so Livewire commits can't wipe it.
            var sbsSup = document.getElementById('sbs-supplier');
            if (sbsSup && !sbsSup.tomselect) { initTomSelectForm($('#sbs-supplier-wrap')); }
            $wire.on('sbs-filter-reset', function () {
                if (sbsSup && sbsSup.tomselect) { sbsSup.tomselect.clear(true); }
                var s = document.getElementById('sbs-start-date'), e = document.getElementById('sbs-end-date');
                if (s && s._flatpickr) s._flatpickr.setDate($wire.get('startDate'), false);
                if (e && e._flatpickr) e._flatpickr.setDate($wire.get('endDate'), false);
            });

            Livewire.hook('commit', function (ref) {
                ref.succeed(function () {
                    queueMicrotask(initFlatpickr);
                });
            });
        })();
    </script>
    @endscript

    <style>
        :root {
            --car-primary: #0ea5e9;
            --car-dark:    #0369a1;
            --car-light:   #f0f9ff;
        }

        .btn-pr { background-color: var(--car-primary); border-color: var(--car-primary); color: #fff; }
        .btn-pr:hover { background-color: var(--car-dark); border-color: var(--car-dark); color: #fff; }
        .text-pr { color: var(--car-primary) !important; }
        .bg-pr-subtle { background-color: #e0f2fe !important; }
        .border-pr-subtle { border-color: #bae6fd !important; }

        .ls-1 { letter-spacing: 0.05em; }
        .x-small { font-size: 0.7rem; }
        .tabular-nums { font-variant-numeric: tabular-nums; }

        .card { border-radius: 1rem; }
        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 0.25rem rgba(14, 165, 233, 0.1);
            border-color: var(--car-primary);
        }

        thead th { vertical-align: bottom; }

        @media print {
            body { background: white !important; }
            .d-print-none { display: none !important; }
            .card { box-shadow: none !important; border: 1px solid #eee !important; }
        }
    </style>
</div>
