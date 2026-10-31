{{-- Shared look for the finance record drawers (customer / supplier / proforma invoice, credit / debit note).
     Add class "doc-ui" to the drawer; it restyles the header, .section cards, .info-grid rows and tables. --}}
<style>
    .doc-ui .offcanvas-header { border-bottom: 1px solid #e3e6f0; background: linear-gradient(90deg, #eef0ff, #f8f9ff 60%, #fff) !important; }
    .doc-ui .dh-ico { width: 40px; height: 40px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; background: #6366f1; color: #fff; font-size: 1.15rem; box-shadow: 0 4px 10px rgba(99, 102, 241, .35); }
    .doc-ui .dh-sub { display: inline-block; margin-top: 2px; padding: 0 10px; border-radius: 999px; background: #e0e3ff; color: #4338ca; font-weight: 700; font-size: .72rem; letter-spacing: .03em; }
    .doc-ui .dh-sub:empty { display: none; }

    .doc-ui .invoice-no-heading { font-weight: 800; color: #2d3a4f; letter-spacing: .01em; }
    .doc-ui .section { margin-bottom: 1.25rem; background: #fff; border: 1px solid #e8eaf3; border-radius: 12px; box-shadow: 0 1px 3px rgba(45, 58, 79, .06); overflow: hidden; }
    .doc-ui .section > p, .doc-ui .section > .total-table, .doc-ui .section > .text-muted { margin-left: 14px; margin-right: 14px; }
    .doc-ui .section > p:last-child { margin-bottom: 10px; }
    .doc-ui .section h6 {
        display: flex; align-items: center; gap: 10px; font-size: 14px; font-weight: 700; color: #2d3a4f; margin: 0 0 .75rem;
        background: linear-gradient(90deg, #eef0ff, #f8f9ff 70%); padding: 10px 14px; border-radius: 0; border-bottom: 1px solid #e8eaf3; border-inline-start: 4px solid #6366f1;
    }
    .doc-ui .section h6::before { content: ''; width: 9px; height: 9px; border-radius: 3px; background: #6366f1; box-shadow: 0 2px 6px rgba(99, 102, 241, .45); }
    .doc-ui .info-grid { gap: 0 1.5rem; padding: 0 14px 10px; }
    .doc-ui .info-grid > div { border-bottom: 1px solid #f0f1f7; padding: 7px 4px; border-radius: 6px; transition: background .15s; }
    .doc-ui .info-grid > div:hover { background: #f7f8ff; }
    .doc-ui .info-grid strong { color: #7a8196; font-weight: 600; font-size: 12.5px; }
    .doc-ui .info-grid span { color: #1f2937; font-weight: 600; }
    /* tables run edge to edge inside their section card (no card inside a card) */
    .doc-ui .section > .table-responsive { border: 0; border-radius: 0; box-shadow: none; margin: 0; }
    .doc-ui table.table { margin-bottom: 0; }
    .doc-ui table.table th { background: #f4f5fb; border-color: #e8eaf3; border-bottom: 2px solid #c7cbf5; color: #4b5563; font-weight: 700; white-space: nowrap; font-size: .72rem; letter-spacing: .05em; text-transform: uppercase; }
    .doc-ui table.table td { border-color: #f0f1f7; }
    .doc-ui .section > .table-responsive table.table > :not(caption) > * > *:first-child { border-left: 0; }
    .doc-ui .section > .table-responsive table.table > :not(caption) > * > *:last-child { border-right: 0; }
    .doc-ui .section > .table-responsive table.table tbody tr:last-child > * { border-bottom: 0; }
    .doc-ui .total-table { margin-bottom: 14px; }
    /* Totals continue the Line Items card instead of being a card of their own */
    .doc-ui .section:has(+ .section-totals) { margin-bottom: 0; border-bottom: 0; border-bottom-left-radius: 0; border-bottom-right-radius: 0; box-shadow: none; }
    .doc-ui .section-totals { border-top: 1px dashed #d9dcf2; border-top-left-radius: 0; border-top-right-radius: 0; padding-top: 14px; }
    .doc-ui .section-totals > h6 { display: none; }
    .doc-ui .total-table tr.table-secondary > * { background: #eef0ff !important; color: #4338ca; font-weight: 800; font-size: 1rem; box-shadow: none; }
    .doc-ui .cn-tabs .nav-link.active, .doc-ui .nav-tabs .nav-link.active { color: #4f46e5; border-bottom: 2px solid #4f46e5; }
    .doc-ui .cn-tabs .nav-link:hover:not(.active), .doc-ui .nav-tabs .nav-link:hover:not(.active) { color: #4f46e5; background: rgba(99, 102, 241, .06); }
    /* a tab that holds just one section (Transactions, Terms, Time Frame...) already has its title on the tab: no card, no repeated heading */
    .doc-ui .tab-pane > .section:only-child { background: transparent; border: 0; box-shadow: none; border-radius: 0; overflow: visible; margin-bottom: 0; }
    .doc-ui .tab-pane > .section:only-child > h6 { display: none; }
    .doc-ui .tab-pane > .section:only-child > .table-responsive { border: 1px solid #e8eaf3; border-radius: 10px; overflow: hidden; }
    .doc-ui .tab-pane > .section:only-child > p { margin-left: 0; margin-right: 0; }
    .doc-ui .section > .table-responsive table.table > thead > tr > th { border-top: 0; }
    .doc-ui .tab-pane > .section:only-child > .table-responsive table.table > thead > tr > th:first-child { border-left: 0; }
    .doc-ui .doc-tiles { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 10px; margin-bottom: 1.25rem; }
    .doc-ui .doc-tile { border-radius: 12px; padding: 10px 14px; border: 1px solid transparent; border-inline-start-width: 4px; }
    .doc-ui .doc-tile small { display: block; font-size: .66rem; text-transform: uppercase; letter-spacing: .06em; font-weight: 700; }
    .doc-ui .doc-tile b { display: block; font-size: 1.1rem; font-weight: 800; line-height: 1.3; }
    .doc-ui .doc-tile em { font-style: normal; font-size: .7rem; opacity: .75; }
    .doc-ui .doc-tile.t-blue { background: #eef2ff; border-color: #6366f1; color: #4338ca; }
    .doc-ui .doc-tile.t-green { background: #ecfdf5; border-color: #10b981; color: #047857; }
    .doc-ui .doc-tile.t-amber { background: #fffbeb; border-color: #f59e0b; color: #b45309; }
    .doc-ui .doc-tile.t-red { background: #fef2f2; border-color: #ef4444; color: #b91c1c; }
    .doc-ui .doc-tile.t-slate { background: #f1f5f9; border-color: #475569; color: #334155; }
    .doc-ui table.ci-lines th.col-cost { background: #e2e8f0; color: #334155; }
    .doc-ui table.ci-lines td.col-cost { background: #f8fafc; color: #334155; }
    .doc-ui table.ci-lines th.col-sale { background: #e0e3ff; color: #4338ca; }
    .doc-ui table.ci-lines td.col-sale { background: #f7f8ff; }
    .doc-ui .ci-src { display: inline-block; margin-top: 3px; padding: 0 8px; border-radius: 999px; font-size: .68rem; font-weight: 700; }
    .doc-ui .ci-src i { margin-inline-end: 4px; }
    .doc-ui .ci-src.src-s { background: #fef3c7; color: #b45309; }
    .doc-ui .ci-src.src-q { background: #e0e3ff; color: #4338ca; }
    .doc-ui .ci-tax { font-size: .72rem; color: #6b7280; font-weight: 500; white-space: nowrap; }
    .doc-ui .dhero { position: relative; overflow: hidden; margin-bottom: 1.25rem; padding: 18px 20px 16px; border-radius: 14px; color: #fff; background: linear-gradient(135deg, #2d3a4f 0%, #4f46e5 70%, #8b5cf6 100%); box-shadow: 0 8px 22px rgba(79, 70, 229, .28); }
    .doc-ui .dhero::after { content: ''; position: absolute; right: -60px; top: -60px; width: 220px; height: 220px; border-radius: 50%; background: rgba(255, 255, 255, .08); pointer-events: none; }
    .doc-ui .dhero > * { position: relative; z-index: 1; }
    .doc-ui .dhero .invoice-no-heading { color: #fff; font-size: 1.35rem; font-weight: 800; margin: 0; }
    .doc-ui .dhero-sub { opacity: .9; font-size: .9rem; }
    .doc-ui .dhero-sub a, .doc-ui .dhero a.dhero-chip { color: #fff; text-decoration: none; }
    .doc-ui .dhero .btn { border-radius: 999px; font-weight: 700; font-size: .8rem; padding: .3rem 1rem; border: 0; }
    .doc-ui .dhero .btn-outline-secondary { background: rgba(255, 255, 255, .18); color: #fff; }
    .doc-ui .dhero .btn-outline-secondary:hover { background: rgba(255, 255, 255, .32); }
    .doc-ui .dhero .btn-outline-danger { background: #fee2e2; color: #b91c1c; }
    .doc-ui .dhero .btn-success { background: #10b981; box-shadow: 0 4px 10px rgba(0, 0, 0, .2); }
    .doc-ui .dhero-chips { display: flex; flex-wrap: wrap; gap: .4rem; margin: 10px 0 12px; }
    .doc-ui .dhero-chip { display: inline-flex; align-items: center; gap: 6px; padding: 3px 12px; border-radius: 999px; font-size: .74rem; font-weight: 700; background: rgba(255, 255, 255, .18); color: #fff; }
    .doc-ui .dhero-chip .bi-circle-fill { font-size: .5rem; color: #fbbf24; }
    .doc-ui a.dhero-chip:hover { background: rgba(255, 255, 255, .3); }
    .doc-ui .dhero-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 10px; }
    .doc-ui .dhero-stat { background: rgba(255, 255, 255, .13); border-radius: 10px; padding: 8px 12px; }
    .doc-ui .dhero-stat small { display: block; font-size: .66rem; text-transform: uppercase; letter-spacing: .06em; opacity: .75; font-weight: 600; }
    .doc-ui .dhero-stat b { font-size: .98rem; font-weight: 700; }
    .doc-ui .dhero-stat.total { background: #fff; color: #4338ca; }
    .doc-ui .da-btn { display: inline-flex; align-items: center; gap: 6px; border: 0; border-radius: 999px; padding: 6px 16px; font-size: .8rem; font-weight: 700; transition: transform .1s, background .15s; }
    .doc-ui .da-btn:hover { transform: translateY(-1px); }
    .doc-ui .da-accept { background: #10b981; color: #fff; box-shadow: 0 4px 10px rgba(16, 185, 129, .3); }
    .doc-ui .da-accept:hover { background: #059669; }
    .doc-ui .da-cancel { background: #fee2e2; color: #b91c1c; }
    .doc-ui .da-cancel:hover { background: #fecaca; }
    .doc-ui .da-soft { background: #eef0ff; color: #4338ca; }
    .doc-ui .da-soft:hover { background: #e0e3ff; }
    .doc-ui .da-icon { width: 34px; height: 34px; border: 0; border-radius: 50%; background: #fff; color: #4b5563; box-shadow: 0 1px 3px rgba(45, 58, 79, .18); display: inline-flex; align-items: center; justify-content: center; transition: background .15s, color .15s; }
    .doc-ui .da-icon:hover { background: #6366f1; color: #fff; }
    .doc-ui .dh-status { margin-inline-start: .4rem; vertical-align: middle; }
    .doc-ui .entry-voucher h6 { flex-wrap: wrap; }
    .doc-ui .entry-voucher h6 .entry-date { font-weight: 500; color: #6b7280; font-size: .8rem; }
    .doc-ui .entry-chip { display: inline-block; padding: 0 8px; border-radius: 999px; background: #e0e3ff; color: #4338ca; font-size: .68rem; font-weight: 700; margin-inline-start: .3rem; }
    .doc-ui .entry-chip.warn { background: #fef3c7; color: #b45309; }
    .doc-ui .entry-chip.bad { background: #fee2e2; color: #b91c1c; }
    .doc-ui .entry-narr { padding: 0 14px 6px; font-size: .8rem; color: #6b7280; }
    .doc-ui .entry-dr { color: #047857; font-weight: 700; }
    .doc-ui .entry-cr { color: #b45309; font-weight: 700; }
    .doc-ui .entry-table tfoot td { background: #f4f5fb; font-weight: 800; color: #2d3a4f; border-top: 2px solid #c7cbf5; }
    .doc-ui .entry-grand { display: grid; grid-template-columns: 1fr 160px 160px; align-items: center; gap: 10px; padding: 12px 16px; border-radius: 12px; color: #fff; background: linear-gradient(135deg, #2d3a4f 0%, #4f46e5 100%); box-shadow: 0 8px 22px rgba(79, 70, 229, .25); }
    .doc-ui .entry-grand.off { background: linear-gradient(135deg, #7f1d1d, #dc2626); }
    .doc-ui .entry-grand .eg-label { font-weight: 700; }
    .doc-ui .entry-grand .eg-label i { color: #6ee7b7; margin-inline-end: 6px; }
    .doc-ui .entry-grand.off .eg-label i { color: #fde68a; }
    .doc-ui .entry-grand .eg-cell { background: rgba(255, 255, 255, .14); border-radius: 10px; padding: 6px 12px; text-align: end; }
    .doc-ui .entry-grand small { display: block; font-size: .66rem; text-transform: uppercase; letter-spacing: .06em; opacity: .75; font-weight: 600; }
    .doc-ui .entry-grand b { font-size: 1.05rem; }
    @media (max-width: 700px) { .doc-ui .entry-grand { grid-template-columns: 1fr 1fr; } .doc-ui .entry-grand .eg-label { grid-column: span 2; } }
</style>
