{{-- Sub drawer that slides out beside a main view drawer (#moduleDrawer) when a linked record is clicked.
     Any element with class "open-linked" and data-type / data-id (+ optional data-title) inside the main drawer opens it.
     Usage: @include('modules.common.linked-drawer', ['mainWidth' => 60, 'subWidth' => 35]) --}}
@php($mainWidth = $mainWidth ?? 55)
@php($subWidth = $subWidth ?? 40)
<div class="offcanvas offcanvas-end customer-drawer" tabindex="-1" id="linkedDrawer" data-bs-backdrop="false">
    <div class="offcanvas-header border-bottom bg-light px-4 py-3 d-flex justify-content-between align-items-center">
        <div class="flex-grow-1">
            <h5 class="mb-0 fw-bold" id="linkedDrawerTitle"></h5>
            <small class="text-muted" id="linkedDrawerSubtitle"></small>
        </div>
        <div id="linkedDrawerActions" class="d-flex gap-2 me-3"></div>
        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 me-3" data-bs-dismiss="offcanvas">
            <i class="bi bi-arrow-left me-1"></i>{{ __('Back') }}
        </button>
        <button type="button" class="btn-close m-0" data-bs-dismiss="offcanvas" aria-label="{{ __('Close') }}"></button>
    </div>
    <div class="offcanvas-body p-0">
        <ul class="nav nav-tabs px-4 pt-3 border-bottom bg-white d-none" id="linkedDrawerTabs" role="tablist"></ul>
        <div class="tab-content px-4 py-3" id="linkedDrawerBody"></div>
    </div>
</div>

<style>
    #linkedDrawer.offcanvas.offcanvas-end { right: {{ $mainWidth }}%; width: {{ $subWidth }}%; z-index: 1099 !important; box-shadow: -6px 0 18px rgba(0, 0, 0, .12); }
    @media (max-width: 991.98px) {
        #linkedDrawer.offcanvas.offcanvas-end { right: 0; width: 90%; z-index: 1110 !important; }
    }
    /* Switching between records (e.g. customer -> job) grows / shrinks the drawer smoothly instead of jumping. */
    #linkedDrawer.offcanvas.offcanvas-end { transition: transform .3s ease-in-out, width .35s ease; }
    .open-linked { cursor: pointer; }
    #moduleDrawer.offcanvas.offcanvas-end { transition: transform .3s ease-in-out, width .35s ease; }
    /* a form in this drawer never scrolls sideways: an open dropdown that is wider than the room is pulled back inside instead */
    #linkedDrawer .offcanvas-body, #linkedDrawerBody { overflow-x: hidden; }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const mainEl = document.getElementById('moduleDrawer');
        const subEl = document.getElementById('linkedDrawer');
        if (!subEl) return;
        // Drawers that can have the sub drawer slide out beside them (the page's main drawer, and the collection history drawer).
        const hosts = ['moduleDrawer', 'paymentHistoryDrawer'].map(id => document.getElementById(id)).filter(Boolean);
        if (!hosts.length) return;
        let hostEl = mainEl || hosts[0];

        // Where each kind of record loads from, and the tabs its content is split into (if any).
        const TYPES = {
            enquiry: {
                title: @json(__('Enquiry Details')), url: id => '/sales/enquiry/' + id + '/overview-drawer',
                tabs: [['enquiryGeneralTab', 'bi-info-circle', @json(__('General'))], ['enquiryTimeFrameTab', 'bi-clock-history', @json(__('Time Frame')), true]]
            },
            quotation: {
                title: @json(__('Quotation Details')), url: id => '/sales/quotation/' + id + '/overview-drawer',
                tabs: [['quotationGeneralTab', 'bi-info-circle', @json(__('General'))], ['quotationContainerTab', 'bi-box-seam', @json(__('Container'))],
                    ['quotationPackageTab', 'bi-boxes', @json(__('Package'))], ['quotationChargesTab', 'bi-receipt', @json(__('Charges'))],
                    ['quotationTimeFrameTab', 'bi-clock-history', @json(__('Time Frame')), true]]
            },
            invoice: {title: @json(__('Customer Invoice Details')), url: id => '/invoice/customer/' + id + '/overview-drawer'},
            supplier: {narrow: true, title: @json(__('Supplier Details')), url: id => '/supplier/' + id + '/overview'},
            supplier_invoice: {title: @json(__('Supplier Invoice Details')), url: id => '/invoice/supplier/' + id + '/overview-drawer'},
            customer: {narrow: true, title: @json(__('Customer Details')), url: id => '/customer/' + id + '/overview'},
            job: {
                title: @json(__('Job Details')), url: id => '/operation/job/' + id + '/overview-drawer',
                tabs: [['jobGeneralTab', 'bi-info-circle', @json(__('General'))], ['jobClearanceTab', 'bi-shield-check', @json(__('Clearance'))], ['jobContainerTab', 'bi-box-seam', @json(__('Container'))],
                    ['jobPackageTab', 'bi-boxes', @json(__('Package'))], ['jobFinanceTab', 'bi-cash-coin', @json(__('Financials'))],
                    ['jobTimelineTab', 'bi-clock-history', @json(__('Timeline'))], ['jobDocumentsTab', 'bi-paperclip', @json(__('Documents'))]]
            },
            collection: {title: @json(__('Collection Details')), url: id => '/transaction/collections/' + id + '/overview-drawer'},
            credit_note: {title: @json(__('Credit Note Details')), url: id => '/adjustment/credit-note/' + id + '/overview'},
            master_bl: {title: @json(__('Master B/L Details')), url: id => '/bl/master-bl/' + id + '/overview'},
            seaway: {title: @json(__('Seaway Bill Details')), url: id => '/bl/seaway/' + id + '/overview'},
            airway: {title: @json(__('Airway Bill Details')), url: id => '/bl/airway-bill/' + id + '/overview'},
        };

        const body = document.getElementById('linkedDrawerBody');
        const tabsEl = document.getElementById('linkedDrawerTabs');

        // Links inside the sub drawer itself (e.g. the Enquiry no. in a quotation) swap its content in place.
        subEl.addEventListener('click', function (e) {
            const a = e.target.closest('.open-linked-enquiry, .open-linked');
            if (!a) return;
            e.preventDefault();
            if (a.classList.contains('open-linked-enquiry')) {
                openLinked({type: 'enquiry', id: a.dataset.id, title: a.dataset.no || ''});
            } else {
                openLinked(a.dataset);
            }
        });

        hosts.forEach(function (host) {
            host.addEventListener('click', function (e) {
                const a = e.target.closest('.open-linked');
                if (!a) return;
                e.preventDefault();
                hostEl = host;
                openLinked(a.dataset);
            });
        });

        function openLinked(a) {
            const def = TYPES[a.type];
            if (!def) return;
            // The main drawer is already showing a customer invoice: its tab ids would clash, so don't open a second copy.
            if (a.type === 'invoice' && hostEl.querySelector('#ciDetailsTab')) return;
            if (a.type === 'supplier_invoice' && hostEl.querySelector('#siDetailsTab')) return;

            // Short content (customer) keeps the default width; long content (job, collection, credit note) fills the rest of the screen.
            // Sit beside whichever drawer the link was clicked in.
            const sideBySide = window.matchMedia('(min-width: 992px)').matches;
            if (sideBySide && hostEl !== mainEl) {
                const hw = hostEl.offsetWidth;
                subEl.style.right = hw + 'px';
                subEl.style.width = def.narrow ? '' : Math.max(360, window.innerWidth - hw) + 'px';
            } else {
                subEl.style.right = '';
                subEl.style.width = sideBySide ? (def.narrow ? '' : (100 - {{ $mainWidth }}) + '%') : '';
            }
            document.getElementById('linkedDrawerActions').innerHTML = '';
            document.getElementById('linkedDrawerTitle').textContent = def.title;
            document.getElementById('linkedDrawerSubtitle').textContent = a.title || '';
            body.innerHTML = '<div class="text-center py-5 text-muted"><div class="spinner-border spinner-border-sm me-2" role="status"></div> ' + @json(__('Loading...')) + '</div>';

            tabsEl.innerHTML = '';
            tabsEl.classList.toggle('d-none', !def.tabs);
            (def.tabs || []).forEach(function (t, i) {
                const icon = !!t[3]; // time frame: icon-only tab at the right end
                tabsEl.insertAdjacentHTML('beforeend', '<li class="nav-item' + (icon ? ' ms-auto' : '') + '"><button class="nav-link fw-semibold' + (i ? '' : ' active') + '" data-bs-toggle="tab" data-bs-target="#' + t[0] + '" type="button" role="tab"' + (icon ? ' title="' + t[2] + '" aria-label="' + t[2] + '"' : '') + '>' + (icon ? '<i class="bi ' + t[1] + ' fs-5"></i>' : '<i class="bi ' + t[1] + ' me-1"></i> ' + t[2]) + '</button></li>');
            });

            // Two open drawers would fight over keyboard focus; pause the main drawer's trap while the sub drawer is on top.
            const mainFocus = bootstrap.Offcanvas.getInstance(hostEl)?._focustrap;
            if (!subEl.classList.contains('show')) {
                mainFocus?.deactivate();
                subEl.addEventListener('hidden.bs.offcanvas', function () { mainFocus?.activate(); }, {once: true});
            }
            bootstrap.Offcanvas.getOrCreateInstance(subEl).show();

            fetch(def.url(a.id), {headers: {'X-Requested-With': 'XMLHttpRequest'}})
                .then(r => { if (!r.ok) throw new Error(r.status); return r.text(); })
                .then(html => { body.innerHTML = html; pullHeading(); })
                .catch(() => { body.innerHTML = '<div class="alert alert-danger m-3">' + @json(__('Failed to load details.')) + '</div>'; });
        }

        // The record number + status badge (and the Print / Collection ... buttons next to it) that open a record's overview are shown once,
        // in this drawer's header, instead of again as the first line of the body.
        function pullHeading() {
            const heading = body.querySelector('.invoice-no-heading');
            if (!heading) return;
            const row = heading.parentElement;
            const title = document.getElementById('linkedDrawerTitle');
            const actions = document.getElementById('linkedDrawerActions');
            title.textContent = '';
            heading.classList.add('mb-0');
            title.appendChild(heading);
            document.getElementById('linkedDrawerSubtitle').textContent = '';
            if (row && row !== body && row.classList.contains('d-flex')) {
                Array.prototype.slice.call(row.children).forEach(function (c) { actions.appendChild(c); });
                row.remove();
            }
        }

        // Print buttons inside a linked record: the owning page's JS isn't loaded here, so print the document through a hidden frame.
        subEl.addEventListener('click', function (e) {   // the buttons sit in the header now, so listen on the whole drawer
            const btn = e.target.closest('.linked-print');
            if (!btn || !btn.dataset.printUrl) return;
            if (window.CREDIT_NOTE && CREDIT_NOTE.printPreview) return; // the page handles it (its own onclick)
            let frame = document.getElementById('linkedPrintFrame');
            if (!frame) {
                frame = document.createElement('iframe');
                frame.id = 'linkedPrintFrame';
                frame.style.cssText = 'position:fixed;width:0;height:0;border:0;visibility:hidden;';
                document.body.appendChild(frame);
            }
            frame.onload = function () {
                try { frame.contentWindow.focus(); frame.contentWindow.print(); }
                catch (err) { window.open(btn.dataset.printUrl, '_blank'); }
            };
            frame.src = btn.dataset.printUrl;
        });

        // Messages (toastr) shown while this sub drawer is open are centred on the drawer, not on the whole screen.
        (function () {
            function place() {
                const c = document.getElementById('toast-container');
                if (!c) return;
                if (subEl.classList.contains('show') && window.matchMedia('(min-width: 992px)').matches) {
                    const r = subEl.getBoundingClientRect();
                    c.style.left = r.left + 'px'; c.style.width = r.width + 'px'; c.style.right = 'auto';
                } else {
                    c.style.left = ''; c.style.width = ''; c.style.right = '';
                }
            }
            new MutationObserver(function () {
                place();
                const c = document.getElementById('toast-container');
                if (c && !c.__lfObserved) { c.__lfObserved = true; new MutationObserver(place).observe(c, {childList: true}); }
            }).observe(document.body, {childList: true});
            // when the drawer closes, let the last message finish where it is, then restore the normal position
            subEl.addEventListener('hidden.bs.offcanvas', function () { setTimeout(place, 3600); });
        })();

        /*
         * A normal module form (Collection, Credit Note ...) shown in this sub drawer instead of a pop-up window:
         * window.openLinkedForm({title, subtitle, url, params, onReady, afterSave}).
         * The form is the module's own blade; this only supplies the Save / Cancel buttons, the field set-up and the save,
         * exactly like the pop-up does (the "Save and Approve" chain uses the form's data-approve-url as well).
         */
        // When the form closes, the main drawer gets back the width it had (60%): one handler for every way of closing,
        // and it only acts if the drawer is really narrowed (so opening a second form meanwhile can never break it).
        subEl.addEventListener('hidden.bs.offcanvas', function (e) {
            if (e.target !== subEl) return;
            if (subEl.classList.contains('show') || subEl.classList.contains('showing')) return;   // re-opened meanwhile
            document.getElementById('linkedDrawerActions').innerHTML = '';
            if (mainEl && mainEl.dataset.lfWidth !== undefined) {
                mainEl.style.width = mainEl.dataset.lfWidth;
                delete mainEl.dataset.lfWidth;
            }
        });

        window.openLinkedForm = function (opts) {
            const sideBySide = window.matchMedia('(min-width: 992px)').matches;
            const shownHost = hosts.find(h => h.classList.contains('show'));
            if (sideBySide && shownHost) {
                hostEl = shownHost;
                if (shownHost === mainEl) {
                    // a form needs room: the main drawer narrows to 20% while the form takes the other 80%
                    // (its width is an inline style set elsewhere: remember it and give it back exactly when the form closes)
                    if (mainEl.dataset.lfWidth === undefined) mainEl.dataset.lfWidth = mainEl.style.width;
                    mainEl.style.width = '20%';
                    subEl.style.right = '20%';
                    subEl.style.width = '80%';
                } else {
                    subEl.style.right = shownHost.offsetWidth + 'px';
                    subEl.style.width = Math.max(360, window.innerWidth - shownHost.offsetWidth) + 'px';
                }
            } else if (sideBySide) {
                subEl.style.right = '0';
                subEl.style.width = 'min(900px, 60%)';
            } else {
                subEl.style.right = '';
                subEl.style.width = '';
            }
            document.getElementById('linkedDrawerActions').innerHTML = '';
            document.getElementById('linkedDrawerTitle').textContent = opts.title || '';
            document.getElementById('linkedDrawerSubtitle').textContent = opts.subtitle || '';
            tabsEl.innerHTML = '';
            tabsEl.classList.add('d-none');
            body.innerHTML = '<div class="text-center py-5 text-muted"><div class="spinner-border spinner-border-sm me-2" role="status"></div> ' + @json(__('Loading...')) + '</div>';

            const mainFocus = shownHost ? bootstrap.Offcanvas.getInstance(shownHost)?._focustrap : null;
            if (!subEl.classList.contains('show')) {
                mainFocus?.deactivate();
                subEl.addEventListener('hidden.bs.offcanvas', function () { mainFocus?.activate(); }, {once: true});
            }
            bootstrap.Offcanvas.getOrCreateInstance(subEl).show();

            const qs = opts.params ? '?' + new URLSearchParams(opts.params).toString() : '';
            fetch(opts.url + qs, {headers: {'X-Requested-With': 'XMLHttpRequest'}})
                .then(r => { if (!r.ok) throw new Error(r.status); return r.text(); })
                .then(html => { body.innerHTML = html; setupLinkedForm(opts); })
                .catch(() => { body.innerHTML = '<div class="alert alert-danger m-3">' + @json(__('Failed to load details.')) + '</div>'; });
        };

        function setupLinkedForm(opts) {
            const form = body.querySelector('form');
            if (!form) return;
            const $form = $(form), $cfg = $(body).find('#modal-buttons');
            const list = String($cfg.data('buttons') || '').split(',');
            let pendingApprove = false;

            let html = '<div class="d-flex justify-content-end gap-2">';
            if (list.includes('cancel')) html += '<button type="button" class="btn btn-outline-secondary" data-lf="cancel"><i class="bi bi-x-circle me-1"></i>' + @json(__('Cancel')) + '</button>';
            if (list.includes('saveDraft')) html += '<button type="button" class="btn btn-outline-secondary" data-lf="draft"><i class="bi bi-file-earmark me-1"></i>' + ($cfg.data('button-draft') || @json(__('Save as Draft'))) + '</button>';
            if (list.includes('saveApprove')) html += '<button type="button" class="btn btn-success" data-lf="approve"><i class="bi bi-check2-circle me-1"></i>' + ($cfg.data('button-approve') || @json(__('Save and Approve'))) + '</button>';
            if (list.includes('save')) html += '<button type="button" class="btn btn-primary" data-lf="save"><i class="bi bi-save me-1"></i>' + ($cfg.data('button-save') || @json(__('Save'))) + '</button>';
            html += '</div>';
            // the buttons sit in the drawer header beside Back; the form's own grey title band ("New Collection") is not needed here
            document.getElementById('linkedDrawerActions').innerHTML = html;
            const band = $(body).find('#show-buttons').closest('.border-bottom');
            if (band.length) band.hide(); else $(body).find('#show-buttons').hide();

            $(subEl).off('click.lf').on('click.lf', '#linkedDrawerActions [data-lf]', function () {
                const k = this.dataset.lf;
                if (k === 'cancel') { bootstrap.Offcanvas.getInstance(subEl).hide(); return; }
                pendingApprove = k === 'approve';
                $form.trigger('submit');
            });

            // the same field set-up the pop-up does after it loads a form
            try { initTomSelectForm(); datepicker(); timepicker(); CURRENCY.currencyRate(); INPUT.load(); } catch (e) { console.error(e); }
            if (typeof opts.onReady === 'function') opts.onReady();
            // an opened dropdown wider than the room to its right opens leftwards (anchored to the box's right edge)
            body.querySelectorAll('select').forEach(function (sel) {
                const ts = sel.tomselect;
                if (!ts || ts.__lfPos) return;
                ts.__lfPos = true;
                ts.on('dropdown_open', function (dd) {
                    dd.style.left = ''; dd.style.right = '';
                    const box = body.getBoundingClientRect(), r = dd.getBoundingClientRect();
                    if (r.right > box.right - 6) { dd.style.left = 'auto'; dd.style.right = '0'; }
                });
            });

            $form.off('submit.lf').on('submit.lf', function (e) {
                e.preventDefault();
                if (!$form.valid()) {
                    const n = $form.find('input.is-invalid, textarea.is-invalid, select.is-invalid').length || 1;
                    toastr.error(trans('Validation Failed') + ': ' + n + ' field' + (n > 1 ? 's' : '') + ' need to be fixed.', '', {positionClass: 'toast-bottom-center'});
                    return;
                }
                // disabled fields (locked currency, ...) are still part of the save
                const disabled = form.querySelectorAll(':disabled');
                disabled.forEach(el => el.removeAttribute('disabled'));
                const formData = new FormData(form);
                disabled.forEach(el => el.setAttribute('disabled', 'disabled'));

                const $btns = $('#linkedDrawerActions [data-lf]').prop('disabled', true);
                $.ajax({
                    url: form.getAttribute('action'), type: 'POST', data: formData, processData: false, contentType: false,
                    success: function (response) {
                        if (response.status !== 'success') {
                            if (response.status === 'error') toastr.error(response.message);
                            if (response.status === 'confirm' && window.confirm(response.message + '\n\n' + trans('Continue?'))) {
                                // the server wants an explicit yes (e.g. a duplicate or too-large credit note): send again with it
                                $('<input type="hidden">').attr('name', response.confirm_field).val('1').appendTo($form);
                                const again = pendingApprove;
                                setTimeout(function () { pendingApprove = again; $form.trigger('submit'); }, 0);
                            }
                            return;
                        }
                        const finish = function () {
                            toastr.success(response.message);
                            if ($.fn.DataTable.isDataTable('#dataTable')) $('#dataTable').DataTable().ajax.reload(null, false);
                            bootstrap.Offcanvas.getInstance(subEl).hide();
                            if (typeof opts.afterSave === 'function') opts.afterSave(response);
                        };
                        const approveUrl = $cfg.data('approve-url'), idKey = $cfg.data('approve-id-key') || 'id';
                        if (pendingApprove && approveUrl && response[idKey]) {
                            $.ajax({
                                url: String(approveUrl).replace('{id}', response[idKey]), method: 'POST',
                                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                                success: finish,
                                error: function () { toastr.warning(trans('Saved, but approving it failed — it is still saved as a draft.')); finish(); }
                            });
                        } else {
                            finish();
                        }
                    },
                    error: function (xhr) {
                        const errors = xhr.responseJSON && xhr.responseJSON.errors;
                        toastr.error(errors ? Object.values(errors).flat().join(' | ') : (xhr.responseJSON && xhr.responseJSON.message) || trans('Something went wrong!'));
                    },
                    complete: function () { $btns.prop('disabled', false); pendingApprove = false; }
                });
            });
        }

        // Closing a host drawer: the sub drawer closes first, then the host closes by itself.
        hosts.forEach(function (host) {
            host.addEventListener('hide.bs.offcanvas', function (e) {
                if (e.target !== host || !subEl.classList.contains('show')) return;
                e.preventDefault();
                subEl.addEventListener('hidden.bs.offcanvas', function () {
                    bootstrap.Offcanvas.getInstance(host)?.hide();
                }, {once: true});
                bootstrap.Offcanvas.getInstance(subEl).hide();
            });
        });
    });
</script>
