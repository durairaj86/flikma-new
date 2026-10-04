@section('page-title', __('Dashboard'))
@section('page-sub-title', __('Overview of the company\'s performance'))
<x-app-layout>

    <style>
        .kpi { height: 100%; padding: 16px 18px 14px; border-radius: 18px; border: 0;
            background: var(--kbg, #fff);
            box-shadow: 0 1px 2px rgba(0,0,0,.04), 0 8px 24px rgba(15,23,42,.07); }
        .kpi-medium-body { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-top: 10px; }
        .kpi-medium-text { min-width: 0; }
        .kpi-medium-chart { position: relative; width: 48%; height: 96px; flex-shrink: 0; }
        .kpi-medium .kpi-main, .kpi-medium .kpi-value { margin-top: 0; }
        .kpi-medium .kpi-legend { margin-top: 10px; }
        .kpi-legend.kpi-legend-3 { grid-template-columns: repeat(3, 1fr); font-size: .72rem; }
        .kpi-list { margin-top: 10px; background: rgba(255,255,255,.75); border-radius: 12px; padding: 4px 10px; }
        .kpi-list-row { display: flex; justify-content: space-between; align-items: center; gap: 8px; padding: 7px 0; font-size: .8rem; color: #101828; border-bottom: 1px solid rgba(15,23,42,.06); }
        .kpi-list-row:last-child { border-bottom: 0; }
        .kpi-list-name { display: flex; flex-direction: column; min-width: 0; font-weight: 600; }
        .kpi-list-name small { font-weight: 400; color: #667085; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .kpi-month-static { font-size: .72rem; color: #667085; background: rgba(255,255,255,.7); border-radius: 999px; padding: 2px 8px; }
        .kpi-duo { display: flex; gap: 22px; }
        .kpi-duo .kpi-value { font-size: 2rem; }
        .kpi-cost-row { display: flex; align-items: center; gap: 8px; font-size: .88rem; color: #475467; padding: 3px 0; }
        .kpi-cost-row em { width: 9px; height: 9px; border-radius: 50%; display: inline-block; }
        .kpi-cost-row b { margin-left: auto; color: #101828; padding-left: 12px; }
        .kpi.kpi-chart-card { min-height: 236px; display: flex; flex-direction: column; }
        .kpi-chart-wrap { position: relative; flex: 1; min-height: 120px; margin-top: 12px; }
        .kpi-chart-wrap > canvas { position: absolute; inset: 0; width: 100% !important; height: 100% !important; }
        /* Responsive sizing: widgets grow with their content instead of scrolling;
           each card is a size container so its inner layout adapts to its own width. */
        .kpi { container-type: inline-size; }
        .kpi-medium { height: auto; min-height: 236px; }
        .kpi-tall { height: auto; max-height: 460px; display: flex; flex-direction: column; overflow: hidden; }
        @media (min-width: 576px) { .kpi-tall { height: calc(2 * 236px + 1rem); max-height: none; } }
        @container (max-width: 340px) {
            .kpi-medium-body { flex-direction: column; align-items: stretch; }
            .kpi-medium-chart { width: 100%; height: 90px; }
            .kpi-value { font-size: 1.6rem; }
            .kpi-duo .kpi-value { font-size: 1.6rem; }
            .kpi-legend.kpi-legend-3 { grid-template-columns: 1fr; }
        }
        @container (max-width: 300px) {
            .kpi-icon { display: none; }
            .wd-month-btn > i { display: none; }
        }
        @container (max-width: 250px) {
            .kpi-head { gap: 6px; }
            .kpi-icon { display: none; }
            .kpi-legend { grid-template-columns: 1fr; }
            .kpi-duo { gap: 12px; }
        }
        .kpi-tall-scroll { flex: 1; min-height: 0; overflow-y: auto; margin-top: 10px; border-radius: 12px; }
        .kpi-tall-foot { font-size: .75rem; color: #667085; padding-top: 8px; }
        .kpi-table thead th { position: sticky; top: 0; background: #f8fafc; font-size: .72rem; text-transform: uppercase; color: #667085; z-index: 1; }
        .kpi-table td { font-size: .8rem; }
        .wd-loading { opacity: .6; transition: opacity .15s; }
        .wd-month { position: relative; flex-shrink: 0; }
        .wd-month-btn { white-space: nowrap; }
        .kpi-title { min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .wd-month-btn { display: inline-flex; align-items: center; gap: 6px; border: 1px solid rgba(15,23,42,.1); background: rgba(255,255,255,.8);
            border-radius: 999px; padding: 4px 10px; font-size: .75rem; font-weight: 600; color: #344054; cursor: pointer; transition: background .15s, box-shadow .15s; }
        .wd-month-btn:hover, .wd-month-btn.is-open { background: #fff; box-shadow: 0 2px 8px rgba(15,23,42,.1); }
        .wd-month-btn i:first-child { color: var(--kc); }
        .wd-month-caret { font-size: .65rem; color: #98a2b3; transition: transform .2s; }
        .wd-month-btn.is-open .wd-month-caret { transform: rotate(180deg); }
        .wd-month-menu { position: absolute; right: 0; top: calc(100% + 6px); z-index: 30; min-width: 150px; max-height: 240px; overflow-y: auto;
            background: #fff; border: 1px solid #eaecf0; border-radius: 14px; padding: 6px; box-shadow: 0 16px 36px rgba(15,23,42,.16); }
        .wd-month-item { display: flex; align-items: center; justify-content: space-between; width: 100%; border: 0; background: transparent;
            padding: 7px 10px; border-radius: 9px; font-size: .8rem; color: #344054; cursor: pointer; text-align: left; }
        .wd-month-item:hover { background: #f2f4f7; }
        .wd-month-item.active { background: color-mix(in srgb, var(--kc, #2563eb) 12%, #fff); color: var(--kc, #2563eb); font-weight: 600; }
        .kpi-compare { position: relative; height: 78px; margin-top: 6px; }
        .kpi-legend.kpi-legend-line { grid-template-columns: 1fr 1fr; margin-top: 6px; }
        .kpi-legend-line span { background: none; padding: 0; }
        .kpi-legend-line i { display: inline-block; width: 18px; height: 0; vertical-align: middle; margin-right: 6px; border-top: 2px solid var(--kc); }
        .kpi-legend-line i.line-dotted { border-top: 2px dotted #98a2b3; }
        .kpi-head { display: flex; align-items: center; gap: 10px; }
        .kpi-icon { width: 34px; height: 34px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center;
            color: #fff; background: var(--kc); font-size: .85rem; border-radius: 50%; width: 32px; height: 32px;
            box-shadow: 0 3px 8px color-mix(in srgb, var(--kc) 40%, transparent); }
        .kpi-title { font-weight: 600; font-size: .9rem; color: #475467; flex: 1; }
        .kpi-month { border: 1px solid #e4e7ec; background: #f9fafb; border-radius: 999px; font-size: .75rem; padding: 2px 8px; color: #344054; cursor: pointer; outline: 0; }
        .kpi-main { display: flex; align-items: baseline; gap: 10px; margin-top: 14px; flex-wrap: wrap; }
        .kpi-value { font-size: 1.9rem; font-weight: 700; letter-spacing: -.02em; color: #101828; white-space: nowrap; line-height: 1.1; }
        .kpi-pill { font-size: .7rem; font-weight: 600; padding: 2px 8px; border-radius: 999px; }
        .kpi-pill.up { background: #dcfae6; color: #067647; } .kpi-pill.down { background: #fee4e2; color: #b42318; }
        .kpi-note { font-size: .8rem; color: #667085; margin-top: 4px; }
        .kpi-bar { height: 8px; border-radius: 99px; background: rgba(15,23,42,.08); overflow: hidden; margin-top: 16px; }
        .kpi-bar i { display: block; height: 100%; border-radius: 99px; background: var(--kc); }
        .kpi-legend { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-top: 12px; font-size: .78rem; color: #667085; }
        .kpi-legend span { background: rgba(255,255,255,.75); border-radius: 12px; padding: 8px 10px; display: block; line-height: 1.5; }
        .kpi-legend b { color: #101828; font-size: .95rem; display: block; margin: 0; }
        .kpi-legend em { display: inline-block; width: 8px; height: 8px; border-radius: 50%; margin-right: 5px; }
        .kpi-legend .dot-a { background: var(--kc); } .kpi-legend .dot-b { background: #d0d5dd; }
        /* Apple-style widgets: large radius, tinted glass surface, small colored
           icon chip, big tight-tracked number, quiet secondary line. */
        .apple-widget {
            --aw-accent: #0a84ff; --aw-tint: rgba(10,132,255,.10);
            position: relative; height: 100%; min-height: 128px;
            padding: 16px 18px; border-radius: 22px;
            background: linear-gradient(145deg, #fff 0%, var(--aw-tint) 140%);
            border: 1px solid rgba(255,255,255,.7);
            box-shadow: 0 1px 2px rgba(0,0,0,.04), 0 8px 24px rgba(0,0,0,.06);
            display: flex; flex-direction: column; justify-content: space-between;
            transition: transform .2s ease, box-shadow .2s ease;
            font-family: -apple-system, BlinkMacSystemFont, "SF Pro Display", "Segoe UI", Inter, sans-serif;
        }
        .apple-widget:hover { transform: translateY(-2px); box-shadow: 0 2px 4px rgba(0,0,0,.05), 0 14px 32px rgba(0,0,0,.10); }
        .aw-blue   { --aw-accent: #0a84ff; --aw-tint: rgba(10,132,255,.14); }
        .aw-teal   { --aw-accent: #30b0c7; --aw-tint: rgba(48,176,199,.16); }
        .aw-orange { --aw-accent: #ff9f0a; --aw-tint: rgba(255,159,10,.16); }
        .aw-green  { --aw-accent: #30d158; --aw-tint: rgba(48,209,88,.16); }
        .aw-top { display: flex; align-items: center; gap: 8px; }
        .aw-icon {
            width: 30px; height: 30px; border-radius: 50%;
            display: inline-flex; align-items: center; justify-content: center;
            background: var(--aw-accent); color: #fff; font-size: .8rem;
            box-shadow: 0 3px 8px color-mix(in srgb, var(--aw-accent) 45%, transparent);
        }
        .aw-label { font-size: .82rem; font-weight: 600; color: #6e6e73; letter-spacing: .01em; }
        .aw-value { font-size: 2rem; font-weight: 700; letter-spacing: -.03em; line-height: 1.1; color: #1d1d1f; margin-top: 10px; }
        .aw-sub { font-size: .8rem; font-weight: 500; color: #86868b; margin-top: 4px; }
        .aw-sub.aw-up { color: #248a3d; }
        .aw-sub.aw-down { color: #d70015; }
        /* Cards */
        .card { border-radius: 10px; box-shadow: 0 6px 18px rgba(20,20,50,0.04); border: 0; }
        .card-header { background: transparent; border-bottom: 1px solid rgba(0,0,0,0.04); padding: .9rem 1rem; }
        .card-body { padding: 1rem; }

        /* Stat boxes (top left) */
        .stat-box { padding: 18px; text-align: left; }
        .stat-box .stat-value { font-size: 1.25rem; font-weight: 600; margin-top: .25rem; }
        .stat-box .stat-label { font-size: .85rem; color: #6c757d; }
        .stat-icon { width: 56px; height: 56px; border-radius: 8px; display:flex; align-items:center; justify-content:center; font-size:1.2rem; }

        /* Right column summary boxes */
        .right-card { margin-bottom: 16px; padding: 14px; background: #fff; border-radius: .65rem; }
        .right-card h6 { margin:0; font-size: .92rem; color: #6c757d; }
        .right-card .big { font-size:1.35rem; font-weight:700; margin-top:6px; }
        .muted-sm { color:#6c757d; font-size:.82rem; }

        /* Mini chart canvases */
        .mini-canvas { width:100%; height:72px !important; }

        /* Recent transactions table */
        .table thead th { border-bottom: 0; font-weight:600; color:#495057; }
        .table tbody td { vertical-align: middle; }

        /* Make sure cards line up vertically */
        @media (min-width: 992px) {
            .left-col { padding-right: 12px; }
            .right-col { padding-left: 12px; /*max-width: 370px;*/ }
        }


        /* Draggable widget grid. One row unit: small = half a medium (width), medium = 1 unit tall,
           large = 2 mediums tall (its list scrolls). Columns follow the grid's own width so every
           widget keeps enough room to fit without scrolling at any resolution. */
        .dash-wrap { container-type: inline-size; }
        .dash-grid { --u: 236px; display: grid; grid-template-columns: repeat(12, minmax(0, 1fr)); gap: 1rem; grid-auto-flow: row dense; grid-auto-rows: var(--u); }
        .dash-item { position: relative; min-width: 0; min-height: 0; grid-column: span 12; }
        .dash-item.dash-small { grid-column: span 6; }
        /* Phones: a medium needs more height at this width, so the unit grows and smalls take a full row. */
        @container (max-width: 429.98px) { .dash-grid { --u: 480px; } .dash-item.dash-small { grid-column: span 12; } }
        @container (min-width: 720px) { .dash-item { grid-column: span 6; } .dash-item.dash-small { grid-column: span 3; } }
        @container (min-width: 1100px) { .dash-item { grid-column: span 4; } .dash-item.dash-small { grid-column: span 2; } }
        .dash-item > .kpi, .dash-item > [wire\:id], .dash-item > div:not(.dash-handle) { height: 100%; }
        .dash-item .kpi-medium, .dash-item .kpi-chart-card, .dash-item .kpi-small { min-height: 0; height: 100%; overflow: hidden; }
        /* Keep every medium inside one row unit. */
        .dash-item .kpi-medium-chart { height: 84px; }
        .dash-item .kpi-compare { height: 64px; }
        .dash-item .kpi-list { margin-top: 8px; }
        .dash-item .kpi-list-row { padding: 4px 0; }
        .dash-item.dash-large { grid-row: span 2; }
        .dash-large > .kpi-tall { position: absolute; inset: 0; height: auto; max-height: none; }
        .dash-handle { position: absolute; top: 4px; left: 50%; transform: translateX(-50%); z-index: 5; padding: 0 14px; line-height: 1; cursor: grab;
            color: #98a2b3; opacity: 0; transition: opacity .15s; font-size: 1.1rem; background: rgba(255,255,255,.85); border-radius: 999px; }
        .dash-item:hover .dash-handle, .dash-handle:focus { opacity: 1; }
        @media (hover: none) { .dash-handle { opacity: .7; } }
        .dash-handle:active { cursor: grabbing; }
        .dash-handle { user-select: none; -webkit-user-select: none; touch-action: none; }
        /* While dragging, never let the pointer select page text/cards (it highlighted everything on scroll). */
        body.dash-dragging, body.dash-dragging * { user-select: none !important; -webkit-user-select: none !important; cursor: grabbing !important; }
        .dash-ghost { opacity: .35; }
        .dash-chosen > :not(.dash-handle) { box-shadow: 0 18px 40px rgba(15,23,42,.25); transform: scale(1.01); }

        /* Small widgets: two fit in the width of one medium widget, so everything inside stays compact. */
        .kpi.kpi-small { min-height: 0; padding: 12px 13px 11px; }
        .kpi-small .kpi-head { gap: 6px; flex-wrap: wrap; }
        .kpi-small .kpi-title { flex: 1 1 auto; font-size: .82rem; }
        .kpi-small .kpi-main { margin-top: 8px; gap: 6px; }
        .kpi-small .kpi-value { font-size: 1.45rem; }
        .kpi-small .kpi-note { margin-top: 2px; }
        .kpi-small .kpi-bar { margin-top: 10px; }
        .kpi-small .kpi-legend { grid-template-columns: 1fr; gap: 5px; margin-top: 9px; font-size: .72rem; }
        .kpi-small .kpi-legend span { padding: 5px 8px; display: flex; justify-content: space-between; align-items: center; gap: 6px; }
        .kpi-small .kpi-legend b { font-size: .8rem; display: inline; }
        .kpi-small .wd-month-btn { padding: 2px 8px; font-size: .68rem; }
        .kpi-small .wd-month-btn > i:first-child { display: none; }
        @container (max-width: 190px) {
            .kpi-small .kpi-icon { display: none; }
            .kpi-small .kpi-value { font-size: 1.25rem; }
            .kpi-small .wd-month-caret { display: none; }
            .kpi-small .kpi-pill { display: none; }
        }

        /* Remove button on each widget (next to the drag handle) */
        /* Remove (x) badges only exist in edit mode, like arranging apps on a phone. */
        .dash-remove { display: none; position: absolute; top: -8px; left: -8px; z-index: 7; width: 26px; height: 26px; border: 2px solid #fff; border-radius: 50%; padding: 0; line-height: 1;
            background: #dc2626; color: #fff; font-size: .7rem; align-items: center; justify-content: center; box-shadow: 0 3px 8px rgba(220,38,38,.45); }
        .dash-remove:hover { background: #b91c1c; transform: scale(1.1); }
        .dash-editing .dash-remove { display: inline-flex; animation: dashPop .2s ease-out; }
        .dash-editing .dash-handle { opacity: 0 !important; pointer-events: none; }
        .dash-editing .dash-item > :not(.dash-handle):not(.dash-remove) { animation: dashJiggle .32s ease-in-out infinite alternate; }
        .dash-editing .dash-item:nth-child(even) > :not(.dash-handle):not(.dash-remove) { animation-delay: -.16s; }
        @keyframes dashJiggle { from { transform: rotate(-.6deg); } to { transform: rotate(.6deg); } }
        @keyframes dashPop { from { transform: scale(0); } to { transform: scale(1); } }
        @media (prefers-reduced-motion: reduce) { .dash-editing .dash-item > * { animation: none !important; } }
        .dash-item.dash-removing { transition: opacity .25s, transform .25s; opacity: 0; transform: scale(.92); }
        .dash-item.dash-entering > :not(.dash-handle):not(.dash-remove) { animation: dashEnter .5s cubic-bezier(.2,.8,.2,1); }
        @keyframes dashEnter { from { opacity: 0; transform: scale(.85); } to { opacity: 1; transform: scale(1); } }
        .dash-item.dash-pulse > :not(.dash-handle):not(.dash-remove) { animation: dashPulse 1.1s ease-out; }
        @keyframes dashPulse { 0% { box-shadow: 0 0 0 0 rgba(79,70,229,.55); } 100% { box-shadow: 0 0 0 18px rgba(79,70,229,0); } }
        .dash-empty { text-align: center; padding: 60px 0; color: #98a2b3; }
        .dash-empty > i { font-size: 2.4rem; display: block; margin-bottom: 8px; }
        .dash-fly { position: fixed; z-index: 2000; pointer-events: none; border-radius: 14px; background: #4f46e5; color: #fff; display: flex; align-items: center; justify-content: center;
            font-size: .8rem; font-weight: 600; box-shadow: 0 14px 34px rgba(79,70,229,.45); transition: transform .6s cubic-bezier(.2,.8,.2,1), opacity .6s, width .6s, height .6s, left .6s, top .6s; }

        /* Floating widget panel */
        .wp-panel { position: fixed; top: 70px; right: 74px; bottom: 16px; width: 330px; max-width: calc(100vw - 90px); z-index: 1060; display: flex; flex-direction: column;
            background: #fff; border-radius: 18px; box-shadow: 0 20px 60px rgba(15,23,42,.28); border: 1px solid #eaecf0;
            transform: translateX(24px) scale(.98); opacity: 0; visibility: hidden; transition: transform .22s ease, opacity .22s ease, visibility .22s; }
        .wp-panel.open { transform: none; opacity: 1; visibility: visible; }
        .wp-head { display: flex; align-items: flex-start; justify-content: space-between; padding: 16px 16px 10px; border-bottom: 1px solid #f2f4f7; }
        .wp-body { flex: 1; overflow-y: auto; padding: 8px 12px 12px; }
        .wp-foot { padding: 8px 16px; border-top: 1px solid #f2f4f7; font-size: .72rem; }
        .wp-group { margin-top: 10px; }
        .wp-group-title { font-size: .7rem; letter-spacing: .06em; text-transform: uppercase; color: #667085; font-weight: 700; padding: 4px 4px 6px; display: flex; align-items: center; gap: 6px; }
        .wp-count { margin-left: auto; font-weight: 600; color: #98a2b3; text-transform: none; letter-spacing: 0; }
        .wp-item { display: flex; align-items: center; gap: 10px; padding: 7px 8px; border-radius: 12px; transition: background .15s; }
        .wp-item:hover { background: #f8fafc; }
        .wp-icon { width: 30px; height: 30px; border-radius: 9px; background: #eef2ff; color: #4f46e5; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .wp-name { flex: 1; font-size: .85rem; font-weight: 500; color: #344054; min-width: 0; }
        .wp-size { font-size: .62rem; font-weight: 700; border-radius: 6px; padding: 1px 6px; background: #f2f4f7; color: #667085; }
        .wp-size-small { background: #ecfdf3; color: #067647; } .wp-size-medium { background: #eff8ff; color: #175cd3; } .wp-size-large { background: #fdf2fa; color: #c11574; }
        .wp-chips { display: inline-flex; gap: 5px; flex-shrink: 0; }
        .wp-chip { width: 28px; height: 28px; border-radius: 9px; border: 1.5px solid #d0d5dd; background: #fff; color: #667085; font-size: .72rem; font-weight: 700; padding: 0;
            display: inline-flex; align-items: center; justify-content: center; position: relative; transition: transform .15s, background .15s, border-color .15s, color .15s; }
        .wp-chip:not(.wp-chip-none):hover { transform: translateY(-1px); border-color: #4f46e5; color: #4f46e5; }
        .wp-chip-none { border: 0; background: transparent; visibility: hidden; }
        .wp-chip.added { background: #4f46e5; border-color: #4f46e5; color: #fff; }
        .wp-chip.added::after { content: '\2713'; position: absolute; top: -6px; right: -6px; width: 14px; height: 14px; border-radius: 50%; background: #16a34a; color: #fff; font-size: .55rem; display: flex; align-items: center; justify-content: center; border: 1.5px solid #fff; }
        .wp-chip.busy { opacity: .5; pointer-events: none; }
        .wp-btn { width: 28px; height: 28px; border-radius: 50%; border: 0; background: #4f46e5; color: #fff; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; transition: transform .15s, background .15s; }
        .wp-btn:hover { transform: scale(1.1); }
        [dir="rtl"] .wp-panel { right: auto; left: 74px; }
        body:not(.has-top-header) .wp-panel { right: 74px; }

        /* Footer small text */
        footer.small { color:#8a8f98; margin-top:12px; }
    </style>

    <div class="p-3">
                <!-- Top row: Title & timeframe -->
                <div class="container-fluid mb-3 d-none">
                    <div class="row align-items-center">
                        <div class="col">
                            {{--<h4 class="mb-0">Dashboard</h4>
                            <small class="muted-sm">Overview of the company's performance</small>--}}
                        </div>
                        <div class="col-auto">
                            <div class="btn-group" role="group" aria-label="Period">
                                <button class="btn btn-outline-secondary btn-sm">{{ __('Today') }}</button>
                                <button class="btn btn-outline-secondary btn-sm">{{ __('Week') }}</button>
                                <button class="btn btn-outline-secondary btn-sm active">{{ __('Month') }}</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Widget grid: drag a widget by its handle to reorder; the order is saved per user -->
                <div class="container-fluid">
                    <div class="d-flex justify-content-end align-items-center gap-2 mb-2 dash-toolbar">
                        <span class="text-muted small d-none d-md-inline"><i class="bi bi-arrows-move me-1"></i>{{ __('Drag widgets by the handle to arrange your dashboard') }}</span>
                        <button type="button" class="btn btn-sm btn-primary rounded-pill" id="dash-open-panel"><i class="bi bi-plus-lg me-1"></i>{{ __('Add widget') }}</button>
                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill" id="dash-edit-toggle" data-label-edit="{{ __('Remove widget') }}" data-label-done="{{ __('Done') }}"><i class="bi bi-dash-circle me-1"></i><span>{{ __('Remove widget') }}</span></button>
                        <span class="small text-success d-none" id="dash-saved"><i class="bi bi-check2-circle me-1"></i>{{ __('Saved') }}</span>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" id="dash-reset"><i class="bi bi-arrow-counterclockwise me-1"></i>{{ __('Reset layout') }}</button>
                    </div>
                    <div class="dash-wrap"><div class="dash-grid" id="dashGrid">
                        @foreach($widgetOrder as $key)
                            @include('dashboard._cell', ['key' => $key])
                        @endforeach
                    </div></div>
                    <div class="dash-empty d-none" id="dashEmpty">
                        <i class="bi bi-grid-1x2"></i>
                        <div class="fw-semibold">{{ __('Your dashboard is empty') }}</div>
                        <div class="text-muted small mb-2">{{ __('Add the widgets you need.') }}</div>
                        <button type="button" class="btn btn-sm btn-primary rounded-pill" data-open-widget-panel><i class="bi bi-plus-lg me-1"></i>{{ __('Add widget') }}</button>
                    </div>
                </div> <!-- /.container-fluid -->







    {{-- Widget panel: floats over the page, grouped by module. Plus adds a widget to the dashboard, minus removes it. --}}
    <div class="wp-panel" id="widgetPanel" aria-hidden="true">
        <div class="wp-head">
            <div>
                <div class="fw-semibold"><i class="bi bi-grid-1x2 me-2 text-primary"></i>{{ __('Widgets') }}</div>
                <div class="text-muted small">{{ __('Choose what you want on your dashboard') }}</div>
            </div>
            <button type="button" class="btn-close" id="wp-close" aria-label="{{ __('Close') }}"></button>
        </div>
        <div class="wp-body">
            @foreach($widgetCatalog as $mKey => $module)
                <div class="wp-group">
                    <div class="wp-group-title"><i class="bi {{ $module['icon'] }}"></i> {{ __($module['title']) }}
                        <span class="wp-count" data-module="{{ $mKey }}"></span>
                    </div>
                    @foreach($module['groups'] as $gKey => $g)
                        <div class="wp-item" data-group="{{ $gKey }}">
                            <span class="wp-icon"><i class="bi {{ $g['icon'] }}"></i></span>
                            <span class="wp-name">{{ __($g['title']) }}</span>
                            <span class="wp-chips">
                                @foreach(['small', 'medium', 'large'] as $size)
                                    @if(isset($g['variants'][$size]))
                                        <button type="button" class="wp-chip wp-chip-{{ $size }}" data-key="{{ $g['variants'][$size] }}"
                                                title="{{ __('Add') }} {{ __(ucfirst($size)) }}">{{ strtoupper($size[0]) }}</button>
                                    @else
                                        <span class="wp-chip wp-chip-none"></span>
                                    @endif
                                @endforeach
                            </span>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
        <div class="wp-foot text-muted small">{{ __('Tap a size to add it. Tap again to remove.') }}<br><span class="wp-size wp-size-small">S</span> {{ __('Small') }} &nbsp; <span class="wp-size wp-size-medium">M</span> 2&times; {{ __('Small') }} &nbsp; <span class="wp-size wp-size-large">L</span> 2&times; {{ __('Medium') }}</div>
    </div>

                <footer class="small text-center mt-3 mb-0">© <span id="y"></span> {{ companyName() }} — {{ __('All rights reserved.') }}</footer>
    </div>

        <!-- Scripts -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.2/Sortable.min.js"></script>

        <script>
            // footer year
            document.getElementById('y').innerText = new Date().getFullYear();

            // Revenue vs Expenses chart (monthly bars, in thousands)
            var monthlyLabels = @json($monthlyLabels);
            var monthlyRevenue = @json($monthlyRevenue);
            var monthlyExpenses = @json($monthlyExpenses);

            function initRevenueExpenses() {
            var cv = document.getElementById('salesMainChart'); if (!cv) return;
            var old = Chart.getChart(cv); if (old) old.destroy();
            new Chart(cv, {
                type: 'bar',
                data: {
                    labels: monthlyLabels,
                    datasets: [
                        { label: @json(__('Revenue')), data: monthlyRevenue, backgroundColor: 'rgba(13,110,253,0.9)', borderRadius: 6 },
                        { label: @json(__('Expenses')), data: monthlyExpenses, backgroundColor: 'rgba(220,53,69,0.85)', borderRadius: 6 }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top' },
                        tooltip: { callbacks: { label: (ctx) => `${ctx.dataset.label}: ₹${ctx.parsed.y.toLocaleString()}K` } }
                    },
                    scales: { y: { beginAtZero: true, ticks: { callback: (v) => '₹' + v + 'K' } } }
                }
            });
            }

            // Main Revenue chart (line)
            var weeklyLabels = @json($weeklyLabels);
            var weeklyRevenueData = @json($weeklyRevenueData);

            function initRevenueTrend() {
            var cv = document.getElementById('revenueMainChart'); if (!cv) return;
            var old = Chart.getChart(cv); if (old) old.destroy();
            new Chart(cv, {
                type: 'line',
                data: {
                    labels: weeklyLabels,
                    datasets: [{
                        label: @json(__('Revenue')),
                        data: weeklyRevenueData,
                        borderColor: 'rgba(13,110,253,0.95)',
                        backgroundColor: 'rgba(13,110,253,0.12)',
                        tension: 0.35,
                        fill: true,
                        pointRadius: 3
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
            });
            }
            initRevenueExpenses();
            initRevenueTrend();

            // Medium widget charts (donut / area / bars) driven by data-* attributes
            function initKpiCharts(root) {
              (root || document).querySelectorAll('canvas.kpi-chart').forEach(function (cv) {
                var existing = Chart.getChart(cv);
                if (existing) { existing.destroy(); }
                var type = cv.dataset.type, color = cv.dataset.color;
                var values = JSON.parse(cv.dataset.values), labels = JSON.parse(cv.dataset.labels);
                var base = { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } };
                if (type === 'donut') {
                    var colors = cv.dataset.colors ? JSON.parse(cv.dataset.colors) : [color, 'rgba(15,23,42,.12)'];
                    var names = cv.dataset.names ? JSON.parse(cv.dataset.names) : ['Approved', 'Draft'];
                    var total = values.reduce(function (a, b) { return a + b; }, 0);
                    new Chart(cv, { type: 'doughnut', data: { labels: total ? names : [''], datasets: [{ data: total ? values : [1], backgroundColor: total ? colors : ['rgba(15,23,42,.12)'], borderWidth: 0 }] },
                        options: Object.assign({}, base, { cutout: '68%' }) });
                    return;
                }
                if (type === 'compare') {
                    var cds = JSON.parse(cv.dataset.datasets).map(function (d) {
                        return { label: d.label, data: d.data, borderColor: d.color, backgroundColor: d.color + '22', borderWidth: 2, borderDash: d.dashed ? [4, 4] : [],
                                 pointRadius: 0, pointHoverRadius: 4, tension: .3, spanGaps: false, fill: !d.dashed };
                    });
                    new Chart(cv, { type: 'line', data: { labels: labels, datasets: cds }, options: {
                        responsive: true, maintainAspectRatio: false, interaction: { mode: 'index', intersect: false },
                        plugins: { legend: { display: false } },
                        scales: { x: { display: false }, y: { display: false, beginAtZero: true } } } });
                    return;
                }
                if (type === 'dualbar') {
                    var ds = JSON.parse(cv.dataset.datasets).map(function (d) { return { label: d.label, data: d.data, backgroundColor: d.color, borderRadius: 3 }; });
                    new Chart(cv, { type: 'bar', data: { labels: labels, datasets: ds }, options: Object.assign({}, base, { scales: { x: { display: false }, y: { display: false } } }) });
                    return;
                }
                var axes = { scales: { x: { display: false }, y: { display: false } } };
                if (type === 'line') {
                    new Chart(cv, { type: 'line', data: { labels: labels, datasets: [{ data: values, borderColor: color, backgroundColor: color + '33', fill: true, tension: .4, pointRadius: 0, borderWidth: 2 }] },
                        options: Object.assign({}, base, axes) });
                } else {
                    new Chart(cv, { type: 'bar', data: { labels: labels, datasets: [{ data: values, borderRadius: 3, backgroundColor: type === 'profitbar' ? values.map(function (v) { return v >= 0 ? color : '#dc2626'; }) : color }] },
                        options: Object.assign({}, base, axes) });
                }
              });
            }

            // Drag & drop: reorder widgets, save the order per user.
            (function () {
                var grid = document.getElementById('dashGrid');
                if (!grid || !window.Sortable) return;
                var token = document.querySelector('meta[name="csrf-token"]').content;
                var savedEl = document.getElementById('dash-saved'), timer;
                function post(url, body) {
                    return fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' }, body: JSON.stringify(body || {}) });
                }
                grid.addEventListener('mousedown', function (e) { if (e.target.closest('.dash-handle')) e.preventDefault(); });
                grid.addEventListener('selectstart', function (e) { if (document.body.classList.contains('dash-dragging')) e.preventDefault(); });
                // Safety net: whatever ends the drag, never leave the page unselectable.
                ['pointerup', 'mouseup', 'pointercancel', 'dragend', 'blur'].forEach(function (t) {
                    window.addEventListener(t, function () { setTimeout(function () { document.body.classList.remove('dash-dragging'); }, 0); }, true);
                });
                Sortable.create(grid, {
                    animation: 180, handle: '.dash-handle', draggable: '.dash-item',
                    ghostClass: 'dash-ghost', chosenClass: 'dash-chosen', forceFallback: true, fallbackTolerance: 3, fallbackOnBody: true, scroll: true, scrollSensitivity: 80, scrollSpeed: 14, bubbleScroll: true,
                    onUnchoose: function () { document.body.classList.remove('dash-dragging'); },
                    onStart: function () { document.body.classList.add('dash-dragging'); var sel = window.getSelection && window.getSelection(); if (sel) sel.removeAllRanges(); },
                    onEnd: function () {
                        document.body.classList.remove('dash-dragging');
                        var sel = window.getSelection && window.getSelection(); if (sel) sel.removeAllRanges();
                        var order = Array.prototype.map.call(grid.querySelectorAll('.dash-item'), function (el) { return el.dataset.key; });
                        post(@json(route('dashboard.layout.save')), { order: order }).then(function (r) {
                            if (!r.ok) throw new Error();
                            savedEl.classList.remove('d-none');
                            clearTimeout(timer);
                            timer = setTimeout(function () { savedEl.classList.add('d-none'); }, 1800);
                        }).catch(function () { if (window.toastr) toastr.error(@json(__('Could not save the layout.'))); });
                    }
                });
                // ---- Widget panel: add / remove widgets -------------------------------------------------------
                var panel = document.getElementById('widgetPanel'), emptyEl = document.getElementById('dashEmpty');
                function currentOrder() { return Array.prototype.map.call(grid.querySelectorAll('.dash-item'), function (el) { return el.dataset.key; }); }
                function saveOrder() {
                    return post(@json(route('dashboard.layout.save')), { order: currentOrder() }).then(function (r) {
                        if (!r.ok) throw new Error();
                        savedEl.classList.remove('d-none'); clearTimeout(timer);
                        timer = setTimeout(function () { savedEl.classList.add('d-none'); }, 1800);
                    }).catch(function () { if (window.toastr) toastr.error(@json(__('Could not save the layout.'))); });
                }
                function refreshPanel() {
                    var on = currentOrder();
                    panel.querySelectorAll('.wp-chip[data-key]').forEach(function (c) {
                        var added = on.indexOf(c.dataset.key) !== -1;
                        c.classList.toggle('added', added);
                        c.title = (added ? @json(__('Remove')) : @json(__('Add'))) + ' ' + c.textContent;
                    });
                    panel.querySelectorAll('.wp-count').forEach(function (c) {
                        var n = 0;
                        c.closest('.wp-group').querySelectorAll('.wp-chip.added').forEach(function () { n++; });
                        c.textContent = n ? n + ' ' + @json(__('added')) : '';
                    });
                    emptyEl.classList.toggle('d-none', on.length > 0);
                }
                function openPanel() { if (typeof setEditing === 'function') setEditing(false); panel.classList.add('open'); panel.setAttribute('aria-hidden', 'false'); refreshPanel(); }
                function closePanel() { panel.classList.remove('open'); panel.setAttribute('aria-hidden', 'true'); }
                window.addEventListener('open-widget-panel', function () { panel.classList.contains('open') ? closePanel() : openPanel(); });
                document.getElementById('dash-open-panel').addEventListener('click', openPanel);
                document.querySelectorAll('[data-open-widget-panel]').forEach(function (b) { b.addEventListener('click', openPanel); });
                document.getElementById('wp-close').addEventListener('click', closePanel);
                document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closePanel(); });
                document.addEventListener('mousedown', function (e) {
                    if (panel.classList.contains('open') && !e.target.closest('#widgetPanel, #dash-open-panel, [data-widget-panel-toggle]')) closePanel();
                });

                // Edit mode: the "Remove widget" button shows the x badges on every widget; "Done" hides them again.
                var editBtn = document.getElementById('dash-edit-toggle'), dashWrap = document.getElementById('dashGrid').parentElement;
                function setEditing(on) {
                    dashWrap.classList.toggle('dash-editing', on);
                    editBtn.classList.toggle('btn-outline-danger', !on);
                    editBtn.classList.toggle('btn-danger', on);
                    editBtn.querySelector('span').textContent = on ? editBtn.dataset.labelDone : editBtn.dataset.labelEdit;
                    editBtn.querySelector('i').className = 'bi me-1 ' + (on ? 'bi-check-lg' : 'bi-dash-circle');
                    if (on) closePanel();
                    if (Sortable.get(grid)) Sortable.get(grid).option('disabled', on);
                }
                editBtn.addEventListener('click', function () { setEditing(!dashWrap.classList.contains('dash-editing')); });
                document.addEventListener('keydown', function (e) { if (e.key === 'Escape') setEditing(false); });

                function removeCell(cell) {
                    cell.classList.add('dash-removing');
                    setTimeout(function () { cell.remove(); saveOrder(); refreshPanel(); }, 260);
                }
                grid.addEventListener('click', function (e) {
                    var btn = e.target.closest('.dash-remove');
                    if (btn) removeCell(btn.closest('.dash-item'));
                });

                // A card "flies" from the panel row to its place on the dashboard.
                function fly(fromEl, toEl) {
                    var a = fromEl.getBoundingClientRect(), b = toEl.getBoundingClientRect();
                    var g = document.createElement('div');
                    g.className = 'dash-fly';
                    g.innerHTML = '<i class="bi bi-plus-lg"></i>';
                    g.style.cssText = 'left:' + a.left + 'px;top:' + a.top + 'px;width:' + a.width + 'px;height:' + a.height + 'px;';
                    document.body.appendChild(g);
                    requestAnimationFrame(function () {
                        g.style.left = b.left + 'px'; g.style.top = b.top + 'px'; g.style.width = b.width + 'px'; g.style.height = Math.min(b.height, 240) + 'px'; g.style.opacity = '.2';
                    });
                    setTimeout(function () { g.remove(); }, 650);
                }
                function addWidget(key, fromEl) {
                    if (grid.querySelector('.dash-item[data-key="' + key + '"]')) return;
                    fromEl.classList.add('busy');
                    fetch(@json(url('/dashboard/widget')) + '/' + key, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
                        .then(function (r) { if (!r.ok) throw new Error(); return r.json(); })
                        .then(function (res) {
                            var tpl = document.createElement('template');
                            tpl.innerHTML = res.html.trim();
                            var cell = tpl.content.firstElementChild;
                            cell.classList.add('dash-entering');
                            grid.insertBefore(cell, grid.firstChild);
                            emptyEl.classList.add('d-none');
                            initKpiCharts(cell);
                            if (key === 'revenue-expenses') initRevenueExpenses();
                            if (key === 'revenue-trend') initRevenueTrend();
                            window.scrollTo({ top: Math.max(0, grid.getBoundingClientRect().top + window.scrollY - 120), behavior: 'smooth' });
                            requestAnimationFrame(function () { fly(fromEl, cell); });
                            setTimeout(function () { cell.classList.remove('dash-entering'); cell.classList.add('dash-pulse'); }, 550);
                            setTimeout(function () { cell.classList.remove('dash-pulse'); }, 1800);
                            saveOrder(); refreshPanel();
                        })
                        .catch(function () { if (window.toastr) toastr.error(@json(__('Could not add the widget.'))); })
                        .finally(function () { fromEl.classList.remove('busy'); });
                }
                panel.addEventListener('click', function (e) {
                    var b = e.target.closest('.wp-chip[data-key]');
                    if (!b) return;
                    var key = b.dataset.key, cell = grid.querySelector('.dash-item[data-key="' + key + '"]');
                    if (cell) removeCell(cell); else addWidget(key, b);
                });
                refreshPanel();

                document.getElementById('dash-reset').addEventListener('click', function () {
                    post(@json(route('dashboard.layout.reset'))).then(function () { window.location.reload(); });
                });
            })();
            initKpiCharts(document);

            // Each Livewire widget re-renders on its own (e.g. month change): redraw only its charts.
            document.addEventListener('livewire:init', function () {
                Livewire.hook('commit', function (ctx) {
                    ctx.succeed(function () { setTimeout(function () { initKpiCharts(ctx.component.el); }, 0); });
                });
            });
        </script>



</x-app-layout>
