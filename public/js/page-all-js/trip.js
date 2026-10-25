TRIP = {
    title: trans('Trip'),
    baseUrl: 'operation/trip',
    actionUrl: 'operation/trip',
    load() {
        TRIP.form.load();
        datepicker();
        TRIP.filter.load();
        TRIP.list.load('all');
        FILTER.filteredColumn();
    },
    filter: {
        load() {
            $('#apply-filter').off().on('click', function () {
                TRIP.list.dataTable();
                FILTER.filteredColumn();
            });
            let timer;
            $('#customSearch').off().on('keyup', function (e) {
                clearTimeout(timer);
                timer = setTimeout(() => TRIP.list.dataTable(), e.key === 'Enter' ? 0 : 500);
            });
            $('#listTabs .status-btn').off().on('click', function () {
                TRIP.list.dataTable();
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
            TRIP.list.dataTable(activeTab);
        },
        dataTable() {
            GLOBAL_FN.destroyDataTable();
            const T = TRIP.list.templates;
            let table = $('#dataTable').DataTable({
                processing: false,
                serverSide: true,
                autoWidth: false,
                lengthChange: false,
                pageLength: 25,
                dom: 'rt<"row mt-2"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7 d-flex justify-content-end"p>>',
                ajax: {
                    url: GLOBAL_FN.buildUrl('operation/trip/data'),
                    type: 'POST',
                    data: function (d) {
                        d.tab = $('#listTabs').find('li button.active').attr('id');
                        d.filterData = TRIP.filter.default();
                    },
                    dataSrc: function (json) {
                        $('#dataTable tbody').find('.loading-row').remove();
                        GLOBAL_FN.setStatusCounts(json.statusCounts);
                        TRIP.list.cardSummary(json);
                        return json.data;
                    }
                },
                columnDefs: [{targets: '_all', orderable: false}],
                columns: [
                    {data: 'row_no', render: (d, t, row) => T.number(row)},
                    {data: 'vehicle', render: (d, t, row) => T.unit(row)},
                    {data: 'origin', render: (d, t, row) => T.route(row)},
                    {data: 'job', render: (d, t, row) => T.linked(row)},
                    {data: 'trip_date_f', render: (d, t, row) => T.when(row)},
                    {data: 'freight_amount', render: (d, t, row) => T.money(row)},
                    GLOBAL_FN.dataTable.optionButton()
                ],
                language: {search: ''},
                initComplete: function () {
                    TRIP.form.open();
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
            $('#cardPlannedCount').text(c.PLANNED ?? 0);
            $('#cardRoadCount').text(c.STARTED ?? 0);
            $('#cardMargin').text(x.margin ?? "0.00");
        },
        templates: {
            badge: {
                1: '<span class="badge bg-warning-subtle text-warning-emphasis">' + trans('Planned') + '</span>',
                2: '<span class="badge bg-primary-subtle text-primary-emphasis">' + trans('Started') + '</span>',
                3: '<span class="badge bg-success-subtle text-success-emphasis">' + trans('Completed') + '</span>',
                4: '<span class="badge bg-danger-subtle text-danger-emphasis">' + trans('Cancelled') + '</span>',
            },
            number: (row) => `<div class="cell-primary text-primary trip-no-link" style="cursor:pointer;">${row.row_no ?? ''}</div><div class="mt-1">${TRIP.list.templates.badge[row.status] ?? ''}</div>`,
            unit: (row) => `<div class="cell-primary"><i class="bi bi-truck me-1 text-muted"></i>${row.vehicle?.plate_no ?? '—'}</div><div class="cell-secondary">${row.driver?.name ?? ''}</div>`,
            route: (row) => `<div class="cell-primary">${row.origin ?? ''} <i class="bi bi-arrow-right mx-1 text-muted"></i> ${row.destination ?? ''}</div><div class="cell-secondary">${row.distance_km > 0 ? row.distance_km + ' km' : ''}</div>`,
            linked: (row) => `<div class="cell-primary">${row.job?.row_no ? 'Job ' + row.job.row_no : '—'}</div><div class="cell-secondary">${row.delivery_order?.row_no ? 'D.O ' + row.delivery_order.row_no : ''}</div>`,
            when: (row) => `<div class="cell-primary">${row.trip_date_f ?? '—'}</div><div class="cell-secondary">${row.completed_f ? trans('Done') + ' ' + row.completed_f : (row.started_f ? trans('Started') + ' ' + row.started_f : '')}</div>`,
            money: (row) => `<div class="cell-primary">${row.freight_f}</div><div class="cell-secondary">${trans('Cost')} ${row.cost_f} · <span class="${row.margin < 0 ? 'text-danger' : 'text-success'}">${trans('Margin')} ${row.margin_f}</span></div>`,
        },
        extraActions(row) {
            TRIP.list.actions.statusChange(row);
            TRIP.list.actions.view(row);
            TRIP.list.actions.delete(row);
        },
        actions: {
            statusChange(row) {
                $('#row_completed,#row_planned,#row_rejected,#row_started').off().on('click', function () {
                    changeCustomerStatus(GLOBAL_FN.buildUrl(TRIP.baseUrl + '/' + row.attr('data-id') + '/status/' + $(this).attr('data-value')), {
                        method: 'POST',
                        data: new FormData(),
                        callBack: 'datatable'
                    }, $(this).attr('data-value'));
                });
            },
            view(row) {
                $('#row_view').off().on('click', function () {
                    TRIP.list.openDrawer(row.attr('data-id'), row.attr('data-name'));
                });
            },
            delete(row) {
                $('#row_delete').off().on('click', function () {
                    deleteRecord(GLOBAL_FN.buildUrl(TRIP.baseUrl + '/' + row.attr('data-id')), function () { TRIP.list.dataTable(); },
                        {name: $.trim(row.find('.trip-no-link').text())});
                });
            },
        },
        openDrawer(id, name) {
            $('#drawerSubtitle').text(name || '');
            bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('moduleDrawer')).show();
            $('#moduleOverview').html('<div class="text-center py-5 text-muted"><div class="spinner-border spinner-border-sm me-2" role="status"></div> ' + trans('Loading...') + '</div>');
            $.get('/' + TRIP.baseUrl + '/' + id + '/overview', function (data) {
                $('#moduleOverview').html(data);
            }).fail(function () {
                $('#moduleOverview').html('<div class="alert alert-danger m-3">' + trans('Failed to load details.') + '</div>');
            });
        },
    },
    form: {
        load() {
            TRIP.form.open();
            $(document).off('click.tripno').on('click.tripno', '#dataTable tbody .trip-no-link', function (e) {
                e.preventDefault();
                const $row = $(this).closest('tr');
                TRIP.list.openDrawer($row.attr('data-id'), $row.attr('data-name'));
            });
        },
        open() {
            $('#new').off().on('click', function () {
                webModal.openGlobalModal({
                    title: trans('New Trip'),
                    url: GLOBAL_FN.buildUrl(TRIP.baseUrl + '/create'),
                    content: null,
                    size: 'xl',
                    scroll: true,
                });
            });
        },
        openCallback() {
            // Choosing a vehicle fills in its usual driver (only if none is chosen yet).
            $('select[name="vehicle_id"]').off('change.trip').on('change.trip', function () {
                const driver = $(this).find('option:selected').attr('data-driver');
                const el = document.querySelector('select[name="driver_id"]');
                if (driver && el && el.tomselect && !el.value) el.tomselect.setValue(driver);
            });
        },
    },
};
