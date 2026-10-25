DRIVER = {
    title: trans('Driver'),
    baseUrl: 'masters/driver',
    actionUrl: 'masters/driver',
    load() {
        DRIVER.form.load();
        datepicker();
        DRIVER.filter.load();
        DRIVER.list.load('all');
        FILTER.filteredColumn();
    },
    filter: {
        load() {
            $('#apply-filter').off().on('click', function () {
                DRIVER.list.dataTable();
                FILTER.filteredColumn();
            });
            let timer;
            $('#customSearch').off().on('keyup', function (e) {
                clearTimeout(timer);
                timer = setTimeout(() => DRIVER.list.dataTable(), e.key === 'Enter' ? 0 : 500);
            });
            $('#listTabs .status-btn').off().on('click', function () {
                DRIVER.list.dataTable();
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
            DRIVER.list.dataTable(activeTab);
        },
        dataTable() {
            GLOBAL_FN.destroyDataTable();
            const T = DRIVER.list.templates;
            let table = $('#dataTable').DataTable({
                processing: false,
                serverSide: true,
                autoWidth: false,
                lengthChange: false,
                pageLength: 25,
                dom: 'rt<"row mt-2"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7 d-flex justify-content-end"p>>',
                ajax: {
                    url: GLOBAL_FN.buildUrl('masters/driver/data'),
                    type: 'POST',
                    data: function (d) {
                        d.tab = $('#listTabs').find('li button.active').attr('id');
                        d.filterData = DRIVER.filter.default();
                    },
                    dataSrc: function (json) {
                        $('#dataTable tbody').find('.loading-row').remove();
                        GLOBAL_FN.setStatusCounts(json.statusCounts);
                        DRIVER.list.cardSummary(json);
                        return json.data;
                    }
                },
                columnDefs: [{targets: '_all', orderable: false}],
                columns: [
                    {data: 'name', render: (d, t, row) => T.number(row)},
                    {data: 'phone', render: (d, t, row) => T.contact(row)},
                    {data: 'license_no', render: (d, t, row) => T.licence(row)},
                    {data: 'row_no', render: (d, t, row) => T.code(row)},
                    GLOBAL_FN.dataTable.optionButton()
                ],
                language: {search: ''},
                initComplete: function () {
                    DRIVER.form.open();
                    webDataTable.actions.menu();
                }
            });
            $('#dataTable_filter').closest('div.row').remove();
            webDataTable.loader(table);
            webDataTable.search(table);
        },
        cardSummary(json) {
            const c = json.statusCounts || {};
            const x = json.cards || {};
            $('#cardAllCount').text(c.all ?? 0);
            $('#cardActiveCount').text(c.ACTIVE ?? 0);
            $('#cardInactiveCount').text(c.INACTIVE ?? 0);
            $('#cardExpiringCount').text(x.expiring ?? 0);
        },
        templates: {
            badge: {
                1: '<span class="badge bg-success-subtle text-success-emphasis">' + trans('Active') + '</span>',
                2: '<span class="badge bg-secondary-subtle text-secondary-emphasis">' + trans('Inactive') + '</span>',
            },
            number: (row) => `<div class="cell-primary text-primary driver-no-link" style="cursor:pointer;">${row.name ?? ''}</div><div class="mt-1">${DRIVER.list.templates.badge[row.status] ?? ''}</div>`,
            contact: (row) => `<div class="cell-primary">${row.phone || '—'}</div><div class="cell-secondary">${row.iqama_no ? 'ID ' + row.iqama_no : ''}</div>`,
            licence: (row) => { const c = row.license_state === 'expired' ? 'late' : (row.license_state === 'soon' ? 'text-warning fw-semibold' : 'cell-primary'); return `<div class="${c}">${row.license_expiry_f ?? '—'}</div><div class="cell-secondary">Licence ${row.license_no || ""}${row.license_state === 'expired' ? ' · expired' : (row.license_state === 'soon' ? ' · ends soon' : '')}</div>`; },
            code: (row) => `<div class="cell-primary">${row.row_no ?? ''}</div>`,
        },
        extraActions(row) {
            DRIVER.list.actions.statusChange(row);
            DRIVER.list.actions.view(row);
            DRIVER.list.actions.delete(row);
        },
        actions: {
            statusChange(row) {
                $('#row_active,#row_inactive').off().on('click', function () {
                    changeCustomerStatus(GLOBAL_FN.buildUrl(DRIVER.baseUrl + '/' + row.attr('data-id') + '/status/' + $(this).attr('data-value')), {
                        method: 'POST',
                        data: new FormData(),
                        callBack: 'datatable'
                    }, $(this).attr('data-value'));
                });
            },
            view(row) {
                $('#row_view').off().on('click', function () {
                    DRIVER.list.openDrawer(row.attr('data-id'), row.attr('data-name'));
                });
            },
            delete(row) {
                $('#row_delete').off().on('click', function () {
                    deleteRecord(GLOBAL_FN.buildUrl(DRIVER.baseUrl + '/' + row.attr('data-id')), function () { DRIVER.list.dataTable(); },
                        {name: $.trim(row.find('.driver-no-link').text())});
                });
            },
        },
        openDrawer(id, name) {
            $('#drawerSubtitle').text(name || '');
            bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('moduleDrawer')).show();
            $('#moduleOverview').html('<div class="text-center py-5 text-muted"><div class="spinner-border spinner-border-sm me-2" role="status"></div> ' + trans('Loading...') + '</div>');
            $.get('/' + DRIVER.baseUrl + '/' + id + '/overview', function (data) {
                $('#moduleOverview').html(data);
            }).fail(function () {
                $('#moduleOverview').html('<div class="alert alert-danger m-3">' + trans('Failed to load details.') + '</div>');
            });
        },
    },
    form: {
        load() {
            DRIVER.form.open();
            $(document).off('click.driverno').on('click.driverno', '#dataTable tbody .driver-no-link', function (e) {
                e.preventDefault();
                const $row = $(this).closest('tr');
                DRIVER.list.openDrawer($row.attr('data-id'), $row.attr('data-name'));
            });
        },
        open() {
            $('#new').off().on('click', function () {
                webModal.openGlobalModal({
                    title: trans('New Driver'),
                    url: GLOBAL_FN.buildUrl(DRIVER.baseUrl + '/create'),
                    content: null,
                    size: 'xl',
                    scroll: true,
                });
            });
        },
        openCallback() {
        },
    },
};
