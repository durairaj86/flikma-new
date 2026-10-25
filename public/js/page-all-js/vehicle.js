VEHICLE = {
    title: trans('Vehicle'),
    baseUrl: 'masters/vehicle',
    actionUrl: 'masters/vehicle',
    load() {
        VEHICLE.form.load();
        datepicker();
        VEHICLE.filter.load();
        VEHICLE.list.load('all');
        FILTER.filteredColumn();
    },
    filter: {
        load() {
            $('#apply-filter').off().on('click', function () {
                VEHICLE.list.dataTable();
                FILTER.filteredColumn();
            });
            let timer;
            $('#customSearch').off().on('keyup', function (e) {
                clearTimeout(timer);
                timer = setTimeout(() => VEHICLE.list.dataTable(), e.key === 'Enter' ? 0 : 500);
            });
            $('#listTabs .status-btn').off().on('click', function () {
                VEHICLE.list.dataTable();
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
            VEHICLE.list.dataTable(activeTab);
        },
        dataTable() {
            GLOBAL_FN.destroyDataTable();
            const T = VEHICLE.list.templates;
            let table = $('#dataTable').DataTable({
                processing: false,
                serverSide: true,
                autoWidth: false,
                lengthChange: false,
                pageLength: 25,
                dom: 'rt<"row mt-2"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7 d-flex justify-content-end"p>>',
                ajax: {
                    url: GLOBAL_FN.buildUrl('masters/vehicle/data'),
                    type: 'POST',
                    data: function (d) {
                        d.tab = $('#listTabs').find('li button.active').attr('id');
                        d.filterData = VEHICLE.filter.default();
                    },
                    dataSrc: function (json) {
                        $('#dataTable tbody').find('.loading-row').remove();
                        GLOBAL_FN.setStatusCounts(json.statusCounts);
                        VEHICLE.list.cardSummary(json);
                        return json.data;
                    }
                },
                columnDefs: [{targets: '_all', orderable: false}],
                columns: [
                    {data: 'plate_no', render: (d, t, row) => T.number(row)},
                    {data: 'type', render: (d, t, row) => T.kind(row)},
                    {data: 'driver', render: (d, t, row) => T.driver(row)},
                    {data: 'registration_expiry', render: (d, t, row) => T.reg(row)},
                    {data: 'insurance_expiry', render: (d, t, row) => T.ins(row)},
                    GLOBAL_FN.dataTable.optionButton()
                ],
                language: {search: ''},
                initComplete: function () {
                    VEHICLE.form.open();
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
            $('#cardMaintCount').text(c.MAINTENANCE ?? 0);
            $('#cardExpiringCount').text(x.expiring ?? 0);
        },
        templates: {
            badge: {
                1: '<span class="badge bg-success-subtle text-success-emphasis">' + trans('Active') + '</span>',
                2: '<span class="badge bg-warning-subtle text-warning-emphasis">' + trans('Maintenance') + '</span>',
                3: '<span class="badge bg-secondary-subtle text-secondary-emphasis">' + trans('Inactive') + '</span>',
            },
            number: (row) => `<div class="cell-primary text-primary vehicle-no-link" style="cursor:pointer;">${row.plate_no ?? ''}</div><div class="mt-1">${VEHICLE.list.templates.badge[row.status] ?? ''}</div>`,
            kind: (row) => `<div class="cell-primary text-capitalize"><i class="bi bi-truck me-1 text-muted"></i>${(row.type || '—').replace('_', ' ')}</div><div class="cell-secondary">${row.make_model ?? ''}</div>`,
            driver: (row) => `<div class="cell-primary">${row.driver?.name ?? '—'}</div><div class="cell-secondary">${row.capacity_kg > 0 ? row.capacity_kg + ' kg' : ''}</div>`,
            reg: (row) => { const c = row.reg_state === 'expired' ? 'late' : (row.reg_state === 'soon' ? 'text-warning fw-semibold' : 'cell-primary'); return `<div class="${c}">${row.registration_expiry_f ?? '—'}</div><div class="cell-secondary">Registration${row.reg_state === 'expired' ? ' · expired' : (row.reg_state === 'soon' ? ' · ends soon' : '')}</div>`; },
            ins: (row) => { const c = row.ins_state === 'expired' ? 'late' : (row.ins_state === 'soon' ? 'text-warning fw-semibold' : 'cell-primary'); return `<div class="${c}">${row.insurance_expiry_f ?? '—'}</div><div class="cell-secondary">Insurance${row.ins_state === 'expired' ? ' · expired' : (row.ins_state === 'soon' ? ' · ends soon' : '')}</div>`; },
        },
        extraActions(row) {
            VEHICLE.list.actions.statusChange(row);
            VEHICLE.list.actions.view(row);
            VEHICLE.list.actions.delete(row);
        },
        actions: {
            statusChange(row) {
                $('#row_active,#row_inactive,#row_maintenance').off().on('click', function () {
                    changeCustomerStatus(GLOBAL_FN.buildUrl(VEHICLE.baseUrl + '/' + row.attr('data-id') + '/status/' + $(this).attr('data-value')), {
                        method: 'POST',
                        data: new FormData(),
                        callBack: 'datatable'
                    }, $(this).attr('data-value'));
                });
            },
            view(row) {
                $('#row_view').off().on('click', function () {
                    VEHICLE.list.openDrawer(row.attr('data-id'), row.attr('data-name'));
                });
            },
            delete(row) {
                $('#row_delete').off().on('click', function () {
                    deleteRecord(GLOBAL_FN.buildUrl(VEHICLE.baseUrl + '/' + row.attr('data-id')), function () { VEHICLE.list.dataTable(); },
                        {name: $.trim(row.find('.vehicle-no-link').text())});
                });
            },
        },
        openDrawer(id, name) {
            $('#drawerSubtitle').text(name || '');
            bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('moduleDrawer')).show();
            $('#moduleOverview').html('<div class="text-center py-5 text-muted"><div class="spinner-border spinner-border-sm me-2" role="status"></div> ' + trans('Loading...') + '</div>');
            $.get('/' + VEHICLE.baseUrl + '/' + id + '/overview', function (data) {
                $('#moduleOverview').html(data);
            }).fail(function () {
                $('#moduleOverview').html('<div class="alert alert-danger m-3">' + trans('Failed to load details.') + '</div>');
            });
        },
    },
    form: {
        load() {
            VEHICLE.form.open();
            $(document).off('click.vehicleno').on('click.vehicleno', '#dataTable tbody .vehicle-no-link', function (e) {
                e.preventDefault();
                const $row = $(this).closest('tr');
                VEHICLE.list.openDrawer($row.attr('data-id'), $row.attr('data-name'));
            });
        },
        open() {
            $('#new').off().on('click', function () {
                webModal.openGlobalModal({
                    title: trans('New Vehicle'),
                    url: GLOBAL_FN.buildUrl(VEHICLE.baseUrl + '/create'),
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
