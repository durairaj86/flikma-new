/*
 * Keyboard-first use of the whole app (loaded on every page).
 *
 *  Forms / modals
 *    Enter            move to the next field (Enter on the last field of the last line-item row adds a new row)
 *    Tab              in a dropdown, picks the highlighted match (type to filter, Tab to accept)
 *    Alt+S            save (existing)           F3 / Shift+F3   next / previous tab of the form
 *    Alt+A            add a line-item row       Alt+R           remove the current row
 *    Esc              close a dropdown / ask to close the form (existing)
 *  Lists
 *    Alt+N            new record                Alt+F or /      jump to the search box
 *    Up / Down        move between rows         Enter           open the row's action menu
 *    Up / Down / Enter / Esc in the menu;  Right / Left opens / closes a sub menu
 *  Anywhere
 *    Alt+G            go to any page (type to filter, Enter to open)
 *    F1, Ctrl+/ or ?  show this list
 */
(function () {
    'use strict';
    if (window.__ffKeyboard) return;
    window.__ffKeyboard = true;

    var ADD_SEL = '.add-row, .addContainerRow, .addPackageRow, .chg-add-row, .add-document-row';
    var REMOVE_SEL = '.remove-row, .chg-remove-row';
    var FIELD_SEL = 'input:not([type=hidden]):not([type=file]):not(.dropdown-input):not(.ts-hidden-accessible), select, textarea';

    // ---- Tom Select: Tab accepts the highlighted option ---------------------------------------------------------
    function tsDefaults() {
        if (window.TomSelect && TomSelect.defaults) TomSelect.defaults.selectOnTab = true;
    }
    tsDefaults();
    document.addEventListener('DOMContentLoaded', tsDefaults);

    // ---- styles -------------------------------------------------------------------------------------------------
    var css = document.createElement('style');
    css.textContent =
        ':focus-visible{outline:2px solid #4f46e5;outline-offset:2px}' +
        '.ts-control:focus-within,.ts-wrapper.focus .ts-control{box-shadow:0 0 0 .2rem rgba(79,70,229,.25)!important;border-color:#4f46e5!important}' +
        'tr[data-id]:focus-visible,tr[data-id].kb-row{outline:2px solid #4f46e5;outline-offset:-2px;background:rgba(79,70,229,.06)}' +
        '.add-row,.remove-row,.addContainerRow,.addPackageRow,.chg-add-row,.chg-remove-row,.dropdown-item{cursor:pointer}' +
        '.dropdown-submenu>.dropdown-menu.show{display:block}' +
        '.dropdown-item:focus,.dropdown-item:focus-visible{background:rgba(79,70,229,.12);outline:none}' +
        '#kbOverlay{position:fixed;inset:0;z-index:20000;background:rgba(15,23,42,.45);display:flex;align-items:flex-start;justify-content:center;padding-top:12vh}' +
        '#kbOverlay .kb-box{background:#fff;border-radius:14px;box-shadow:0 20px 60px rgba(0,0,0,.3);width:min(560px,92vw);max-height:70vh;overflow:auto;padding:16px 18px}' +
        '#kbOverlay h6{font-weight:700;margin:0 0 10px}' +
        '#kbOverlay.kb-center{align-items:center;padding-top:0}' +
        '#kbOverlay.kb-center .kb-box{width:min(680px,94vw);max-height:78vh;display:flex;flex-direction:column;padding:0;overflow:hidden}' +
        '#kbOverlay .kb-search{display:flex;align-items:center;gap:10px;padding:14px 18px;border-bottom:1px solid #eef0f3}' +
        '#kbOverlay .kb-search i{color:#667085;font-size:1.1rem}' +
        '#kbOverlay .kb-search input{border:0;padding:4px 0;font-size:17px;box-shadow:none!important}' +
        '#kbOverlay .kb-results{overflow:auto;padding:6px 8px;flex:1 1 auto}' +
        '#kbOverlay .kb-group{font-size:.7rem;font-weight:700;letter-spacing:.04em;text-transform:uppercase;color:#667085;padding:10px 10px 4px}' +
        '#kbOverlay .kb-item{display:flex;justify-content:space-between;gap:14px;align-items:baseline;padding:8px 10px;border-radius:8px;cursor:pointer}' +
        '#kbOverlay .kb-item small{color:#98a2b3;white-space:nowrap}' +
        '#kbOverlay .kb-item.on{background:rgba(79,70,229,.12)}' +
        '#kbOverlay .kb-item mark{background:transparent;color:#4f46e5;font-weight:700;padding:0}' +
        '#kbOverlay .kb-none{padding:22px;text-align:center;color:#98a2b3}' +
        '#kbOverlay .kb-foot{display:flex;gap:16px;align-items:center;padding:8px 16px;border-top:1px solid #eef0f3;font-size:.75rem;color:#667085}' +
        '#kbOverlay .kb-foot #kbCount{margin-left:auto}' +
        '#kbOverlay input{width:100%;border:1px solid #d0d5dd;border-radius:8px;padding:10px 12px;font-size:15px;outline:none}' +
        '#kbOverlay input:focus{border-color:#4f46e5;box-shadow:0 0 0 .2rem rgba(79,70,229,.2)}' +
        '#kbOverlay ul{list-style:none;margin:10px 0 0;padding:0}' +
        '#kbOverlay li{padding:8px 10px;border-radius:8px;cursor:pointer;display:flex;justify-content:space-between;gap:12px}' +
        '#kbOverlay li small{color:#6b7280}' +
        '#kbOverlay li.on{background:rgba(79,70,229,.12)}' +
        '#kbOverlay table{width:100%;font-size:14px}#kbOverlay td{padding:5px 6px;border-bottom:1px solid #eef0f3}' +
        '#kbOverlay kbd{background:#f2f4f7;color:#344054;border:1px solid #d0d5dd;border-bottom-width:2px;border-radius:5px;padding:1px 7px;font-size:12px;font-weight:600;font-family:inherit;white-space:nowrap}' +
        '#kbOverlay .kb-box table td:first-child{white-space:nowrap}';
    (document.head || document.documentElement).appendChild(css);

    // ---- helpers ------------------------------------------------------------------------------------------------
    function visible(el) { return !!(el && (el.offsetParent !== null || el.getClientRects().length)); }
    function modalOpen() { var m = document.getElementById('globalModal'); return !!(m && m.classList.contains('show')); }
    function confirmOpen() { return !!document.querySelector('.jconfirm'); }
    function typing(el) { return !!(el && el.matches && el.matches('input:not([type=checkbox]):not([type=radio]):not([type=button]):not([type=submit]), textarea, select, [contenteditable=true]')); }
    function tsOf(el) {
        // the Tom Select behind any element that lives inside its wrapper (control, search input, dropdown)
        var w = el && el.closest && el.closest('.ts-wrapper, .ts-dropdown');
        if (!w) return null;
        if (w.classList.contains('ts-dropdown')) w = w.closest('.ts-wrapper') || w;
        var host = w.parentNode;
        var sel = host && host.querySelector('select.tomselected, select.ts-hidden-accessible');
        return sel && sel.tomselect ? sel.tomselect : null;
    }
    function fieldOf(el) { var ts = tsOf(el); return ts ? ts.input : el; }

    function fields(scope) {
        var out = [];
        scope.querySelectorAll(FIELD_SEL).forEach(function (el) {
            if (el.disabled) return;
            if (el.type === 'hidden' || el.closest('.ts-dropdown')) return;
            if (el.tomselect) { if (visible(el.tomselect.wrapper)) out.push(el); return; }
            if (el.closest('.ts-wrapper')) return;
            if (el.readOnly) return;
            if (visible(el)) out.push(el);
        });
        return out;
    }
    function focusField(el) {
        if (!el) return;
        if (el.tomselect) { el.tomselect.focus(); return; }
        el.focus();
        if (el.select && /^(text|number|search|tel|email|)$/.test(el.type || '')) { try { el.select(); } catch (e) { /* */ } }
    }
    function currentField(target) {
        var ts = tsOf(target);
        return ts ? ts.input : target;
    }

    // ---- 1. make mouse-only controls reachable ------------------------------------------------------------------
    function makeFocusable(root) {
        if (!root || root.nodeType !== 1) return;
        var list = root.matches && root.matches(ADD_SEL + ',' + REMOVE_SEL + ',.dropdown-item,[data-bs-toggle=dropdown]:not(button):not([href])') ? [root] : [];
        list = list.concat(Array.prototype.slice.call(root.querySelectorAll ? root.querySelectorAll(ADD_SEL + ',' + REMOVE_SEL + ',.dropdown-item,[data-bs-toggle=dropdown]:not(button):not([href])') : []));
        list.forEach(function (el) {
            if (el.__kb) return;
            el.__kb = true;
            if (!el.hasAttribute('tabindex')) el.setAttribute('tabindex', el.classList.contains('dropdown-item') ? '-1' : '0');
            if (!el.getAttribute('role')) el.setAttribute('role', el.classList.contains('dropdown-item') ? 'menuitem' : 'button');
            if (!el.getAttribute('aria-label') && (el.matches(ADD_SEL + ',' + REMOVE_SEL))) {
                el.setAttribute('aria-label', el.matches(ADD_SEL) ? 'Add row (Alt+A)' : 'Remove row (Alt+R)');
            }
        });
        // grey help lines under fields are not Tab stops (they still show their text on hover)
        (root.matches && root.matches('.field-hint') ? [root] : []).concat(Array.prototype.slice.call(root.querySelectorAll ? root.querySelectorAll('.field-hint') : [])).forEach(function (h) { h.setAttribute('tabindex', '-1'); });
        // list rows
        var rows = root.matches && root.matches('#dataTable tbody tr[data-id]') ? [root] : [];
        rows = rows.concat(Array.prototype.slice.call(root.querySelectorAll ? root.querySelectorAll('#dataTable tbody tr[data-id]') : []));
        rows.forEach(function (r, i) { if (!r.hasAttribute('tabindex')) r.setAttribute('tabindex', '0'); });
    }
    function startObserver() {
        makeFocusable(document.body);
        new MutationObserver(function (list) {
            for (var i = 0; i < list.length; i++) {
                for (var j = 0; j < list[i].addedNodes.length; j++) makeFocusable(list[i].addedNodes[j]);
            }
        }).observe(document.body, {childList: true, subtree: true});
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', startObserver); else startObserver();

    // Enter / Space presses the control that has focus (div/anchor buttons)
    document.addEventListener('keydown', function (e) {
        var el = e.target;
        if (!el || !el.matches || !el.matches(ADD_SEL + ',' + REMOVE_SEL + ',.dropdown-item,[data-bs-toggle=dropdown]:not(button)')) return;
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault(); e.stopPropagation();
            el.click();
        }
    }, true);

    // ---- 2. forms: Enter = next field, line items ---------------------------------------------------------------
    function rowOf(el) { return el && el.closest ? el.closest('tbody > tr') : null; }
    // has the user typed anything in this row? (untouched defaults such as 0, 0.00 and 1 do not count)
    function rowFilled(tr) {
        // a dropdown with a choice made in it counts too (new rows start with every dropdown empty)
        if (Array.prototype.some.call(tr.querySelectorAll('select'), function (sel) { return sel.value !== '' && sel.value != null; })) return true;
        return Array.prototype.some.call(tr.querySelectorAll('input:not([type=hidden]):not([type=checkbox]):not([type=radio]), textarea'), function (el) {
            if (el.closest('.ts-wrapper, .ts-dropdown, .flatpickr-calendar')) return false;
            var v = String(el.value || '').trim();
            return v !== '' && !/^(0+(\.0*)?|\.0+|1)$/.test(v.replace(/,/g, ''));
        });
    }
    function addRowFrom(el) {
        var tr = rowOf(el), tbody = tr && tr.parentNode;
        var btn = tr && tr.querySelector(ADD_SEL);
        if (!btn) return false;
        var before = tbody.children.length;
        btn.click();
        setTimeout(function () {
            var last = tbody.lastElementChild, f = last && fields(last)[0];
            if (tbody.children.length > before) focusField(f);
        }, 60);
        return true;
    }
    function removeRowFrom(el) {
        var tr = rowOf(el), tbody = tr && tr.parentNode;
        var btn = tr && tr.querySelector(REMOVE_SEL);
        if (!btn) return false;
        var prev = tr.previousElementSibling || tr.nextElementSibling;
        btn.click();
        setTimeout(function () {
            var tgt = (tr.isConnected ? tr : prev), f = tgt && fields(tgt)[0];
            focusField(f);
        }, 60);
        return true;
    }

    // The tabs of a form (General / Containers / Packages ...): moving past the last field of a tab opens the next tab.
    function tabButtons(scope) {
        var box = scope.closest ? (scope.closest('#globalModal') || scope.closest('.modal-content') || scope.closest('form')) : null;
        return Array.prototype.slice.call((box || document).querySelectorAll('#modalTabs .nav-link, .nav-tabs .nav-link')).filter(visible);
    }
    function openTab(btn, last) {
        var target = btn.getAttribute('data-bs-target') || btn.getAttribute('href');
        var pane = target && document.querySelector(target);
        var go = function () {
            var list = pane ? fields(pane) : [];
            var f = last ? list[list.length - 1] : list[0];
            if (f) focusField(f); else btn.focus();
        };
        if (btn.classList.contains('active')) { go(); return; }
        btn.addEventListener('shown.bs.tab', function once() { btn.removeEventListener('shown.bs.tab', once); setTimeout(go, 20); });
        if (window.bootstrap && bootstrap.Tab) bootstrap.Tab.getOrCreateInstance(btn).show(); else btn.click();
    }
    // next / previous field from cur; crosses into the neighbouring tab. Returns false when there is nowhere to go.
    function step(cur, dir) {
        var pane = cur.closest('.tab-pane'), scope = pane || cur.closest('form');
        if (!scope) return false;
        var list = fields(scope), i = list.indexOf(cur);
        if (i === -1) return false;
        var nx = list[i + dir];
        if (nx) { focusField(nx); return true; }
        if (pane) {
            var tabs = tabButtons(pane), cur_i = tabs.findIndex(function (b) { return b.classList.contains('active'); });
            var nt = tabs[cur_i + dir];
            if (nt) { openTab(nt, dir < 0); return true; }
        }
        return false;
    }

    document.addEventListener('keydown', function (e) {
        if (e.defaultPrevented && e.key !== 'Enter') return;
        if (confirmOpen() || e.isComposing) return;
        var t = e.target;

        // remember that the user typed / arrowed in an open dropdown: only then is its highlighted option "chosen"
        var navTs = tsOf(t);
        if (navTs && (e.key === 'ArrowDown' || e.key === 'ArrowUp' || (e.key.length === 1 && !e.ctrlKey && !e.altKey && !e.metaKey))) {
            navTs.__kbNav = true;
            if (!navTs.__kbBound) { navTs.__kbBound = true; navTs.on('dropdown_close', function () { navTs.__kbNav = false; }); }
        }

        // Tab / Shift+Tab inside a form: dropdowns take the highlighted match and use one stop; the end of a tab opens the next tab
        if (e.key === 'Tab' && !e.ctrlKey && !e.altKey && !e.metaKey && t && t.closest && t.closest('#globalModal form')) {
            var tts = tsOf(t);
            var curF = tts ? tts.input : t;
            if (tts && tts.settings.maxItems === 1 && tts.isOpen && tts.activeOption && tts.__kbNav && !e.shiftKey) {
                var v = tts.activeOption.dataset.value;
                if (v !== undefined && v !== '' && String(tts.getValue()) !== String(v)) tts.addItem(v);
            }
            if (t.closest('.flatpickr-calendar')) return;
            // Tab out of the last field of the last line-item row (container, package, charge...) always adds a new row,
            // so entering many rows is just Tab, Tab, Tab. Use F3 (next tab) to move on; untouched extra rows are cleared when you leave the tab.
            if (!e.shiftKey) {
                var rtr = rowOf(curF), rb = rtr && rtr.querySelector(ADD_SEL);
                if (rb && rtr === rtr.parentNode.lastElementChild) {
                    var rf = fields(rtr);
                    if (rf.length && rf[rf.length - 1] === curF) {
                        if (tts) tts.close();
                        e.preventDefault(); e.stopPropagation();
                        addRowFrom(curF);
                        return;
                    }
                }
            }
            var tpane = curF.closest && curF.closest('.tab-pane'), tlist = fields(tpane || curF.closest('form')), ti = tlist.indexOf(curF);
            if (ti !== -1) {
                var atEnd = e.shiftKey ? ti === 0 : ti === tlist.length - 1;
                if (tts || atEnd) {
                    if (tts) tts.close();
                    if (step(curF, e.shiftKey ? -1 : 1)) { e.preventDefault(); e.stopPropagation(); return; }
                }
            }
            if (curF._flatpickr && curF._flatpickr.isOpen) curF._flatpickr.close();
            return;
        }

        // Enter -> next field (inside a form that sits in the global modal)
        if (e.key === 'Enter' && !e.altKey && !e.metaKey && !e.shiftKey && t && t.closest) {
            var form = t.closest('#globalModal form');
            if (!form) return;
            if (t.matches('button, [type=submit], [type=button], a, [type=checkbox], [type=radio], ' + ADD_SEL + ', ' + REMOVE_SEL)) return;
            // a note box keeps its new lines, except a one-line cell of a line-item table (Shift+Enter for a new line there)
            if (t.matches('textarea') && !t.closest('td') && !e.ctrlKey) return;
            var ts = tsOf(t);
            var list = fields(form);
            var cur = ts ? ts.input : t;
            var idx = list.indexOf(cur);
            if (idx === -1) return;
            var advance = function () {
                var isLast = idx === list.length - 1 || !list.slice(idx + 1).some(function (f) { return rowOf(f) === rowOf(cur); });
                var tr = rowOf(cur), lastRow = tr && tr === tr.parentNode.lastElementChild && tr.querySelector(ADD_SEL);
                if (lastRow && isLast) { addRowFrom(cur); return; }
                step(cur, 1);
            };
            if (ts && ts.isOpen && ts.activeOption && ts.__kbNav) {
                // the user typed / arrowed to an option: let Tom Select pick it; a single dropdown then continues to the next field
                if (ts.settings.maxItems === 1) setTimeout(advance, 30);
                return;
            }
            if (ts && ts.isOpen) { e.preventDefault(); e.stopPropagation(); ts.close(); advance(); return; }
            e.preventDefault(); e.stopPropagation();
            advance();
            return;
        }

        if (!e.altKey || e.ctrlKey || e.metaKey) return;
        // Alt+A / Alt+R : add / remove row (in a form)
        if (e.code === 'KeyA' && t.closest && t.closest('#globalModal, form')) {
            if (addRowFrom(t)) { e.preventDefault(); e.stopPropagation(); }
        } else if (e.code === 'KeyR' && t.closest && t.closest('#globalModal, form')) {
            if (removeRowFrom(t)) { e.preventDefault(); e.stopPropagation(); }
        }
    }, true);

    // tabs of the form: F3 next (existing), Shift+F3 previous
    document.addEventListener('keydown', function (e) {
        if (e.key !== 'F3' || !e.shiftKey || !modalOpen()) return;
        e.preventDefault(); e.stopPropagation();
        var tabs = Array.prototype.slice.call(document.querySelectorAll('#globalModal .nav-link')).filter(visible);
        if (!tabs.length) return;
        var cur = tabs.findIndex(function (x) { return x.classList.contains('active'); });
        var prev = tabs[(cur - 1 + tabs.length) % tabs.length];
        if (window.bootstrap && bootstrap.Tab) bootstrap.Tab.getOrCreateInstance(prev).show(); else prev.click();
        prev.focus();
    }, true);

    // leaving a tab: drop the untouched rows that were added at its end (the first row of a table always stays)
    document.addEventListener('hide.bs.tab', function (e) {
        var btn = e.target, sel = btn && (btn.getAttribute('data-bs-target') || btn.getAttribute('href'));
        var pane = sel && sel.charAt(0) === '#' ? document.querySelector(sel) : null;
        if (!pane || !btn.closest('#globalModal')) return;
        pane.querySelectorAll('tbody').forEach(function (tb) {
            var guard = 50;
            while (guard-- > 0 && tb.children.length > 1) {
                var last = tb.lastElementChild, rm = last.querySelector(REMOVE_SEL);
                if (!rm || !last.querySelector(ADD_SEL) || rowFilled(last)) break;
                rm.click();
                if (last.parentNode === tb) break;   // the page's own handler refused to remove it
            }
        });
    });

    // when a form opens, start in its first field (not in the header buttons)
    document.addEventListener('shown.bs.modal', function (e) {
        if (!e.target || e.target.id !== 'globalModal') return;
        setTimeout(function () {
            var modal = e.target, a = document.activeElement;
            if (a && modal.contains(a) && typing(a) && !a.closest('.modal-header')) return;
            var form = modal.querySelector('form');
            var f = form && fields(form)[0];
            // only a plain text box is focused on open; a dropdown (it would pop open) or a date field (calendar) is left alone
            if (f && !f.tomselect && f.tagName !== 'SELECT' && !f.classList.contains('datepicker') && !f.classList.contains('flatpickr-input') && !f._flatpickr && /^(text|number|search|tel|email|url|password|)$/.test(f.type || '')) focusField(f);
        }, 150);
    });

    // ---- 3. lists: rows, search, action menu --------------------------------------------------------------------
    function rows() { return Array.prototype.slice.call(document.querySelectorAll('#dataTable tbody tr[data-id]')).filter(visible); }
    function openMenuOf(row) {
        var btn = row.querySelector('.dropdown a.btn, .dropdown [data-bs-toggle=dropdown]');
        if (!btn) return false;
        window.__kbMenuFocus = row;
        btn.click();
        return true;
    }
    function menuItems(ul) {
        return Array.prototype.slice.call(ul.querySelectorAll(':scope > li > a.dropdown-item'));
    }
    function focusFirstMenuItem(ul) {
        var items = menuItems(ul);
        if (items.length) { items[0].focus(); return true; }
        return false;
    }
    // the action menu is filled by ajax; focus its first item once it arrives
    new MutationObserver(function (list) {
        if (!window.__kbMenuFocus) return;
        list.forEach(function (m) {
            m.addedNodes.forEach(function (n) {
                if (n.nodeType === 1 && n.tagName === 'LI' && !n.classList.contains('loader-item') && n.parentNode.classList.contains('dropdown-menu')) {
                    var ul = n.parentNode;
                    if (ul.closest('tr') === window.__kbMenuFocus) {
                        setTimeout(function () { if (focusFirstMenuItem(ul)) window.__kbMenuFocus = null; }, 40);
                    }
                }
            });
        });
    }).observe(document.documentElement, {childList: true, subtree: true});

    function closeMenus(refocus) {
        var open = document.querySelector('#dataTable .dropdown-menu.show');
        if (!open) return false;
        var row = open.closest('tr');
        var toggle = row && row.querySelector('.dropdown a.btn, .dropdown [data-bs-toggle=dropdown]');
        if (toggle && window.bootstrap && bootstrap.Dropdown) { try { bootstrap.Dropdown.getOrCreateInstance(toggle).hide(); } catch (err) { /* */ } }
        open.classList.remove('show');
        if (refocus && row) setTimeout(function () { row.focus(); }, 30);
        return true;
    }

    document.addEventListener('keydown', function (e) {
        if (confirmOpen() || modalOpen() || document.getElementById('kbOverlay')) return;
        var t = e.target;

        // inside the action menu
        var item = t.closest && t.closest('.dropdown-menu .dropdown-item');
        if (item) {
            var ul = item.closest('ul'), items = menuItems(ul), i = items.indexOf(item);
            if (e.key === 'ArrowDown') { e.preventDefault(); items[(i + 1) % items.length].focus(); }
            else if (e.key === 'ArrowUp') { e.preventDefault(); items[(i - 1 + items.length) % items.length].focus(); }
            else if (e.key === 'Home') { e.preventDefault(); items[0].focus(); }
            else if (e.key === 'End') { e.preventDefault(); items[items.length - 1].focus(); }
            else if (e.key === 'ArrowRight' && item.parentNode.classList.contains('dropdown-submenu')) {
                e.preventDefault();
                var sub = item.parentNode.querySelector(':scope > ul'); sub.classList.add('show');
                var first = sub.querySelector('a.dropdown-item'); if (first) first.focus();
            } else if (e.key === 'ArrowLeft' && ul.parentNode.classList.contains('dropdown-submenu')) {
                e.preventDefault(); ul.classList.remove('show'); ul.parentNode.querySelector(':scope > a').focus();
            } else if (e.key === 'Escape') { e.preventDefault(); e.stopPropagation(); closeMenus(true); }
            else if (e.key === 'Tab') { closeMenus(false); }
            return;
        }

        // on a row
        var row = t.closest && t.closest('#dataTable tbody tr[data-id]');
        if (row && t === row) {
            var rs = rows(), k = rs.indexOf(row);
            if (e.key === 'ArrowDown' && rs[k + 1]) { e.preventDefault(); rs[k + 1].focus(); }
            else if (e.key === 'ArrowUp' && rs[k - 1]) { e.preventDefault(); rs[k - 1].focus(); }
            else if (e.key === 'Home') { e.preventDefault(); rs[0].focus(); }
            else if (e.key === 'End') { e.preventDefault(); rs[rs.length - 1].focus(); }
            else if (e.key === 'Enter' || (e.key === 'F10' && e.shiftKey)) { if (openMenuOf(row)) e.preventDefault(); }
            return;
        }

        // page level shortcuts (not while typing)
        if (e.altKey && !e.ctrlKey && !e.metaKey) {
            if (e.code === 'KeyN') {
                var nb = document.getElementById('new') || document.querySelector('[data-kb-new]');
                if (nb && visible(nb)) { e.preventDefault(); nb.click(); }
            } else if (e.code === 'KeyF') {
                var sb = searchBox(); if (sb) { e.preventDefault(); sb.focus(); sb.select && sb.select(); }
            }
            return;
        }
        if (e.key === '/' && !typing(t) && !e.ctrlKey && !e.metaKey) {
            var sb2 = searchBox(); if (sb2) { e.preventDefault(); sb2.focus(); sb2.select && sb2.select(); }
        }
    }, true);
    function searchBox() {
        var cands = ['#customSearch', 'input[type=search]', '.dataTables_filter input', 'input[placeholder^="Search"]'];
        for (var i = 0; i < cands.length; i++) { var el = document.querySelector(cands[i]); if (el && visible(el)) return el; }
        return null;
    }
    // Esc in the search box returns to the first row
    document.addEventListener('keydown', function (e) {
        if (e.key !== 'ArrowDown' || modalOpen()) return;
        var t = e.target;
        if (t && t.matches && t.matches('#customSearch, .dataTables_filter input, input[type=search]')) {
            var rs = rows(); if (rs.length) { e.preventDefault(); rs[0].focus(); }
        }
    }, true);

    // ---- 4. overlays: go-to palette and shortcut help -----------------------------------------------------------
    function closeOverlay() {
        var o = document.getElementById('kbOverlay');
        if (o) { o.remove(); if (o.__back && o.__back.focus) o.__back.focus(); }
    }
    function overlay(html) {
        closeOverlay();
        var o = document.createElement('div');
        o.id = 'kbOverlay'; o.__back = document.activeElement;
        o.innerHTML = '<div class="kb-box" role="dialog" aria-modal="true">' + html + '</div>';
        o.addEventListener('mousedown', function (e) { if (e.target === o) closeOverlay(); });
        document.body.appendChild(o);
        return o;
    }
    // ---- Ctrl+K / Alt+G : search every page of the menu ---------------------------------------------------------
    var RECENT_KEY = 'ff.recentPages';
    function recentPages() { try { return JSON.parse(localStorage.getItem(RECENT_KEY) || '[]'); } catch (e) { return []; } }
    function rememberPage(href) {
        try { localStorage.setItem(RECENT_KEY, JSON.stringify([href].concat(recentPages().filter(function (h) { return h !== href; })).slice(0, 6))); } catch (e) { /* private mode */ }
    }
    function esc(t) { return String(t).replace(/[&<>"]/g, function (c) { return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;'}[c]; }); }
    function menuLinks() {
        var seen = {}, out = [];
        document.querySelectorAll('.app-sidebar a.nav-link[href]').forEach(function (a) {
            var h = a.getAttribute('href');
            if (!h || h === '#' || h.indexOf('javascript') === 0 || seen[h]) return;
            seen[h] = 1;
            // the menu path above the link: "Customers", "Reports > Customers" ...
            var path = [], ul = a.closest('ul.nav-treeview');
            while (ul && ul.parentNode) {
                var parent = ul.parentNode.querySelector(':scope > a.nav-link');
                if (parent) path.unshift(parent.textContent.trim().replace(/\s+/g, ' '));
                ul = ul.parentNode.closest('ul.nav-treeview');
            }
            out.push({text: a.textContent.trim().replace(/\s+/g, ' '), href: h, group: path.join(' › ')});
        });
        return out;
    }
    // every word typed must be found (start of a word scores best): "customer sta" finds "Customer Statement"
    function matchScore(item, tokens) {
        var name = item.text.toLowerCase(), path = (item.group + ' ' + item.text).toLowerCase(), score = 0;
        for (var i = 0; i < tokens.length; i++) {
            var t = tokens[i], pos = name.indexOf(t);
            if (pos === 0) score += 100;
            else if (pos > 0 && name.charAt(pos - 1) === ' ') score += 70;
            else if (pos > 0) score += 35;
            else {
                var pp = path.indexOf(t);
                if (pp === -1) return -1;
                score += (pp === 0 || path.charAt(pp - 1) === ' ' || path.charAt(pp - 1) === '›') ? 20 : 8;
            }
        }
        return score - name.length * 0.05;
    }
    function highlight(text, tokens) {
        var low = text.toLowerCase(), marks = [];
        tokens.forEach(function (t) { var i = low.indexOf(t); while (t && i !== -1) { marks.push([i, i + t.length]); i = low.indexOf(t, i + t.length); } });
        marks.sort(function (a, b) { return a[0] - b[0]; });
        var out = '', last = 0;
        marks.forEach(function (m) { if (m[0] < last) { if (m[1] > last) { out += '<mark>' + esc(text.slice(last, m[1])) + '</mark>'; last = m[1]; } return; } out += esc(text.slice(last, m[0])) + '<mark>' + esc(text.slice(m[0], m[1])) + '</mark>'; last = m[1]; });
        return out + esc(text.slice(last));
    }
    function goTo() {
        var links = menuLinks();
        var o = overlay('<div class="kb-search"><i class="bi bi-search"></i><input type="text" id="kbQ" placeholder="Search the menu: customer sta, statement, invoice…" autocomplete="off" spellcheck="false"></div>' +
            '<div id="kbList" class="kb-results" role="listbox"></div>' +
            '<div class="kb-foot"><span><kbd>↑</kbd> <kbd>↓</kbd> move</span><span><kbd>Enter</kbd> open</span><span><kbd>Esc</kbd> close</span><span id="kbCount"></span></div>');
        o.classList.add('kb-center');
        var q = o.querySelector('#kbQ'), list = o.querySelector('#kbList'), count = o.querySelector('#kbCount'), on = 0, shown = [];
        function render() {
            var v = q.value.trim().toLowerCase(), tokens = v ? v.split(/\s+/) : [], html = '';
            if (!tokens.length) {
                // nothing typed: the pages used last, then the whole menu, grouped
                var rec = recentPages().map(function (h) { return links.filter(function (l) { return l.href === h; })[0]; }).filter(Boolean);
                var rest = links.filter(function (l) { return rec.indexOf(l) === -1; });
                shown = rec.concat(rest);
                var groupOf = function (l) { return l.group || 'Main'; }, lastGroup = null;
                shown.forEach(function (l, i) {
                    var g = i < rec.length ? 'Recent' : groupOf(l);
                    if (g !== lastGroup) { html += '<div class="kb-group">' + esc(g) + '</div>'; lastGroup = g; }
                    html += '<div class="kb-item' + (i === on ? ' on' : '') + '" data-i="' + i + '" role="option"><span>' + esc(l.text) + '</span></div>';
                });
            } else {
                shown = links.map(function (l) { return {l: l, s: matchScore(l, tokens)}; }).filter(function (x) { return x.s >= 0; })
                    .sort(function (a, b) { return b.s - a.s; }).map(function (x) { return x.l; });
                shown.forEach(function (l, i) {
                    html += '<div class="kb-item' + (i === on ? ' on' : '') + '" data-i="' + i + '" role="option"><span>' + highlight(l.text, tokens) + '</span><small>' + esc(l.group) + '</small></div>';
                });
            }
            on = Math.min(on, Math.max(0, shown.length - 1));
            list.innerHTML = html || '<div class="kb-none">No page matches “' + esc(q.value) + '”</div>';
            count.textContent = shown.length + (shown.length === 1 ? ' page' : ' pages');
            var act = list.querySelector('.kb-item[data-i="' + on + '"]');
            if (act) { act.classList.add('on'); act.scrollIntoView({block: 'nearest'}); }
        }
        function open(i) { var l = shown[i]; if (l) { rememberPage(l.href); window.location.href = l.href; } }
        function move(d) {
            var prev = list.querySelector('.kb-item.on'); if (prev) prev.classList.remove('on');
            on = Math.max(0, Math.min(on + d, shown.length - 1));
            var act = list.querySelector('.kb-item[data-i="' + on + '"]'); if (act) { act.classList.add('on'); act.scrollIntoView({block: 'nearest'}); }
        }
        q.addEventListener('input', function () { on = 0; render(); });
        q.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowDown') { e.preventDefault(); move(1); }
            else if (e.key === 'ArrowUp') { e.preventDefault(); move(-1); }
            else if (e.key === 'PageDown') { e.preventDefault(); move(8); }
            else if (e.key === 'PageUp') { e.preventDefault(); move(-8); }
            else if (e.key === 'Enter') { e.preventDefault(); open(on); }
            else if (e.key === 'Escape') { e.preventDefault(); closeOverlay(); }
        });
        list.addEventListener('click', function (e) { var it = e.target.closest('.kb-item'); if (it) open(+it.dataset.i); });
        list.addEventListener('mousemove', function (e) {
            var it = e.target.closest('.kb-item'); if (!it || +it.dataset.i === on) return;
            var prev = list.querySelector('.kb-item.on'); if (prev) prev.classList.remove('on');
            on = +it.dataset.i; it.classList.add('on');
        });
        render(); q.focus();
    }
    window.ffKeyHelp = function () { help(); };
    document.addEventListener('click', function (e) {
        var b = e.target.closest && e.target.closest('[data-kb-help]');
        if (b) { e.preventDefault(); help(); }
    });
    function help() {
        var rowsHtml = [
            ['Ctrl + K  or  Alt + G', 'Search the whole menu and open any page'],
            ['Alt + N', 'New record (on a list)'],
            ['Alt + F  or  /', 'Search box'],
            ['↑ ↓  then  Enter', 'Move between rows, open the row menu'],
            ['Enter', 'Next field (on the last line of an invoice: new row)'],
            ['Tab', 'Next field; at the end of a filled container / package / charge row it adds a new row (keep pressing Tab to add more); F3 moves to the next tab'],
            ['~Type, or ↓ in a dropdown', 'Filter / move, then Enter or Tab to choose'],
            ['Ctrl + Enter', 'Next field from a notes box (Enter there = new line)'],
            ['Alt + A  /  Alt + R', 'Add / remove a line-item row'],
            ['F3  /  Shift + F3', 'Next / previous tab of a form'],
            ['Alt + S', 'Save the form'],
            ['Esc', 'Close dropdown, menu or form'],
            ['F1  or  Ctrl + /', 'This list']
        ].map(function (r) {
            var keys = r[0].charAt(0) === '~' ? esc(r[0].slice(1))
                : '<kbd>' + r[0].replace(/ {2}(or|then|\/) {2}/g, '</kbd> $1 <kbd>').replace(/ \+ /g, '</kbd> + <kbd>') + '</kbd>';
            return '<tr><td>' + keys + '</td><td>' + r[1] + '</td></tr>';
        }).join('');
        var o = overlay('<h6>Keyboard shortcuts</h6><table>' + rowsHtml + '</table><div class="mt-2"><small>Press Esc to close</small></div>');
        o.tabIndex = -1; o.focus();
        o.addEventListener('keydown', function (e) { if (e.key === 'Escape' || e.key === 'Enter') { e.preventDefault(); closeOverlay(); } });
    }
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && document.getElementById('kbOverlay')) { e.preventDefault(); e.stopPropagation(); closeOverlay(); return; }
        if (confirmOpen()) return;
        if ((e.altKey && e.code === 'KeyG' && !e.ctrlKey && !e.metaKey) || ((e.ctrlKey || e.metaKey) && !e.altKey && !e.shiftKey && e.code === 'KeyK')) { e.preventDefault(); e.stopPropagation(); goTo(); }
        else if (e.key === 'F1' || (e.ctrlKey && e.key === '/') || (e.key === '?' && !typing(e.target))) { e.preventDefault(); help(); }
    }, true);
})();
