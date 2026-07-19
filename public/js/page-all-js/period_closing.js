PERIOD_CLOSING = {
    title: 'Period Closing',
    baseUrl: 'masters/period-closing',
    actionUrl: 'masters/period-closing',
    load() {
        PERIOD_CLOSING.form.open();
    },
    list: {
        load() {
            PERIOD_CLOSING.list.dataTable();
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
                    url: '/masters/period-closing/data',
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
                    {data: 'year', render: (data, type, row) => row.year},
                    {data: 'closing_date', render: (data, type, row) => row.closing_date},
                    {data: 'notes', render: (data, type, row) => row.notes || '—'},
                    {data: 'is_closed', render: (data, type, row) => row.is_closed},
                    GLOBAL_FN.dataTable.optionButton()
                ],
                language: {
                    search: ""
                },
                deferLoading: 0,
                initComplete: function () {
                    PERIOD_CLOSING.form.open();
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
            PERIOD_CLOSING.list.actions.delete(row);
            PERIOD_CLOSING.list.actions.close(row);
            PERIOD_CLOSING.list.actions.reopen(row);
        },
        actions: {
            delete(row) {
                $('#row_delete').off().on('click', function () {
                    $.confirm({
                        title: 'Confirm Delete',
                        content: 'Are you sure you want to delete this period?',
                        type: 'red',
                        buttons: {
                            cancel: function () {
                            },
                            delete: {
                                text: 'Delete',
                                btnClass: 'btn-red',
                                action: function () {
                                    $.ajax({
                                        url: GLOBAL_FN.buildUrl('masters/period-closing/' + row.attr('data-id')),
                                        type: 'DELETE',
                                        dataType: 'json',
                                        headers: {
                                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                        },
                                        success: function () {
                                            $('#dataTable').DataTable().ajax.reload(null, false);
                                        },
                                        error: function (xhr) {
                                            $.alert(xhr.responseJSON?.message || 'Failed to delete this period.');
                                        }
                                    });
                                }
                            }
                        }
                    });
                });
            },
            close(row) {
                $('#row_close').off().on('click', function () {
                    $.confirm({
                        title: 'Close Period',
                        content: 'Closing this period LOCKS every transaction dated on or before its closing date. This cannot be undone by regular users. Continue?',
                        type: 'orange',
                        buttons: {
                            cancel: function () {
                            },
                            close: {
                                text: 'Close Period',
                                btnClass: 'btn-warning',
                                action: function () {
                                    $.ajax({
                                        url: GLOBAL_FN.buildUrl('masters/period-closing/' + row.attr('data-id') + '/close'),
                                        type: 'POST',
                                        dataType: 'json',
                                        headers: {
                                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                        },
                                        success: function (res) {
                                            toastr.success(res.message);
                                            $('#dataTable').DataTable().ajax.reload(null, false);
                                        },
                                        error: function (xhr) {
                                            $.alert(xhr.responseJSON?.message || 'Failed to close this period.');
                                        }
                                    });
                                }
                            }
                        }
                    });
                });
            },
            reopen(row) {
                $('#row_reopen').off().on('click', function () {
                    $.confirm({
                        title: 'Reopen Period',
                        content: 'Reopening this period unlocks every transaction dated on or before its closing date for editing again. Continue?',
                        type: 'orange',
                        buttons: {
                            cancel: function () {
                            },
                            reopen: {
                                text: 'Reopen Period',
                                btnClass: 'btn-warning',
                                action: function () {
                                    $.ajax({
                                        url: GLOBAL_FN.buildUrl('masters/period-closing/' + row.attr('data-id') + '/reopen'),
                                        type: 'POST',
                                        dataType: 'json',
                                        headers: {
                                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                        },
                                        success: function (res) {
                                            toastr.success(res.message);
                                            $('#dataTable').DataTable().ajax.reload(null, false);
                                        },
                                        error: function (xhr) {
                                            $.alert(xhr.responseJSON?.message || 'Failed to reopen this period.');
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
                    title: 'New Period',
                    url: GLOBAL_FN.buildUrl('masters/period-closing/create'),
                    content: null,
                    size: 'md',
                    callBack: null
                });
            })
        },
    },
}
