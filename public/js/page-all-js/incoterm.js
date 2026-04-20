INCOTERM = {
    baseUrl: 'masters/services',
    load() {

    },
    list: {
        load() {
            INCOTERM.list.dataTable();
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
                    url: '/masters/incoterm/data',
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
                        data: 'code', render: function (data, type, row) {
                            return row.code;
                        }
                    },
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
                    {
                        data: 'transport_mode', render: function (data, type, row) {
                            return row.transport_mode;
                        }
                    },
                ],
                language: {
                    search: "" // removes "Search:" label
                },
                deferLoading: 0, // don't load immediately

                initComplete: function () {
                    // Add custom class to default search input
                    INCOTERM.openModel();
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
    openModel() {
        $('#new').click(function () {
            webModal.openGlobalModal('Add Service', globalFunction.buildUrl('masters/services/create'), null, 'xl', true);
        })
    },
}
