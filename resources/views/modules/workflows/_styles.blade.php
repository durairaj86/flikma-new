<style>
        /* Workflow flowchart (shared by every module's workflow popup) — compact so the whole chart fits without scrolling. */
        .fc { display: flex; flex-direction: column; align-items: center; font-size: .76rem; }
        .fc-node { position: relative; text-align: center; padding: 5px 12px; border: 1.5px solid; border-radius: 10px; min-width: 165px; max-width: 220px; line-height: 1.25; background: #fff; }
        .fc-node small { display: block; font-weight: 400; opacity: .8; font-size: .67rem; margin-top: 1px; }
        .fc-node .fc-step { display: block; font-size: .6rem; letter-spacing: .06em; text-transform: uppercase; opacity: .6; }
        .fc-node .badge { font-size: .62rem; padding: 2px 6px; }
        .fc-start, .fc-end { border-radius: 999px; }
        .fc-draft    { border-color: #f59e0b; background: #fffbeb; color: #92400e; }
        .fc-system   { border-color: #8b5cf6; background: #f5f3ff; color: #5b21b6; }
        .fc-ok       { border-color: #16a34a; background: #f0fdf4; color: #166534; }
        .fc-bad      { border-color: #dc2626; background: #fef2f2; color: #991b1b; }
        .fc-money    { border-color: #0891b2; background: #ecfeff; color: #155e75; }
        .fc-decision { width: 172px; height: 62px; padding: 0 30px; border: 0; background: #ddd6fe; color: #4c1d95; display: flex; align-items: center; justify-content: center; font-weight: 600; line-height: 1.15;
            clip-path: polygon(50% 0, 100% 50%, 50% 100%, 0 50%); text-align: center; }
        .fc-line { width: 2px; height: 14px; background: #94a3b8; position: relative; margin: 0 auto; }
        .fc-line::after { content: ''; position: absolute; bottom: -1px; left: 50%; transform: translateX(-50%); border: 4px solid transparent; border-top: 6px solid #94a3b8; border-bottom: 0; }
        .fc-row { display: flex; align-items: center; gap: 0; }
        .fc-hline { width: 16px; height: 2px; background: #94a3b8; position: relative; }
        .fc-hline::after { content: ''; position: absolute; right: -1px; top: 50%; transform: translateY(-50%); border: 4px solid transparent; border-left: 6px solid #94a3b8; border-right: 0; }
        .fc-branches { display: flex; justify-content: center; gap: 14px; width: 100%; }
        .fc-branch { position: relative; display: flex; flex-direction: column; align-items: center; padding-top: 13px; flex: 1 1 0; min-width: 0; }
        .fc-branch::before { content: ''; position: absolute; top: 0; left: 0; right: 0; border-top: 2px solid #94a3b8; }
        .fc-branch:first-child::before { left: 50%; } .fc-branch:last-child::before { right: 50%; } .fc-branch:only-child::before { display: none; }
        .fc-branch::after { content: ''; position: absolute; top: 0; left: 50%; width: 2px; height: 13px; background: #94a3b8; transform: translateX(-1px); }
        .fc-tag { position: absolute; top: 0; left: calc(50% + 6px); z-index: 1; font-size: .63rem; font-weight: 700; padding: 0 4px; border-radius: 5px; background: #fff; line-height: 1.2; }
        .fc-tag.yes { color: #16a34a; } .fc-tag.no { color: #dc2626; }
        .fc-note { font-size: .67rem; color: #64748b; max-width: 260px; text-align: center; margin: 1px 0 0; }
        /* left info panel */
        .fc-info h6 { font-size: .7rem; letter-spacing: .06em; text-transform: uppercase; color: #94a3b8; font-weight: 700; margin: 0 0 6px; }
        .fc-info .fc-stat { display: flex; align-items: flex-start; gap: 6px; padding: 5px 7px; border-radius: 8px; border: 1px solid; margin-bottom: 6px; font-size: .74rem; color: #475569; line-height: 1.3; }
        .fc-info .fc-stat .badge { font-size: .58rem; flex-shrink: 0; }
        .fc-info ul { list-style: none; padding: 0; margin: 0; font-size: .74rem; color: #475569; }
        .fc-info li { display: flex; gap: 6px; padding: 3px 0; line-height: 1.3; }
        .fc-info li i { color: #6366f1; margin-top: 2px; flex-shrink: 0; }
        .fc-dialog { max-width: 900px; }
        .fc-wrap { display: grid; grid-template-columns: 230px 1fr; gap: 18px; align-items: start; }
        @media (max-width: 991.98px) { .fc-wrap { grid-template-columns: 1fr; } }
    </style>
