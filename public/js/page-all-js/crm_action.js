CRM_ACTION = {
    baseUrl: 'masters/crm-action',
    actionUrl: 'masters/crm-action',
    load() {
        CRM_ACTION.form.open();
    },
    list: {
        load() {
            CRM_ACTION.list.dataTable();
        },
        dataTable() {
            GLOBAL_FN.destroyDataTable();
            let table = $('#dataTable').DataTable({
                processing: false,
                serverSide: true,
                autoWidth: false,
                lengthChange: false,
                pageLength: 25,
                dom: 'rt<"row mt-2"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7 d-flex justify-content-end"p>>',
                order: [[4, 'asc']],
                ajax: {
                    url: '/masters/crm-action/data',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    dataSrc: function (json) {
                        $('#dataTable tbody').find('.loading-row').remove();
                        return json.data;
                    }
                },
                columnDefs: [
                    {targets: [0, 5], searchable: false},
                    {targets: [0, 5], orderable: false},
                ],
                columns: [
                    {data: 'DT_RowIndex', class: 'text-center hide-tooltip fav-index'},
                    {
                        data: 'name', render: function (data, type, row) {
                            const e = $('<span>').text(data).html();
                            return e + (row.auto ? ' <span class="badge bg-light text-secondary border ms-1" title="' + trans('Also logged by the system') + '">' + trans('Auto') + '</span>' : '');
                        }
                    },
                    {
                        data: 'category_label', name: 'category', render: function (data, type, row) {
                            return '<span class="badge rounded-pill" style="' + row.category_style + '">' + $('<span>').text(data).html() + '</span>';
                        }
                    },
                    {data: 'description', class: 'small text-muted'},
                    {data: 'sort_order'},
                    {data: 'used'},
                    {data: 'is_active'},
                    GLOBAL_FN.dataTable.optionButton(true, true)
                ],
                language: {
                    search: ""
                },
                deferLoading: 0,

                initComplete: function () {
                    CRM_ACTION.form.open();
                    webDataTable.actions.menu();
                }
            });
            $('#customSearch').on('keyup input', window.debounceSearch(function () {
                table.search(this.value).draw();
            }));
            webDataTable.loader(table);
            webDataTable.search(table);
        },
        extraActions(row) {
            CRM_ACTION.list.actions.statusChange(row);
            CRM_ACTION.list.actions.delete(row);
        },
        actions: {
            delete(row) {
                $('#row_delete').off().on('click', function () {
                    deleteRecord(GLOBAL_FN.buildUrl('masters/crm-action/' + row.attr('data-id')), function () {
                        $('#dataTable').DataTable().ajax.reload(null, false);
                    }, {name: row.find('td').eq(1).text()});
                })
            },
            statusChange(row) {
                $('#row_active,#row_inactive').off().on('click', function () {
                    changeCustomerStatus(GLOBAL_FN.buildUrl('masters/crm-action/' + row.attr('data-id') + '/status/' + $(this).attr('data-value')), {
                        method: 'POST',
                        data: new FormData(),
                        callBack: 'datatable'
                    }, $(this).attr('data-value'));
                })
            },
        },
    },
    form: {
        open() {
            $('#new').off().on('click', function () {
                webModal.openGlobalModal({
                    title: trans('New Action'),
                    url: GLOBAL_FN.buildUrl('masters/crm-action/create'),
                    content: null,
                    size: 'md',
                    callBack: null
                });
            })
        },
    },
}
