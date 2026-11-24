/*
 * User rights in the browser: in the menus (left sidebar, Master Data menu) a page the user's department may not view is hidden, and
 * a group with nothing left in it disappears (no Customers right at all: no Customers menu). Links elsewhere on a page are shown
 * muted (grey, not clickable, not a Tab stop) instead. Neither is in the Ctrl+K / sidebar menu search. window.FLIKMA_LINK_RIGHTS comes from userLinkRights() in
 * app/Helpers/pre-defined-helpers.php: every right's path prefixes with whether the user may view it; it is empty for the Super User.
 * The longest matching prefix decides, the same way the server's CheckModulePermission middleware does, and the server still refuses
 * the page (403) when a muted link is opened another way.
 */
(function () {
    'use strict';

    var RIGHTS = window.FLIKMA_LINK_RIGHTS || [];
    if (!RIGHTS.length) return;

    function message() {
        return (window.trans ? window.trans('You do not have access to this') : 'You do not have access to this');
    }

    // the path of a link, or null for "#", javascript:, other sites and in-page anchors
    function linkPath(href) {
        if (!href || href.charAt(0) === '#' || /^(javascript|mailto|tel):/i.test(href)) return null;
        var a = document.createElement('a');
        a.href = href;
        if (a.host !== window.location.host) return null;
        return a.pathname.replace(/^\/+|\/+$/g, '');
    }

    function allowed(path, action) {
        var best = null, length = -1;
        for (var i = 0; i < RIGHTS.length; i++) {
            var r = RIGHTS[i];
            if ((path === r.p || (!r.x && path.indexOf(r.p + '/') === 0)) && r.p.length >= length) {
                best = r;
                length = r.p.length;
            }
        }
        // a path no right covers (dashboard, own account ...) stays open; the user's own account page too
        if (path === 'settings/account' || path.indexOf('settings/account/') === 0) return true;
        // the Reports page: open to everyone, it lists only the reports the user may open
        if (path === 'reports') return true;
        if (!best) return true;
        return action === 'create' ? best.a && best.c : best.a;
    }

    function mute(a, href) {
        a.setAttribute('data-denied-href', href);
        a.removeAttribute('href');
        a.classList.add('no-access');
        a.setAttribute('aria-disabled', 'true');
        a.setAttribute('tabindex', '-1');
        a.setAttribute('title', message());
    }

    // menu entries are hidden rather than muted
    var MENUS = '.app-sidebar, #master-navigation';
    (function () {
        var st = document.createElement('style');
        st.textContent = '.rights-hidden{display:none!important}';
        document.head.appendChild(st);
    })();
    function hideItem(li) { if (li) li.classList.add('rights-hidden'); }

    function muteButton(el) {
        if (el.classList.contains('no-access')) return;
        el.classList.add('no-access');
        el.setAttribute('aria-disabled', 'true');
        el.setAttribute('title', message());
        if (el.tagName === 'BUTTON') el.disabled = true;
    }

    function muteLinks(root) {
        (root || document).querySelectorAll('a[href]').forEach(function (a) {
            var href = a.getAttribute('href'), path = linkPath(href);
            if (path !== null && path !== '' && !allowed(path)) {
                mute(a, href);
                if (a.closest(MENUS)) hideItem(a.closest('li'));
            }
        });

        // "New ..." buttons: the list page's own #new button, and the header's quick create menu (data-create-url)
        var here = window.location.pathname.replace(/^\/+|\/+$/g, '');
        var newBtn = document.getElementById('new');
        if (newBtn && !allowed(here, 'create')) muteButton(newBtn);
        (root || document).querySelectorAll('[data-create-url]').forEach(function (el) {
            var path = linkPath(el.getAttribute('data-create-url'));
            if (path && !allowed(path, 'create')) muteButton(el);
        });

        // a sidebar group with nothing left to open disappears (Customers, Payroll, Reports ...); deepest groups first,
        // so "Reports > Customers" going away can empty "Reports"
        Array.prototype.slice.call(document.querySelectorAll('.app-sidebar ul.nav-treeview')).reverse().forEach(function (ul) {
            var links = ul.querySelectorAll('a.nav-link[data-denied-href], a.nav-link[href]:not([href="#"])');
            var visible = Array.prototype.filter.call(links, function (a) { return !a.closest('.rights-hidden'); });
            if (links.length && !visible.length) hideItem(ul.parentNode && ul.parentNode.closest('li'));
        });
        // Master Data secondary menu: a collapsible group with every link hidden
        document.querySelectorAll('#master-navigation ul.collapse').forEach(function (ul) {
            var links = ul.querySelectorAll('a'), hidden = ul.querySelectorAll('.rights-hidden a');
            if (links.length && links.length === hidden.length) hideItem(ul.parentNode && ul.parentNode.closest('li'));
        });
    }

    // a link added after the scan (drawers, ajax tables) is caught when clicked
    document.addEventListener('click', function (e) {
        var btn = e.target.closest && e.target.closest('.no-access');
        if (btn && !btn.matches('a[href]')) {
            e.preventDefault();
            e.stopImmediatePropagation();
            return;
        }
        var a = e.target.closest && e.target.closest('a');
        if (!a) return;
        if (a.classList.contains('no-access') && a.hasAttribute('data-denied-href')) {
            e.preventDefault();
            e.stopImmediatePropagation();
            return;
        }
        var href = a.getAttribute('href'), path = linkPath(href);
        if (path !== null && path !== '' && !allowed(path)) {
            e.preventDefault();
            e.stopImmediatePropagation();
            mute(a, href);
            if (window.toastr) window.toastr.warning(message());
        }
    }, true);

    var timer = null;
    function scheduleMute() {
        clearTimeout(timer);
        timer = setTimeout(function () { muteLinks(document); }, 80);
    }

    function start() {
        muteLinks(document);
        if (window.MutationObserver) new MutationObserver(scheduleMute).observe(document.body, {childList: true, subtree: true});
    }

    window.FlikmaRights = {allowed: function (href) { var p = linkPath(href); return p === null || p === '' || allowed(p); }, mute: muteLinks};

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start);
    else start();
})();
