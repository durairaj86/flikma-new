CUSTOMS = {
    title: 'Customs Clearance',
    baseUrl: 'operation/customs',
    actionUrl: 'operation/customs',
    load() {
        datepicker();
        CUSTOMS.filter.load();
        CUSTOMS.list.dataTable();
        FILTER.filteredColumn();
        // The job number in the list opens the details.
        $(document).off('click.ccno').on('click.ccno', '#dataTable tbody .customs-no-link', function (e) {
            e.preventDefault();
            const $row = $(this).closest('tr');
            CUSTOMS.list.openDrawer($row.attr('data-id'), $row.attr('data-name'));
        });
    },
    filter: {
        load() {
            $('#apply-filter').off().on('click', function () {
                CUSTOMS.list.dataTable();
                FILTER.filteredColumn();
            });
            let timer;
            $('#customSearch').off().on('keyup', function (e) {
                clearTimeout(timer);
                timer = setTimeout(() => CUSTOMS.list.dataTable(), e.key === 'Enter' ? 0 : 500);
            });
            $('#listTabs .status-btn').off().on('click', function () {
                CUSTOMS.list.dataTable();
            });
        },
        default() {
            let data = {};
            new URLSearchParams($('#list-filter').serialize()).forEach((value, key) => {
                data[key] = data[key] ? [].concat(data[key], value) : value;
            });
            data['customSearch'] = $('#customSearch').val();
            return data;
        },
    },
    list: {
        load() {
            CUSTOMS.list.dataTable();
        },
        dataTable() {
            GLOBAL_FN.destroyDataTable();
            const T = CUSTOMS.list.templates;
            let table = $('#dataTable').DataTable({
                processing: false,
                serverSide: true,
                autoWidth: false,
                lengthChange: false,
                pageLength: 25,
                dom: 'rt<"row mt-2"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7 d-flex justify-content-end"p>>',
                ajax: {
                    url: GLOBAL_FN.buildUrl('operation/customs/data'),
                    type: 'POST',
                    data: function (d) {
                        d.tab = $('#listTabs').find('li button.active').attr('id');
                        d.filterData = CUSTOMS.filter.default();
                    },
                    dataSrc: function (json) {
                        $('#dataTable tbody').find('.loading-row').remove();
                        // tab ids contain a dash (under-process), so set the counts by hand
                        const c = json.statusCounts || {};
                        ['all', 'pending', 'under-process', 'cleared', 'on-hold'].forEach(k => $('[id="' + k + 'Count"]').text(c[k] ?? 0));
                        CUSTOMS.list.cardSummary(json);
                        return json.data;
                    }
                },
                columnDefs: [{targets: '_all', orderable: false}],
                columns: [
                    {data: 'job_no', render: (d, t, row) => T.job(row)},
                    {data: 'customer', render: (d, t, row) => T.customer(row)},
                    {data: 'declaration_no', render: (d, t, row) => T.declaration(row)},
                    {data: 'customs_broker', render: (d, t, row) => T.broker(row)},
                    {data: 'type_of_clearance', render: (d, t, row) => T.type(row)},
                    {data: 'duty_f', class: 'text-end', render: (d, t, row) => T.duty(row)},
                    {data: 'clearance_date_f', render: (d, t, row) => T.progress(row)},
                    GLOBAL_FN.dataTable.optionButton()
                ],
                language: {search: ''},
                initComplete: function () {
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
            $('#cardOpenCount').text((c['pending'] ?? 0) + (c['under-process'] ?? 0) + (c['on-hold'] ?? 0));
            $('#cardClearedCount').text(c['cleared'] ?? 0);
            const d = json.cards || {};
            $('#cardDuty').text((d.duty ?? '0.00') + ' / ' + (d.duty_client ?? '0.00'));
        },
        templates: {
            badge: {
                'pending': '<span class="badge bg-warning-subtle text-warning-emphasis">Pending</span>',
                'under-process': '<span class="badge bg-info-subtle text-info-emphasis">Under Process</span>',
                'cleared': '<span class="badge bg-success-subtle text-success-emphasis">Cleared</span>',
                'on-hold': '<span class="badge bg-danger-subtle text-danger-emphasis">On Hold</span>',
            },
            job: (row) => `<div class="cell-primary text-primary customs-no-link" style="cursor:pointer;">${row.job_no ?? '—'}</div><div class="mt-1">${CUSTOMS.list.templates.badge[row.status_key] ?? ''}</div>`,
            customer: (row) => `<div class="cell-primary">${row.customer?.name_en ?? '—'}</div><div class="cell-secondary">${row.customer?.row_no ?? ''}</div>`,
            declaration: (row) => `<div class="cell-primary">${row.declaration_no || '—'}</div><div class="cell-secondary">${row.bayan_no ? 'Bayan ' + row.bayan_no : ''}${row.do_no ? ' · D.O ' + row.do_no : ''}</div>`,
            broker: (row) => `<div class="cell-primary">${row.customs_broker || '—'}</div><div class="cell-secondary">${row.port_clearance || ''}</div>`,
            type: (row) => `<div class="cell-primary text-capitalize">${row.type_of_clearance || '—'}</div>`
                + `<div class="cell-secondary">${[row.lab_clearance ? 'Lab' : '', row.inspection ? 'Inspection' : ''].filter(Boolean).join(' · ')}</div>`,
            duty: (row) => `<div class="cell-primary">${row.duty_f}</div><div class="cell-secondary">${row.duty_client_f}</div>`,
            progress: (row) => {
                if (row.clearance_date_f) return `<div class="cell-primary text-success"><i class="bi bi-check-circle me-1"></i>${row.clearance_date_f}</div><div class="cell-secondary">Cleared</div>`;
                if (row.do_date_f) return `<div class="cell-primary">${row.do_date_f}</div><div class="cell-secondary">D.O issued</div>`;
                if (row.bayan_date_f) return `<div class="cell-primary">${row.bayan_date_f}</div><div class="cell-secondary">Bayan filed</div>`;
                return '<div class="cell-secondary">—</div>';
            },
        },
        extraActions(row) {
            // "Move to" items carry their target status in data-value
            $('.row_status').off().on('click', function () {
                changeCustomerStatus(GLOBAL_FN.buildUrl(CUSTOMS.baseUrl + '/' + row.attr('data-id') + '/status/' + $(this).attr('data-value')), {
                    method: 'POST',
                    data: new FormData(),
                    callBack: 'datatable'
                }, $(this).attr('data-value'));
            });
            $('#row_view').off().on('click', function () {
                CUSTOMS.list.openDrawer(row.attr('data-id'), row.attr('data-name'));
            });
        },
        openDrawer(id, name) {
            $('#drawerSubtitle').text(name || '');
            bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('moduleDrawer')).show();
            $('#moduleOverview').html('<div class="text-center py-5 text-muted"><div class="spinner-border spinner-border-sm me-2" role="status"></div> Loading...</div>');
            $.get('/' + CUSTOMS.baseUrl + '/' + id + '/overview', function (data) {
                $('#moduleOverview').html(data);
            }).fail(function () {
                $('#moduleOverview').html('<div class="alert alert-danger m-3">Failed to load clearance details.</div>');
            });
        },
    },
    form: {
        // The edit form is opened by the generic row menu ("Update"); nothing extra to wire up.
        load() {},
        openCallback() {},
    },
};
