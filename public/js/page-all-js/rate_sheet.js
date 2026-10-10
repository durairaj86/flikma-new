RATE_SHEET = {
    title: 'Rate',
    baseUrl: 'sales/rate-sheet',
    actionUrl: 'sales/rate-sheet',
    load() {
        RATE_SHEET.form.load();
        datepicker();
        RATE_SHEET.filter.load();
        RATE_SHEET.list.load('all');
        FILTER.filteredColumn();
    },
    filter: {
        load() {
            $('#apply-filter').off().on('click', function () {
                RATE_SHEET.list.dataTable();
                FILTER.filteredColumn();
            });
            let timer;
            $('#customSearch').off().on('keyup', function (e) {
                clearTimeout(timer);
                timer = setTimeout(() => RATE_SHEET.list.dataTable(), e.key === 'Enter' ? 0 : 500);
            });
            $('#listTabs .status-btn').off().on('click', function () {
                RATE_SHEET.list.dataTable();
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
            RATE_SHEET.list.dataTable(activeTab);
        },
        dataTable() {
            GLOBAL_FN.destroyDataTable();
            const T = RATE_SHEET.list.templates;
            let table = $('#dataTable').DataTable({
                processing: false,
                serverSide: true,
                autoWidth: false,
                lengthChange: false,
                pageLength: 25,
                dom: 'rt<"row mt-2"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7 d-flex justify-content-end"p>>',
                ajax: {
                    url: GLOBAL_FN.buildUrl('sales/rate-sheet/data'),
                    type: 'POST',
                    data: function (d) {
                        d.tab = $('#listTabs').find('li button.active').attr('id');
                        d.filterData = RATE_SHEET.filter.default();
                    },
                    dataSrc: function (json) {
                        $('#dataTable tbody').find('.loading-row').remove();
                        const sc = json.statusCounts || {};
                        ['all', 'active', 'expiring', 'expired', 'inactive'].forEach(k => $('[id="' + k + 'Count"]').text(sc[k] ?? 0));
                        RATE_SHEET.list.cardSummary(json);
                        return json.data;
                    }
                },
                columnDefs: [{targets: '_all', orderable: false}],
                columns: [
                    {data: 'row_no', render: (d, t, row) => T.number(row)},
                    {data: 'origin', render: (d, t, row) => T.lane(row)},
                    {data: 'container_type', render: (d, t, row) => T.equipment(row)},
                    {data: 'buy_f', class: 'text-end', render: (d, t, row) => T.money(row, row.buy_f)},
                    {data: 'sell_f', class: 'text-end', render: (d, t, row) => T.money(row, row.sell_f)},
                    {data: 'margin_f', class: 'text-end', render: (d, t, row) => T.margin(row)},
                    {data: 'valid_to_f', render: (d, t, row) => T.validity(row)},
                    GLOBAL_FN.dataTable.optionButton()
                ],
                language: {search: ''},
                initComplete: function () {
                    RATE_SHEET.form.open();
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
            $('#cardActiveCount').text(c.active ?? 0);
            $('#cardExpiringCount').text(c.expiring ?? 0);
            $('#cardMargin').text(((json.cards && json.cards.avg_margin) || 0) + '%');
        },
        templates: {
            badge: {
                active: '<span class="badge bg-success-subtle text-success-emphasis">Active</span>',
                expiring: '<span class="badge bg-warning-subtle text-warning-emphasis">Expiring</span>',
                expired: '<span class="badge bg-danger-subtle text-danger-emphasis">Expired</span>',
                upcoming: '<span class="badge bg-info-subtle text-info-emphasis">Upcoming</span>',
                inactive: '<span class="badge bg-secondary-subtle text-secondary-emphasis">Inactive</span>',
            },
            modeIcon: {sea: 'bi-water', air: 'bi-airplane', road: 'bi-truck'},
            number: (row) => `<div class="cell-primary text-primary rt-no-link" style="cursor:pointer;">${row.row_no ?? ''}</div><div class="mt-1">${RATE_SHEET.list.templates.badge[row.validity] ?? ''}</div>`,
            lane: (row) => `<div class="cell-primary rt-route"><i class="bi ${RATE_SHEET.list.templates.modeIcon[row.shipment_mode] ?? 'bi-box'} me-1 text-muted"></i>${row.origin} <i class="bi bi-arrow-right mx-1 text-muted"></i> ${row.destination}</div>`
                + `<div class="cell-secondary">${row.carrier?.name ?? 'Any carrier'}${row.transit_days ? ' · ' + row.transit_days + ' days' : ''}</div>`,
            equipment: (row) => `<div class="cell-primary">${row.container_type || '—'}</div><div class="cell-secondary">${row.basis_label ?? ''}</div>`,
            money: (row, v) => `<div class="cell-primary">${v}</div><div class="cell-secondary">${row.currency}</div>`,
            margin: (row) => `<div class="cell-primary ${row.margin_pct < 0 ? 'text-danger' : 'text-success'}">${row.margin_f}</div><div class="cell-secondary">${row.margin_pct}%</div>`,
            validity: (row) => {
                const left = row.days_left;
                const cls = row.validity === 'expired' ? 'text-danger' : (row.validity === 'expiring' ? 'text-warning' : '');
                return `<div class="cell-primary ${cls}">${row.valid_to_f ?? 'No end date'}</div>`
                    + `<div class="cell-secondary">${row.valid_from_f ? 'from ' + row.valid_from_f : ''}${left !== null && left >= 0 ? ' · ' + left + ' days left' : ''}</div>`;
            },
        },
        extraActions(row) {
            $('#row_view').off().on('click', function () {
                RATE_SHEET.list.openDrawer(row.attr('data-id'), row.attr('data-name'));
            });
            $('#row_active,#row_inactive').off().on('click', function () {
                $.post(GLOBAL_FN.buildUrl(RATE_SHEET.baseUrl + '/' + row.attr('data-id') + '/status/' + $(this).attr('data-value')), {_token: $('meta[name="csrf-token"]').attr('content')})
                    .done(res => { toastr.success(res.message); RATE_SHEET.list.dataTable(); })
                    .fail(xhr => toastr.error((xhr.responseJSON && xhr.responseJSON.message) || trans('Server error')));
            });
            $('#row_duplicate').off().on('click', function () {
                $.post(GLOBAL_FN.buildUrl(RATE_SHEET.baseUrl + '/' + row.attr('data-id') + '/duplicate'), {_token: $('meta[name="csrf-token"]').attr('content')})
                    .done(res => { toastr.success(res.message); RATE_SHEET.list.dataTable(); })
                    .fail(xhr => toastr.error((xhr.responseJSON && xhr.responseJSON.message) || trans('Server error')));
            });
            $('#row_delete').off().on('click', function () {
                deleteRecord(GLOBAL_FN.buildUrl(RATE_SHEET.baseUrl + '/' + row.attr('data-id')), function () { RATE_SHEET.list.dataTable(); },
                    {name: $.trim(row.find('.rt-no-link').text())});
            });
        },
        openDrawer(id, name) {
            $('#drawerSubtitle').text(name || '');
            bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('moduleDrawer')).show();
            $('#moduleOverview').html('<div class="text-center py-5 text-muted"><div class="spinner-border spinner-border-sm me-2" role="status"></div> Loading...</div>');
            $.get('/' + RATE_SHEET.baseUrl + '/' + id + '/overview', function (data) {
                $('#moduleOverview').html(data);
            }).fail(function () {
                $('#moduleOverview').html('<div class="alert alert-danger m-3">Failed to load rate details.</div>');
            });
        },
    },
    form: {
        load() {
            RATE_SHEET.form.open();
            // The rate number in the list opens the details.
            $(document).off('click.rtno').on('click.rtno', '#dataTable tbody .rt-no-link', function (e) {
                e.preventDefault();
                const $row = $(this).closest('tr');
                RATE_SHEET.list.openDrawer($row.attr('data-id'), $row.attr('data-name'));
            });
        },
        open() {
            $('#new').off().on('click', function () {
                webModal.openGlobalModal({
                    title: 'New Rate',
                    url: GLOBAL_FN.buildUrl(RATE_SHEET.baseUrl + '/create'),
                    content: null,
                    size: 'xl',
                    scroll: true,
                });
            });
        },
        // Origin / Destination search like the Job form's POL / POD: sea ports, airports, or any typed place for road.
        ports() {
            const mode = $('#rt-mode').val() || 'sea';
            ['#rt-origin', '#rt-destination'].forEach(function (sel) {
                const el = document.querySelector(sel);
                if (!el) return;
                const keep = el.value;
                if (el.tomselect) el.tomselect.destroy();
                if (mode === 'road') {
                    new TomSelect(el, {create: true, persist: false, maxItems: 1, allowEmptyOption: true, placeholder: el.dataset.placeholder || ''});
                } else {
                    initTomSelectSearch(sel, mode, 50, true);
                }
                if (keep && el.tomselect && !el.tomselect.options[keep]) { el.tomselect.addOption({[el.tomselect.settings.valueField]: keep, name: keep, text: keep, code: ''}); }
                if (keep && el.tomselect) el.tomselect.setValue(keep, true);
            });
        },
        openCallback() {
            // after the modal's own select setup has run
            setTimeout(RATE_SHEET.form.ports, 250);
            $('#rt-mode').off('change.rtports').on('change.rtports', function () {
                ['#rt-origin', '#rt-destination'].forEach(function (sel) { const el = document.querySelector(sel); if (el && el.tomselect) el.tomselect.clear(true); });
                RATE_SHEET.form.ports();
            });
            // Shows the margin while you type the buy and sell rates.
            const hint = () => {
                const buy = parseFloat(String($('#rt-buy').val()).replace(/,/g, '')) || 0;
                const sell = parseFloat(String($('#rt-sell').val()).replace(/,/g, '')) || 0;
                if (!sell && !buy) { $('#rt-margin-hint').text(''); return; }
                const m = sell - buy;
                $('#rt-margin-hint').html('Margin: <strong class="' + (m < 0 ? 'text-danger' : 'text-success') + '">' + m.toFixed(2) + '</strong>' + (sell ? ' (' + (m / sell * 100).toFixed(1) + '%)' : ''));
            };
            $('#rt-buy,#rt-sell').off('input.rt').on('input.rt', hint);
            hint();
        },
    },
};
