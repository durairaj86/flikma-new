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
                    {data: 'commission', orderable: false, searchable: false},
                    {data: 'late_limit', orderable: false, searchable: false},
                    {data: 'target', class: 'text-end', orderable: false, searchable: false},
                    {data: 'user_id'},
                    {data: 'created_at'},
                    {
                        // On / Off switch, as the unit list
                        data: 'status', render: function (data, type, row) {
                            if (type !== 'display') return data;
                            return '<div class="form-check form-switch m-0 d-inline-flex align-items-center gap-2 sp-switch-wrap">'
                                + '<input class="form-check-input sp-switch" type="checkbox" role="switch" data-id="' + row.id + '"'
                                + (row.is_on ? ' checked' : '') + (row.can_switch ? '' : ' disabled') + ' aria-label="' + data + '">'
                                + '<span class="small ' + (row.is_on ? 'text-success' : 'text-secondary') + '">' + data + '</span></div>';
                        }
                    },
                    {
                        data: null, orderable: false, searchable: false, className: 'text-center text-nowrap',
                        render: function (data, type, row) {
                            return '<a href="javascript:void(0)" class="btn btn-outline-secondary btn-sm rounded-circle sp-edit" data-id="' + row.id + '" title="' + trans('Edit') + '" aria-label="' + trans('Edit') + '"><i class="bi bi-pencil"></i></a>'
                                + (row.can_delete ? ' <a href="javascript:void(0)" class="btn btn-outline-danger btn-sm rounded-circle sp-delete" data-id="' + row.id + '" title="' + trans('Delete') + '" aria-label="' + trans('Delete') + '"><i class="bi bi-trash"></i></a>' : '');
                        }
                    }
                ],
                language: {
                    search: "" // removes "Search:" label
                },
                deferLoading: 0, // don't load immediately

                initComplete: function () {
                    SALESPERSON.form.open();
                }
            });
            $('#customSearch').on('keyup input', window.debounceSearch(function () {
                table.search(this.value).draw();
            }));
            webDataTable.loader(table);
            webDataTable.search(table);
            SALESPERSON.list.bind(table);
        },
        bind(table) {
            $('#dataTable').off('.sp')
                .on('click.sp', '.sp-switch-wrap', function (e) { e.stopPropagation(); })
                .on('click.sp', '.sp-edit', function () {
                    webModal.openGlobalModal({
                        title: trans('Edit') + ' ' + trans('Salesperson'),
                        url: GLOBAL_FN.buildUrl('masters/salesperson/' + $(this).data('id') + '/create'),
                        content: null,
                        size: 'smd',
                        callBack: null,
                        minHeight: 'min-height:0;'
                    });
                })
                .on('click.sp', '.sp-delete', function () {
                    const $tr = $(this).closest('tr');
                    deleteRecord(GLOBAL_FN.buildUrl('masters/salesperson/' + $(this).data('id')), function () {
                        table.ajax.reload(null, false);
                    }, {name: $.trim($tr.find('td').eq(1).text())});
                })
                .on('change.sp', '.sp-switch', function () {
                    const box = this, on = box.checked ? 1 : 0;
                    box.disabled = true;
                    $.ajax({
                        url: GLOBAL_FN.buildUrl('masters/salesperson/' + $(box).data('id') + '/status/' + on),
                        method: 'POST',
                        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                        success: function (res) {
                            toastr.success(res.message || trans('Saved'));
                            table.ajax.reload(null, false);
                        },
                        error: function (xhr) {
                            box.checked = !on;
                            box.disabled = false;
                            toastr.error((xhr.responseJSON && xhr.responseJSON.message) || trans('Something went wrong.'));
                        }
                    });
                });
        }
    },
    form: {
        open() {
            $('#new').off().on('click', function () {
                webModal.openGlobalModal({
                    title: trans('Add Salesperson'),
                    url: GLOBAL_FN.buildUrl('masters/salesperson/create'),
                    content: null,
                    size: 'smd',
                    callBack: null,
                    minHeight: 'min-height:0;'
                });
            })
        },
    },
}
