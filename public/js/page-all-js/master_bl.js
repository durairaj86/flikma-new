MASTER_BL = {
    title: 'Master B/L',
    baseUrl: 'bl/master-bl',
    actionUrl: 'bl/master-bl',
    load() {
        MASTER_BL.form.load();
        datepicker();
        MASTER_BL.filter.load();
        MASTER_BL.list.load('all');
        FILTER.filteredColumn();
    },
    filter: {
        load() {
            $('#apply-filter').off().on('click', function () {
                MASTER_BL.list.dataTable();
                FILTER.filteredColumn();
            });
            let timer;
            $('#customSearch').off().on('keyup', function (e) {
                clearTimeout(timer);
                timer = setTimeout(() => MASTER_BL.list.dataTable(), e.key === 'Enter' ? 0 : 500);
            });
            $('#listTabs .status-btn').off().on('click', function () {
                MASTER_BL.list.dataTable();
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
            MASTER_BL.list.dataTable(activeTab);
        },
        dataTable() {
            GLOBAL_FN.destroyDataTable();
            const T = MASTER_BL.list.templates;
            let table = $('#dataTable').DataTable({
                processing: false,
                serverSide: true,
                autoWidth: false,
                lengthChange: false,
                pageLength: 25,
                dom: 'rt<"row mt-2"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7 d-flex justify-content-end"p>>',
                ajax: {
                    url: GLOBAL_FN.buildUrl('bl/master-bl/data'),
                    type: 'POST',
                    data: function (d) {
                        d.tab = $('#listTabs').find('li button.active').attr('id');
                        d.filterData = MASTER_BL.filter.default();
                    },
                    dataSrc: function (json) {
                        $('#dataTable tbody').find('.loading-row').remove();
                        GLOBAL_FN.setStatusCounts(json.statusCounts);
                        MASTER_BL.list.cardSummary(json);
                        return json.data;
                    }
                },
                columnDefs: [{targets: '_all', orderable: false}],
                columns: [
                    {data: 'row_no', render: (d, t, row) => T.number(row)},
                    {data: 'carrier', render: (d, t, row) => T.carrier(row)},
                    {data: 'pol', render: (d, t, row) => T.route(row)},
                    {data: 'etd_f', render: (d, t, row) => T.dates(row)},
                    {data: 'shipper', render: (d, t, row) => T.parties(row)},
                    {data: 'house_count', class: 'text-center', render: (d, t, row) => T.houses(row)},
                    GLOBAL_FN.dataTable.optionButton()
                ],
                language: {search: ''},
                initComplete: function () {
                    MASTER_BL.form.open();
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
            $('#cardIssuedCount').text(c.ISSUED ?? 0);
            $('#cardHouseCount').text((json.cards && json.cards.houses) || 0);
        },
        templates: {
            badge: {
                1: '<span class="badge bg-warning-subtle text-warning-emphasis">Draft</span>',
                2: '<span class="badge bg-success-subtle text-success-emphasis">Issued</span>',
                3: '<span class="badge bg-primary-subtle text-primary-emphasis">Closed</span>',
                4: '<span class="badge bg-danger-subtle text-danger-emphasis">Cancelled</span>',
            },
            modeIcon: {sea: 'bi-water', air: 'bi-airplane'},
            number: (row) => `<div class="cell-primary text-primary master-bl-no-link" style="cursor:pointer;">${row.row_no ?? ''}</div><div class="mt-1">${MASTER_BL.list.templates.badge[row.status] ?? ''}</div>`,
            carrier: (row) => `<div class="cell-primary"><i class="bi ${MASTER_BL.list.templates.modeIcon[row.shipment_mode] ?? 'bi-box'} me-1 text-muted"></i>${row.carrier?.name ?? '—'}</div>`
                + `<div class="cell-secondary">${[row.vessel_flight, row.voyage_no].filter(Boolean).join(' · ') || (row.mbl_no ?? '')}</div>`,
            route: (row) => `<div class="cell-primary">${row.pol ?? ''} <i class="bi bi-arrow-right mx-1 text-muted"></i> ${row.pod ?? ''}</div><div class="cell-secondary">${row.mbl_no ?? ''}</div>`,
            dates: (row) => `<div class="cell-primary">${row.etd_f ?? '—'}</div><div class="cell-secondary">${row.eta_f ? 'ETA ' + row.eta_f : ''}</div>`,
            parties: (row) => `<div class="cell-primary">${row.shipper || '—'}</div><div class="cell-secondary">${row.consignee || ''}</div>`,
            houses: (row) => `<span class="badge bg-light text-dark border">${row.house_count ?? 0}</span>`,
        },
        extraActions(row) {
            MASTER_BL.list.actions.statusChange(row);
            MASTER_BL.list.actions.view(row);
            MASTER_BL.list.actions.delete(row);
        },
        actions: {
            statusChange(row) {
                $('#row_issued,#row_closed,#row_draft,#row_rejected').off().on('click', function () {
                    changeCustomerStatus(GLOBAL_FN.buildUrl(MASTER_BL.baseUrl + '/' + row.attr('data-id') + '/status/' + $(this).attr('data-value')), {
                        method: 'POST',
                        data: new FormData(),
                        callBack: 'datatable'
                    }, $(this).attr('data-value'));
                });
            },
            view(row) {
                $('#row_view').off().on('click', function () {
                    MASTER_BL.list.openDrawer(row.attr('data-id'), row.attr('data-name'));
                });
            },
            delete(row) {
                $('#row_delete').off().on('click', function () {
                    deleteRecord(GLOBAL_FN.buildUrl(MASTER_BL.baseUrl + '/' + row.attr('data-id')), function () { MASTER_BL.list.dataTable(); },
                        {name: $.trim(row.find('.master-bl-no-link').text())});
                });
            },
        },
        openDrawer(id, name) {
            $('#drawerSubtitle').text(name || '');
            bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('moduleDrawer')).show();
            $('#moduleOverview').html('<div class="text-center py-5 text-muted"><div class="spinner-border spinner-border-sm me-2" role="status"></div> Loading...</div>');
            $.get('/' + MASTER_BL.baseUrl + '/' + id + '/overview', function (data) {
                $('#moduleOverview').html(data);
            }).fail(function () {
                $('#moduleOverview').html('<div class="alert alert-danger m-3">Failed to load master B/L details.</div>');
            });
        },
    },
    form: {
        load() {
            MASTER_BL.form.open();
            // The master number in the list opens the details.
            $(document).off('click.mblno').on('click.mblno', '#dataTable tbody .master-bl-no-link', function (e) {
                e.preventDefault();
                const $row = $(this).closest('tr');
                MASTER_BL.list.openDrawer($row.attr('data-id'), $row.attr('data-name'));
            });
        },
        open() {
            $('#new').off().on('click', function () {
                webModal.openGlobalModal({
                    title: 'New Master B/L',
                    url: GLOBAL_FN.buildUrl(MASTER_BL.baseUrl + '/create'),
                    content: null,
                    size: 'xl',
                    scroll: true,
                });
            });
        },
        openCallback() {
            // The house-bill list follows the shipment mode; bills already here stay ticked.
            const masterId = $('#mbl-master-id').val() || '';
            const loadHouses = (mode) => {
                const $box = $('#mbl-houses');
                $box.html('<div class="text-muted small p-2">Loading...</div>');
                $.get(GLOBAL_FN.buildUrl('bl/master-bl/house-bills/' + mode + (masterId ? '/' + masterId : '')), function (rows) {
                    if (!rows.length) {
                        $box.html('<div class="text-muted small p-2">No free house bills for this mode.</div>');
                        return;
                    }
                    $box.html(rows.map(r => `<label class="d-flex align-items-center gap-2 py-1 px-2 border-bottom">
                        <input type="checkbox" class="form-check-input mt-0" name="house_ids[]" value="${r.id}" ${r.linked ? 'checked' : ''}>
                        <span class="fw-semibold">${r.no}</span><span class="text-muted">${r.customer}${r.job ? ' · Job ' + r.job : ''}</span></label>`).join(''));
                });
            };
            loadHouses($('#mbl-mode').val() || 'sea');
            $('#mbl-mode').off('change').on('change', function () { loadHouses($(this).val()); });
        },
    },
};
