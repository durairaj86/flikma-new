@section('page-title','Quotations')
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
    <main class="gmail-content bg-white px-3">

        <div id="filterPanel" class="card shadow-sm border-0 d-none">
            <div class="card-header bg-light border-0 py-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-funnel-fill text-primary"></i>
                    <h6 class="mb-0 fw-semibold">Advanced Filters</h6>
                </div>
            </div>
            <div class="card-body">
                <form id="list-filter" method="post" novalidate="novalidate">
                    @csrf
                    <div class="bg-light rounded p-3 mb-4">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3 form-filter">
                                <label class="form-label fw-medium">Quotation Date</label>
                                <div class="d-flex input-group-filter gap-2">
                                    <input type="date" class="form-control" id="filter-from-date" name="filter-from-date">
                                    <input type="date" class="form-control" id="filter-to-date" name="filter-to-date">
                                </div>
                            </div>

                            <div class="col-md-3 form-filter">
                                <label class="form-label fw-medium">Customer</label>
                                <x-common.customers multiple></x-common.customers>
                            </div>

                            <div class="col-md-3 form-filter pol-pod-select">
                                <label class="form-label fw-medium">POL <small class="text-muted">(Port of Loading)</small></label>
                                <div class="position-relative">
                                    <div class="shipment-toggle">
                                        <input type="radio" class="btn-check basic-sync-sea" name="basic_shipment_mode" id="basicPolSea" value="sea" checked>
                                        <label for="basicPolSea">Sea</label>
                                        <input type="radio" class="btn-check basic-sync-air" name="basic_shipment_mode" id="basicPolAir" value="air">
                                        <label for="basicPolAir">Air</label>
                                    </div>
                                    <select id="filter-pol" name="filter-pol" class="tom-select-search" data-placeholder="Select Port of Loading">
                                        <option value=""></option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3 pol-pod-select">
                                <label class="form-label fw-medium">POD <small class="text-muted">(Port of Discharge)</small></label>
                                <div class="position-relative">
                                    <div class="shipment-toggle">
                                        <input type="radio" class="btn-check basic-sync-sea" name="basic_shipment_mode_2" id="basicPodSea" value="sea" checked>
                                        <label for="basicPodSea">Sea</label>
                                        <input type="radio" class="btn-check basic-sync-air" name="basic_shipment_mode_2" id="basicPodAir" value="air">
                                        <label for="basicPodAir">Air</label>
                                    </div>
                                    <select id="filter-pod" name="filter-pod" class="tom-select-search" data-placeholder="Select Port of Discharge">
                                        <option value=""></option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <button class="btn btn-primary btn-round px-4" type="button" id="apply-filter">
                                <i class="bi bi-search me-1"></i> Search
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabs -->
        <div class="d-flex justify-content-between align-items-start py-3">
            <div class="align-items-center flex-shrink-0">
                <ul class="nav align-items-center" id="basicListTabs" role="tablist">
                    <li class="nav-item me-2">
                        <button class="nav-link px-3 py-2 d-flex align-items-center justify-content-between active status-btn"
                                type="button" data-tab="pending">
                            <span><i class="bi bi-clock me-1"></i> Pending -</span>
                            <span class="status-count ms-2" id="pendingCount">0</span>
                        </button>
                    </li>
                    <li class="nav-item me-2">
                        <button class="nav-link py-2 d-flex align-items-center justify-content-between status-btn"
                                type="button" data-tab="accepted">
                            <span><i class="bi bi-check-circle me-1"></i> Accepted -</span>
                            <span class="status-count ms-2" id="acceptedCount">0</span>
                        </button>
                    </li>
                    <li class="nav-item me-2">
                        <button class="nav-link py-2 d-flex align-items-center justify-content-between status-btn"
                                type="button" data-tab="converted">
                            <span><i class="bi bi-arrow-repeat me-1"></i> Converted To Job -</span>
                            <span class="status-count ms-2" id="convertedCount">0</span>
                        </button>
                    </li>
                    <li class="nav-item me-2">
                        <button class="nav-link py-2 d-flex align-items-center justify-content-between status-btn"
                                type="button" data-tab="cancelled">
                            <span><i class="bi bi-x-circle me-1"></i> Cancelled / Expired -</span>
                            <span class="status-count ms-2" id="cancelledCount">0</span>
                        </button>
                    </li>
                </ul>
            </div>
            <div class="d-flex justify-content-between align-items-center gap-2">
                <button class="btn btn-outline-secondary btn-round" id="filter-box">
                    <i class="bi bi-funnel"></i> Filter
                </button>
                <button class="btn btn-primary rounded-pill px-4" id="new">New Quotation</button>
            </div>
        </div>

        <!-- Table Section -->
        <div class="shadow bdr-r-10 py-3 flex-grow-1">
            <div class="flex-grow-1 overflow-auto" style="min-height:320px;">
                <table class="table align-middle" id="basicQuotationTable">
                    <thead class="table-light sticky-top">
                    <tr>
                        <th>Quote No</th>
                        <th>Client</th>
                        <th>Activity</th>
                        <th>Services</th>
                        <th>Origin &rarr; Destination</th>
                        <th>Salesperson</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </main>

    @include('modules.quotation.quotation-view')

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
                    html += `<small class="d-block text-muted lh-sm">Enquiry: ${row.linked_enquiry_no}</small>`;
                }
                if (row.linked_job_no) {
                    html += `<small class="d-block text-muted lh-sm">Job: ${row.linked_job_no}</small>`;
                }
                return html;
            }

            function renderRoute(data, type, row) {
                if (type !== 'display') return `${row.pol ?? ''} ${row.pod ?? ''}`;
                return `${row.pol ?? '—'} &rarr; ${row.pod ?? '—'}`;
            }

            // Mirrors services() in app/Helpers/pre-defined-helpers.php.
            const SERVICE_LABELS = {
                1: 'Freight Forwarding',
                2: 'Customs Clearance',
                3: 'Transportation',
                4: 'Warehousing',
                5: 'Moving & Relocation',
                6: 'Import/Export Trading',
                7: 'Courier & Express Delivery',
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

            function loadTab(tab) {
                currentTab = tab;
                if ($.fn.DataTable.isDataTable('#basicQuotationTable')) {
                    table.destroy();
                    $('#basicQuotationTable tbody').empty();
                }

                table = $('#basicQuotationTable').DataTable({
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
                loadTab(currentTab);
            });

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
                    title: 'New Quotation',
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
                $('#moduleOverview').html('<div class="text-center py-5 text-muted"><div class="spinner-border spinner-border-sm me-2"></div> Loading...</div>');
                bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('moduleDrawer')).show();
                $.get('/sales/quotation/' + id + '/overview-drawer', function (data) {
                    $('#moduleOverview').html(data);
                }).fail(function () {
                    $('#moduleOverview').html('<div class="alert alert-danger m-3">Failed to load quotation details.</div>');
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
                    toastr.error('Failed to load actions');
                });
            });

            // Delegated handlers for whichever menu items are currently
            // rendered — ids match app/Http/Controllers/Quotation/
            // QuotationController::actions().
            $('#basicQuotationTable tbody').on('click', '#row_view', function () {
                const row = $(this).closest('tr');
                openDrawer(row.data('id'), row.data('name'));
            });
            $('#basicQuotationTable tbody').on('click', '#row_print', function () {
                printQuotation($(this).closest('tr').data('id'));
            });
            $('#basicQuotationTable tbody').on('click', '#row_edit', function () {
                const id = $(this).data('id');
                webModal.openGlobalModal({
                    title: 'Edit Quotation',
                    url: '/sales/quotation/' + id + '/create',
                    size: 'xl',
                    scroll: true,
                    callBack: function () {
                        setTimeout(bindCustomerProspectToggle);
                    },
                });
            });
            $('#basicQuotationTable tbody').on('click', '#row_accepted,#row_pending,#row_rejected,#row_convert_to_job', function () {
                const id = $(this).data('id');
                const value = $(this).data('value');
                let confirmMessage = 'Are you sure you want to change status?';
                if (this.id === 'row_convert_to_job') confirmMessage = 'Are you sure you want to convert this quotation to a job?';
                else if (this.id === 'row_accepted') confirmMessage = 'Are you sure you want to mark this quotation as Accepted?';
                else if (this.id === 'row_pending') confirmMessage = 'Are you sure you want to move this quotation back to Pending?';
                else if (this.id === 'row_rejected') confirmMessage = 'Are you sure you want to cancel this quotation?';
                changeStatus(id, value, confirmMessage);
            });

            loadTab('pending');
        });
    </script>
</x-app-layout>
