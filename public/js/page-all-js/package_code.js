PACKAGE_CODE = {
    title: 'Package Code',
    baseUrl: 'masters/package/code',
    actionUrl: 'masters/package/codes',
    load() {
        PACKAGE_CODE.form.open();
    },
    list: {
        load() {
            PACKAGE_CODE.list.dataTable();
        },
        dataTable() {
            let table = $('#dataTable').DataTable({
                processing: false,
                serverSide: true,
                autoWidth: false,
                lengthChange: false,
                pageLength: 25,
                dom: 'rt<"row mt-2"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7 d-flex justify-content-end"p>>',
                order: [[1, 'desc']],
                ajax: {
                    url: '/masters/package/code/data',
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
                    /*{
                        data: 'code', render: function (data, type, row) {
                            return row.code;
                        }
                    },*/
                    {
                        data: 'name', render: function (data, type, row) {
                            return row.name;
                        }
                    },
                    {
                        data: 'description', render: function (data, type, row) {
                            return row.description;
                        }
                    },
                    // Actions column
                    GLOBAL_FN.dataTable.optionButton()
                ],
                language: {
                    search: "" // removes "Search:" label
                },
                deferLoading: 0, // don't load immediately

                initComplete: function () {
                    // Add custom class to default search input
                    PACKAGE_CODE.form.open();
                    webDataTable.actions.menu();
                }
            });
            $('#customSearch').on('keyup', function () {
                table.search(this.value).draw();
            });
            webDataTable.loader(table);
            webDataTable.search(table);
            //webDataTable.actions.menu();
        }
    },
    form: {
        open() {
            $('#new').off().on('click', function () {
                webModal.openGlobalModal({
                    title: 'Add Package Code',
                    url: GLOBAL_FN.buildUrl('masters/package/code/create'),
                    content: null,
                    size: 'lg',
                    callBack: null
                });
            })
        },
    },
}
