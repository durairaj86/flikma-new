{{-- Ajax page behaviour for payroll list pages (same feel as the invoice list): saving, deleting or paging
     re-renders only #payrollAjaxRoot in the background, a small spinner shows next to the title, and the
     page itself never reloads. Controllers are unchanged: the form's redirect target is fetched and swapped in. --}}
<script>
    if (!window.PayrollAjax) {
        window.PayrollAjax = {
            root() { return document.getElementById('payrollAjaxRoot'); },
            loader(on) {
                const root = this.root(); if (!root) return;
                let sp = root.querySelector('[data-ajax-loader]');
                if (!sp) {
                    const h = root.querySelector('h1'); if (!h) return;
                    sp = document.createElement('span');
                    sp.setAttribute('data-ajax-loader', '1');
                    sp.className = 'spinner-border spinner-border-sm text-primary ms-2 align-middle';
                    sp.style.display = 'none';
                    h.appendChild(sp);
                }
                sp.style.display = on ? 'inline-block' : 'none';
            },
            swap(html) {
                const root = this.root();
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const fresh = doc.getElementById('payrollAjaxRoot');
                if (!fresh) throw new Error('no root');
                root.querySelectorAll('.modal.show').forEach(function (m) { const i = bootstrap.Modal.getInstance(m); if (i) i.hide(); });
                const codes = Array.from(fresh.querySelectorAll('script')).map(function (s) { const t = s.textContent; s.remove(); return t; });
                root.innerHTML = fresh.innerHTML;
                document.querySelectorAll('.modal-backdrop').forEach(function (b) { b.remove(); });
                document.body.classList.remove('modal-open');
                document.body.style.removeProperty('overflow');
                document.body.style.removeProperty('padding-right');
                codes.forEach(function (c) { const el = document.createElement('script'); el.textContent = c; document.body.appendChild(el).remove(); });
                if (typeof datepicker === 'function') datepicker();
                if (typeof initTomSelectForm === 'function' && window.jQuery) initTomSelectForm(window.jQuery('#content-wrapper'));
            },
            go(url, options, push, ctx) {
                this.loader(true);
                return fetch(url, Object.assign({ credentials: 'same-origin', headers: { 'Accept': 'text/html' } }, options || {}))
                    .then(function (r) { if (!r.ok && r.status !== 422) throw new Error(); return r.text().then(function (t) { return { html: t, url: r.url }; }); })
                    .then((res) => {
                        this.swap(res.html);
                        if (push !== false && res.url !== location.href) history.pushState({}, '', res.url);
                        if (ctx) this.reopenOnErrors(ctx);
                    })
                    .catch(function () { if (window.toastr) toastr.error(@json(__('Something went wrong. Please try again.'))); })
                    .finally(() => this.loader(false));
            },
            // A failed save closes nothing for the user: the same popup reopens with what they typed and the errors.
            reopenOnErrors(ctx) {
                const root = this.root();
                const alert = root.querySelector('.alert-danger');
                if (!alert || !ctx.modalId) return;
                const modal = root.querySelector('#' + ctx.modalId);
                if (!modal) return;
                const form = modal.querySelector('form');
                if (ctx.action) form.action = ctx.action;
                form.querySelectorAll('input[type=checkbox]:not([name="_token"])').forEach(function (c) { c.checked = false; });
                ctx.data.forEach(function (v, k) {
                    if (k === '_token' || k === '_method') return;
                    const els = form.querySelectorAll('[name="' + k.replace(/"/g, '\\"') + '"]');
                    els.forEach(function (el) {
                        if (el.type === 'checkbox' || el.type === 'radio') { if (el.value === v) el.checked = true; }
                        else el.value = v;
                    });
                });
                const body = modal.querySelector('.modal-body');
                const copy = alert.cloneNode(true); copy.classList.add('mb-3');
                body.prepend(copy);
                alert.remove();
                bootstrap.Modal.getOrCreateInstance(modal).show();
            },
        };
        // Styled confirmation (replaces the browser confirm box) for any form carrying data-confirm.
        PayrollAjax.confirm = function (form) {
            let el = document.getElementById('payrollConfirmModal');
            if (!el) {
                el = document.createElement('div');
                el.id = 'payrollConfirmModal';
                el.className = 'modal fade';
                el.tabIndex = -1;
                el.innerHTML = '<div class="modal-dialog modal-delete-top"><div class="modal-content border-0 shadow"><div class="modal-body text-center p-4">'
                    + '<div class="mb-3"><span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-danger-subtle text-danger" style="width:56px;height:56px;font-size:1.5rem;"><i class="bi bi-exclamation-triangle"></i></span></div>'
                    + '<h5 class="fw-bold mb-2" data-c-title></h5><p class="text-muted mb-4" data-c-msg></p>'
                    + '<div class="d-flex justify-content-center gap-2"><button type="button" class="btn btn-light border px-4" data-bs-dismiss="modal">' + @json(__('Cancel')) + '</button>'
                    + '<button type="button" class="btn px-4" data-c-ok></button></div></div></div></div>';
                document.body.appendChild(el);
            }
            const destructive = !!form.querySelector('input[name=_method][value=DELETE]');
            el.querySelector('[data-c-title]').textContent = destructive ? @json(__('Delete')) : @json(__('Please confirm'));
            el.querySelector('[data-c-msg]').textContent = form.dataset.confirm;
            const ok = el.querySelector('[data-c-ok]');
            ok.className = 'btn px-4 ' + (destructive ? 'btn-danger' : 'btn-primary');
            ok.textContent = destructive ? @json(__('Delete')) : @json(__('Confirm'));
            ok.onclick = function () {
                form.dataset.confirmed = '1';
                bootstrap.Modal.getInstance(el).hide();
                form.requestSubmit();
            };
            bootstrap.Modal.getOrCreateInstance(el).show();
        };
        document.addEventListener('submit', function (e) {
            const f = e.target.closest('form');
            if (!f || e.defaultPrevented) return;
            if (f.dataset.confirm && !f.dataset.confirmed) { e.preventDefault(); PayrollAjax.confirm(f); return; }
            delete f.dataset.confirmed;
            if (!f.closest('#payrollAjaxRoot') || f.hasAttribute('data-no-ajax')) return;
            // Filter forms (GET): rebuild the list in place with the chosen filters in the address bar.
            if ((f.method || 'get').toLowerCase() === 'get') {
                e.preventDefault();
                const params = new URLSearchParams(new FormData(f));
                [...params.keys()].forEach(function (k) { if (!params.get(k)) params.delete(k); });
                PayrollAjax.go(f.action + (params.toString() ? '?' + params.toString() : ''));
                return;
            }
            if ((f.method || 'get').toLowerCase() !== 'post') return;
            e.preventDefault();
            const modal = f.closest('.modal');
            PayrollAjax.go(f.action, { method: 'POST', body: new FormData(f) }, true, modal ? { modalId: modal.id, action: f.action, data: new FormData(f) } : null);
        });
        document.addEventListener('click', function (e) {
            const a = e.target.closest('#payrollAjaxRoot a.page-link[href], #payrollAjaxRoot .pagination a[href]');
            if (!a || e.ctrlKey || e.metaKey) return;
            e.preventDefault();
            PayrollAjax.go(a.href);
        });
        window.addEventListener('popstate', function () { if (PayrollAjax.root()) PayrollAjax.go(location.href, null, false); });
    }
</script>
