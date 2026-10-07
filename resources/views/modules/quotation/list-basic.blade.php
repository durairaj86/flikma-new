@section('page-title', __('Quotations'))
@section('hide-topbar', true)
@push('page-title-action')
    <button class="btn btn-link btn-sm text-muted p-0 text-decoration-none lh-1"
            data-bs-toggle="modal" data-bs-target="#quotationWorkflowModal"
            title="{{ __('How quotations work') }}">
        <i class="bi bi-info-circle fs-6"></i><span class="d-none d-md-inline ms-1" style="font-size:0.8rem;">{{ __('How it works') }}</span>
    </button>
@endpush
{{--
    Temporary basic/static replacement for modules.quotation.list.
    The original dynamic, column-settings-driven list is left untouched at
    resources/views/modules/quotation/list.blade.php (its Column Settings
    button/modal are just hidden with d-none there) — swap the route back to
    it whenever the dynamic column list is wanted again.

    This page intentionally does NOT load the "quotation" JS module (no
    @section('js', ...)), so none of the dynamic column-settings machinery
    runs here. Columns below are hardcoded (no Status column — it's already
    shown via the tabs). Filter, New Quotation, row actions, and click-to-view
    are all wired up with self-contained handlers below, reusing the same
    endpoints/shared helpers (FILTER, webModal, webDataTable.actions.icons,
    changeCustomerStatus, initTomSelectSearch) the rest of the app uses —
    module-agnostic pieces that don't need quotation.js loaded.
--}}
<x-app-layout>
    <style>
        /* Same tab look as the Customers list: inactive = soft grey with amber icon, active = blue with white icon. */
        #basicListTabs .status-btn:not(.active) { background: #f1f3f5; color: #495057; }
        #basicListTabs .status-btn:not(.active) > span:first-child > i { color: var(--bs-warning) !important; }
        #basicListTabs .status-btn.active { background: rgb(13, 110, 253) !important; color: #fff !important; }
        #basicListTabs .status-btn.active > span:first-child > i { color: #fff !important; }
        /* Header row background only (same light grey as the other lists); text styling unchanged. */
        #basicQuotationTable thead th { background-color: #f8f9fa !important; }
    </style>
    <main class="gmail-content bg-white px-3">
        <style>
            .quo-title { display: none; }
            body:not(.has-top-header) .quo-title { display: block; }
        </style>
        <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap pt-2 pb-0">
            <div class="d-flex align-items-center gap-2 quo-title">
                <h4 class="fw-bold text-dark mb-0">@yield('page-title')</h4>
                @stack('page-title-action')
            </div>
            <div class="d-flex align-items-center gap-2 ms-auto">
                <button class="btn btn-primary rounded-pill px-4" id="new">{{ __('New Quotation') }}</button>
            </div>
        </div>

        <div id="filterPanel" class="card shadow-sm border-0 d-none filter-panel-card">

            <!-- Header -->
            <div class="card-header bg-white border-0 pt-3 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <span class="filter-panel-icon"><i class="bi bi-funnel-fill"></i></span>
                    <h6 class="mb-0 fw-semibold">{{ __('Filters') }}</h6>
                </div>
            </div>

            <div class="card-body pt-3">
                <form id="list-filter" method="post" novalidate="novalidate">
                    @csrf
                    <div class="row g-4">

                        <div class="col-md-6 col-xl-4 form-filter">
                            <label class="form-label fw-medium filter-label-row">{{ __('Quotation Date') }}</label>
                            <div class="filter-date-range">
                                <input type="text" class="form-control datepicker from-date default-filter" id="filter-from-date" name="filter-from-date" autocomplete="off" data-min-date="01-01-2000" data-max-date="31-12-2099"
                                       value="{{ \Carbon\Carbon::today()->subMonths(3)->startOfMonth()->format('d-m-Y') }}">
                                <i class="bi bi-arrow-right filter-date-range-arrow"></i>
                                <input type="text" class="form-control datepicker to-date default-filter" id="filter-to-date" name="filter-to-date" autocomplete="off" data-min-date="01-01-2000" data-max-date="31-12-2099"
                                       value="{{ \Carbon\Carbon::today()->format('d-m-Y') }}">
                            </div>
                        </div>

                        <div class="col-md-6 col-xl-2 form-filter">
                            <label class="form-label fw-medium filter-label-row">{{ __('Customer') }}</label>
                            <x-common.customers multiple></x-common.customers>
                        </div>

                        <div class="col-md-6 col-xl-3 form-filter pol-pod-select">
                            <div class="d-flex align-items-center justify-content-between filter-label-row">
                                <label class="form-label fw-medium mb-0">
                                    {{ __('POL') }} <span class="text-muted fw-normal">({{ __('Port of Loading') }})</span>
                                </label>
                                <div class="shipment-toggle">
                                    <input type="radio" class="btn-check basic-sync-sea avoid-filter" name="basic_shipment_mode" id="basicPolSea" value="sea" checked>
                                    <label for="basicPolSea">{{ __('Sea') }}</label>
                                    <input type="radio" class="btn-check basic-sync-air avoid-filter" name="basic_shipment_mode" id="basicPolAir" value="air">
                                    <label for="basicPolAir">{{ __('Air') }}</label>
                                </div>
                            </div>
                            <select id="filter-pol" name="filter-pol" class="tom-select-search" data-placeholder="{{ __('Select Port of Loading') }}">
                                <option value=""></option>
                            </select>
                        </div>

                        <div class="col-md-6 col-xl-3 form-filter pol-pod-select">
                            <div class="d-flex align-items-center justify-content-between filter-label-row">
                                <label class="form-label fw-medium mb-0">
                                    {{ __('POD') }} <span class="text-muted fw-normal">({{ __('Port of Discharge') }})</span>
                                </label>
                                <div class="shipment-toggle">
                                    <input type="radio" class="btn-check basic-sync-sea avoid-filter" name="basic_shipment_mode_2" id="basicPodSea" value="sea" checked>
                                    <label for="basicPodSea">{{ __('Sea') }}</label>
                                    <input type="radio" class="btn-check basic-sync-air avoid-filter" name="basic_shipment_mode_2" id="basicPodAir" value="air">
                                    <label for="basicPodAir">{{ __('Air') }}</label>
                                </div>
                            </div>
                            <select id="filter-pod" name="filter-pod" class="tom-select-search" data-placeholder="{{ __('Select Port of Discharge') }}">
                                <option value=""></option>
                            </select>
                        </div>

                    </div>

                    <!-- Action Buttons -->
                    <div class="text-center mt-4 pt-2 border-top filter-panel-actions">
                        <button class="btn btn-primary btn-round px-4" type="button" id="apply-filter">
                            <i class="bi bi-search me-1"></i> {{ __('Search') }}
                        </button>
                    </div>

                </form>
            </div>
        </div>

        <!-- Tabs -->
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 py-3">
            <div class="align-items-center flex-shrink-0">
                <ul class="nav align-items-center" id="basicListTabs" role="tablist">
                    <li class="nav-item me-2">
                        <button class="nav-link px-3 py-2 d-flex align-items-center justify-content-between active status-btn"
                                type="button" data-tab="pending">
                            <span><i class="bi bi-clock me-1"></i> {{ __('Pending') }} -</span>
                            <span class="status-count ms-2" id="pendingCount">0</span>
                        </button>
                    </li>
                    <li class="nav-item me-2">
                        <button class="nav-link py-2 d-flex align-items-center justify-content-between status-btn"
                                type="button" data-tab="accepted">
                            <span><i class="bi bi-check-circle me-1"></i> {{ __('Accepted') }} -</span>
                            <span class="status-count ms-2" id="acceptedCount">0</span>
                        </button>
                    </li>
                    <li class="nav-item me-2">
                        <button class="nav-link py-2 d-flex align-items-center justify-content-between status-btn"
                                type="button" data-tab="converted">
                            <span><i class="bi bi-arrow-repeat me-1"></i> {{ __('Converted To Job') }} -</span>
                            <span class="status-count ms-2" id="convertedCount">0</span>
                        </button>
                    </li>
                    <li class="nav-item me-2">
                        <button class="nav-link py-2 d-flex align-items-center justify-content-between status-btn"
                                type="button" data-tab="cancelled">
                            <span><i class="bi bi-x-circle me-1"></i> {{ __('Cancelled / Expired') }} -</span>
                            <span class="status-count ms-2" id="cancelledCount">0</span>
                        </button>
                    </li>
                </ul>
            </div>
            <div class="d-flex align-items-center gap-2">
                <div class="search-box position-relative">
                    <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                    <input type="text" id="customSearch" class="form-control rounded-pill ps-5"
                           placeholder="{{ __('Search quotations...') }}" aria-label="{{ __('Search quotations...') }}">
                </div>
                <button class="btn btn-icon-search rounded-circle" id="filter-box" type="button"
                        title="{{ __('Filter') }}" aria-label="{{ __('Filter') }}"><i class="bi bi-funnel"></i></button>
            </div>
        </div>

        <!-- Table Section -->
        <div class="shadow bdr-r-10 py-3 flex-grow-1">
            <!-- Applied filters appear here as removable chips (filled by FILTER.filteredColumn in startup.js) -->
            <div class="d-flex justify-content-between align-items-start gap-2 px-3 flex-shrink-0">
                <div id="filtered-data" class="d-flex flex-wrap align-items-center"></div>
                <div class="d-flex align-items-center gap-2 ms-auto mb-2 flex-shrink-0">
                    <label for="qtnPageLength" class="small text-muted mb-0">{{ __('Show') }}</label>
                    <select id="qtnPageLength" class="form-select form-select-sm w-auto" aria-label="{{ __('Rows per page') }}">
                        <option value="10" selected>10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span class="small text-muted">{{ __('entries') }}</span>
                </div>
            </div>
            <div class="flex-grow-1">
                <table class="table align-middle" id="basicQuotationTable">
                    <thead class="table-light sticky-top">
                    <tr>
                        <th>{{ __('Quote No') }}</th>
                        <th>{{ __('Client') }}</th>
                        <th>{{ __('Department') }}</th>
                        <th>{{ __('Services') }}</th>
                        <th>{{ __('Origin') }} &rarr; {{ __('Destination') }}</th>
                        <th>{{ __('Salesperson') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </main>

    @include('modules.quotation.quotation-view')
    @include('modules.quotation.linked-enquiry-drawer')
    @include('modules.email.send-email')
    @include('modules.workflows.quotation')

    <script>
        $(function () {
            // ── POL/POD live search + Sea/Air toggle in the filter panel ──
            initTomSelectSearch('#filter-pol', 'sea', 100);
            initTomSelectSearch('#filter-pod', 'sea', 100);

            $('input[name=basic_shipment_mode]').on('change', function () {
                document.querySelector('#filter-pol').tomselect.destroy();
                initTomSelectSearch('#filter-pol', $(this).val(), 100);
            });
            $('input[name=basic_shipment_mode_2]').on('change', function () {
                document.querySelector('#filter-pod').tomselect.destroy();
                initTomSelectSearch('#filter-pod', $(this).val(), 100);
            });

            function collectFilterData() {
                return {
                    'filter-from-date': $('#filter-from-date').val() || '',
                    'filter-to-date': $('#filter-to-date').val() || '',
                    'customers': $('#customers').val() || [],
                    'filter-pol': $('#filter-pol').val() || '',
                    'filter-pod': $('#filter-pod').val() || '',
                };
            }

            function renderRowNo(data, type, row) {
                if (type !== 'display') return data ?? '';
                let html = `<span class="fw-semibold text-primary quotation-no-link" style="cursor:pointer;">${data ?? ''}</span>`;
                if (row.linked_enquiry_no) {
                    html += `<small class="d-block text-muted lh-sm">{{ __('Enquiry') }}: ${row.linked_enquiry_no}</small>`;
                }
                if (row.linked_job_no) {
                    html += `<small class="d-block text-muted lh-sm">{{ __('Job') }}: ${row.linked_job_no}</small>`;
                }
                return html;
            }

            function renderRoute(data, type, row) {
                if (type !== 'display') return `${row.pol ?? ''} ${row.pod ?? ''}`;
                return `${row.pol ?? '—'} &rarr; ${row.pod ?? '—'}`;
            }

            // Mirrors services() in app/Helpers/pre-defined-helpers.php.
            const SERVICE_LABELS = {
                1: "{{ __('Freight Forwarding') }}",
                2: "{{ __('Customs Clearance') }}",
                3: "{{ __('Transportation') }}",
                4: "{{ __('Warehousing') }}",
                5: "{{ __('Moving & Relocation') }}",
                6: "{{ __('Import/Export Trading') }}",
                7: "{{ __('Courier & Express Delivery') }}",
            };

            function renderServices(data) {
                if (!data) return '';
                const items = Array.isArray(data) ? data : String(data).split(',');
                return items.filter(Boolean).map(id => SERVICE_LABELS[id] ?? id).join(', ');
            }

            // Same 3-dot dropdown markup as the full list's action column
            // (GLOBAL_FN.dataTable.optionButton() in startup.js) — the menu
            // itself is populated on click from the real /actions endpoint,
            // same as everywhere else in the app.
            function renderActions(data, type, row) {
                if (type !== 'display') return '';
                if (!row.company_id) return '';
                return `<div class="dropdown">
                    <a class="btn btn-outline-secondary btn-sm rounded-circle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-three-dots-vertical"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end"></ul>
                </div>`;
            }

            function setStatusCounts(counts) {
                if (!counts) return;
                Object.entries(counts).forEach(([status, count]) => {
                    $('#' + status.toLowerCase() + 'Count').text(count);
                });
            }

            let table = null;
            let currentTab = 'pending';

            // Shared modal-save code calls this to refresh the list after a quotation is saved.
            window.afterModalSave = function () { if (table) { table.ajax.reload(null, false); } };

            function loadTab(tab) {
                currentTab = tab;
                if ($.fn.DataTable.isDataTable('#basicQuotationTable')) {
                    table.destroy();
                    $('#basicQuotationTable tbody').empty();
                }

                table = $('#basicQuotationTable').DataTable({
                    lengthChange: false,                       // the "Show N entries" selector lives in the chips row
                    pageLength: parseInt($('#qtnPageLength').val(), 10) || 10,
                    serverSide: true,
                    processing: true,
                    order: [],
                    ajax: {
                        url: '/sales/quotation/data',
                        type: 'POST',
                        data: (d) => {
                            d.tab = tab;
                            d.filterData = collectFilterData();
                        },
                        dataSrc: (json) => {
                            setStatusCounts(json.statusCounts);
                            return json.data;
                        },
                    },
                    columns: [
                        {data: 'row_no', render: renderRowNo, defaultContent: ''},
                        {data: 'client_name', defaultContent: ''},
                        {data: 'activity_name', defaultContent: ''},
                        {data: 'services', render: renderServices, defaultContent: ''},
                        {data: null, render: renderRoute, defaultContent: ''},
                        {data: 'salesperson_name', defaultContent: ''},
                        {data: 'posted_at', defaultContent: ''},
                        {data: null, orderable: false, searchable: false, render: renderActions, defaultContent: ''},
                    ],
                });
            }

            $('#basicListTabs .status-btn').on('click', function () {
                $('#basicListTabs .status-btn').removeClass('active');
                $(this).addClass('active');
                loadTab($(this).data('tab'));
            });

            $('#apply-filter').on('click', function () {
                if (window.FILTER && FILTER.filteredColumn) { FILTER.filteredColumn(); }   // show the applied filters as chips
                loadTab(currentTab);
            });

            $('#qtnPageLength').on('change', function () {
                if (table) { table.page.len(parseInt(this.value, 10)).draw(); }
            });

            // Search box: wait until typing stops, then ask the server.
            $('#customSearch').on('keyup input', window.debounceSearch(function () {
                if (table) { table.search(this.value).draw(); }
            }));

            // Same customer/prospect mutual-exclusion as ENQUIRY.form.customerProspectToggle()
            // in enquiry.js — picking one disables the other. Normally wired up by
            // QUOTATION.form.openCallback() (loadJs('form.openCallback') inside
            // webModal.openGlobalModal's success handler), but that's a no-op here
            // since this page doesn't load the quotation module — bind it ourselves
            // via openGlobalModal's callBack option instead.
            function bindCustomerProspectToggle() {
                $('#customer').off('change.custProspect').on('change.custProspect', function () {
                    const customerValue = $(this).val();
                    const prospectSelect = document.querySelector('#prospect');
                    if (customerValue && customerValue !== '') {
                        if (prospectSelect?.tomselect) prospectSelect.tomselect.disable();
                    } else {
                        if (prospectSelect?.tomselect) prospectSelect.tomselect.enable();
                    }
                });
                $('#prospect').off('change.custProspect').on('change.custProspect', function () {
                    const prospectValue = $(this).val();
                    const customerSelect = document.querySelector('#customer');
                    if (prospectValue && prospectValue !== '') {
                        if (customerSelect?.tomselect) customerSelect.tomselect.disable();
                    } else {
                        if (customerSelect?.tomselect) customerSelect.tomselect.enable();
                    }
                });
                // Reflect whichever is already filled in (e.g. editing an
                // existing quotation, or a New Quotation pre-filled from an
                // enquiry) as soon as the form loads, not just on change.
                $('#customer').trigger('change.custProspect');
                $('#prospect').trigger('change.custProspect');
            }

            function openNewQuotationModal(content) {
                webModal.openGlobalModal({
                    title: {{ Illuminate\Support\Js::from(__('New Quotation')) }},
                    url: '/sales/quotation/create',
                    size: 'lg',
                    scroll: false,
                    content: content,
                    callBack: function () {
                        setTimeout(bindCustomerProspectToggle);
                    },
                });
            }

            $('#new').on('click', function () {
                openNewQuotationModal();
            });

            // Coming from Enquiry's "Convert to Quotation" (which stores the
            // enquiry id then redirects here) — auto-open the New Quotation
            // modal pre-filled with that enquiry's details, same as the
            // dynamic list's QUOTATION.form.load() used to.
            const convertEnquiryId = localStorage.getItem('convert-enquiry');
            if (convertEnquiryId) {
                openNewQuotationModal({enquiryId: convertEnquiryId});
                localStorage.removeItem('convert-enquiry');
            }

            // ── Row actions dropdown ──────────────────────────────────────
            // Mirrors webDataTable.actions.menu()/menuCallBack() in
            // startup.js, but self-contained (that shared version needs
            // window[MODULE].actionUrl, which only exists when the full
            // "quotation" JS module is loaded — this page deliberately
            // doesn't load it). Same /actions endpoint, same menu JSON,
            // same icon map (webDataTable.actions.icons is itself
            // module-agnostic so it's reused directly).
            function openDrawer(id, name) {
                $('#drawerSubtitle').text(name || '');
                $('#moduleOverview').html('<div class="text-center py-5 text-muted"><div class="spinner-border spinner-border-sm me-2"></div> {{ __('Loading...') }}</div>');
                bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('moduleDrawer')).show();
                loadDrawerActions(id);
                $.get('/sales/quotation/' + id + '/overview-drawer', function (data) {
                    $('#moduleOverview').html(data);
                }).fail(function () {
                    $('#moduleOverview').html('<div class="alert alert-danger m-3">{{ __('Failed to load quotation details.') }}</div>');
                });
            }

            // The next-step actions for this quotation's status (same ones the row menu offers):
            // pending → Accepted / Cancelled, accepted → Move to Pending / Convert To Job.
            const DRAWER_ACTIONS = {
                row_accepted: {cls: 'btn-primary', icon: 'bi-check-circle', label: {{ Illuminate\Support\Js::from(__('Mark as Accepted')) }}},
                row_convert_to_job: {cls: 'btn-primary', icon: 'bi-arrow-repeat'},
                row_pending: {cls: 'btn-outline-secondary', icon: 'bi-arrow-counterclockwise'},
                row_rejected: {cls: 'btn-outline-danger', icon: 'bi-x-circle', label: {{ Illuminate\Support\Js::from(__('Mark as Cancelled')) }}},
            };

            function loadDrawerActions(id) {
                const $box = $('#drawerActions').empty();
                $.get('/sales/quotation/' + id + '/actions', function (items) {
                    const flat = [];
                    items.forEach(i => { if (i.items) i.items.forEach(s => flat.push(s)); else flat.push(i); });
                    // Accepted / Move to Pending / Convert To Job first, Cancelled last.
                    const order = ['row_accepted', 'row_pending', 'row_convert_to_job', 'row_rejected'];
                    order.forEach(key => {
                        const a = flat.find(x => x.id === key);
                        if (!a || !DRAWER_ACTIONS[key]) return;
                        const d = DRAWER_ACTIONS[key];
                        $box.append(`<button type="button" class="btn btn-sm rounded-pill px-3 ${d.cls} drawer-action" data-id="${a['data-id']}" data-value="${a['data-value']}" data-action="${key}"><i class="bi ${d.icon} me-1"></i>${d.label || a.label}</button>`);
                    });
                    // Print is available at every status.
                    $box.append(`<button type="button" class="btn btn-sm rounded-pill px-3 btn-outline-secondary drawer-print" data-id="${id}"><i class="bi bi-printer me-1"></i>{{ __('Print') }}</button>`);
                });
            }

            function printQuotation(id) {
                const iframe = document.getElementById('print-frame');
                iframe.onload = function () {
                    try {
                        iframe.contentWindow.focus();
                        iframe.contentWindow.print();
                    } catch (e) {
                        console.error('Cannot print iframe content.', e);
                    }
                };
                iframe.src = '/sales/quotation/' + id + '/print';
            }

            function changeStatus(id, newStatus, confirmMessage) {
                let fd = new FormData();
                changeCustomerStatus('/sales/quotation/' + id + '/status/' + newStatus, {
                    method: 'POST',
                    data: fd,
                    confirmMessage: confirmMessage,
                    // changeCustomerStatus() already toasts success itself
                    // before calling this — just refresh the table here.
                    callBack: function () {
                        table.ajax.reload(null, false);
                        const dr = bootstrap.Offcanvas.getInstance(document.getElementById('moduleDrawer'));
                        if (dr) dr.hide();
                    },
                }, String(newStatus));
            }

            function buildMenuHtml(actions) {
                let menu = '';
                actions.forEach(item => {
                    if (item.type === 'item') {
                        if (item.separator === 'before') menu += '<li class="separator"></li>';
                        menu += `<li><a class="dropdown-item" href="#" id="${item.id}" data-id="${item['data-id']}" ${item['data-value'] !== undefined ? `data-value="${item['data-value']}"` : ''}><i class="${webDataTable.actions.icons(item.icon)}"></i> ${item.label}</a></li>`;
                        if (item.separator === 'after') menu += '<li class="separator"></li>';
                    } else if (item.type === 'submenu') {
                        menu += `<li class="dropdown-submenu"><a class="dropdown-item dropdown-toggle"><i class="${webDataTable.actions.icons(item.icon)}"></i> ${item.label}</a><ul class="dropdown-menu">`;
                        item.items.forEach(sub => {
                            menu += `<li><a class="dropdown-item" href="#" id="${sub.id}" data-id="${sub['data-id']}" data-value="${sub['data-value']}"><i class="${webDataTable.actions.icons(sub.icon)}"></i> ${sub.label}</a></li>`;
                        });
                        menu += `</ul></li>`;
                        if (item.separator === 'after') menu += '<li class="separator"></li>';
                    }
                });
                return menu;
            }

            // Quote No click — opens the same view drawer as the "View" menu item.
            $('#basicQuotationTable tbody').on('click', '.quotation-no-link', function (e) {
                e.preventDefault();
                e.stopPropagation();
                const row = $(this).closest('tr');
                openDrawer(row.data('id'), row.data('name'));
            });

            $('#basicQuotationTable tbody').on('click', '.dropdown > a.btn', function (e) {
                e.preventDefault();
                e.stopPropagation();
                const row = $(this).closest('tr');
                const $menu = $(this).siblings('ul.dropdown-menu');

                $('#basicQuotationTable').find('.dropdown .dropdown-menu').not($menu).removeClass('show').empty();
                $menu.html('<li class="text-center p-3"><div class="spinner-border spinner-border-sm text-primary"></div></li>');

                $.get('/sales/quotation/' + row.data('id') + '/actions', function (actions) {
                    $menu.html(buildMenuHtml(actions));
                }).fail(function () {
                    $menu.html('');
                    toastr.error({{ Illuminate\Support\Js::from(__('Failed to load actions')) }});
                });
            });

            // Delegated handlers for whichever menu items are currently
            // rendered — ids match app/Http/Controllers/Quotation/
            // QuotationController::actions().
            $('#basicQuotationTable tbody').on('click', '#row_view', function () {
                const row = $(this).closest('tr');
                openDrawer(row.data('id'), row.data('name'));
            });
            // Send Email (row menu) — mirrors QUOTATION.list.actions.email in quotation.js, which this page doesn't load.
            $('#basicQuotationTable tbody').on('click', '#row_email', function () {
                const id = $(this).data('id');
                $.get('/sales/quotation/' + id + '/email-data', function (data) {
                    $('#emailTo').val(data.to);
                    $('#emailCc').val(data.cc);
                    $('#emailSubject').val('Quotation #' + data.id);
                    bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('sendEmailDrawer')).show();
                    $('#sendEmailForm').off('submit').on('submit', function (e) {
                        e.preventDefault();
                        const formData = new FormData(this);
                        const submitBtn = $(this).find('button[type="submit"]');
                        const originalBtnText = submitBtn.html();
                        submitBtn.html('<span class="spinner-border spinner-border-sm"></span> ' + {{ Illuminate\Support\Js::from(__('Sending...')) }});
                        submitBtn.prop('disabled', true);
                        $.ajax({
                            url: '/sales/quotation/send-email',
                            type: 'POST',
                            data: formData,
                            processData: false,
                            contentType: false,
                            success(response) {
                                bootstrap.Offcanvas.getInstance(document.getElementById('sendEmailDrawer')).hide();
                                toastr.success(response.message);
                                $('#sendEmailForm')[0].reset();
                            },
                            error(xhr) {
                                toastr.error((xhr.responseJSON && xhr.responseJSON.message) || {{ Illuminate\Support\Js::from(__('An error occurred while sending the email.')) }});
                            },
                            complete() {
                                submitBtn.html(originalBtnText);
                                submitBtn.prop('disabled', false);
                            }
                        });
                    });
                }).fail(function () {
                    toastr.error({{ Illuminate\Support\Js::from(__('Could not load the email details.')) }});
                });
            });

            $('#basicQuotationTable tbody').on('click', '#row_print', function () {
                printQuotation($(this).closest('tr').data('id'));
            });
            $('#basicQuotationTable tbody').on('click', '#row_edit', function () {
                const id = $(this).data('id');
                webModal.openGlobalModal({
                    title: {{ Illuminate\Support\Js::from(__('Edit Quotation')) }},
                    url: '/sales/quotation/' + id + '/create',
                    size: 'xl',
                    scroll: true,
                    callBack: function () {
                        setTimeout(bindCustomerProspectToggle);
                    },
                });
            });
            function statusConfirmMessage(action) {
                if (action === 'row_convert_to_job') return {{ Illuminate\Support\Js::from(__('Are you sure you want to convert this quotation to a job?')) }};
                if (action === 'row_accepted') return {{ Illuminate\Support\Js::from(__('Are you sure you want to mark this quotation as Accepted?')) }};
                if (action === 'row_pending') return {{ Illuminate\Support\Js::from(__('Are you sure you want to move this quotation back to Pending?')) }};
                if (action === 'row_rejected') return {{ Illuminate\Support\Js::from(__('Are you sure you want to cancel this quotation?')) }};
                return {{ Illuminate\Support\Js::from(__('Are you sure you want to change status?')) }};
            }

            // Enquiry no. in the quotation view: slide the enquiry over the quotation drawer; closing it returns to the quotation.
            $('#moduleDrawer').on('click', '.open-linked-enquiry', function (e) {
                e.preventDefault();
                const id = $(this).data('id');
                const qEl = document.getElementById('moduleDrawer');
                const eEl = document.getElementById('linkedEnquiryDrawer');
                $('#linkedEnquirySubtitle').text($(this).data('no') || '');
                $('#linkedEnquiryOverview').html('<div class="text-center py-5 text-muted"><div class="spinner-border spinner-border-sm me-2" role="status"></div> {{ __('Loading...') }}</div>');
                bootstrap.Tab.getOrCreateInstance(document.getElementById('linked-enquiry-general-tab')).show();
                // Two open drawers would fight over keyboard focus; pause the quotation drawer's trap while the enquiry is on top.
                const qFocus = bootstrap.Offcanvas.getInstance(qEl)?._focustrap;
                qFocus?.deactivate();
                eEl.addEventListener('hidden.bs.offcanvas', function () { qFocus?.activate(); }, {once: true});
                bootstrap.Offcanvas.getOrCreateInstance(eEl).show();
                $.get('/sales/enquiry/' + id + '/overview-drawer', function (data) {
                    $('#linkedEnquiryOverview').html(data);
                }).fail(function () {
                    $('#linkedEnquiryOverview').html('<div class="alert alert-danger m-3">{{ __('Failed to load enquiry details.') }}</div>');
                });
            });

            // Closing the quotation (main) drawer: the enquiry slide closes first, then the quotation closes by itself.
            document.getElementById('moduleDrawer').addEventListener('hide.bs.offcanvas', function (e) {
                if (e.target !== this) return;
                const linkedEl = document.getElementById('linkedEnquiryDrawer');
                if (!linkedEl.classList.contains('show')) return;
                e.preventDefault();
                linkedEl.addEventListener('hidden.bs.offcanvas', function () {
                    bootstrap.Offcanvas.getInstance(document.getElementById('moduleDrawer'))?.hide();
                }, {once: true});
                bootstrap.Offcanvas.getInstance(linkedEl).hide();
            });

            $('#moduleDrawer').on('click', '.drawer-print', function () {
                printQuotation($(this).data('id'));
            });

            // Buttons in the view drawer's header
            $('#moduleDrawer').on('click', '.drawer-action', function () {
                changeStatus($(this).data('id'), $(this).data('value'), statusConfirmMessage($(this).data('action')));
            });

            $('#basicQuotationTable tbody').on('click', '#row_accepted,#row_pending,#row_rejected,#row_convert_to_job', function () {
                const id = $(this).data('id');
                const value = $(this).data('value');
                let confirmMessage = {{ Illuminate\Support\Js::from(__('Are you sure you want to change status?')) }};
                if (this.id === 'row_convert_to_job') confirmMessage = {{ Illuminate\Support\Js::from(__('Are you sure you want to convert this quotation to a job?')) }};
                else if (this.id === 'row_accepted') confirmMessage = {{ Illuminate\Support\Js::from(__('Are you sure you want to mark this quotation as Accepted?')) }};
                else if (this.id === 'row_pending') confirmMessage = {{ Illuminate\Support\Js::from(__('Are you sure you want to move this quotation back to Pending?')) }};
                else if (this.id === 'row_rejected') confirmMessage = {{ Illuminate\Support\Js::from(__('Are you sure you want to cancel this quotation?')) }};
                changeStatus(id, value, confirmMessage);
            });

            if (typeof datepicker === 'function') { datepicker(); }                     // calendar pickers for the date range
            if (window.FILTER && FILTER.filteredColumn) { FILTER.filteredColumn(); }   // default date range shows as a chip
            loadTab('pending');
        });
    </script>
</x-app-layout>
