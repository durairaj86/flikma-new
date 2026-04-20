SALESPERSON = {
    baseUrl: 'masters/salesperson',
    actionUrl: 'masters/salesperson',
    load() {
        SALESPERSON.form.open();
    },
    list: {
        load() {
            SALESPERSON.list.dataTable();
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
                    url: '/masters/salesperson/data',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    dataSrc: function (json) {
                        // Remove loader rows when data arrives
                        $('#dataTable tbody').find('.loading-row').remove();
                        return json.data;
                    }
                },
                columnDefs: [
                    {targets: [0], searchable: false},
                    {targets: [0], orderable: false},
                    /*{targets: [1], class: 'hide', visible: false},*/
                ],
                columns: [
                    {data: 'DT_RowIndex', class: 'text-center hide-tooltip fav-index'},
                    {data: 'name'},
                    {data: 'user_id'},
                    {data: 'created_at'},
                    {data: 'status'},
                    GLOBAL_FN.dataTable.optionButton()
                ],
                language: {
                    search: "" // removes "Search:" label
                },
                deferLoading: 0, // don't load immediately

                initComplete: function () {
                    SALESPERSON.form.open();
                    webDataTable.actions.menu();
                }
            });
            $('#customSearch').on('keyup', function () {
                table.search(this.value).draw();
            });
            webDataTable.loader(table);
            webDataTable.search(table);
            //webDataTable.actions.menu();
        },
        extraActions(row) {
            SALESPERSON.list.actions.statusChange(row);
        },
        actions: {
            statusChange(row) {
                $('#row_active,#row_inactive').off().on('click', function () {
                    let fd = new FormData();
                    changeCustomerStatus(GLOBAL_FN.buildUrl('masters/salesperson/' + row.attr('data-id') + '/status/' + $(this).attr('data-value')), {
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
                    title: 'Add Salesperson',
                    url: GLOBAL_FN.buildUrl('masters/salesperson/create'),
                    content: null,
                    size: 'md',
                    callBack: null,
                    minHeight:'min-height:35v;'
                });
            })
        },
    },
}
