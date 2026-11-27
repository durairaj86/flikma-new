UNIT = {
    baseUrl: 'masters/unit',
    actionUrl: 'masters/unit',
    load() {
        UNIT.form.open();
    },
    list: {
        load() {
            UNIT.list.dataTable();
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
                    url: '/masters/unit/data',
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
                    {data: 'unit_name'},
                    {data: 'unit_symbol'},
                    {data: 'created_at'},
                    {
                        // On / Off: an Off unit is not offered in the unit dropdowns of new documents (a document that already uses it keeps it)
                        data: 'status', render: function (data, type, row) {
                            if (type !== 'display') return data;
                            return '<div class="form-check form-switch m-0 d-inline-flex align-items-center gap-2 unit-switch-wrap">'
                                + '<input class="form-check-input unit-switch" type="checkbox" role="switch" data-id="' + row.id + '"'
                                + (row.is_on ? ' checked' : '') + (row.can_switch ? '' : ' disabled')
                                + ' aria-label="' + trans('Show in unit dropdowns') + '" title="' + trans('Show in unit dropdowns') + '">'
                                + '<span class="small ' + (row.is_on ? 'text-success' : 'text-secondary') + '">' + data + '</span></div>';
                        }
                    },
                    {
                        // the On / Off switch already covers Active / In-Active, so the row only needs Edit (own units; a shared unit is not editable)
                        data: null, orderable: false, searchable: false, className: 'text-center',
                        render: function (data, type, row) {
                            return row.company_id
                                ? '<a href="javascript:void(0)" class="btn btn-outline-secondary btn-sm rounded-circle unit-edit" data-id="' + row.id + '" title="' + trans('Edit') + '" aria-label="' + trans('Edit') + '"><i class="bi bi-pencil"></i></a>'
                                : '';
                        }
                    }
                ],
                language: {
                    search: "" // removes "Search:" label
                },
                deferLoading: 0, // don't load immediately

                initComplete: function () {
                    UNIT.form.open();
                }
            });
            $('#customSearch').on('keyup input', window.debounceSearch(function () {
                table.search(this.value).draw();
            }));
            webDataTable.loader(table);
            webDataTable.search(table);
            // the switch works on its own: it does not open the row menu
            $('#dataTable').off('click.unitEdit').on('click.unitEdit', '.unit-edit', function () {
                webModal.openGlobalModal({
                    title: trans('Edit') + ' ' + ($('#dataTable').data('title') || trans('Unit')),
                    url: GLOBAL_FN.buildUrl('masters/unit/' + $(this).data('id') + '/create'),
                    content: null,
                    size: 'compact',
                    callBack: null,
                    minHeight: 'min-height:0;'
                });
            });
            $('#dataTable').off('click.unitSwitch').on('click.unitSwitch', '.unit-switch-wrap', function (e) { e.stopPropagation(); })
                .off('change.unitSwitch').on('change.unitSwitch', '.unit-switch', function () {
                    const box = this, on = box.checked ? 1 : 0;
                    box.disabled = true;
                    $.ajax({
                        url: GLOBAL_FN.buildUrl('masters/unit/' + $(box).data('id') + '/status/' + on),
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
            //webDataTable.actions.menu();
        },
        extraActions(row) {
            UNIT.list.actions.statusChange(row);
        },
        actions: {
            statusChange(row) {
                $('#row_active,#row_inactive').off().on('click', function () {
                    let fd = new FormData();
                    changeCustomerStatus(GLOBAL_FN.buildUrl('masters/unit/' + row.attr('data-id') + '/status/' + $(this).attr('data-value')), {
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
                    title: trans('Add Unit'),
                    url: GLOBAL_FN.buildUrl('masters/unit/create'),
                    content: null,
                    size: 'compact',
                    callBack: null,
                    minHeight: 'min-height:0;'
                });
            })
        },
    },
}
