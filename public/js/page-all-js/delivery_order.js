DELIVERY_ORDER = {
    title: 'Delivery Order',
    baseUrl: 'operation/delivery-order',
    actionUrl: 'operation/delivery-order',
    load() {
        DELIVERY_ORDER.form.load();
        datepicker();
        DELIVERY_ORDER.filter.load();
        DELIVERY_ORDER.list.load('all');
        FILTER.filteredColumn();
    },
    filter: {
        load() {
            $('#apply-filter').off().on('click', function () {
                DELIVERY_ORDER.list.dataTable();
                FILTER.filteredColumn();
            });
            let timer;
            $('#customSearch').off().on('keyup', function (e) {
                clearTimeout(timer);
                timer = setTimeout(() => DELIVERY_ORDER.list.dataTable(), e.key === 'Enter' ? 0 : 500);
            });
            $('#listTabs .status-btn').off().on('click', function () {
                DELIVERY_ORDER.list.dataTable();
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
            DELIVERY_ORDER.list.dataTable(activeTab);
        },
        dataTable() {
            GLOBAL_FN.destroyDataTable();
            const T = DELIVERY_ORDER.list.templates;
            let table = $('#dataTable').DataTable({
                processing: false,
                serverSide: true,
                autoWidth: false,
                lengthChange: false,
                pageLength: 25,
                dom: 'rt<"row mt-2"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7 d-flex justify-content-end"p>>',
                ajax: {
                    url: GLOBAL_FN.buildUrl('operation/delivery-order/data'),
                    type: 'POST',
                    data: function (d) {
                        d.tab = $('#listTabs').find('li button.active').attr('id');
                        d.filterData = DELIVERY_ORDER.filter.default();
                    },
                    dataSrc: function (json) {
                        $('#dataTable tbody').find('.loading-row').remove();
                        GLOBAL_FN.setStatusCounts(json.statusCounts);
                        DELIVERY_ORDER.list.cardSummary(json);
                        return json.data;
                    }
                },
                columnDefs: [{targets: '_all', orderable: false}],
                columns: [
                    {data: 'row_no', render: (d, t, row) => T.number(row)},
                    {data: 'customer', render: (d, t, row) => T.customer(row)},
                    {data: 'pickup_location', render: (d, t, row) => T.route(row)},
                    {data: 'vehicle_no', render: (d, t, row) => T.transport(row)},
                    {data: 'delivery_date_f', render: (d, t, row) => T.dates(row)},
                    {data: 'container_no', render: (d, t, row) => T.cargo(row)},
                    GLOBAL_FN.dataTable.optionButton()
                ],
                language: {search: ''},
                initComplete: function () {
                    DELIVERY_ORDER.form.open();
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
            $('#cardRoadCount').text(c.DISPATCHED ?? 0);
            $('#cardLateCount').text((json.cards && json.cards.late) || 0);
        },
        templates: {
            badge: {
                1: '<span class="badge bg-warning-subtle text-warning-emphasis">Pending</span>',
                2: '<span class="badge bg-primary-subtle text-primary-emphasis">Dispatched</span>',
                3: '<span class="badge bg-success-subtle text-success-emphasis">Delivered</span>',
                4: '<span class="badge bg-danger-subtle text-danger-emphasis">Cancelled</span>',
            },
            number: (row) => `<div class="cell-primary text-primary dlv-no-link" style="cursor:pointer;">${row.row_no ?? ''}</div><div class="mt-1">${DELIVERY_ORDER.list.templates.badge[row.status] ?? ''}</div>`,
            customer: (row) => `<div class="cell-primary">${row.customer?.name_en ?? '—'}</div><div class="cell-secondary">${row.job?.row_no ? 'Job ' + row.job.row_no : (row.customer?.row_no ?? '')}</div>`,
            route: (row) => `<div class="cell-primary">${row.pickup_location || '—'} <i class="bi bi-arrow-right mx-1 text-muted"></i> ${row.consignee || ''}</div>`
                + `<div class="cell-secondary text-truncate" style="max-width:260px;">${row.delivery_address ?? ''}</div>`,
            transport: (row) => `<div class="cell-primary"><i class="bi bi-truck me-1 text-muted"></i>${row.vehicle_no || '—'}</div>`
                + `<div class="cell-secondary">${[row.transporter, row.driver_name].filter(Boolean).join(' · ')}</div>`,
            dates: (row) => {
                if (row.delivered_at_f) return `<div class="cell-primary text-success"><i class="bi bi-check2-circle me-1"></i>${row.delivered_at_f}</div><div class="cell-secondary">Delivered</div>`;
                return `<div class="${row.is_late ? 'late' : 'cell-primary'}">${row.delivery_date_f ?? '—'}${row.is_late ? ' <i class="bi bi-exclamation-triangle-fill"></i>' : ''}</div>`
                    + `<div class="cell-secondary">${row.is_late ? 'Late' : 'Planned'}</div>`;
            },
            cargo: (row) => `<div class="cell-primary text-truncate" style="max-width:200px;">${row.container_no || '—'}</div>`
                + `<div class="cell-secondary">${[row.packages ? row.packages + ' pkgs' : '', parseFloat(row.weight) > 0 ? row.weight + ' kg' : ''].filter(Boolean).join(' · ')}</div>`,
        },
        extraActions(row) {
            DELIVERY_ORDER.list.actions.statusChange(row);
            DELIVERY_ORDER.list.actions.view(row);
            DELIVERY_ORDER.list.actions.delete(row);
        },
        actions: {
            statusChange(row) {
                $('#row_pending,#row_dispatched,#row_delivered,#row_rejected').off().on('click', function () {
                    changeCustomerStatus(GLOBAL_FN.buildUrl(DELIVERY_ORDER.baseUrl + '/' + row.attr('data-id') + '/status/' + $(this).attr('data-value')), {
                        method: 'POST',
                        data: new FormData(),
                        callBack: 'datatable'
                    }, $(this).attr('data-value'));
                });
            },
            view(row) {
                $('#row_view').off().on('click', function () {
                    DELIVERY_ORDER.list.openDrawer(row.attr('data-id'), row.attr('data-name'));
                });
            },
            delete(row) {
                $('#row_delete').off().on('click', function () {
                    deleteRecord(GLOBAL_FN.buildUrl(DELIVERY_ORDER.baseUrl + '/' + row.attr('data-id')), function () { DELIVERY_ORDER.list.dataTable(); },
                        {name: $.trim(row.find('.dlv-no-link').text())});
                });
            },
        },
        openDrawer(id, name) {
            $('#drawerSubtitle').text(name || '');
            bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('moduleDrawer')).show();
            $('#moduleOverview').html('<div class="text-center py-5 text-muted"><div class="spinner-border spinner-border-sm me-2" role="status"></div> Loading...</div>');
            $.get('/' + DELIVERY_ORDER.baseUrl + '/' + id + '/overview', function (data) {
                $('#moduleOverview').html(data);
            }).fail(function () {
                $('#moduleOverview').html('<div class="alert alert-danger m-3">Failed to load delivery order details.</div>');
            });
        },
    },
    printPreview(id) {
        const iframe = document.getElementById('print-frame');
        iframe.onload = function () {
            try { iframe.contentWindow.focus(); iframe.contentWindow.print(); } catch (e) { console.error(e); }
        };
        iframe.src = '/' + DELIVERY_ORDER.baseUrl + '/' + id + '/print';
    },
    form: {
        load() {
            DELIVERY_ORDER.form.open();
            // The order number in the list opens the details.
            $(document).off('click.dlvno').on('click.dlvno', '#dataTable tbody .dlv-no-link', function (e) {
                e.preventDefault();
                const $row = $(this).closest('tr');
                DELIVERY_ORDER.list.openDrawer($row.attr('data-id'), $row.attr('data-name'));
            });
        },
        open() {
            $('#new').off().on('click', function () {
                webModal.openGlobalModal({
                    title: 'New Delivery Order',
                    url: GLOBAL_FN.buildUrl(DELIVERY_ORDER.baseUrl + '/create'),
                    content: null,
                    size: 'xl',
                    scroll: true,
                });
            });
        },
        openCallback() {
            // Choosing a job fills the consignee, address, pickup and cargo (only where the form is still empty).
            $('#dlv-job').off('change').on('change', function () {
                const o = $(this).find('option:selected');
                if (!o.val()) return;
                const customer = document.getElementById('customer');
                if (customer && customer.tomselect && o.attr('data-customer-id')) customer.tomselect.setValue(o.attr('data-customer-id'));
                [['dlv-consignee', 'data-consignee'], ['dlv-address', 'data-address'], ['dlv-pickup', 'data-pickup'], ['dlv-container', 'data-container'],
                    ['dlv-packages', 'data-packages'], ['dlv-weight', 'data-weight'], ['dlv-description', 'data-description']].forEach(([id, attr]) => {
                    const el = document.getElementById(id);
                    if (el && (!el.value || el.value === '0.00') && o.attr(attr)) el.value = o.attr(attr);
                });
            });
        },
    },
};
