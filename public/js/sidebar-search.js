/*
 * Menu search above Dashboard: as you type, the left menu shows only the pages that match (submenus included) and opens
 * their groups; "customer sta" finds Customers > Customer Statement. Every word typed must be found in the page name or in
 * its group names. Keyboard: Enter opens the first match, Down / Up move through the matches, Esc clears.
 * Pages hidden by the user's rights (user-rights.js, class rights-hidden) are never shown or matched.
 */
(function () {
    'use strict';

    // this file is loaded before the sidebar is on the page: start once it is
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
    else init();

    function init() {
    var input = document.getElementById('sidebarMenuSearch');
    if (!input) return;
    var menu = input.closest('.sidebar-menu');
    var none = document.getElementById('sidebarMenuSearchNone');
    var clear = document.getElementById('sidebarMenuSearchClear');
    if (clear) {
        clear.addEventListener('click', function () { input.value = ''; restore(); input.focus(); });
    }
    var saved = null;   // the groups' open / closed state before searching, put back when the search is cleared

    function norm(t) { return String(t || '').toLowerCase().replace(/\s+/g, ' ').trim(); }

    // every page link of the menu with the names of the groups above it
    function pages() {
        var out = [];
        menu.querySelectorAll('a.nav-link').forEach(function (a) {
            var href = a.getAttribute('href');
            if (!href || href === '#' || href.indexOf('javascript') === 0) return;
            if (a.closest('.rights-hidden')) return;
            var groups = [], ul = a.closest('ul.nav-treeview');
            while (ul) {
                var toggle = ul.parentNode && ul.parentNode.querySelector(':scope > a.nav-link');
                if (toggle) groups.unshift(norm(toggle.textContent));
                ul = ul.parentNode ? ul.parentNode.closest('ul.nav-treeview') : null;
            }
            out.push({a: a, text: norm(a.textContent), groups: groups.join(' ')});
        });
        return out;
    }

    function items() { return menu.querySelectorAll('li.nav-item:not(.sidebar-search-item)'); }

    function remember() {
        if (saved) return;
        saved = [];
        menu.querySelectorAll('li.nav-item').forEach(function (li) {
            var ul = li.querySelector(':scope > ul.nav-treeview');
            if (ul) saved.push({li: li, ul: ul, open: li.classList.contains('menu-open'), display: ul.style.display});
        });
    }

    function restore() {
        items().forEach(function (li) { li.classList.remove('menu-search-hidden'); });
        menu.querySelectorAll('.menu-search-hit').forEach(function (a) { a.classList.remove('menu-search-hit'); });
        (saved || []).forEach(function (s) {
            s.li.classList.toggle('menu-open', s.open);
            s.ul.style.display = s.display;
        });
        saved = null;
        none.classList.add('d-none');
        if (clear) clear.classList.add('d-none');
    }

    function visibleLinks() {
        return Array.prototype.filter.call(menu.querySelectorAll('a.nav-link.menu-search-hit'), function (a) { return a.offsetParent !== null; });
    }

    function filter() {
        if (clear) clear.classList.toggle('d-none', !input.value);
        var q = norm(input.value);
        if (!q) { restore(); return; }
        remember();
        var tokens = q.split(' ');
        menu.querySelectorAll('.menu-search-hit').forEach(function (a) { a.classList.remove('menu-search-hit'); });
        items().forEach(function (li) { li.classList.add('menu-search-hidden'); });

        var hits = 0;
        pages().forEach(function (p) {
            var hay = p.text + ' ' + p.groups;
            for (var i = 0; i < tokens.length; i++) if (hay.indexOf(tokens[i]) === -1) return;
            hits++;
            p.a.classList.add('menu-search-hit');
            // show the page and every group above it, opened
            var li = p.a.closest('li.nav-item');
            while (li && menu.contains(li)) {
                li.classList.remove('menu-search-hidden');
                var ul = li.querySelector(':scope > ul.nav-treeview');
                if (ul && ul.contains(p.a)) { li.classList.add('menu-open'); ul.style.display = 'block'; }
                li = li.parentNode ? li.parentNode.closest('li.nav-item') : null;
            }
        });
        none.classList.toggle('d-none', hits > 0);
    }

    input.addEventListener('input', filter);
    input.addEventListener('search', filter);   // the box's own clear (x) button
    input.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            if (input.value) { e.preventDefault(); e.stopPropagation(); input.value = ''; restore(); if (clear) clear.classList.add('d-none'); }
            else input.blur();
        } else if (e.key === 'Enter') {
            var first = visibleLinks()[0];
            if (first) { e.preventDefault(); first.click(); }
        } else if (e.key === 'ArrowDown') {
            var links = visibleLinks();
            if (links.length) { e.preventDefault(); links[0].focus(); }
        }
    });
    // Down / Up between the matches, Up from the first one goes back to the box
    menu.addEventListener('keydown', function (e) {
        if (!input.value || (e.key !== 'ArrowDown' && e.key !== 'ArrowUp')) return;
        var links = visibleLinks(), i = links.indexOf(document.activeElement);
        if (i === -1) return;
        e.preventDefault();
        if (e.key === 'ArrowDown') (links[i + 1] || links[i]).focus();
        else if (i === 0) input.focus();
        else links[i - 1].focus();
    });
    // collapsed sidebar: the icon-sized box opens the page search instead
    input.addEventListener('focus', function () {
        var box = input.closest('#sidebar-container');
        if (box && box.classList.contains('sidebar-collapsed') && !box.matches(':hover') && window.ffGoTo) {
            input.blur();
            window.ffGoTo();
        }
    });
    }
})();
