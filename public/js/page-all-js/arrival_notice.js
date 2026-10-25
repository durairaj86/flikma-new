ARRIVAL_NOTICE = {
    title: trans('Arrival Notice'),
    baseUrl: 'operation/arrival-notice',
    actionUrl: 'operation/arrival-notice',
    load() {
        ARRIVAL_NOTICE.form.load();
        datepicker();
        ARRIVAL_NOTICE.filter.load();
        ARRIVAL_NOTICE.list.load('all');
        FILTER.filteredColumn();
    },
    filter: {
        load() {
            $('#apply-filter').off().on('click', function () {
                ARRIVAL_NOTICE.list.dataTable();
                FILTER.filteredColumn();
            });
            let timer;
            $('#customSearch').off().on('keyup', function (e) {
                clearTimeout(timer);
                timer = setTimeout(() => ARRIVAL_NOTICE.list.dataTable(), e.key === 'Enter' ? 0 : 500);
            });
            $('#listTabs .status-btn').off().on('click', function () {
                ARRIVAL_NOTICE.list.dataTable();
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
            ARRIVAL_NOTICE.list.dataTable(activeTab);
        },
        dataTable() {
            GLOBAL_FN.destroyDataTable();
            const T = ARRIVAL_NOTICE.list.templates;
            let table = $('#dataTable').DataTable({
                processing: false,
                serverSide: true,
                autoWidth: false,
                lengthChange: false,
                pageLength: 25,
                dom: 'rt<"row mt-2"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7 d-flex justify-content-end"p>>',
                ajax: {
                    url: GLOBAL_FN.buildUrl('operation/arrival-notice/data'),
                    type: 'POST',
                    data: function (d) {
                        d.tab = $('#listTabs').find('li button.active').attr('id');
                        d.filterData = ARRIVAL_NOTICE.filter.default();
                    },
                    dataSrc: function (json) {
                        $('#dataTable tbody').find('.loading-row').remove();
                        GLOBAL_FN.setStatusCounts(json.statusCounts);
                        ARRIVAL_NOTICE.list.cardSummary(json);
                        return json.data;
                    }
                },
                columnDefs: [{targets: '_all', orderable: false}],
                columns: [
                    {data: 'row_no', render: (d, t, row) => T.number(row)},
                    {data: 'customer', render: (d, t, row) => T.customer(row)},
                    {data: 'vessel_name', render: (d, t, row) => T.vessel(row)},
                    {data: 'pol', render: (d, t, row) => T.route(row)},
                    {data: 'eta_f', render: (d, t, row) => T.dates(row)},
                    {data: 'container_nos', render: (d, t, row) => T.cargo(row)},
                    GLOBAL_FN.dataTable.optionButton()
                ],
                language: {search: ''},
                initComplete: function () {
                    ARRIVAL_NOTICE.form.open();
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
            $('#cardDraftCount').text(c.DRAFT ?? 0);
            $('#cardNotifiedCount').text(c.NOTIFIED ?? 0);
            $('#cardUrgentCount').text((json.cards && json.cards.urgent) || 0);
        },
        templates: {
            badge: {
                1: '<span class="badge bg-warning-subtle text-warning-emphasis">' + trans('Draft') + '</span>',
                2: '<span class="badge bg-primary-subtle text-primary-emphasis">' + trans('Notified') + '</span>',
                3: '<span class="badge bg-success-subtle text-success-emphasis">' + trans('D.O Collected') + '</span>',
                4: '<span class="badge bg-danger-subtle text-danger-emphasis">' + trans('Cancelled') + '</span>',
            },
            number: (row) => `<div class="cell-primary text-primary an-no-link" style="cursor:pointer;">${row.row_no ?? ''}</div><div class="mt-1">${ARRIVAL_NOTICE.list.templates.badge[row.status] ?? ''}</div>`,
            customer: (row) => `<div class="cell-primary">${row.customer?.name_en ?? '—'}</div><div class="cell-secondary">${row.job?.row_no ? 'Job ' + row.job.row_no : (row.customer?.row_no ?? '')}</div>`,
            vessel: (row) => `<div class="cell-primary"><i class="bi bi-water me-1 text-muted"></i>${row.vessel_name || '—'}${row.voyage_no ? ' · ' + row.voyage_no : ''}</div><div class="cell-secondary">B/L ${row.bl_no ?? ''}</div>`,
            route: (row) => `<div class="cell-primary an-route">${row.pol ?? ''} <i class="bi bi-arrow-right mx-1 text-muted"></i> ${row.pod ?? ''}</div>`,
            dates: (row) => {
                const eta = `<div class="${row.is_urgent ? 'late' : 'cell-primary'}">${row.eta_f ?? '—'}${row.is_urgent ? ' <i class="bi bi-exclamation-triangle-fill"></i>' : ''}</div>`;
                const sub = row.notified_at_f ? `<div class="cell-secondary text-success"><i class="bi bi-megaphone me-1"></i>${row.notified_at_f}</div>`
                    : `<div class="cell-secondary">${row.is_urgent ? 'Not notified yet' : 'ETA'}</div>`;
                return eta + sub;
            },
            cargo: (row) => `<div class="cell-primary">${[row.containers_20 > 0 ? row.containers_20 + " × 20'" : '', row.containers_40 > 0 ? row.containers_40 + " × 40'" : ''].filter(Boolean).join(' · ') || '—'}</div>`
                + `<div class="cell-secondary text-truncate" style="max-width:220px;">${row.commodity ?? ''}</div>`,
        },
        extraActions(row) {
            ARRIVAL_NOTICE.list.actions.statusChange(row);
            ARRIVAL_NOTICE.list.actions.view(row);
            ARRIVAL_NOTICE.list.actions.delete(row);
        },
        actions: {
            statusChange(row) {
                $('#row_draft,#row_notified,#row_collected,#row_rejected').off().on('click', function () {
                    changeCustomerStatus(GLOBAL_FN.buildUrl(ARRIVAL_NOTICE.baseUrl + '/' + row.attr('data-id') + '/status/' + $(this).attr('data-value')), {
                        method: 'POST',
                        data: new FormData(),
                        callBack: 'datatable'
                    }, $(this).attr('data-value'));
                });
            },
            view(row) {
                $('#row_view').off().on('click', function () {
                    ARRIVAL_NOTICE.list.openDrawer(row.attr('data-id'), row.attr('data-name'));
                });
            },
            delete(row) {
                $('#row_delete').off().on('click', function () {
                    deleteRecord(GLOBAL_FN.buildUrl(ARRIVAL_NOTICE.baseUrl + '/' + row.attr('data-id')), function () { ARRIVAL_NOTICE.list.dataTable(); },
                        {name: $.trim(row.find('.an-no-link').text())});
                });
            },
        },
        openDrawer(id, name) {
            $('#drawerSubtitle').text(name || '');
            bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('moduleDrawer')).show();
            $('#moduleOverview').html('<div class="text-center py-5 text-muted"><div class="spinner-border spinner-border-sm me-2" role="status"></div> ' + trans('Loading...') + '</div>');
            $.get('/' + ARRIVAL_NOTICE.baseUrl + '/' + id + '/overview', function (data) {
                $('#moduleOverview').html(data);
            }).fail(function () {
                $('#moduleOverview').html('<div class="alert alert-danger m-3">' + trans('Failed to load arrival notice details.') + '</div>');
            });
        },
    },
    printPreview(id, format) {
        const iframe = document.getElementById('print-frame');
        iframe.onload = function () {
            try { iframe.contentWindow.focus(); iframe.contentWindow.print(); } catch (e) { console.error(e); }
        };
        iframe.src = '/' + ARRIVAL_NOTICE.baseUrl + '/' + id + '/print?format=' + (format || 'notice');
    },
    form: {
        load() {
            ARRIVAL_NOTICE.form.open();
            // The order number in the list opens the details.
            $(document).off('click.anno').on('click.anno', '#dataTable tbody .an-no-link', function (e) {
                e.preventDefault();
                const $row = $(this).closest('tr');
                ARRIVAL_NOTICE.list.openDrawer($row.attr('data-id'), $row.attr('data-name'));
            });
        },
        open() {
            $('#new').off().on('click', function () {
                webModal.openGlobalModal({
                    title: trans('New Arrival Notice'),
                    url: GLOBAL_FN.buildUrl(ARRIVAL_NOTICE.baseUrl + '/create'),
                    content: null,
                    size: 'xl',
                    scroll: true,
                });
            });
        },
        openCallback() {
            // Choosing a job fills the parties, vessel details, route and containers (only where the form is still empty).
            $('#an-job').off('change').on('change', function () {
                const o = $(this).find('option:selected');
                if (!o.val()) return;
                const customer = document.getElementById('customer');
                if (customer && customer.tomselect && o.attr('data-customer-id')) customer.tomselect.setValue(o.attr('data-customer-id'));
                [['an-mobile', 'data-phone'], ['an-shipper', 'data-shipper'], ['an-consignee', 'data-consignee'], ['an-address', 'data-address'],
                    ['an-pol', 'data-pol'], ['an-pod', 'data-pod'], ['an-eta', 'data-eta'], ['an-carrier', 'data-carrier'], ['an-voyage', 'data-voyage'],
                    ['an-bl', 'data-bl'], ['an-commodity', 'data-commodity'], ['an-packages', 'data-packages'], ['an-final', 'data-final'],
                    ['an-containers', 'data-containers'], ['an-c20', 'data-c20'], ['an-c40', 'data-c40']].forEach(([id, attr]) => {
                    const el = document.getElementById(id);
                    const v = o.attr(attr);
                    if (el && (!el.value || el.value === '0') && v && v !== '0') {
                        el.value = v;
                        if (el._flatpickr) el._flatpickr.setDate(v, false, 'd-m-Y');
                    }
                });
                const pod = document.getElementById('an-pod'), sub = document.getElementById('an-subject');
                if (sub && !sub.value.replace('Vessel Arriving At', '').trim() && pod && pod.value) sub.value = 'Vessel Arriving At ' + pod.value;
            });
        },
    },
};
