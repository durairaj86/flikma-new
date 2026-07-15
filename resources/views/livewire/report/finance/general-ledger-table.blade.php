<div>
@php
    $allAccounts      = $generalLedgerData['accounts']          ?? [];
    $grandTotalDebit  = $generalLedgerData['grand_total_debit']  ?? 0;
    $grandTotalCredit = $generalLedgerData['grand_total_credit'] ?? 0;
    $netBalance       = $generalLedgerData['net_balance']        ?? 0;

    // Filter to accounts with activity
    $activeAccounts = [];
    foreach ($allAccounts as $code => $data) {
        if (abs($data['opening_balance'] ?? 0) > 0.001
            || count($data['transactions'] ?? []) > 0
            || abs($data['closing_balance'] ?? 0) > 0.001) {
            $activeAccounts[$code] = $data;
        }
    }

    $voucherTypeMap = [
        'CI' => ['label' => 'Customer Invoice', 'cls' => 'gl-vt-ci'],
        'SI' => ['label' => 'Supplier Invoice', 'cls' => 'gl-vt-si'],
        'PV' => ['label' => 'Payment Voucher',  'cls' => 'gl-vt-pv'],
        'CR' => ['label' => 'Collection',        'cls' => 'gl-vt-cr'],
        'JV' => ['label' => 'Journal Voucher',   'cls' => 'gl-vt-jv'],
        'EX' => ['label' => 'Expense',           'cls' => 'gl-vt-ex'],
    ];
@endphp

<div class="d-print-none">
{{-- Summary cards — same as provisional report --}}
@if(count($activeAccounts) > 0)
<div class="row g-3 p-3 border-bottom">
    <div class="col-lg col-md-4">
        <div class="gl-stat-card gl-stat-neutral">
            <div class="gl-stat-label">Accounts</div>
            <div class="gl-stat-value">{{ count($activeAccounts) }}</div>
        </div>
    </div>
    <div class="col-lg col-md-4">
        <div class="gl-stat-card gl-stat-debit">
            <div class="gl-stat-label">Total Debit (DR)</div>
            <div class="gl-stat-value tabular-nums">{{ number_format($grandTotalDebit, 2) }}</div>
        </div>
    </div>
    <div class="col-lg col-md-4">
        <div class="gl-stat-card gl-stat-credit">
            <div class="gl-stat-label">Total Credit (CR)</div>
            <div class="gl-stat-value tabular-nums">{{ number_format($grandTotalCredit, 2) }}</div>
        </div>
    </div>
    <div class="col-lg col-md-4">
        <div class="gl-stat-card {{ $netBalance >= 0 ? 'gl-stat-debit' : 'gl-stat-credit' }}">
            <div class="gl-stat-label">Net Balance</div>
            <div class="gl-stat-value tabular-nums">
                {{ number_format(abs($netBalance), 2) }}
                <span style="font-size:.7rem;font-weight:700;opacity:.7;">{{ $netBalance >= 0 ? 'DR' : 'CR' }}</span>
            </div>
        </div>
    </div>
</div>
@endif

@if(count($activeAccounts) > 0)
    @php $accIdx = 0; @endphp
    @foreach($activeAccounts as $accountCode => $accountData)
        @php
            $accIdx++;
            $opening = (float)($accountData['opening_balance'] ?? 0);
            $closing = (float)($accountData['closing_balance'] ?? 0);
            $totalDr = (float)($accountData['total_debit']    ?? 0);
            $totalCr = (float)($accountData['total_credit']   ?? 0);
            $txns    = $accountData['transactions'] ?? [];
        @endphp

        {{-- Account block: header bar + table --}}
        <div class="gl-account-block {{ !$loop->last ? 'border-bottom' : '' }}" style="border-bottom: 4px solid #f1f5f9;">

            {{-- Account header (card-header style, dark) --}}
            <div class="gl-acc-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="gl-acc-num">{{ $accIdx }}</div>
                    <div>
                        <div class="gl-acc-code">{{ $accountData['account_code'] ?? $accountCode }}</div>
                        <div class="gl-acc-name">{{ $accountData['account_name'] }}</div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="gl-acc-type-badge">{{ $accountData['account_type'] }}</span>
                    <div class="gl-acc-closing {{ $closing >= 0 ? 'gl-closing-dr' : 'gl-closing-cr' }}">
                        <span class="gl-closing-label">Closing Balance</span>
                        <span class="gl-closing-val tabular-nums">
                            {{ number_format(abs($closing), 2) }}
                            <small>{{ $closing >= 0 ? 'DR' : 'CR' }}</small>
                        </span>
                    </div>
                </div>
            </div>

            {{-- Transactions table --}}
            <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                <tr class="gl-table-head">
                    <th class="ps-4 border-0 gl-th-date">Date</th>
                    <th class="border-0 gl-th-voucher">Voucher No</th>
                    <th class="border-0 gl-th-vtype">Type</th>
                    <th class="border-0 gl-th-ref">Reference</th>
                    <th class="border-0">Description</th>
                    <th class="text-end border-0 gl-th-num">
                        <span class="d-block gl-th-sub">Debit</span>DR
                    </th>
                    <th class="text-end border-0 gl-th-num">
                        <span class="d-block gl-th-sub">Credit</span>CR
                    </th>
                    <th class="text-end pe-4 border-0 gl-th-num">
                        <span class="d-block gl-th-sub">Running</span>Balance
                    </th>
                </tr>
                </thead>
                <tbody class="border-top-0">

                    {{-- Opening balance --}}
                    <tr class="gl-opening-row">
                        <td class="ps-4" colspan="4"><strong>Opening Balance</strong></td>
                        <td></td>
                        <td class="text-end tabular-nums {{ $opening > 0 ? 'gl-dr-val' : 'gl-zero' }}">
                            {{ $opening > 0 ? number_format($opening, 2) : '—' }}
                        </td>
                        <td class="text-end tabular-nums {{ $opening < 0 ? 'gl-cr-val' : 'gl-zero' }}">
                            {{ $opening < 0 ? number_format(abs($opening), 2) : '—' }}
                        </td>
                        <td class="text-end pe-4 tabular-nums fw-semibold">
                            {{ number_format($opening, 2) }}
                        </td>
                    </tr>

                    {{-- Transactions --}}
                    @forelse($txns as $idx => $txn)
                        @php
                            $dr  = (float)($txn['debit']   ?? 0);
                            $cr  = (float)($txn['credit']  ?? 0);
                            $bal = (float)($txn['balance'] ?? 0);
                            $vt  = $txn['voucher_type'] ?? '';
                            $vtCls = $voucherTypeMap[$vt]['cls'] ?? 'gl-vt-default';
                        @endphp
                        <tr class="{{ $idx % 2 === 0 ? '' : 'gl-row-alt' }}">
                            <td class="ps-4 gl-date">
                                {{ \Carbon\Carbon::parse($txn['date'])->format('d M Y') }}
                            </td>
                            <td class="gl-voucher">{{ $txn['voucher_no'] }}</td>
                            <td>
                                <span class="gl-vtype-badge {{ $vtCls }}">{{ $vt ?: '—' }}</span>
                            </td>
                            <td class="gl-ref">{{ $txn['reference_no'] ?? '—' }}</td>
                            <td class="gl-desc">{{ $txn['description'] ?? '—' }}</td>
                            <td class="text-end tabular-nums {{ $dr > 0 ? 'gl-dr-val' : 'gl-zero' }}">
                                {{ $dr > 0 ? number_format($dr, 2) : '—' }}
                            </td>
                            <td class="text-end tabular-nums {{ $cr > 0 ? 'gl-cr-val' : 'gl-zero' }}">
                                {{ $cr > 0 ? number_format($cr, 2) : '—' }}
                            </td>
                            <td class="text-end pe-4 tabular-nums gl-bal-val fw-semibold">
                                {{ number_format($bal, 2) }}
                                <span class="gl-bal-dir">{{ $bal >= 0 ? 'DR' : 'CR' }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-3 text-muted small fst-italic">
                                <i class="bi bi-info-circle me-1"></i>No transactions in the selected period
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                {{-- Account subtotal footer --}}
                <tfoot>
                <tr class="gl-acc-total">
                    <td colspan="5" class="ps-4 gl-acc-total-label">
                        <i class="bi bi-sigma me-1"></i>Account Total
                    </td>
                    <td class="text-end tabular-nums gl-acc-total-dr">{{ number_format($totalDr, 2) }}</td>
                    <td class="text-end tabular-nums gl-acc-total-cr">{{ number_format($totalCr, 2) }}</td>
                    <td class="text-end pe-4 tabular-nums gl-acc-total-bal">
                        {{ number_format(abs($closing), 2) }}
                        <span class="gl-bal-dir">{{ $closing >= 0 ? 'DR' : 'CR' }}</span>
                    </td>
                </tr>
                </tfoot>
            </table>
            </div>
        </div>
    @endforeach

    {{-- Grand total row (same as provisional report tfoot) --}}
    <table class="table mb-0">
        <tfoot>
        <tr class="gl-grand-total">
            <td class="ps-4 gl-gt-label" style="width:55%">
                <i class="bi bi-calculator me-2"></i>Grand Total — All Accounts
            </td>
            <td class="text-end tabular-nums gl-gt-dr" style="width:15%">{{ number_format($grandTotalDebit, 2) }}</td>
            <td class="text-end tabular-nums gl-gt-cr" style="width:15%">{{ number_format($grandTotalCredit, 2) }}</td>
            <td class="text-end pe-4 tabular-nums gl-gt-net" style="width:15%">
                {{ number_format(abs($netBalance), 2) }}
                <span style="font-size:.65rem;opacity:.7;">{{ $netBalance >= 0 ? 'DR' : 'CR' }}</span>
            </td>
        </tr>
        </tfoot>
    </table>

@else
    <div class="text-center py-5 text-muted">
        <div class="bg-light rounded-circle p-4 d-inline-block mb-3">
            <i class="bi bi-journals h2 text-muted"></i>
        </div>
        <div class="small fw-semibold mb-1">No Ledger Data Found</div>
        <div class="x-small">Select a different date range or account to view transactions.</div>
    </div>
@endif
</div>

{{-- Bank-statement style layout: used for Print and PDF export only --}}
<div id="gl-print" class="stmt-print d-none d-print-block"
     data-pdf-filename="GeneralLedger-{{ $startDate ?? '' }}-{{ $endDate ?? '' }}.pdf">

    <table class="stmt-meta">
        <tr>
            <td>
                <div class="stmt-company">{{ optional(authUserCompany())->name ?? config('app.name') }}</div>
            </td>
            <td class="text-end">
                <div class="stmt-title">GENERAL LEDGER</div>
                <div class="stmt-sub">Period: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} — {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</div>
                <div class="stmt-sub">Generated: {{ now()->format('d M Y H:i') }} &nbsp;|&nbsp; Currency: SAR</div>
            </td>
        </tr>
    </table>

    @forelse($activeAccounts as $code => $acc)
        <table class="stmt-meta stmt-box">
            <tr>
                <td>
                    <div class="stmt-strong">{{ $acc['account_code'] }} — {{ $acc['account_name'] }}</div>
                    <div class="stmt-sub">Opening Balance: {{ number_format($acc['opening_balance'], 2) }}</div>
                </td>
                <td class="text-end">
                    <div class="stmt-sub">Closing Balance</div>
                    <div class="stmt-strong">{{ number_format($acc['closing_balance'], 2) }}</div>
                </td>
            </tr>
        </table>
        <table class="stmt-table">
            <thead>
            <tr>
                <th>Date</th>
                <th>Voucher No</th>
                <th>Description</th>
                <th class="text-end">Debit</th>
                <th class="text-end">Credit</th>
                <th class="text-end">Balance</th>
            </tr>
            </thead>
            <tbody>
            @forelse($acc['transactions'] as $txn)
                <tr>
                    <td>{{ $txn['date'] }}</td>
                    <td>{{ $txn['voucher_no'] }}</td>
                    <td>{{ $txn['description'] }}</td>
                    <td class="text-end">{{ $txn['debit'] > 0 ? number_format($txn['debit'], 2) : '' }}</td>
                    <td class="text-end">{{ $txn['credit'] > 0 ? number_format($txn['credit'], 2) : '' }}</td>
                    <td class="text-end">{{ number_format($txn['balance'], 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center">No transactions in this period.</td></tr>
            @endforelse
            </tbody>
            <tfoot>
            <tr class="stmt-strong">
                <td colspan="3">Total</td>
                <td class="text-end">{{ number_format($acc['total_debit'], 2) }}</td>
                <td class="text-end">{{ number_format($acc['total_credit'], 2) }}</td>
                <td class="text-end">{{ number_format($acc['closing_balance'], 2) }}</td>
            </tr>
            </tfoot>
        </table>
    @empty
        <div class="stmt-footnote">No ledger data found for the selected period.</div>
    @endforelse

    <table class="stmt-table">
        <tfoot>
        <tr class="stmt-strong">
            <td>Grand Total</td>
            <td class="text-end">{{ number_format($grandTotalDebit, 2) }}</td>
            <td class="text-end">{{ number_format($grandTotalCredit, 2) }}</td>
        </tr>
        </tfoot>
    </table>

    <div class="stmt-signatures">
        <table class="stmt-meta">
            <tr>
                <td>Prepared By: _________________</td>
                <td>Verified By: _________________</td>
                <td>Approved By: _________________</td>
            </tr>
        </table>
    </div>
</div>

@include('includes.report-print-css', ['orientation' => 'landscape'])

<style>
/* ── General Ledger table — provisional report design system ── */
.x-small { font-size: .7rem; }
.tabular-nums { font-variant-numeric: tabular-nums; }

/* Stat cards */
.gl-stat-card {
    border-radius: .75rem; padding: .75rem 1rem;
    text-align: center; border: 1px solid transparent;
}
.gl-stat-label {
    font-size: .67rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .07em; margin-bottom: .2rem;
}
.gl-stat-value { font-size: 1rem; font-weight: 800; }

.gl-stat-neutral { background: #f8fafc; border-color: #e2e8f0; }
.gl-stat-neutral .gl-stat-label { color: #64748b; }
.gl-stat-neutral .gl-stat-value { color: #0f172a; }

.gl-stat-debit { background: #eff6ff; border-color: #bfdbfe; }
.gl-stat-debit .gl-stat-label { color: #1d4ed8; }
.gl-stat-debit .gl-stat-value { color: #1e40af; }

.gl-stat-credit { background: #f0fdf4; border-color: #bbf7d0; }
.gl-stat-credit .gl-stat-label { color: #15803d; }
.gl-stat-credit .gl-stat-value { color: #166534; }

/* Account block */
.gl-account-block { background: #fff; }

/* Account header */
.gl-acc-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: .8rem 1.5rem;
    background: #1e293b;
    gap: 1rem; flex-wrap: wrap;
}
.gl-acc-num {
    display: flex; align-items: center; justify-content: center;
    width: 26px; height: 26px; border-radius: 6px;
    background: #4f46e5; color: #fff;
    font-size: .68rem; font-weight: 800; flex-shrink: 0;
}
.gl-acc-code { font-family: ui-monospace, monospace; font-size: .7rem; color: #94a3b8; margin-bottom: .1rem; }
.gl-acc-name { font-size: .88rem; font-weight: 700; color: #f1f5f9; }

.gl-acc-type-badge {
    padding: .2rem .65rem; border-radius: 20px;
    background: #334155; color: #94a3b8;
    font-size: .63rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .05em;
}
.gl-acc-closing {
    display: flex; flex-direction: column; align-items: flex-end;
    padding: .3rem .75rem; border-radius: 8px;
}
.gl-closing-dr { background: rgba(29,78,216,.2); border: 1px solid rgba(29,78,216,.4); }
.gl-closing-cr { background: rgba(21,128,61,.2); border: 1px solid rgba(21,128,61,.4); }
.gl-closing-label { font-size: .6rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #94a3b8; }
.gl-closing-val { font-size: .82rem; font-weight: 800; color: #e2e8f0; }
.gl-closing-val small { font-size: .62rem; margin-left: .2rem; opacity: .7; }

/* Table head */
.gl-table-head { background: #f8fafc; }
.gl-table-head th {
    color: #64748b; font-size: .66rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .07em;
    padding: .6rem 1rem;
}
.gl-table-head th.ps-4 { padding-left: 1.5rem !important; }
.gl-table-head th.pe-4 { padding-right: 1.5rem !important; }
.gl-th-date    { width: 105px; }
.gl-th-voucher { width: 120px; }
.gl-th-vtype   { width: 60px; }
.gl-th-ref     { width: 100px; }
.gl-th-num     { width: 130px; }
.gl-th-sub     { font-size: .58rem; color: #94a3b8; font-weight: 600; text-transform: uppercase; margin-bottom: .1rem; }

/* Opening balance row */
.gl-opening-row { background: #f8fafc; }
.gl-opening-row td { color: #64748b; font-size: .77rem; padding: .5rem 1rem; border-bottom: 1px solid #f1f5f9; }
.gl-opening-row td.ps-4 { padding-left: 1.5rem !important; }
.gl-opening-row td.pe-4 { padding-right: 1.5rem !important; }

/* Data rows */
.gl-row-alt { background: #f8fafc; }
tbody tr:not(.gl-opening-row):hover { background: #eef2ff !important; }
tbody td { padding: .58rem 1rem; border-bottom: 1px solid #f1f5f9; font-size: .79rem; }
tbody td.ps-4 { padding-left: 1.5rem !important; }
tbody td.pe-4 { padding-right: 1.5rem !important; }

.gl-date    { color: #475569; white-space: nowrap; font-size: .75rem; }
.gl-voucher { font-family: ui-monospace, monospace; font-size: .75rem; font-weight: 600; color: #334155; }
.gl-ref     { color: #64748b; font-size: .73rem; }
.gl-desc    { color: #334155; max-width: 240px; font-size: .78rem; }

/* Values */
.gl-dr-val  { color: #1d4ed8; font-weight: 600; }
.gl-cr-val  { color: #15803d; font-weight: 600; }
.gl-zero    { color: #cbd5e1; }
.gl-bal-val { color: #4f46e5; }
.gl-bal-dir { font-size: .6rem; font-weight: 700; margin-left: .15rem; color: #94a3b8; }

/* Voucher type badges */
.gl-vtype-badge {
    display: inline-block; padding: .15rem .45rem;
    border-radius: 4px; font-size: .62rem; font-weight: 800;
    text-transform: uppercase; letter-spacing: .04em;
}
.gl-vt-ci  { background: #eff6ff; color: #1d4ed8; }
.gl-vt-si  { background: #fef3c7; color: #92400e; }
.gl-vt-pv  { background: #f0fdf4; color: #15803d; }
.gl-vt-cr  { background: #f0fdf4; color: #15803d; }
.gl-vt-jv  { background: #faf5ff; color: #7c3aed; }
.gl-vt-ex  { background: #fff7ed; color: #c2410c; }
.gl-vt-default { background: #f1f5f9; color: #475569; }

/* Account subtotal */
.gl-acc-total { background: #1e293b; }
.gl-acc-total td { padding: .65rem 1rem; border: none; }
.gl-acc-total td.ps-4 { padding-left: 1.5rem !important; }
.gl-acc-total td.pe-4 { padding-right: 1.5rem !important; }
.gl-acc-total-label { color: #94a3b8; font-size: .75rem; font-weight: 700; }
.gl-acc-total-dr  { color: #93c5fd; font-size: .8rem; font-weight: 800; }
.gl-acc-total-cr  { color: #86efac; font-size: .8rem; font-weight: 800; }
.gl-acc-total-bal { color: #c4b5fd; font-size: .8rem; font-weight: 800; }

/* Grand total (same bg-light tfoot as provisional) */
.gl-grand-total { background: #0f172a; }
.gl-grand-total td { padding: .9rem 1rem; border: none; }
.gl-grand-total td.ps-4 { padding-left: 1.5rem !important; }
.gl-grand-total td.pe-4 { padding-right: 1.5rem !important; }
.gl-gt-label { color: #e2e8f0; font-size: .82rem; font-weight: 700; }
.gl-gt-dr  { color: #93c5fd; font-size: .88rem; font-weight: 900; }
.gl-gt-cr  { color: #86efac; font-size: .88rem; font-weight: 900; }
.gl-gt-net { color: #fde68a; font-size: .88rem; font-weight: 900; }

@media print {
    .gl-account-block { page-break-inside: avoid; }
}
</style>
</div>
