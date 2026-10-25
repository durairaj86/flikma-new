DOCUMENT_CENTER = {
    title: trans('Document'),
    baseUrl: 'operation/document',
    actionUrl: 'operation/document',
    load() {
        DOCUMENT_CENTER.form.load();
        datepicker();
        DOCUMENT_CENTER.filter.load();
        DOCUMENT_CENTER.list.load();
        FILTER.filteredColumn();
    },
    filter: {
        load() {
            $('#apply-filter').off().on('click', function () {
                DOCUMENT_CENTER.list.dataTable();
                FILTER.filteredColumn();
            });
            let timer;
            $('#customSearch').off().on('keyup', function (e) {
                clearTimeout(timer);
                timer = setTimeout(() => DOCUMENT_CENTER.list.dataTable(), e.key === 'Enter' ? 0 : 500);
            });
            $('#listTabs .status-btn').off().on('click', function () {
                DOCUMENT_CENTER.list.dataTable();
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
            DOCUMENT_CENTER.list.dataTable();
        },
        dataTable() {
            GLOBAL_FN.destroyDataTable();
            const T = DOCUMENT_CENTER.list.templates;
            let table = $('#dataTable').DataTable({
                processing: false,
                serverSide: true,
                autoWidth: false,
                lengthChange: false,
                pageLength: 25,
                dom: 'rt<"row mt-2"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7 d-flex justify-content-end"p>>',
                ajax: {
                    url: GLOBAL_FN.buildUrl('operation/document/data'),
                    type: 'POST',
                    data: function (d) {
                        d.tab = $('#listTabs').find('li button.active').attr('id');
                        d.filterData = DOCUMENT_CENTER.filter.default();
                    },
                    dataSrc: function (json) {
                        $('#dataTable tbody').find('.loading-row').remove();
                        const c = json.statusCounts || {};
                        ['all', 'valid', 'expiring', 'expired'].forEach(k => $('#' + k + 'Count').text(c[k] ?? 0));
                        $('#cardAll').text(c.all ?? 0);
                        $('#cardValid').text(c.valid ?? 0);
                        $('#cardSoon').text(c.expiring ?? 0);
                        $('#cardExpired').text(c.expired ?? 0);
                        return json.data;
                    }
                },
                columnDefs: [{targets: '_all', orderable: false}],
                columns: [
                    {data: 'title', render: (d, t, row) => T.doc(row)},
                    {data: 'owner_label', render: (d, t, row) => T.owner(row)},
                    {data: 'type_label', render: (d, t, row) => T.type(row)},
                    {data: 'expiry_f', render: (d, t, row) => T.expiry(row)},
                    {data: 'posted_f', render: (d, t, row) => `<div class="cell-primary">${row.posted_f ?? '—'}</div>`},
                    GLOBAL_FN.dataTable.optionButton()
                ],
                language: {search: ''},
                initComplete: function () {
                    DOCUMENT_CENTER.form.open();
                    webDataTable.actions.menu();
                }
            });
            $('#dataTable_filter').closest('div.row').remove();
            webDataTable.loader(table);
            webDataTable.search(table);
        },
        templates: {
            doc: (row) => `<div class="cell-primary doc-title">${row.title ?? ''}</div><div class="cell-secondary"><i class="bi bi-paperclip me-1"></i>${row.file_name ?? ''}</div>`,
            owner: (row) => `<div class="cell-primary">${row.owner_label?.type ?? ''}</div><div class="cell-secondary">${row.owner_label?.name ?? '—'}</div>`,
            type: (row) => `<span class="badge bg-light text-dark border">${row.type_label ?? '—'}</span>`,
            expiry: (row) => {
                if (!row.expiry_f) return '<div class="cell-secondary">' + trans('No expiry') + '</div>';
                const cls = row.expiry_state === 'expired' ? 'late' : (row.expiry_state === 'soon' ? 'text-warning fw-semibold' : 'cell-primary');
                const sub = row.expiry_state === 'expired' ? trans(':n days ago').replace(':n', Math.abs(row.days_left)) : trans(':n days left').replace(':n', row.days_left);
                return `<div class="${cls}">${row.expiry_f}</div><div class="cell-secondary">${sub}</div>`;
            },
        },
        extraActions(row) {
            const id = row.attr('data-id');
            $('#row_open').off().on('click', function () { window.open('/operation/document/' + id + '/file?inline=1', '_blank'); });
            $('#row_download').off().on('click', function () { window.location.href = '/operation/document/' + id + '/file'; });
            $('#row_delete').off().on('click', function () {
                deleteRecord(GLOBAL_FN.buildUrl('operation/document/' + id), function () { DOCUMENT_CENTER.list.dataTable(); }, {name: $.trim(row.find('.doc-title').text())});
            });
        },
    },
    form: {
        load() {
            DOCUMENT_CENTER.form.open();
        },
        open() {
            $('#new').off().on('click', function () {
                webModal.openGlobalModal({
                    title: trans('New Document'),
                    url: GLOBAL_FN.buildUrl(DOCUMENT_CENTER.baseUrl + '/create'),
                    content: null,
                    size: 'lg',
                    scroll: true,
                });
            });
        },
        openCallback() {
            // The record list follows what the document belongs to.
            $('#doc-owner-type').off('change').on('change', function () {
                const type = $(this).val();
                const el = document.getElementById('doc-owner-id');
                if (!el || !el.tomselect) return;
                el.tomselect.clear(true);
                el.tomselect.clearOptions();
                if (!type) return;
                $.getJSON('/operation/document/owners/' + type, function (rows) {
                    rows.forEach(r => el.tomselect.addOption({value: String(r.id), text: r.name}));
                    el.tomselect.refreshOptions(false);
                });
            });
        },
    },
};
