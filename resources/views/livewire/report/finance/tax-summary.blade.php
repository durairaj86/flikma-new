@section('js', 'tax_summary')
@section('page-title', __('Tax Summary'))
@section('hide-topbar', true)
@section('page-subtitle'){{ __('VAT reconciliation for') }} {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} &mdash; {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}@endsection

<div class="provisional-wrapper min-vh-100 bg-light pt-1 pb-4">
    <div class="container-fluid px-3">

        <style>
            .rpt-title { display: none; }
            body:not(.has-top-header) .rpt-title { display: block; }
        </style>
        <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap pt-2 pb-3 d-print-none">
            <div class="rpt-title">
                <h4 class="fw-bold text-dark mb-0">@yield('page-title')</h4>
                @hasSection('page-subtitle')<div class="text-muted small mt-1">@yield('page-subtitle')</div>@endif
            </div>
            <div class="btn-group shadow-sm ms-auto position-relative">
                <button class="btn btn-white border border-end-0" onclick="window.print()">
                    <i class="bi bi-printer me-2"></i>{{ __('Print') }}
                </button>
                <div class="btn-group position-relative" x-data="{ open: false, pos: '', toggle() { this.open = !this.open; if (this.open) { const r = this.$refs.btn.getBoundingClientRect(); this.pos = 'position:fixed;left:auto;bottom:auto;right:' + (window.innerWidth - r.right) + 'px;top:' + (r.bottom + 4) + 'px;'; } } }" @click.outside="open = false" @keydown.escape.window="open = false" @scroll.window="open = false" @resize.window="open = false">
                    <button type="button" class="btn btn-white border dropdown-toggle" x-ref="btn" @click="toggle()" :aria-expanded="open">
                        <i class="bi bi-download me-2"></i>{{ __('Export') }}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow" :class="{ show: open }" x-cloak :style="pos" @click="open = false">
                        <li><a class="dropdown-item py-2" href="#" onclick="reportExportPdf(event, 'ts-print')"><i class="bi bi-file-pdf text-danger me-2"></i>{{ __('PDF Document') }}</a></li>
                        <li><a class="dropdown-item py-2" href="#" wire:click.prevent="exportExcel"><i class="bi bi-file-excel text-success me-2"></i>{{ __('Excel Sheet') }}</a></li>
                    </ul>
                </div>
            </div>
        </div>

                {{-- Filters --}}
        <div class="card border-0 shadow-sm mb-4 d-print-none">
            <div class="card-body p-4">
                <div class="row g-3 align-items-end">
<div class="col-12 col-sm-6 col-lg">
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">{{ __('From Date') }}</label>
                        <input type="hidden" id="ts-start-date-hidden" wire:model="startDate" value="{{ $startDate }}" />
                        <input type="text" id="ts-start-date"
                               class="form-control bg-light border-0 py-2"
                               placeholder="dd-mm-yyyy"
                               value="{{ $startDate }}" />
                    </div>
<div class="col-12 col-sm-6 col-lg">
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">{{ __('To Date') }}</label>
                        <input type="hidden" id="ts-end-date-hidden" wire:model="endDate" value="{{ $endDate }}" />
                        <input type="text" id="ts-end-date"
                               class="form-control bg-light border-0 py-2"
                               placeholder="dd-mm-yyyy"
                               value="{{ $endDate }}" />
                    </div>
<div class="col-12 col-lg-auto">
    <div class="d-flex flex-wrap gap-2 justify-content-lg-end align-items-center">
        <button type="button" class="btn btn-pr fw-bold py-2 shadow-sm"
                                            wire:click="applyFilter" wire:loading.attr="disabled">
                                        <i class="bi bi-filter-left me-2"></i>
                                        <span wire:loading.remove>{{ __('Generate') }}</span>
                                        <span wire:loading><span class="spinner-border spinner-border-sm me-1"></span>{{ __('Loading...') }}</span>
                                    </button>
        <button type="button" class="btn btn-outline-secondary border-0 bg-light py-2 px-3"
                                            wire:click="resetFilter">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </button>
    </div>
</div>
</div>
            </div>
        </div>

        @php
            $f = fn ($n) => number_format((float) $n, 2);
            $na = __('Not tracked here — enter it on the ZATCA portal if it applies to you.');
            // [box no, English, Arabic, values or null (not tracked), show VAT]
            $salesLines = [
                [1, 'Standard rated sales', 'المبيعات الخاضعة للنسبة الأساسية', $vr['sales']['standard'], true],
                [2, 'Private healthcare / private education sales to citizens', 'المبيعات للمواطنين (الخدمات الصحية الخاصة / التعليم الأهلي الخاص)', $vr['sales']['citizens'], false],
                [3, 'Zero rated domestic sales', 'المبيعات المحلية الخاضعة للنسبة الصفرية', $vr['sales']['zero'], false],
                [4, 'Exports', 'الصادرات', $vr['sales']['exports'], false],
                [5, 'Exempt sales', 'المبيعات المعفاة', $vr['sales']['exempt'], false],
            ];
            $purchaseLines = [
                [7, 'Standard rated domestic purchases', 'المشتريات الخاضعة للنسبة الأساسية', $vr['purchases']['standard'], true],
                [8, 'Imports subject to VAT paid at customs', 'الاستيرادات الخاضعة للضريبة المدفوعة في الجمارك', null, true],
                [9, 'Imports subject to VAT accounted for through reverse charge', 'الاستيرادات الخاضعة للضريبة بآلية الاحتساب العكسي', null, true],
                [10, 'Zero rated purchases', 'المشتريات الخاضعة للنسبة الصفرية', $vr['purchases']['zero'], false],
                [11, 'Exempt purchases', 'المشتريات المعفاة', $vr['purchases']['exempt'], false],
            ];
        @endphp

        <div class="d-print-none">
        {{-- headline figures --}}
        <div class="ff-stats row g-0 mb-4">
            <div class="col-md-4 ff-stat">
                <div class="ff-lbl">{{ __('VAT on sales') }}</div>
                <div class="ff-num" style="color:#4f46e5">{{ $f($vr['salesTotal']['vat']) }}</div>
            </div>
            <div class="col-md-4 ff-stat">
                <div class="ff-lbl">{{ __('VAT on purchases') }}</div>
                <div class="ff-num" style="color:#b45309">{{ $f($vr['purchasesTotal']['vat']) }}</div>
            </div>
            <div class="col-md-4 ff-stat">
                <div class="ff-lbl">{{ $vr['vatDue'] >= 0 ? __('Net VAT due') : __('Net VAT claim (refund)') }}</div>
                <div class="ff-num {{ $vr['vatDue'] >= 0 ? 'text-danger' : 'text-success' }}">{{ $f(abs($vr['vatDue'])) }}</div>
            </div>
        </div>

        {{-- VAT return, laid out like the ZATCA form (boxes 1–16) --}}
        <div class="vr-head d-none d-md-grid">
            <span></span>
            <span>{{ __('Amount (SAR)') }}</span>
            <span>{{ __('Adjustment (SAR)') }}</span>
            <span>{{ __('VAT Amount (SAR)') }}</span>
        </div>

        <div class="vr-card vr-sales mb-3">
            <div class="vr-band"><span>{{ __('VAT on Sales') }}</span></div>
            <div class="vr-body">
                @foreach($salesLines as [$no, $en, $ar, $b, $hasVat])
                    <div class="vr-row">
                        <div class="vr-label"><span class="vr-no">{{ $no }}</span>{{ __($en) }}<div class="vr-ar" dir="rtl">{{ $ar }}</div></div>
                        <div class="vr-box">{{ $f($b['amount']) }}</div>
                        <div class="vr-box {{ $b['adjustment'] < 0 ? 'vr-neg' : '' }}">{{ $f($b['adjustment']) }}</div>
                        <div class="vr-box {{ $hasVat ? 'vr-calc' : 'vr-empty' }}">{{ $hasVat ? $f($b['vat']) : '' }}</div>
                    </div>
                @endforeach
                <div class="vr-row vr-total">
                    <div class="vr-label"><span class="vr-no">6</span>{{ __('Total sales') }}<div class="vr-ar" dir="rtl">إجمالي المبيعات</div></div>
                    <div class="vr-box vr-calc">{{ $f($vr['salesTotal']['amount']) }}</div>
                    <div class="vr-box vr-calc">{{ $f($vr['salesTotal']['adjustment']) }}</div>
                    <div class="vr-box vr-calc">{{ $f($vr['salesTotal']['vat']) }}</div>
                </div>
            </div>
        </div>

        <div class="vr-card vr-purchases mb-3">
            <div class="vr-band"><span>{{ __('VAT on Purchases') }}</span></div>
            <div class="vr-body">
                @foreach($purchaseLines as [$no, $en, $ar, $b, $hasVat])
                    <div class="vr-row">
                        <div class="vr-label"><span class="vr-no">{{ $no }}</span>{{ __($en) }}<div class="vr-ar" dir="rtl">{{ $ar }}</div></div>
                        @if($b === null)
                            <div class="vr-box vr-na" title="{{ $na }}">—</div>
                            <div class="vr-box vr-na" title="{{ $na }}">—</div>
                            <div class="vr-box vr-na" title="{{ $na }}">—</div>
                        @else
                            <div class="vr-box">{{ $f($b['amount']) }}</div>
                            <div class="vr-box {{ $b['adjustment'] < 0 ? 'vr-neg' : '' }}">{{ $f($b['adjustment']) }}</div>
                            <div class="vr-box {{ $hasVat ? 'vr-calc' : 'vr-empty' }}">{{ $hasVat ? $f($b['vat']) : '' }}</div>
                        @endif
                    </div>
                @endforeach
                <div class="vr-row vr-total">
                    <div class="vr-label"><span class="vr-no">12</span>{{ __('Total purchases') }}<div class="vr-ar" dir="rtl">إجمالي المشتريات</div></div>
                    <div class="vr-box vr-calc">{{ $f($vr['purchasesTotal']['amount']) }}</div>
                    <div class="vr-box vr-calc">{{ $f($vr['purchasesTotal']['adjustment']) }}</div>
                    <div class="vr-box vr-calc">{{ $f($vr['purchasesTotal']['vat']) }}</div>
                </div>
                <div class="vr-row">
                    <div class="vr-label"><span class="vr-no">13</span>{{ __('Total VAT due for current period') }}<div class="vr-ar" dir="rtl">إجمالي ضريبة القيمة المضافة المستحقة عن الفترة الحالية</div></div>
                    <div></div><div></div>
                    <div class="vr-box vr-calc">{{ $f($vr['vatDue']) }}</div>
                </div>
                <div class="vr-row">
                    <div class="vr-label"><span class="vr-no">14</span>{{ __('Corrections from previous period (between SAR ±5,000)') }}<div class="vr-ar" dir="rtl">تصحيحات من الفترات السابقة (بين ±5,000 ريال)</div></div>
                    <div></div><div></div>
                    <div class="vr-box vr-na" title="{{ $na }}">—</div>
                </div>
                <div class="vr-row">
                    <div class="vr-label"><span class="vr-no">15</span>{{ __('VAT credit carried forward from previous period(s)') }}<div class="vr-ar" dir="rtl">الرصيد الدائن المرحل من الفترات السابقة</div></div>
                    <div></div><div></div>
                    <div class="vr-box vr-na" title="{{ $na }}">—</div>
                </div>
                <div class="vr-row vr-net">
                    <div class="vr-label"><span class="vr-no">16</span>{{ __('Net VAT due (or claim)') }}<div class="vr-ar" dir="rtl">صافي الضريبة المستحقة (أو المطالب بها)</div></div>
                    <div></div><div></div>
                    <div class="vr-box {{ $vr['vatDue'] >= 0 ? 'vr-due' : 'vr-claim' }}">{{ $f($vr['vatDue']) }}</div>
                </div>
            </div>
        </div>

        <div class="small text-muted mb-4 d-flex flex-column gap-1">
            <span><i class="bi bi-info-circle me-1"></i>{{ __('Adjustment = credit notes on sales and debit notes on purchases in this period. Boxes marked — are not tracked here; add them on the ZATCA portal if they apply.') }}</span>
            @if(abs($vr['outOfScope']) > 0.001)
                <span><i class="bi bi-dash-circle me-1"></i>{{ __('Out-of-scope amounts (:amount) are not part of the VAT return and are not shown above.', ['amount' => $f($vr['outOfScope'])]) }}</span>
            @endif
        </div>

        {{-- per-category breakdown with drill-down to the documents --}}
        <details class="vr-details mb-4">
            <summary class="fw-semibold">{{ __('Breakdown by VAT category') }}</summary>
            <div class="pt-3">
                @foreach(['sales' => ['VAT On Sales', $vr['salesRows'], $vr['salesTotal'], 'Total Sales', 'إجمالي المبيعات', 'text-primary'], 'purchases' => ['VAT On Purchases', $vr['purchaseRows'], $vr['purchasesTotal'], 'Total Purchases', 'إجمالي المشتريات', 'text-warning']] as $side => [$title, $rowsList, $tot, $totLabel, $totAr, $cls])
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white py-3 border-0"><h6 class="mb-0 fw-bold {{ $cls }}">{{ __($title) }}</h6></div>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0 tax-vat-table">
                                <colgroup><col style="width:34%"><col style="width:15%"><col style="width:15%"><col style="width:36%"></colgroup>
                                <thead class="table-light small text-uppercase">
                                <tr>
                                    <th class="ps-4">{{ __('Description') }}</th>
                                    <th class="text-end">{{ __('Amount') }}</th>
                                    <th class="text-end">{{ __('VAT') }}</th>
                                    <th class="text-end pe-4" dir="rtl">وصف</th>
                                </tr>
                                </thead>
                                <tbody class="small">
                                @forelse($rowsList as $row)
                                    @php $open = "tsDetails('" . $side . "','" . $row->code . "'," . \Illuminate\Support\Js::from($row->label_en) . ")"; @endphp
                                    <tr>
                                        <td class="ps-4">{{ __($row->label_en) }}</td>
                                        <td class="text-end tabular-nums fast-link" onclick="{{ $open }}">{{ $f($row->net_amount) }}</td>
                                        <td class="text-end tabular-nums fast-link" onclick="{{ $open }}">{{ $f($row->tax_amount) }}</td>
                                        <td class="text-end pe-4" dir="rtl">{{ $row->label_ar }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="ps-4 text-muted small fst-italic">{{ $side === 'sales' ? __('No sales recorded for this period') : __('No purchases recorded for this period') }}</td></tr>
                                @endforelse
                                </tbody>
                                <tfoot class="table-light fw-bold">
                                <tr>
                                    <td class="ps-4">{{ __($totLabel) }}</td>
                                    <td class="text-end">{{ $f($rowsList->sum('net_amount')) }}</td>
                                    <td class="text-end {{ $cls }}">{{ $f($rowsList->sum('tax_amount')) }}</td>
                                    <td class="text-end pe-4" dir="rtl">{{ $totAr }}</td>
                                </tr>
                                @if($side === 'purchases')
                                    <tr>
                                        <td class="ps-4">{{ __('Net VAT Due (or Claim)') }}</td>
                                        <td class="text-end">—</td>
                                        <td class="text-end {{ $vr['vatDue'] >= 0 ? 'text-danger' : 'text-success' }}">{{ $f($vr['vatDue']) }}</td>
                                        <td class="text-end pe-4" dir="rtl">صافي الضريبه المستحقة</td>
                                    </tr>
                                @endif
                                </tfoot>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>
        </details>
        </div>

        {{-- Print / PDF layout (same figures as the return above) --}}
        <div id="ts-print" class="stmt-print d-none d-print-block" data-pdf-filename="TaxSummary-{{ $startDate }}-{{ $endDate }}.pdf">
            <div class="stmt-head">
                <div class="stmt-title">{{ __('TAX SUMMARY (VAT RETURN)') }}</div>
                <div class="stmt-period">{{ __('Period:') }} {{ \Carbon\Carbon::parse($startDate)->format('d-m-Y') }} {{ __('to') }} {{ \Carbon\Carbon::parse($endDate)->format('d-m-Y') }}</div>
            </div>
            <div class="stmt-band">
                <div class="stmt-band-left"><div class="stmt-company">{{ optional(authUserCompany())->name ?? config('app.name') }}</div></div>
                <div class="stmt-band-right">
                    <div class="stmt-sub">{{ __('Currency:') }} SAR</div>
                    <div class="stmt-sub">{{ __('Generated:') }} {{ now()->format('d-m-Y H:i') }}</div>
                </div>
            </div>
            <div class="stmt-cards">
                <div class="stmt-card tsp-c-sales"><div class="stmt-card-label">{{ __('VAT on sales') }}</div><div class="stmt-card-value">{{ $f($vr['salesTotal']['vat']) }}</div></div>
                <div class="stmt-card tsp-c-purchases"><div class="stmt-card-label">{{ __('VAT on purchases') }}</div><div class="stmt-card-value">{{ $f($vr['purchasesTotal']['vat']) }}</div></div>
                <div class="stmt-card {{ $vr['vatDue'] >= 0 ? 'tsp-c-due' : 'tsp-c-claim' }}"><div class="stmt-card-label">{{ $vr['vatDue'] >= 0 ? __('Net VAT due') : __('Net VAT claim (refund)') }}</div><div class="stmt-card-value">{{ $f(abs($vr['vatDue'])) }}</div></div>
            </div>
            @foreach([['VAT on Sales', $salesLines, 'sales'], ['VAT on Purchases', $purchaseLines, 'purchases']] as [$ptitle, $plines, $pside])
                <table class="tsp-card tsp-{{ $pside }}"><tr>
                <td class="tsp-band"><span>{{ __($ptitle) }}</span></td>
                <td class="tsp-body">
                <table class="stmt-table">
                    <thead>
                    <tr>
                        <th style="width:7%">{{ __('Box') }}</th>
                        <th>{{ __($ptitle) }}</th>
                        <th class="text-end">{{ __('Amount (SAR)') }}</th>
                        <th class="text-end">{{ __('Adjustment (SAR)') }}</th>
                        <th class="text-end">{{ __('VAT Amount (SAR)') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($plines as [$no, $en, $ar, $b, $hasVat])
                        <tr>
                            <td>{{ $no }}</td><td>{{ __($en) }}</td>
                            <td class="text-end">{{ $b === null ? '—' : $f($b['amount']) }}</td>
                            <td class="text-end">{{ $b === null ? '—' : $f($b['adjustment']) }}</td>
                            <td class="text-end">{{ $b === null ? '—' : ($hasVat ? $f($b['vat']) : '') }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                    @php $t = $pside === 'sales' ? $vr['salesTotal'] : $vr['purchasesTotal']; @endphp
                    <tr class="stmt-strong">
                        <td>{{ $pside === 'sales' ? 6 : 12 }}</td><td>{{ $pside === 'sales' ? __('Total sales') : __('Total purchases') }}</td>
                        <td class="text-end">{{ $f($t['amount']) }}</td><td class="text-end">{{ $f($t['adjustment']) }}</td><td class="text-end">{{ $f($t['vat']) }}</td>
                    </tr>
                    @if($pside === 'purchases')
                        <tr class="stmt-strong"><td>13</td><td colspan="3">{{ __('Total VAT due for current period') }}</td><td class="text-end">{{ $f($vr['vatDue']) }}</td></tr>
                        <tr class="stmt-strong"><td>16</td><td colspan="3">{{ __('Net VAT due (or claim)') }} — {{ $vr['vatDue'] >= 0 ? __('Payable') : __('Refundable') }}</td><td class="text-end">{{ $f($vr['vatDue']) }}</td></tr>
                    @endif
                    </tfoot>
                </table>
                </td></tr></table>
                <div style="height:10px"></div>
            @endforeach
            <div class="stmt-footnote">{{ __('Adjustment = credit notes on sales and debit notes on purchases in this period. Boxes marked — are not tracked here.') }}</div>
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

        {{-- Disclaimer --}}
        <div class="mt-4 text-center text-muted d-print-none">
            <p class="small">{{ __('** This is a computer-generated VAT return summary for internal use. Verify against official ZATCA records. **') }}</p>
        </div>

    </div>

    @script
    <script>
        window.tsDetails = function (side, code, title) {
            const q = new URLSearchParams({side: side, code: code, from: $wire.startDate, to: $wire.endDate});
            webModal.openGlobalModal({title: title, url: GLOBAL_FN.buildUrl('reports/tax-summary/details') + '?' + q.toString(), size: 'l'});
        };
        (function () {
            function syncHidden(hiddenId, dateStr) {
                var hidden = document.getElementById(hiddenId);
                if (hidden) {
                    hidden.value = dateStr;
                    hidden.dispatchEvent(new Event('input', { bubbles: true }));
                }
            }

            function initFlatpickr() {
                var startEl = document.getElementById('ts-start-date');
                var endEl   = document.getElementById('ts-end-date');

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
                            syncHidden('ts-start-date-hidden', dateStr);
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
                            syncHidden('ts-end-date-hidden', dateStr);
                        },
                    });
                }
            }

            initFlatpickr();

            Livewire.hook('commit', function (ref) {
                ref.succeed(function () {
                    requestAnimationFrame(initFlatpickr);
                });
            });
        })();
    </script>
    @endscript

    <style>
        :root {
            --ts-primary: #0ea5e9;
            --ts-dark:    #0369a1;
            --ts-light:   #f0f9ff;
        }

        .btn-pr { background-color: var(--ts-primary); border-color: var(--ts-primary); color: #fff; }
        .btn-pr:hover { background-color: var(--ts-dark); border-color: var(--ts-dark); color: #fff; }
        .text-pr { color: var(--ts-primary) !important; }
        .bg-pr-subtle { background-color: #e0f2fe !important; }
        .border-pr-subtle { border-color: #bae6fd !important; }

        .btn-outline-pr { color: var(--ts-primary); border-color: var(--ts-primary); }
        .btn-outline-pr:hover, .btn-check:checked + .btn-outline-pr {
            background-color: var(--ts-primary); border-color: var(--ts-primary); color: #fff;
        }

        .ls-1 { letter-spacing: 0.05em; }
        .x-small { font-size: 0.7rem; }
        .tabular-nums { font-variant-numeric: tabular-nums; }

        .card { border-radius: 1rem; }
        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 0.25rem rgba(14, 165, 233, 0.1);
            border-color: var(--ts-primary);
        }

        thead th { vertical-align: bottom; }


        .ff-stats { background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; overflow: hidden; }
        .ff-stat { padding: 14px 20px; border-inline-end: 1px solid #e5e7eb; }
        .ff-stat:last-child { border-inline-end: 0; }
        .ff-lbl { font-size: .78rem; color: #6b7280; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; }
        .ff-num { font-size: 1.5rem; font-weight: 700; font-variant-numeric: tabular-nums; }
        .tax-vat-table { table-layout: fixed; }
        .fast-link { color: #2563eb; cursor: pointer; }
        .fast-link:hover { color: #1d4ed8; text-decoration: underline; }

        /* VAT return (ZATCA form layout): sales = indigo, purchases = amber */
        .vr-head, .vr-row { display: grid; grid-template-columns: minmax(0, 1fr) 170px 170px 170px; gap: 14px; align-items: center; }
        .vr-head { padding: 0 18px 8px calc(56px + 18px); font-size: .78rem; font-weight: 600; color: #6b7280; text-align: end; }
        .vr-card { display: flex; border-radius: 10px; overflow: hidden; border: 1px solid #e5e7eb; }
        .vr-band { flex: 0 0 56px; display: flex; align-items: center; justify-content: center; }
        .vr-band span { writing-mode: vertical-rl; transform: rotate(180deg); color: #fff; font-weight: 700; letter-spacing: .03em; white-space: nowrap; }
        .vr-sales .vr-band { background: #4f46e5; } .vr-sales .vr-body { background: #f5f5ff; }
        .vr-purchases .vr-band { background: #d97706; } .vr-purchases .vr-body { background: #fffaf0; }
        .vr-body { flex: 1; padding: 10px 18px; min-width: 0; }
        .vr-row { padding: 8px 0; border-bottom: 1px dashed rgba(0,0,0,.06); }
        .vr-row:last-child { border-bottom: 0; }
        .vr-label { font-size: .9rem; color: #1f2937; display: block; padding-inline-start: 34px; position: relative; }
        .vr-no { position: absolute; inset-inline-start: 0; top: 0; width: 24px; height: 24px; border-radius: 50%; font-size: .72rem; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; }
        .vr-sales .vr-no { background: #e0e7ff; color: #3730a3; } .vr-purchases .vr-no { background: #fde9c8; color: #92400e; }
        .vr-ar { font-size: .75rem; color: #6b7280; text-align: start; }
        .vr-box { background: #fff; border: 1px solid #e5e7eb; border-radius: 6px; padding: 7px 12px; text-align: end; font-variant-numeric: tabular-nums; font-size: .9rem; min-height: 36px; }
        .vr-calc { font-weight: 600; }
        .vr-sales .vr-calc { background: #e0e7ff; border-color: #c7d2fe; color: #312e81; }
        .vr-purchases .vr-calc { background: #fde9c8; border-color: #f8d49a; color: #78350f; }
        .vr-empty { background: transparent; border-color: transparent; }
        .vr-na { color: #9ca3af; background: #f9fafb; text-align: center; cursor: help; }
        .vr-neg { color: #b45309; }
        .vr-total .vr-label { font-weight: 700; }
        .vr-net .vr-label { font-weight: 700; font-size: 1rem; }
        .vr-due { background: #fee2e2 !important; border-color: #fecaca !important; color: #b91c1c !important; font-weight: 700; font-size: 1rem; }
        .vr-claim { background: #dcfce7 !important; border-color: #bbf7d0 !important; color: #15803d !important; font-weight: 700; font-size: 1rem; }
        .vr-details { background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; padding: 12px 18px; }
        .vr-details summary { cursor: pointer; color: #374151; }
        @media (max-width: 767.98px) {
            .vr-row { grid-template-columns: 1fr 1fr 1fr; }
            .vr-label { grid-column: 1 / -1; }
            .vr-row > div:empty { display: none; }
            .vr-band { flex-basis: 34px; }
        }


        /* print: each section has a coloured side band with the title running up it (sales indigo, purchases amber) */
        #ts-print table.tsp-card { width: 100%; border-collapse: separate; border-spacing: 0; border-radius: 6px; overflow: hidden; }
        #ts-print .tsp-band { width: 34px; text-align: center; vertical-align: middle; color: #fff; padding: 0; }
        #ts-print .tsp-band span { writing-mode: vertical-rl; transform: rotate(180deg); display: inline-block; white-space: nowrap; font-weight: 700; font-size: 12px; letter-spacing: .08em; text-transform: uppercase; }
        #ts-print .tsp-body { padding: 0; }
        #ts-print .tsp-sales .tsp-band { background: #4f46e5; } #ts-print .tsp-sales .tsp-body { background: #f5f5ff; }
        #ts-print .tsp-purchases .tsp-band { background: #d97706; } #ts-print .tsp-purchases .tsp-body { background: #fffaf0; }
        #ts-print .tsp-sales .stmt-table th { background: #4f46e5; } #ts-print .tsp-purchases .stmt-table th { background: #d97706; }
        #ts-print .tsp-card .stmt-table { margin-top: 0; }
        #ts-print .tsp-card .stmt-table tbody tr:nth-child(even) td { background: transparent; }
        #ts-print .tsp-sales .stmt-table tfoot td { background: #e0e7ff; color: #312e81; border-color: #4f46e5; }
        #ts-print .tsp-purchases .stmt-table tfoot td { background: #fde9c8; color: #78350f; border-color: #d97706; }

        #ts-print .stmt-card.tsp-c-sales { background: #f5f5ff; border-top-color: #4f46e5; } #ts-print .tsp-c-sales .stmt-card-value { color: #312e81; }
        #ts-print .stmt-card.tsp-c-purchases { background: #fffaf0; border-top-color: #d97706; } #ts-print .tsp-c-purchases .stmt-card-value { color: #78350f; }
        #ts-print .stmt-card.tsp-c-due { background: #fee2e2; border-top-color: #dc2626; } #ts-print .tsp-c-due .stmt-card-value { color: #b91c1c; }
        #ts-print .stmt-card.tsp-c-claim { background: #dcfce7; border-top-color: #16a34a; } #ts-print .tsp-c-claim .stmt-card-value { color: #15803d; }

        @media print {
            body { background: white !important; }
            .d-print-none { display: none !important; }
            .card { box-shadow: none !important; border: 1px solid #eee !important; }
            .bg-danger, .bg-success { -webkit-print-color-adjust: exact; }
        }
    </style>
    @include('includes.report-print-css', ['orientation' => 'portrait'])
    @include('includes.statement-print-css', ['id' => 'ts-print'])
</div>
