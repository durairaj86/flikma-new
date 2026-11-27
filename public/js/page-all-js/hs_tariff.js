HS_TARIFF = {
    title: trans('HS Tariff'),
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
                    {
                        // On / Off switch, as the unit list
                        data: 'status', render: function (data, type, row) {
                            if (type !== 'display') return data;
                            return '<div class="form-check form-switch m-0 d-inline-flex align-items-center gap-2 hs-switch-wrap">'
                                + '<input class="form-check-input hs-switch" type="checkbox" role="switch" data-id="' + row.id + '"'
                                + (row.is_on ? ' checked' : '') + (row.can_switch ? '' : ' disabled') + ' aria-label="' + data + '">'
                                + '<span class="small ' + (row.is_on ? 'text-success' : 'text-secondary') + '">' + data + '</span></div>';
                        }
                    },
                    {
                        // Edit and Delete for the company's own tariffs (a shared one is not editable)
                        data: null, orderable: false, searchable: false, className: 'text-center text-nowrap',
                        render: function (data, type, row) {
                            if (!row.company_id) return '';
                            return '<a href="javascript:void(0)" class="btn btn-outline-secondary btn-sm rounded-circle hs-edit" data-id="' + row.id + '" title="' + trans('Edit') + '" aria-label="' + trans('Edit') + '"><i class="bi bi-pencil"></i></a>'
                                + (row.can_delete ? ' <a href="javascript:void(0)" class="btn btn-outline-danger btn-sm rounded-circle hs-delete" data-id="' + row.id + '" title="' + trans('Delete') + '" aria-label="' + trans('Delete') + '"><i class="bi bi-trash"></i></a>' : '');
                        }
                    }
                ],
                language: {
                    search: ""
                },
                deferLoading: 0,
                initComplete: function () {
                    HS_TARIFF.form.open();
                }
            });
            $('#customSearch').on('keyup input', window.debounceSearch(function () {
                table.search(this.value).draw();
            }));
            webDataTable.loader(table);
            webDataTable.search(table);
            HS_TARIFF.list.bind(table);
        },
        bind(table) {
            $('#dataTable').off('.hsTariff')
                .on('click.hsTariff', '.hs-switch-wrap', function (e) { e.stopPropagation(); })
                .on('click.hsTariff', '.hs-edit', function () {
                    webModal.openGlobalModal({
                        title: trans('Edit') + ' ' + trans('HS Tariff'),
                        url: GLOBAL_FN.buildUrl('masters/hs-tariff/' + $(this).data('id') + '/create'),
                        content: null,
                        size: 'mid',
                        callBack: null
                    });
                })
                .on('click.hsTariff', '.hs-delete', function () {
                    const id = $(this).data('id');
                    $.confirm({
                        title: trans('Confirm Delete'),
                        content: trans('Are you sure you want to delete this HS Tariff?'),
                        type: 'red',
                        buttons: {
                            cancel: function () {
                            },
                            delete: {
                                text: trans('Delete'),
                                btnClass: 'btn-red',
                                action: function () {
                                    $.ajax({
                                        url: GLOBAL_FN.buildUrl('masters/hs-tariff/' + id),
                                        type: 'DELETE',
                                        dataType: 'json',
                                        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                                        success: function () {
                                            table.ajax.reload(null, false);
                                        },
                                        error: function () {
                                            $.alert(trans('Failed to delete this HS Tariff.'));
                                        }
                                    });
                                }
                            }
                        }
                    });
                })
                .on('change.hsTariff', '.hs-switch', function () {
                    const box = this, on = box.checked ? 1 : 0;
                    box.disabled = true;
                    $.ajax({
                        url: GLOBAL_FN.buildUrl('masters/hs-tariff/' + $(box).data('id') + '/status/' + on),
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
                    title: trans('New HS Tariff'),
                    url: GLOBAL_FN.buildUrl('masters/hs-tariff/create'),
                    content: null,
                    size: 'mid',
                    callBack: null
                });
            })
        },
    },
}
