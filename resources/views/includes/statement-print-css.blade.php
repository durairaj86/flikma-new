{{-- Restrained "statement of account" print/PDF style (navy accent, grey text, no coloured amounts).
     Usage: @include('includes.statement-print-css', ['id' => 'my-print-block']) --}}
<style>
        /* Styles live outside @media print so html2pdf renders the same markup. */
        #{{ $id }} {
            --ink: #111827; --muted: #6b7280; --line: #d1d5db; --accent: #1f2d4d;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            line-height: 1.5;
            color: var(--ink);
            background: #fff;
            -webkit-print-color-adjust: exact; print-color-adjust: exact;
        }
        #{{ $id }} table { width: 100%; border-collapse: collapse; }
        #{{ $id }} .text-end { text-align: right; }
        #{{ $id }} .text-center { text-align: center; }
        #{{ $id }} .stmt-band { display: flex; justify-content: space-between; gap: 16px; padding-bottom: 10px; border-bottom: 2px solid var(--accent); }
        #{{ $id }} .stmt-band-right { text-align: right; }
        #{{ $id }} .stmt-company { font-size: 17px; font-weight: 700; color: var(--accent); }
        #{{ $id }} .stmt-head { text-align: center; margin-bottom: 10px; }
        #{{ $id }} .stmt-period { font-size: 11px; color: var(--muted); margin-top: 2px; }
        #{{ $id }} .stmt-title { font-size: 15px; font-weight: 700; letter-spacing: .08em; color: var(--accent); }
        #{{ $id }} .stmt-sub { font-size: 10px; color: var(--muted); }
        #{{ $id }} .stmt-strong { font-weight: 700; }
        #{{ $id }} .stmt-holder { padding: 10px 0 8px; }
        #{{ $id }} .stmt-holder-label { font-size: 9px; letter-spacing: .1em; text-transform: uppercase; color: var(--muted); }
        #{{ $id }} .stmt-holder-name { font-size: 13px; font-weight: 700; }
        #{{ $id }} .stmt-holder-name span { font-weight: 400; color: var(--muted); font-size: 11px; }
        #{{ $id }} .stmt-holder-line { font-size: 10px; color: var(--muted); }
        #{{ $id }} .stmt-cards { display: flex; margin: 6px 0 14px; border: 1px solid var(--line); }
        #{{ $id }} .stmt-card { flex: 1; padding: 7px 12px; border-right: 1px solid var(--line); }
        #{{ $id }} .stmt-card:last-child { border-right: 0; background: #f3f4f6; }
        #{{ $id }} .stmt-card-label { font-size: 9px; text-transform: uppercase; letter-spacing: .06em; color: var(--muted); }
        #{{ $id }} .stmt-card-value { font-size: 13px; font-weight: 700; }
        #{{ $id }} .stmt-table { margin-top: 4px; }
        #{{ $id }} .stmt-table th {
            padding: 6px 8px;
            font-size: 9.5px;
            letter-spacing: .05em;
            text-transform: uppercase;
            text-align: left;
            color: var(--ink);
            background: #f3f4f6;
            border-top: 1px solid var(--ink);
            border-bottom: 1px solid var(--ink);
        }
        #{{ $id }} .stmt-table th.text-end { text-align: right; }
        #{{ $id }} .stmt-table td { border: 0; border-bottom: 1px solid #e5e7eb; padding: 6px 8px; vertical-align: top; }
        #{{ $id }} .stmt-table tr.stmt-bf td { font-weight: 700; background: #fafafa; border-bottom: 1px solid var(--line); }
        #{{ $id }} .stmt-bal { font-weight: 700; }
        #{{ $id }} .stmt-table tfoot td { font-weight: 700; padding: 7px 8px; border-top: 1px solid var(--ink); border-bottom: 2px solid var(--ink); background: #f3f4f6; }
        #{{ $id }} .stmt-footnote { margin-top: 12px; font-size: 9px; color: var(--muted); font-style: italic; }
        #{{ $id }} .stmt-signatures { margin-top: 34px; font-size: 10px; color: #374151; }
        #{{ $id }} .stmt-meta td { vertical-align: top; padding: 2px 0; }

        #{{ $id }} .stmt-num { text-align: right; white-space: nowrap; }
        #{{ $id }} .stmt-table tfoot td { white-space: nowrap; }
        @media print { .inline-page-title { display: none !important; } }
</style>
