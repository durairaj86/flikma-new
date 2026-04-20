CURRENCY = {
    baseUrl: 'masters/services',
    load() {

    },
    list: {
        load() {
            CURRENCY.list.dataTable();
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
                    url: '/masters/currency/data',
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
                        data: 'country', render: function (data, type, row) {
                            return row.country;
                        }
                    },
                ],
                deferLoading: 0, // don't load immediately
            });
            $('#customSearch').on('keyup', function () {
                table.search(this.value).draw();
            });
            webDataTable.loader(table);
            webDataTable.search(table);
            //webDataTable.actions.menu();
        }
    },
}
