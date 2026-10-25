/* Today's briefing: greeting + date use the user's own clock; the cards come from /briefing. */
(function () {
    var modalEl = document.getElementById('briefingModal');
    if (!modalEl || !window.bootstrap) return;
    var D = modalEl.dataset;
    var body = document.getElementById('briefBody');
    var stats = document.getElementById('briefStats');
    var KEY = 'briefing_count';
    var greetText = '', runId = 0, skipTyping = false;
    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function el(tag, cls, text) { var e = document.createElement(tag); if (cls) e.className = cls; if (text != null) e.textContent = text; return e; }

    function setBadge(n) {
        try { sessionStorage.setItem(KEY, String(n)); } catch (e) {}
        document.querySelectorAll('[data-briefing-badge]').forEach(function (b) { b.textContent = n > 0 ? (n > 99 ? '99+' : n) : ''; });
    }

    function header(name) {
        var now = new Date(), h = now.getHours();
        var greet = h < 12 ? D.morning : (h < 18 ? D.afternoon : D.evening);
        greetText = greet + (name ? ', ' + name : '');
        document.getElementById('briefGreet').textContent = greetText;
        var day;
        try { day = new Intl.DateTimeFormat(D.locale === 'ar' ? 'ar-SA' : undefined, { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }).format(now); } catch (e) { day = now.toDateString(); }
        document.getElementById('briefDate').textContent = D.today + ' ' + day;
    }

    function render(d) {
        header(d.name);
        setBadge(d.count || 0);
        stats.innerHTML = '';
        stats.classList.add('pre');
        (d.stats || []).forEach(function (s) {
            var c = el('div', 'brief-stat ' + (s.tone === 'bad' ? 'bad' : ''));
            c.appendChild(el('div', 'v', s.value));
            var l = el('div', 'l'); l.innerHTML = '<i class="bi ' + s.icon + '"></i>'; l.appendChild(document.createTextNode(s.label));
            c.appendChild(l); stats.appendChild(c);
        });
        body.innerHTML = '';
        if (!d.items || !d.items.length) {
            var e = el('div', 'brief-empty'); e.innerHTML = '<i class="bi bi-check2-circle"></i>'; e.appendChild(document.createTextNode(D.empty));
            body.appendChild(e);
            prepare(d, e);
            return;
        }
        var bubble = el('div', 'brief-ai');
        bubble.innerHTML = '<span class="brief-ai-ico"><i class="bi bi-stars"></i></span><span class="brief-ai-txt"></span>';
        body.appendChild(bubble);
        var lastTone = null, names = { bad: D.secBad, warn: D.secWarn, info: D.secInfo, good: D.secGood };
        d.items.forEach(function (it) {
            if (it.tone !== lastTone) { lastTone = it.tone; if (names[it.tone]) body.appendChild(el('div', 'brief-sec', names[it.tone])); }
            var card = el('div', 'brief-card ' + (it.tone || 'info'));
            var ico = el('div', 'brief-ico'); ico.innerHTML = '<i class="bi ' + (it.icon || 'bi-info-circle') + '"></i>';
            var main = el('div', 'brief-main');
            if (it.group) main.appendChild(el('div', 'brief-group', it.group));
            main.appendChild(el('div', 'brief-title', it.title));
            main.appendChild(el('div', 'brief-text', it.text));
            if (it.rows && it.rows.length) {
                var ul = el('ul', 'brief-rows');
                it.rows.slice(0, 3).forEach(function (r) {
                    var li = el('li'); li.appendChild(el('div', 'rl', r.label)); if (r.sub) li.appendChild(el('div', 'rs', r.sub)); ul.appendChild(li);
                });
                main.appendChild(ul);
                var hidden = Math.max((it.count || it.rows.length) - Math.min(3, it.rows.length), 0);
                if (hidden > 0) main.appendChild(el('div', 'brief-more', '+' + hidden + ' ' + D.more));
            }
            if (it.link_url) { var a = el('a', 'brief-link', it.link_label + ' →'); a.href = it.link_url; main.appendChild(a); }
            card.appendChild(ico); card.appendChild(main); body.appendChild(card);
        });
        prepare(d, bubble);
    }

    /* ---- typing, like an assistant writing the briefing ---- */
    function sleep(ms) { return new Promise(function (r) { setTimeout(r, ms); }); }

    function typeInto(node, text, id) {
        return new Promise(function (resolve) {
            node.textContent = '';
            var caret = el('span', 'brief-caret'); node.appendChild(caret);
            var i = 0, step = Math.max(6, Math.min(24, Math.floor(1800 / Math.max(text.length, 1))));
            (function tick() {
                if (id !== runId) return resolve(false);
                if (skipTyping || i >= text.length) { caret.remove(); node.textContent = text; return resolve(true); }
                caret.before(document.createTextNode(text.charAt(i++)));
                setTimeout(tick, step);
            })();
        });
    }

    function summary(d) {
        var c = { bad: 0, warn: 0, info: 0 };
        (d.items || []).forEach(function (it) { if (c[it.tone] !== undefined) c[it.tone]++; });
        var parts = [];
        if (c.bad) parts.push(c.bad + ' ' + D.sBad);
        if (c.warn) parts.push(c.warn + ' ' + D.sWarn);
        if (c.info) parts.push(c.info + ' ' + D.sInfo);
        if (!parts.length) return d.items && d.items.length ? D.sLead.trim() : D.empty;
        var first = (d.items || []).filter(function (it) { return it.tone === 'bad' || it.tone === 'warn'; })[0];
        return D.sLead + parts.join(', ') + '.' + (first ? '\n' + D.sFirst + ' ' + first.title + '.' : '');
    }

    // after typing: bold the counts and the first alert, one idea per line
    function highlight(node, text) {
        var esc = function (s) { return s.replace(/[&<>]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;' }[c]; }); };
        var lines = text.split('\n');
        var html = esc(lines[0]).replace(/(\d+)/g, '<strong>$1</strong>');
        if (lines[1]) {
            var label = D.sFirst, rest = lines[1].indexOf(label) === 0 ? lines[1].slice(label.length).trim() : lines[1];
            html += '<br><span class="brief-ai-first">' + esc(label) + ' <strong>' + esc(rest) + '</strong></span>';
        }
        node.innerHTML = html;
    }

    var pending = null;
    function prepare(d, bubble) {
        pending = { text: summary(d), bubble: bubble, hasItems: !!(d.items && d.items.length) };
        body.querySelectorAll('.brief-sec, .brief-card').forEach(function (n) { n.classList.add('pre'); });
        if (reduce) { play(); return; }
        if (modalEl.classList.contains('show')) play();
        else modalEl.addEventListener('shown.bs.modal', function once() { modalEl.removeEventListener('shown.bs.modal', once); play(); });
    }

    function play() {
        var p = pending; if (!p) return;
        var id = ++runId; skipTyping = reduce;
        var gEl = document.getElementById('briefGreet'), dEl = document.getElementById('briefDate');
        var dateText = dEl.textContent;
        gEl.textContent = ''; dEl.style.opacity = 0;
        typeInto(gEl, greetText, id).then(function (ok) {
            if (!ok) return;
            dEl.style.opacity = 1; stats.classList.remove('pre');
            return sleep(reduce ? 0 : 250).then(function () {
                if (id !== runId) return;
                var txt = p.bubble.querySelector('.brief-ai-txt');
                if (!txt) { return; }
                return typeInto(txt, p.text, id).then(function (ok) { if (ok) highlight(txt, p.text); });
            });
        }).then(function () {
            if (id !== runId) return;
            var nodes = body.querySelectorAll('.pre');
            var i = 0;
            (function reveal() {
                if (id !== runId) return;
                if (skipTyping) { nodes.forEach(function (n) { n.classList.remove('pre'); }); return; }
                if (i >= nodes.length) return;
                nodes[i++].classList.remove('pre');
                setTimeout(reveal, 160);
            })();
        });
    }

    // a click anywhere in the popup body finishes the typing at once
    body.addEventListener('click', function () { skipTyping = true; });
    modalEl.addEventListener('hidden.bs.modal', function () { runId++; });

    function get(auto) {
        return fetch(D.url + (auto ? '?auto=1' : ''), { credentials: 'same-origin', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { if (!r.ok) throw new Error(); return r.json(); });
    }

    var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    function load() {
        header('');
        stats.innerHTML = '';
        body.innerHTML = '<div class="brief-skel"></div><div class="brief-skel"></div><div class="brief-skel short"></div>';
        get(false).then(render).catch(function () { body.innerHTML = ''; body.appendChild(el('div', 'brief-empty', D.error)); });
    }

    document.addEventListener('click', function (e) {
        var t = e.target.closest('[data-briefing-open]');
        if (!t) return;
        e.preventDefault();
        modal.show(); load();
    });
    document.getElementById('briefRefresh').addEventListener('click', load);

    // Once per day (decided server-side): look quietly first and only pop up when there is something to act on.
    if (D.auto === '1') {
        // Not straight after login: wait a random 30 s - 2 min first. The server is only asked (and only marks today's
        // briefing as shown) when the timer fires, so leaving the page earlier doesn't use up the day's popup.
        var delay = (30 + Math.floor(Math.random() * 91)) * 1000;
        var fire = function () {
            // don't pop over something the user is working in — try again shortly
            if (document.querySelector('.modal.show, .offcanvas.show')) { setTimeout(fire, 20000); return; }
            get(true).then(function (d) {
                setBadge(d.count || 0);
                if (d.allowed && d.actionable > 0) { render(d); modal.show(); }
            }).catch(function () {});
        };
        setTimeout(fire, delay);
    } else {
        var c = null; try { c = sessionStorage.getItem(KEY); } catch (e) {}
        if (c !== null) setBadge(parseInt(c, 10) || 0);
        else get(false).then(function (d) { setBadge(d.count || 0); }).catch(function () {});
    }
})();
