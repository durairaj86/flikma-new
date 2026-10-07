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
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const mainEl = document.getElementById('moduleDrawer');
        const subEl = document.getElementById('linkedDrawer');
        if (!mainEl || !subEl) return;

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
                tabs: [['jobGeneralTab', 'bi-info-circle', @json(__('General'))], ['jobContainerTab', 'bi-box-seam', @json(__('Container'))],
                    ['jobPackageTab', 'bi-boxes', @json(__('Package'))], ['jobDocumentsTab', 'bi-paperclip', @json(__('Documents'))]]
            },
            collection: {title: @json(__('Collection Details')), url: id => '/transaction/collections/' + id + '/overview-drawer'},
            credit_note: {title: @json(__('Credit Note Details')), url: id => '/adjustment/credit-note/' + id + '/overview'},
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

        mainEl.addEventListener('click', function (e) {
            const a = e.target.closest('.open-linked');
            if (!a) return;
            e.preventDefault();
            openLinked(a.dataset);
        });

        function openLinked(a) {
            const def = TYPES[a.type];
            if (!def) return;
            // The main drawer is already showing a customer invoice: its tab ids would clash, so don't open a second copy.
            if (a.type === 'invoice' && mainEl.querySelector('#ciDetailsTab')) return;
            if (a.type === 'supplier_invoice' && mainEl.querySelector('#siDetailsTab')) return;

            // Short content (customer) keeps the default width; long content (job, collection, credit note) fills the rest of the screen.
            if (window.matchMedia('(min-width: 992px)').matches) {
                subEl.style.width = def.narrow ? '' : (100 - {{ $mainWidth }}) + '%';
            } else {
                subEl.style.width = '';
            }
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
            const mainFocus = bootstrap.Offcanvas.getInstance(mainEl)?._focustrap;
            if (!subEl.classList.contains('show')) {
                mainFocus?.deactivate();
                subEl.addEventListener('hidden.bs.offcanvas', function () { mainFocus?.activate(); }, {once: true});
            }
            bootstrap.Offcanvas.getOrCreateInstance(subEl).show();

            fetch(def.url(a.id), {headers: {'X-Requested-With': 'XMLHttpRequest'}})
                .then(r => { if (!r.ok) throw new Error(r.status); return r.text(); })
                .then(html => { body.innerHTML = html; })
                .catch(() => { body.innerHTML = '<div class="alert alert-danger m-3">' + @json(__('Failed to load details.')) + '</div>'; });
        }

        // Print buttons inside a linked record: the owning page's JS isn't loaded here, so print the document through a hidden frame.
        body.addEventListener('click', function (e) {
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

        // Closing the main drawer: the sub drawer closes first, then the main one closes by itself.
        mainEl.addEventListener('hide.bs.offcanvas', function (e) {
            if (e.target !== mainEl || !subEl.classList.contains('show')) return;
            e.preventDefault();
            subEl.addEventListener('hidden.bs.offcanvas', function () {
                bootstrap.Offcanvas.getInstance(mainEl)?.hide();
            }, {once: true});
            bootstrap.Offcanvas.getInstance(subEl).hide();
        });
    });
</script>
