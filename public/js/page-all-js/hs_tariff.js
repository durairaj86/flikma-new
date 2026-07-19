HS_TARIFF = {
    title: 'HS Tariff',
    baseUrl: 'masters/hs-tariff',
    actionUrl: 'masters/hs-tariff',
    load() {
        HS_TARIFF.form.open();
    },
    list: {
        load() {
            HS_TARIFF.list.dataTable();
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
                    url: '/masters/hs-tariff/data',
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
                    {data: 'hs_code', render: (data, type, row) => row.hs_code},
                    {data: 'description', render: (data, type, row) => row.description},
                    {data: 'duty_rate', render: (data, type, row) => row.duty_rate},
                    {data: 'unit', render: (data, type, row) => row.unit || '—'},
                    GLOBAL_FN.dataTable.optionButton()
                ],
                language: {
                    search: ""
                },
                deferLoading: 0,
                initComplete: function () {
                    HS_TARIFF.form.open();
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
            HS_TARIFF.list.actions.delete(row);
        },
        actions: {
            delete(row) {
                $('#row_delete').off().on('click', function () {
                    $.confirm({
                        title: 'Confirm Delete',
                        content: 'Are you sure you want to delete this HS Tariff?',
                        type: 'red',
                        buttons: {
                            cancel: function () {
                            },
                            delete: {
                                text: 'Delete',
                                btnClass: 'btn-red',
                                action: function () {
                                    $.ajax({
                                        url: GLOBAL_FN.buildUrl('masters/hs-tariff/' + row.attr('data-id')),
                                        type: 'DELETE',
                                        dataType: 'json',
                                        headers: {
                                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                        },
                                        success: function () {
                                            $('#dataTable').DataTable().ajax.reload(null, false);
                                        },
                                        error: function () {
                                            $.alert('Failed to delete this HS Tariff.');
                                        }
                                    });
                                }
                            }
                        }
                    });
                });
            }
        }
    },
    form: {
        open() {
            $('#new').off().on('click', function () {
                webModal.openGlobalModal({
                    title: 'New HS Tariff',
                    url: GLOBAL_FN.buildUrl('masters/hs-tariff/create'),
                    content: null,
                    size: 'md',
                    callBack: null
                });
            })
        },
    },
}
