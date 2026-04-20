LOGISTICS_SERVICE = {
    title: 'Services',
    baseUrl: 'masters/services',
    actionUrl: 'masters/services',
    load() {
        LOGISTICS_SERVICE.form.open();
    },
    list: {
        load() {
            LOGISTICS_SERVICE.list.dataTable();
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
                    url: '/masters/services/data',
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
                    {
                        data: 'service_name_en', render: function (data, type, row) {
                            return row.service_name_en;
                        }
                    },
                    {
                        data: 'service_name_ar', render: function (data, type, row) {
                            return row.service_name_ar;
                        }
                    },
                    {
                        data: 'category', render: function (data, type, row) {
                            return row.category;
                        }
                    },
                    {
                        data: 'code', render: function (data, type, row) {
                            return row.code;
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
                    LOGISTICS_SERVICE.form.open();
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
                    title: 'Add Service',
                    url: GLOBAL_FN.buildUrl('masters/services/create'),
                    content: null,
                    size: 'lg',
                    callBack: null
                });
            })
        },
    },
}
