CRM_ACTION = {
    baseUrl: 'masters/crm-action',
    actionUrl: 'masters/crm-action',
    load() {
        CRM_ACTION.form.open();
    },
    list: {
        load() {
            CRM_ACTION.list.dataTable();
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
                order: [[4, 'asc']],
                ajax: {
                    url: '/masters/crm-action/data',
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
                    {targets: [0, 5], searchable: false},
                    {targets: [0, 5], orderable: false},
                ],
                columns: [
                    {data: 'DT_RowIndex', class: 'text-center hide-tooltip fav-index'},
                    {
                        data: 'name', render: function (data, type, row) {
                            const e = $('<span>').text(data).html();
                            return e + (row.auto ? ' <span class="badge bg-light text-secondary border ms-1" title="' + trans('Also logged by the system') + '">' + trans('Auto') + '</span>' : '');
                        }
                    },
                    {
                        data: 'category_label', name: 'category', render: function (data, type, row) {
                            return '<span class="badge rounded-pill" style="' + row.category_style + '">' + $('<span>').text(data).html() + '</span>';
                        }
                    },
                    {data: 'description', class: 'small text-muted'},
                    {data: 'sort_order'},
                    {data: 'used'},
                    {
                        // On / Off switch, as the unit list
                        data: 'is_active', render: function (data, type, row) {
                            if (type !== 'display') return data;
                            return '<div class="form-check form-switch m-0 d-inline-flex align-items-center gap-2 crm-action-switch-wrap">'
                                + '<input class="form-check-input crm-action-switch" type="checkbox" role="switch" data-id="' + row.id + '"'
                                + (row.is_on ? ' checked' : '') + (row.can_switch ? '' : ' disabled') + ' aria-label="' + data + '">'
                                + '<span class="small ' + (row.is_on ? 'text-success' : 'text-secondary') + '">' + data + '</span></div>';
                        }
                    },
                    {
                        // Edit; Delete only for an action the company added itself (a standard one is switched off instead)
                        data: null, orderable: false, searchable: false, className: 'text-center text-nowrap',
                        render: function (data, type, row) {
                            return '<a href="javascript:void(0)" class="btn btn-outline-secondary btn-sm rounded-circle crm-action-edit" data-id="' + row.id + '" title="' + trans('Edit') + '" aria-label="' + trans('Edit') + '"><i class="bi bi-pencil"></i></a>'
                                + (row.can_delete ? ' <a href="javascript:void(0)" class="btn btn-outline-danger btn-sm rounded-circle crm-action-delete" data-id="' + row.id + '" title="' + trans('Delete') + '" aria-label="' + trans('Delete') + '"><i class="bi bi-trash"></i></a>' : '');
                        }
                    }
                ],
                language: {
                    search: ""
                },
                deferLoading: 0,

                initComplete: function () {
                    CRM_ACTION.form.open();
                }
            });
            $('#customSearch').on('keyup input', window.debounceSearch(function () {
                table.search(this.value).draw();
            }));
            webDataTable.loader(table);
            webDataTable.search(table);
            $('#dataTable').off('.crmAction')
                .on('click.crmAction', '.crm-action-switch-wrap', function (e) { e.stopPropagation(); })
                .on('click.crmAction', '.crm-action-edit', function () {
                    webModal.openGlobalModal({
                        title: trans('Edit') + ' ' + trans('Action'),
                        url: GLOBAL_FN.buildUrl('masters/crm-action/' + $(this).data('id') + '/create'),
                        content: null,
                        size: 'md',
                        callBack: null
                    });
                })
                .on('click.crmAction', '.crm-action-delete', function () {
                    const id = $(this).data('id');
                    deleteRecord(GLOBAL_FN.buildUrl('masters/crm-action/' + id), function () {
                        table.ajax.reload(null, false);
                    }, {name: $(this).closest('tr').find('td').eq(1).text()});
                })
                .on('change.crmAction', '.crm-action-switch', function () {
                    const box = this, on = box.checked ? 1 : 0;
                    box.disabled = true;
                    $.ajax({
                        url: GLOBAL_FN.buildUrl('masters/crm-action/' + $(box).data('id') + '/status/' + on),
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
        },
    },
    form: {
        open() {
            $('#new').off().on('click', function () {
                webModal.openGlobalModal({
                    title: trans('New Action'),
                    url: GLOBAL_FN.buildUrl('masters/crm-action/create'),
                    content: null,
                    size: 'md',
                    callBack: null
                });
            })
        },
    },
}
