<div>
@php
    $accounts    = $trialBalanceData['accounts']     ?? [];
    $debitTotal  = $trialBalanceData['total_debit']  ?? 0;
    $creditTotal = $trialBalanceData['total_credit'] ?? 0;
    $diff        = abs($debitTotal - $creditTotal);
    $balanced    = $diff <= 0.001;

    // Group accounts by type
    $grouped = [];
    foreach ($accounts as $acc) {
        $type = $acc['account_type'] ?? 'Other';
        $grouped[$type][] = $acc;
    }
    $typeTotals = [];
    foreach ($grouped as $type => $accs) {
        $typeTotals[$type] = [
            'debit'  => array_sum(array_column($accs, 'debit')),
            'credit' => array_sum(array_column($accs, 'credit')),
        ];
    }
@endphp

{{-- Summary cards (same style as provisional report) --}}
@if(count($accounts) > 0)
<div class="row g-3 p-3 border-bottom">
    <div class="col-lg col-md-4">
        <div class="rpt-stat-card rpt-stat-neutral">
            <div class="rpt-stat-label">Total Accounts</div>
            <div class="rpt-stat-value">{{ count($accounts) }}</div>
        </div>
    </div>
    <div class="col-lg col-md-4">
        <div class="rpt-stat-card rpt-stat-debit">
            <div class="rpt-stat-label">Total Debit (DR)</div>
            <div class="rpt-stat-value tabular-nums">{{ number_format($debitTotal, 2) }}</div>
        </div>
    </div>
    <div class="col-lg col-md-4">
        <div class="rpt-stat-card rpt-stat-credit">
            <div class="rpt-stat-label">Total Credit (CR)</div>
            <div class="rpt-stat-value tabular-nums">{{ number_format($creditTotal, 2) }}</div>
        </div>
    </div>
    <div class="col-lg col-md-4">
        <div class="rpt-stat-card {{ $balanced ? 'rpt-stat-success' : 'rpt-stat-danger' }}">
            <div class="rpt-stat-label">Status</div>
            <div class="rpt-stat-value">
                @if($balanced)
                    <i class="bi bi-check-circle-fill me-1" style="font-size:.9rem;"></i>Balanced
                @else
                    <i class="bi bi-exclamation-triangle-fill me-1" style="font-size:.9rem;"></i>Δ {{ number_format($diff, 2) }}
                @endif
            </div>
        </div>
    </div>
</div>
@endif

{{-- Table --}}
<table class="table table-hover align-middle mb-0">
    <thead>
    <tr class="rpt-table-head">
        <th class="ps-4 border-0 rpt-th-code">Code</th>
        <th class="border-0">Account Name</th>
        <th class="border-0 rpt-th-type">Type</th>
        <th class="text-end border-0 rpt-th-num">
            <span class="d-block rpt-th-sub">Debit</span>DR
        </th>
        <th class="text-end border-0 rpt-th-num">
            <span class="d-block rpt-th-sub">Credit</span>CR
        </th>
        <th class="text-end pe-4 border-0 rpt-th-num">
            <span class="d-block rpt-th-sub">Net</span>Balance
        </th>
    </tr>
    </thead>
    <tbody class="border-top-0">
    @if(count($accounts) > 0)

        @foreach($grouped as $type => $typeAccounts)
            {{-- Section header row --}}
            <tr class="rpt-section-row">
                <td colspan="6" class="rpt-section-label ps-4">
                    @php
                        $typeIcons = [
                            'Asset'     => 'bi-building',
                            'Liability' => 'bi-shield-exclamation',
                            'Equity'    => 'bi-person-badge',
                            'Revenue'   => 'bi-graph-up-arrow',
                            'Expense'   => 'bi-receipt',
                            'Income'    => 'bi-graph-up-arrow',
                        ];
                        $icon = $typeIcons[$type] ?? 'bi-journal';
                    @endphp
                    <i class="bi {{ $icon }} me-2"></i>{{ $type }}
                    <span class="rpt-section-count">{{ count($typeAccounts) }} {{ Str::plural('account', count($typeAccounts)) }}</span>
                </td>
            </tr>

            @foreach($typeAccounts as $idx => $acc)
                @php
                    $dr  = (float)($acc['debit']  ?? 0);
                    $cr  = (float)($acc['credit'] ?? 0);
                    $net = $dr - $cr;
                @endphp
                <tr class="{{ $idx % 2 === 0 ? '' : 'rpt-row-alt' }}">
                    <td class="ps-4 rpt-code">{{ $acc['account_code'] }}</td>
                    <td class="rpt-name">{{ $acc['account_name'] }}</td>
                    <td>
                        <span class="rpt-type-badge rpt-type-{{ strtolower(str_replace([' ','/'], '-', $type)) }}">
                            {{ $type }}
                        </span>
                    </td>
                    <td class="text-end tabular-nums {{ $dr > 0 ? 'rpt-dr-val' : 'rpt-zero' }}">
                        {{ $dr > 0 ? number_format($dr, 2) : '—' }}
                    </td>
                    <td class="text-end tabular-nums {{ $cr > 0 ? 'rpt-cr-val' : 'rpt-zero' }}">
                        {{ $cr > 0 ? number_format($cr, 2) : '—' }}
                    </td>
                    <td class="text-end pe-4 tabular-nums fw-semibold {{ $net > 0 ? 'rpt-dr-val' : ($net < 0 ? 'rpt-cr-val' : 'rpt-zero') }}">
                        @if($net != 0)
                            {{ number_format(abs($net), 2) }}
                            <span class="rpt-dir-label">{{ $net > 0 ? 'DR' : 'CR' }}</span>
                        @else
                            —
                        @endif
                    </td>
                </tr>
            @endforeach

            {{-- Section subtotal --}}
            <tr class="rpt-subtotal-row">
                <td colspan="3" class="ps-4 rpt-subtotal-label">
                    Subtotal — {{ $type }}
                </td>
                <td class="text-end tabular-nums rpt-dr-val fw-bold">
                    {{ number_format($typeTotals[$type]['debit'], 2) }}
                </td>
                <td class="text-end tabular-nums rpt-cr-val fw-bold">
                    {{ number_format($typeTotals[$type]['credit'], 2) }}
                </td>
                <td class="text-end pe-4 tabular-nums fw-bold">
                    @php $sn = $typeTotals[$type]['debit'] - $typeTotals[$type]['credit']; @endphp
                    <span class="{{ $sn > 0 ? 'rpt-dr-val' : ($sn < 0 ? 'rpt-cr-val' : 'rpt-zero') }}">
                        {{ $sn != 0 ? number_format(abs($sn), 2) . ($sn > 0 ? ' DR' : ' CR') : '—' }}
                    </span>
                </td>
            </tr>
        @endforeach

    @else
        <tr>
            <td colspan="6" class="text-center py-5 text-muted">
                <div class="bg-light rounded-circle p-4 d-inline-block mb-3">
                    <i class="bi bi-list-columns h2 text-muted"></i>
                </div>
                <div class="small">No accounts found for the selected period or search criteria.</div>
            </td>
        </tr>
    @endif
    </tbody>

    @if(count($accounts) > 0)
    <tfoot>
        <tr class="rpt-grand-total">
            <td colspan="3" class="ps-4 rpt-gt-label">
                <i class="bi bi-sigma me-2"></i>Grand Total
            </td>
            <td class="text-end tabular-nums rpt-gt-dr">{{ number_format($debitTotal, 2) }}</td>
            <td class="text-end tabular-nums rpt-gt-cr">{{ number_format($creditTotal, 2) }}</td>
            <td class="text-end pe-4">
                @if($balanced)
                    <span class="badge rounded-pill px-3 py-2 bg-success-subtle text-success border border-success-subtle">
                        <i class="bi bi-check-circle-fill me-1"></i>Balanced
                    </span>
                @else
                    <span class="badge rounded-pill px-3 py-2 bg-danger-subtle text-danger border border-danger-subtle">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>Δ {{ number_format($diff, 2) }}
                    </span>
                @endif
            </td>
        </tr>
    </tfoot>
    @endif
</table>

<style>
/* ── Trial Balance table — provisional report design system ── */

/* Stat cards */
.rpt-stat-card {
    border-radius: .75rem;
    padding: .75rem 1rem;
    text-align: center;
    border: 1px solid transparent;
}
.rpt-stat-label {
    font-size: .67rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .07em;
    margin-bottom: .2rem;
}
.rpt-stat-value { font-size: 1rem; font-weight: 800; }

.rpt-stat-neutral { background: #f8fafc; border-color: #e2e8f0; }
.rpt-stat-neutral .rpt-stat-label { color: #64748b; }
.rpt-stat-neutral .rpt-stat-value { color: #0f172a; }

.rpt-stat-debit { background: #eff6ff; border-color: #bfdbfe; }
.rpt-stat-debit .rpt-stat-label { color: #1d4ed8; }
.rpt-stat-debit .rpt-stat-value { color: #1e40af; }

.rpt-stat-credit { background: #f0fdf4; border-color: #bbf7d0; }
.rpt-stat-credit .rpt-stat-label { color: #15803d; }
.rpt-stat-credit .rpt-stat-value { color: #166534; }

.rpt-stat-success { background: #f0fdf4; border-color: #86efac; }
.rpt-stat-success .rpt-stat-label { color: #15803d; }
.rpt-stat-success .rpt-stat-value { color: #15803d; }

.rpt-stat-danger { background: #fff7ed; border-color: #fed7aa; }
.rpt-stat-danger .rpt-stat-label { color: #c2410c; }
.rpt-stat-danger .rpt-stat-value { color: #c2410c; }

/* Table head */
.rpt-table-head { background: #1e293b; }
.rpt-table-head th {
    color: #94a3b8;
    font-size: .67rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .07em;
    padding: .75rem 1rem;
}
.rpt-table-head th.ps-4 { padding-left: 1.5rem !important; }
.rpt-th-code { width: 100px; }
.rpt-th-type { width: 120px; }
.rpt-th-num  { width: 140px; }
.rpt-th-sub  { font-size: .6rem; color: #64748b; font-weight: 600; text-transform: uppercase; margin-bottom: .1rem; }

/* Section rows */
.rpt-section-row { background: #f8fafc; }
.rpt-section-label {
    font-size: .72rem; font-weight: 800;
    text-transform: uppercase; letter-spacing: .06em;
    color: #334155; padding-top: .65rem; padding-bottom: .65rem;
    border-top: 2px solid #e2e8f0;
}
.rpt-section-count {
    font-weight: 400; text-transform: none;
    letter-spacing: 0; color: #94a3b8;
    font-size: .7rem; margin-left: .4rem;
}

/* Data rows */
.rpt-row-alt { background: #f8fafc; }
tbody tr:not(.rpt-section-row):not(.rpt-subtotal-row):hover { background: #eff6ff !important; }
tbody td { padding: .6rem 1rem; border-bottom: 1px solid #f1f5f9; font-size: .8rem; }
tbody td.ps-4 { padding-left: 1.5rem !important; }
tbody td.pe-4 { padding-right: 1.5rem !important; }

.rpt-code { font-family: ui-monospace, monospace; font-size: .74rem; color: #475569; font-weight: 600; }
.rpt-name { font-weight: 500; color: #1e293b; }

/* Values */
.rpt-dr-val  { color: #1d4ed8; }
.rpt-cr-val  { color: #15803d; }
.rpt-zero    { color: #cbd5e1; }
.rpt-dir-label { font-size: .62rem; font-weight: 700; margin-left: .2rem; opacity: .7; }

/* Type badges */
.rpt-type-badge {
    display: inline-block; padding: .18rem .55rem;
    border-radius: 20px; font-size: .65rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .04em;
    background: #f1f5f9; color: #475569;
}
.rpt-type-asset      { background: #eff6ff; color: #1d4ed8; }
.rpt-type-liability  { background: #fef2f2; color: #dc2626; }
.rpt-type-equity     { background: #fefce8; color: #92400e; }
.rpt-type-revenue,
.rpt-type-income     { background: #f0fdf4; color: #15803d; }
.rpt-type-expense    { background: #fff7ed; color: #c2410c; }

/* Subtotal rows */
.rpt-subtotal-row { background: #f1f5f9 !important; }
.rpt-subtotal-row td {
    padding: .5rem 1rem;
    border-top: 1px solid #cbd5e1;
    border-bottom: 2px solid #e2e8f0;
    font-size: .76rem;
}
.rpt-subtotal-row td.ps-4 { padding-left: 1.5rem !important; }
.rpt-subtotal-label { color: #475569; font-weight: 700; text-align: right; }

/* Grand total */
.rpt-grand-total { background: #0f172a; }
.rpt-grand-total td { padding: .85rem 1rem; border: none; }
.rpt-grand-total td.ps-4 { padding-left: 1.5rem !important; }
.rpt-grand-total td.pe-4 { padding-right: 1.5rem !important; }
.rpt-gt-label { color: #e2e8f0; font-size: .8rem; font-weight: 700; }
.rpt-gt-dr { color: #93c5fd; font-size: .85rem; font-weight: 800; }
.rpt-gt-cr { color: #86efac; font-size: .85rem; font-weight: 800; }
</style>
</div>
