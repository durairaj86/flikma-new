LOST_REASON = {
    baseUrl: 'masters/lost-reason',
    actionUrl: 'masters/lost-reason',
    load() {
        LOST_REASON.form.open();
    },
    list: {
        load() {
            LOST_REASON.list.dataTable();
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
                order: [[2, 'asc']],
                ajax: {
                    url: '/masters/lost-reason/data',
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
                    {targets: [0, 3], searchable: false},
                    {targets: [0, 3], orderable: false},
                ],
                columns: [
                    {data: 'DT_RowIndex', class: 'text-center hide-tooltip fav-index'},
                    {data: 'name'},
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
                    LOST_REASON.form.open();
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
            LOST_REASON.list.actions.statusChange(row);
            LOST_REASON.list.actions.delete(row);
        },
        actions: {
            delete(row) {
                $('#row_delete').off().on('click', function () {
                    deleteRecord(GLOBAL_FN.buildUrl('masters/lost-reason/' + row.attr('data-id')), function () {
                        $('#dataTable').DataTable().ajax.reload(null, false);
                    }, {name: row.find('td').eq(1).text()});
                })
            },
            statusChange(row) {
                $('#row_active,#row_inactive').off().on('click', function () {
                    changeCustomerStatus(GLOBAL_FN.buildUrl('masters/lost-reason/' + row.attr('data-id') + '/status/' + $(this).attr('data-value')), {
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
                    title: trans('Add Lost Reason'),
                    url: GLOBAL_FN.buildUrl('masters/lost-reason/create'),
                    content: null,
                    size: 'md',
                    callBack: null
                });
            })
        },
    },
}
