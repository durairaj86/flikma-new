DEPARTMENT = {
    baseUrl: 'masters/department',
    actionUrl: 'masters/department',
    load() {
        DEPARTMENT.form.open();
    },
    list: {
        load() {
            DEPARTMENT.list.dataTable();
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
                order: [[1, 'desc']],
                ajax: {
                    url: '/masters/department/data',
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
                    {targets: [0], searchable: false},
                    {targets: [0], orderable: false},
                ],
                columns: [
                    {data: 'DT_RowIndex', class: 'text-center hide-tooltip fav-index'},
                    {data: 'name'},
                    {data: 'code'},
                    {data: 'users_count'},
                    {data: 'created_at'},
                    {data: 'is_active'},
                    GLOBAL_FN.dataTable.optionButton()
                ],
                language: {
                    search: ""
                },
                deferLoading: 0,

                initComplete: function () {
                    DEPARTMENT.form.open();
                    webDataTable.actions.menu();
                }
            });
            $('#customSearch').on('keyup', function () {
                table.search(this.value).draw();
            });
            webDataTable.loader(table);
            webDataTable.search(table);
        },
        extraActions(row) {
            DEPARTMENT.list.actions.statusChange(row);
        },
        actions: {
            statusChange(row) {
                $('#row_active,#row_inactive').off().on('click', function () {
                    let fd = new FormData();
                    changeCustomerStatus(GLOBAL_FN.buildUrl('masters/department/' + row.attr('data-id') + '/status/' + $(this).attr('data-value')), {
                        method: 'POST',
                        data: fd,
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
                    title: 'Add Department',
                    url: GLOBAL_FN.buildUrl('masters/department/create'),
                    content: null,
                    size: 'xl',
                    callBack: null
                });
            })
        },
    },
}
