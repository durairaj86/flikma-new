{{-- Bank-statement style print layout, shared by finance report pages.
     Applied to elements with .stmt-print (shown only for print / PDF export). --}}
<style>
    .stmt-print {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 11px;
        line-height: 1.5;
        color: #000;
        background: #fff;
    }
    .stmt-print table { width: 100%; border-collapse: collapse; }
    .stmt-print .text-end { text-align: right; }
    .stmt-print .text-center { text-align: center; }
    .stmt-print .stmt-company { font-size: 16px; font-weight: 700; letter-spacing: 0.02em; }
    .stmt-print .stmt-title { font-size: 14px; font-weight: 700; letter-spacing: 0.08em; }
    .stmt-print .stmt-sub { font-size: 10px; color: #333; }
    .stmt-print .stmt-strong { font-weight: 700; }
    .stmt-print .stmt-meta td { vertical-align: top; padding: 2px 0; }
    .stmt-print .stmt-box { margin-top: 10px; border: 1px solid #000; }
    .stmt-print .stmt-box > tbody > tr > td { padding: 8px 10px; }
    .stmt-print .stmt-summary { width: auto; margin-left: auto; }
    .stmt-print .stmt-summary td { padding: 1px 0 1px 24px; font-size: 11px; }
    .stmt-print .stmt-table { margin-top: 12px; }
    .stmt-print .stmt-table th {
        border: 1px solid #000;
        background: #efefef;
        padding: 5px 8px;
        font-size: 10px;
        text-transform: uppercase;
        text-align: left;
    }
    .stmt-print .stmt-table th.text-end { text-align: right; }
    .stmt-print .stmt-table td { border: 1px solid #999; padding: 5px 8px; }
    .stmt-print .stmt-table tfoot td { background: #efefef; border: 1px solid #000; }
    .stmt-print .stmt-footnote { margin-top: 10px; font-size: 9px; color: #444; font-style: italic; }
    .stmt-print .stmt-signatures { margin-top: 36px; font-size: 10px; }

    @media print {
        @page { size: A4 {{ $orientation ?? 'landscape' }}; margin: 12mm; }
        body { background: white !important; }
        .stmt-print .stmt-table thead { display: table-header-group; }
        .stmt-print .stmt-table tr { page-break-inside: avoid; }
    }
</style>
