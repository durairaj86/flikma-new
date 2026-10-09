@section('page-title', 'Autocheck')
@section('hide-topbar', true)
<x-app-layout>
    <style>
        .ac-wrap { display: grid; grid-template-columns: 400px 1fr; gap: 1rem; align-items: start; }
        @media (max-width: 1100px) { .ac-wrap { grid-template-columns: 1fr; } }
        .ac-card { background: #fff; border-radius: 14px; box-shadow: 0 1px 2px rgba(0,0,0,.04), 0 8px 24px rgba(15,23,42,.07); }
        .ac-step { display: flex; gap: .75rem; padding: .7rem 1rem; border-bottom: 1px solid #f1f5f9; }
        .ac-step:last-child { border-bottom: 0; }
        .ac-ico { width: 26px; height: 26px; border-radius: 50%; flex-shrink: 0; display: inline-flex; align-items: center; justify-content: center; font-size: .8rem; background: #eef2f6; color: #94a3b8; }
        .ac-step.running .ac-ico { background: #e7f0fe; color: #0d6efd; }
        .ac-step.pass .ac-ico { background: #dcfce7; color: #16a34a; }
        .ac-step.fail .ac-ico { background: #fee2e2; color: #dc2626; }
        .ac-title { font-weight: 600; font-size: .9rem; color: #0f172a; }
        .ac-checks { list-style: none; margin: .35rem 0 0; padding: 0; font-size: .78rem; color: #475569; }
        .ac-checks li { display: flex; gap: .4rem; }
        .ac-checks .bad { color: #dc2626; font-weight: 600; }
        .ac-checks .val { color: #94a3b8; margin-left: auto; text-align: right; }
        .ac-frame { width: 100%; height: calc(100vh - 170px); min-height: 520px; border: 0; border-radius: 14px; background: #fff; }
        .ac-banner { border-radius: 12px; padding: .75rem 1rem; font-weight: 600; }
        .ac-banner.pass { background: #dcfce7; color: #166534; }
        .ac-banner.fail { background: #fee2e2; color: #991b1b; }
    </style>

    <main class="gmail-content px-3 pt-3 pb-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <div>
                <h4 class="fw-bold text-dark mb-0">Autocheck</h4>
                <div class="text-muted small">End-to-end check across customers, prospects, suppliers, sales, jobs, invoices, credit notes, collections, expenses and the finance reports (statements, aging, general ledger, trial balance, balance sheet, tax summary). Local environment only.</div>
            </div>
        </div>

        <div class="ac-wrap">
            <section class="ac-card">
                <div class="p-3 border-bottom">
                    <div class="d-flex flex-wrap gap-2 mb-2">
                        <button class="btn btn-primary rounded-pill px-3" id="runHead"><i class="bi bi-display me-1"></i> Run with head (live)</button>
                        <button class="btn btn-outline-primary rounded-pill px-3" id="runHeadless"><i class="bi bi-terminal me-1"></i> Run headless</button>
                    </div>
                    <div class="row g-2 align-items-end">
                        <div class="col-6">
                            <label class="form-label small text-muted mb-1">Run until</label>
                            <select class="form-select form-select-sm" id="until">
                                @foreach($steps as $key => [$title, $url])
                                    <option value="{{ $key }}" @selected($loop->last)>{{ $title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small text-muted mb-1">Live speed</label>
                            <select class="form-select form-select-sm" id="speed">
                                <option value="5000">Slow</option>
                                <option value="3000" selected>Normal</option>
                                <option value="1500">Fast</option>
                            </select>
                        </div>
                    </div>
                    <div class="small text-muted mt-2">
                        <b>Headless</b> runs everything in one transaction and rolls it back — nothing is saved.<br>
                        <b>Head</b> saves real records and shows each screen on the right as it happens.
                    </div>
                </div>

                <div id="banner" class="d-none m-3 ac-banner"></div>

                <div id="steps">
                    @foreach($steps as $key => [$title, $url])
                        <div class="ac-step" id="step-{{ $key }}" data-step="{{ $key }}">
                            <span class="ac-ico"><i class="bi bi-circle"></i></span>
                            <div class="flex-grow-1">
                                <div class="ac-title">{{ $loop->iteration }}. {{ $title }}</div>
                                <ul class="ac-checks"></ul>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="p-3 border-top d-flex flex-wrap gap-2">
                    <button class="btn btn-sm btn-outline-danger rounded-pill" id="cleanup"><i class="bi bi-trash me-1"></i> Clean up head-run data</button>
                    <button class="btn btn-sm btn-outline-secondary rounded-pill" id="clear"><i class="bi bi-arrow-counterclockwise me-1"></i> Reset view</button>
                </div>
            </section>

            <section>
                <div class="ac-card p-0 overflow-hidden" id="frameCard">
                    <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
                        <div class="small fw-semibold text-muted" id="frameLabel">Live view — the real screens appear here during a head run</div>
                        <span class="badge bg-light text-dark border" id="frameUrl"></span>
                    </div>
                    <iframe class="ac-frame" id="frame" src="about:blank"></iframe>
                </div>
            </section>
        </div>
    </main>

    <script>
        (function () {
            const csrf = @json(csrf_token());
            const stepKeys = @json(array_keys($steps));
            const $ = (s) => document.querySelector(s);
            let busy = false;

            async function post(url, body) {
                const r = await fetch(url, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
                    body: JSON.stringify(body || {}),
                });
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.json();
            }

            function setState(key, state, res) {
                const el = $('#step-' + key);
                if (!el) return;
                el.className = 'ac-step ' + state;
                el.querySelector('.ac-ico').innerHTML = state === 'running'
                    ? '<span class="spinner-border spinner-border-sm" style="width:14px;height:14px"></span>'
                    : state === 'pass' ? '<i class="bi bi-check-lg"></i>'
                    : state === 'fail' ? '<i class="bi bi-x-lg"></i>' : '<i class="bi bi-circle"></i>';
                const ul = el.querySelector('.ac-checks');
                ul.innerHTML = '';
                (res?.checks || []).forEach(c => {
                    const li = document.createElement('li');
                    li.innerHTML = '<span>' + (c.ok ? '✓' : '✗') + '</span><span class="' + (c.ok ? '' : 'bad') + '">' + c.label + '</span>' +
                        '<span class="val">' + (c.value === null || c.value === undefined ? '' : String(c.value).replace(/</g, '&lt;')) + '</span>';
                    ul.appendChild(li);
                });
            }

            function resetView() {
                stepKeys.forEach(k => setState(k, 'pending'));
                $('#banner').className = 'd-none m-3 ac-banner';
            }

            function banner(ok, text) {
                const b = $('#banner');
                b.className = 'm-3 ac-banner ' + (ok ? 'pass' : 'fail');
                b.textContent = text;
            }

            function show(url) {
                $('#frame').src = url;
                $('#frameUrl').textContent = url;
            }

            const sleep = (ms) => new Promise(r => setTimeout(r, ms));

            function lock(on) {
                busy = on;
                ['#runHead', '#runHeadless', '#cleanup', '#clear'].forEach(s => $(s).disabled = on);
            }

            $('#runHeadless').addEventListener('click', async () => {
                if (busy) return;
                lock(true); resetView();
                $('#frameLabel').textContent = 'Headless run — no screens shown, nothing saved';
                $('#frame').src = 'about:blank'; $('#frameUrl').textContent = '';
                stepKeys.forEach(k => setState(k, 'running'));
                try {
                    const out = await post('{{ route('autocheck.headless') }}', {until: $('#until').value});
                    stepKeys.forEach(k => setState(k, 'pending'));
                    out.results.forEach(r => setState(r.step, r.ok ? 'pass' : 'fail', r));
                    const ok = out.results.every(r => r.ok);
                    const checks = out.results.reduce((n, r) => n + r.checks.length, 0);
                    banner(ok, (ok ? 'All checks passed' : 'A check failed') + ' — ' + checks + ' checks, data rolled back.');
                } catch (e) { banner(false, 'Run failed: ' + e.message); }
                lock(false);
            });

            $('#runHead').addEventListener('click', async () => {
                if (busy) return;
                lock(true); resetView();
                $('#frameLabel').textContent = 'Live view — watching the real screens';
                try {
                    await post('{{ route('autocheck.reset') }}');
                    const until = $('#until').value, delay = parseInt($('#speed').value, 10);
                    let all = true, n = 0;
                    for (const k of stepKeys) {
                        setState(k, 'running');
                        const res = await post('{{ route('autocheck.step') }}', {step: k});
                        n += res.checks.length;
                        setState(k, res.ok ? 'pass' : 'fail', res);
                        show(res.url);
                        await sleep(delay);
                        if (!res.ok) { all = false; break; }
                        if (k === until) break;
                    }
                    banner(all, (all ? 'All steps passed' : 'A step failed') + ' — ' + n + ' checks. The records are saved; use "Clean up" to remove them.');
                } catch (e) { banner(false, 'Run failed: ' + e.message); }
                lock(false);
            });

            $('#cleanup').addEventListener('click', async () => {
                if (busy) return;
                lock(true);
                try {
                    const out = await post('{{ route('autocheck.cleanup') }}');
                    banner(true, out.hadData ? 'Head-run data removed.' : 'Nothing to clean up.');
                    resetView();
                    $('#frame').src = 'about:blank'; $('#frameUrl').textContent = '';
                } catch (e) { banner(false, 'Cleanup failed: ' + e.message); }
                lock(false);
            });

            $('#clear').addEventListener('click', () => { resetView(); $('#frame').src = 'about:blank'; $('#frameUrl').textContent = ''; });
        })();
    </script>
</x-app-layout>
