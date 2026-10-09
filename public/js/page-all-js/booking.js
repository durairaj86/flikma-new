BOOKING = {
    title: 'Booking',
    baseUrl: 'operation/booking',
    actionUrl: 'operation/booking',
    load() {
        BOOKING.form.load();
        datepicker();
        BOOKING.filter.load();
        BOOKING.list.load('all');
        FILTER.filteredColumn();
    },
    filter: {
        load() {
            $('#apply-filter').off().on('click', function () {
                BOOKING.list.dataTable();
                FILTER.filteredColumn();
            });
            let timer;
            $('#customSearch').off().on('keyup', function (e) {
                clearTimeout(timer);
                timer = setTimeout(() => BOOKING.list.dataTable(), e.key === 'Enter' ? 0 : 500);
            });
            $('#listTabs .status-btn').off().on('click', function () {
                BOOKING.list.dataTable();
            });
        },
        default() {
            let data = {}, tab = $('#listTabs').find('li button.active').attr('id');
            new URLSearchParams($('#list-filter').serialize()).forEach((value, key) => {
                data[key] = data[key] ? [].concat(data[key], value) : value;
            });
            data['tab'] = tab;
            data['customSearch'] = $('#customSearch').val();
            return data;
        },
    },
    list: {
        load(activeTab) {
            BOOKING.list.dataTable(activeTab);
        },
        dataTable() {
            GLOBAL_FN.destroyDataTable();
            const T = BOOKING.list.templates;
            let table = $('#dataTable').DataTable({
                processing: false,
                serverSide: true,
                autoWidth: false,
                lengthChange: false,
                pageLength: 25,
                dom: 'rt<"row mt-2"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7 d-flex justify-content-end"p>>',
                ajax: {
                    url: GLOBAL_FN.buildUrl('operation/booking/data'),
                    type: 'POST',
                    data: function (d) {
                        d.tab = $('#listTabs').find('li button.active').attr('id');
                        d.filterData = BOOKING.filter.default();
                    },
                    dataSrc: function (json) {
                        $('#dataTable tbody').find('.loading-row').remove();
                        GLOBAL_FN.setStatusCounts(json.statusCounts);
                        BOOKING.list.cardSummary(json);
                        return json.data;
                    }
                },
                columnDefs: [{targets: '_all', orderable: false}],
                columns: [
                    {data: 'row_no', render: (d, t, row) => T.number(row)},
                    {data: 'customer', render: (d, t, row) => T.customer(row)},
                    {data: 'carrier', render: (d, t, row) => T.carrier(row)},
                    {data: 'pol', render: (d, t, row) => T.route(row)},
                    {data: 'cargo_cutoff_f', render: (d, t, row) => T.cutoff(row)},
                    {data: 'etd_f', render: (d, t, row) => T.dates(row)},
                    {data: 'container_type', render: (d, t, row) => T.cargo(row)},
                    GLOBAL_FN.dataTable.optionButton()
                ],
                language: {search: ''},
                initComplete: function () {
                    BOOKING.form.open();
                    webDataTable.actions.menu();
                }
            });
            $('#dataTable_filter').closest('div.row').remove();
            webDataTable.loader(table);
            webDataTable.search(table);
        },
        cardSummary(json) {
            const c = json.statusCounts || {};
            $('#cardAllCount').text(c.all ?? 0);
            $('#cardPendingCount').text(c.PENDING ?? 0);
            $('#cardConfirmedCount').text(c.CONFIRMED ?? 0);
            $('#cardCutoffCount').text((json.cards && json.cards.cutoff_soon) || 0);
        },
        templates: {
            badge: {
                1: '<span class="badge bg-warning-subtle text-warning-emphasis">Pending</span>',
                2: '<span class="badge bg-success-subtle text-success-emphasis">Confirmed</span>',
                3: '<span class="badge bg-primary-subtle text-primary-emphasis">Shipped</span>',
                4: '<span class="badge bg-danger-subtle text-danger-emphasis">Cancelled</span>',
            },
            modeIcon: {sea: 'bi-water', air: 'bi-airplane', road: 'bi-truck'},
            number: (row) => `<div class="cell-primary text-primary booking-no-link" style="cursor:pointer;">${row.row_no ?? ''}</div><div class="mt-1">${BOOKING.list.templates.badge[row.status] ?? ''}</div>`,
            customer: (row) => `<div class="cell-primary">${row.customer?.name_en ?? '—'}</div><div class="cell-secondary">${row.job?.row_no ? 'Job ' + row.job.row_no : (row.customer?.row_no ?? '')}</div>`,
            carrier: (row) => `<div class="cell-primary"><i class="bi ${BOOKING.list.templates.modeIcon[row.shipment_mode] ?? 'bi-box'} me-1 text-muted"></i>${row.carrier?.name ?? '—'}</div>`
                + `<div class="cell-secondary">${[row.vessel_flight, row.voyage_no].filter(Boolean).join(' · ') || (row.booking_ref ? 'Ref ' + row.booking_ref : '')}</div>`,
            route: (row) => `<div class="cell-primary bk-route">${row.pol ?? ''} <i class="bi bi-arrow-right mx-1 text-muted"></i> ${row.pod ?? ''}</div>`,
            cutoff: (row) => {
                const cls = row.cutoff_soon ? 'cutoff-soon' : 'cell-primary';
                return `<div class="${cls}">${row.cargo_cutoff_f ?? '—'}${row.cutoff_soon ? ' <i class="bi bi-exclamation-triangle-fill"></i>' : ''}</div>`
                    + `<div class="cell-secondary">${row.doc_cutoff_f ? 'Docs ' + row.doc_cutoff_f : ''}</div>`;
            },
            dates: (row) => `<div class="cell-primary">${row.etd_f ?? '—'}</div><div class="cell-secondary">${row.eta_f ? 'ETA ' + row.eta_f : ''}</div>`,
            cargo: (row) => `<div class="cell-primary">${[row.container_qty, row.container_type].filter(Boolean).join(' × ') || '—'}</div>`
                + `<div class="cell-secondary">${parseFloat(row.gross_weight) > 0 ? row.gross_weight + ' kg' : ''}</div>`,
        },
        extraActions(row) {
            BOOKING.list.actions.statusChange(row);
            BOOKING.list.actions.view(row);
            BOOKING.list.actions.delete(row);
        },
        actions: {
            statusChange(row) {
                $('#row_pending,#row_confirmed,#row_shipped,#row_rejected').off().on('click', function () {
                    changeCustomerStatus(GLOBAL_FN.buildUrl(BOOKING.baseUrl + '/' + row.attr('data-id') + '/status/' + $(this).attr('data-value')), {
                        method: 'POST',
                        data: new FormData(),
                        callBack: 'datatable'
                    }, $(this).attr('data-value'));
                });
            },
            view(row) {
                $('#row_view').off().on('click', function () {
                    BOOKING.list.openDrawer(row.attr('data-id'), row.attr('data-name'));
                });
            },
            delete(row) {
                $('#row_delete').off().on('click', function () {
                    deleteRecord(GLOBAL_FN.buildUrl(BOOKING.baseUrl + '/' + row.attr('data-id')), function () { BOOKING.list.dataTable(); },
                        {name: $.trim(row.find('.booking-no-link').text())});
                });
            },
        },
        openDrawer(id, name) {
            $('#drawerSubtitle').text(name || '');
            bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('moduleDrawer')).show();
            $('#moduleOverview').html('<div class="text-center py-5 text-muted"><div class="spinner-border spinner-border-sm me-2" role="status"></div> Loading...</div>');
            $.get('/' + BOOKING.baseUrl + '/' + id + '/overview', function (data) {
                $('#moduleOverview').html(data);
            }).fail(function () {
                $('#moduleOverview').html('<div class="alert alert-danger m-3">Failed to load booking details.</div>');
            });
        },
    },
    form: {
        load() {
            BOOKING.form.open();
            // The booking number in the list opens the details.
            $(document).off('click.bkno').on('click.bkno', '#dataTable tbody .booking-no-link', function (e) {
                e.preventDefault();
                const $row = $(this).closest('tr');
                BOOKING.list.openDrawer($row.attr('data-id'), $row.attr('data-name'));
            });
        },
        open() {
            $('#new').off().on('click', function () {
                webModal.openGlobalModal({
                    title: 'New Booking',
                    url: GLOBAL_FN.buildUrl(BOOKING.baseUrl + '/create'),
                    content: null,
                    size: 'xl',
                    scroll: true,
                });
            });
        },
        openCallback() {
            // Choosing a job fills in the customer, mode, route and dates (only where the form is still empty).
            $('#bk-job').off('change').on('change', function () {
                const o = $(this).find('option:selected');
                if (!o.val()) return;
                const customer = document.getElementById('customer');
                if (customer && customer.tomselect && o.attr('data-customer-id')) customer.tomselect.setValue(o.attr('data-customer-id'));
                const mode = document.getElementById('bk-mode');
                if (mode && mode.tomselect && ['sea', 'air', 'road'].includes(o.attr('data-mode'))) mode.tomselect.setValue(o.attr('data-mode'));
                [['bk-pol', 'data-pol'], ['bk-pod', 'data-pod'], ['bk-etd', 'data-etd'], ['bk-eta', 'data-eta']].forEach(([id, attr]) => {
                    const el = document.getElementById(id);
                    if (el && !el.value && o.attr(attr)) {
                        el.value = o.attr(attr);
                        if (el._flatpickr) el._flatpickr.setDate(o.attr(attr), false, 'd-m-Y');
                    }
                });
            });
        },
    },
};
