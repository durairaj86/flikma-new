DESCRIPTION = {
    title: trans('Description'),
    baseUrl: 'masters/description',
    actionUrl: 'masters/description',
    load() {
        DESCRIPTION.form.open();
    },
    list: {
        load(activeTab) {
            DESCRIPTION.list.dataTable(activeTab);
        },
        dataTable(activeTab = null) {
            GLOBAL_FN.destroyDataTable();
            activeTab = (activeTab && (typeof activeTab !== 'object')) ? activeTab : $("#listTabs").find('li button.active').attr('id');
            let table = $('#dataTable').DataTable({
                processing: false,
                serverSide: true,
                autoWidth: false,
                lengthChange: false,
                pageLength: 25,
                dom: 'rt<"row mt-2"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7 d-flex justify-content-end"p>>',
                order: [[1, 'desc']],
                ajax: {
                    url: GLOBAL_FN.buildUrl('masters/description/data'),
                    type: 'POST',
                    data: {
                        'tab': activeTab
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
                    {data: 'DT_RowIndex', class: 'hide-tooltip fav-index'},
                    {
                        data: 'description', render: function (data, type, row) {
                            return row.description;
                        }
                    },
                    {
                        data: 'description_local', render: function (data, type, row) {
                            return row.description_local;
                        }
                    },
                    {
                        data: 'sale_account_id', render: function (data, type, row) {
                            return row.sale_account_id;
                        }
                    },
                    {
                        data: 'purchase_account_id', render: function (data, type, row) {
                            return row.purchase_account_id;
                        }
                    },
                    {
                        // On / Off switch, as the unit list
                        data: 'is_active', render: function (data, type, row) {
                            if (type !== 'display') return data;
                            const on = Number(row.is_active) === 1, label = on ? trans('Active') : trans('In-Active');
                            return '<div class="form-check form-switch m-0 d-inline-flex align-items-center gap-2 desc-switch-wrap">'
                                + '<input class="form-check-input desc-switch" type="checkbox" role="switch" data-id="' + row.id + '"'
                                + (on ? ' checked' : '') + (row.can_switch ? '' : ' disabled') + ' aria-label="' + label + '">'
                                + '<span class="small ' + (on ? 'text-success' : 'text-secondary') + '">' + label + '</span></div>';
                        }
                    },
                    {data: 'created_at', name: 'created_at'},
                    {
                        // Edit and Delete for the company's own descriptions (a shared one can only be switched on / off)
                        data: null, orderable: false, searchable: false, className: 'text-center text-nowrap',
                        render: function (data, type, row) {
                            if (!row.company_id) return '';
                            return '<a href="javascript:void(0)" class="btn btn-outline-secondary btn-sm rounded-circle desc-edit" data-id="' + row.id + '" title="' + trans('Edit') + '" aria-label="' + trans('Edit') + '"><i class="bi bi-pencil"></i></a>'
                                + (row.can_delete ? ' <a href="javascript:void(0)" class="btn btn-outline-danger btn-sm rounded-circle desc-delete" data-id="' + row.id + '" title="' + trans('Delete') + '" aria-label="' + trans('Delete') + '"><i class="bi bi-trash"></i></a>' : '');
                        }
                    }

                ],
                language: {
                    search: "" // removes "Search:" label
                },

                deferLoading: 0, // don't load immediately
                initComplete: function () {
                    DESCRIPTION.form.open();
                }
            });

            $('#customSearch').on('keyup input', window.debounceSearch(function () {
                table.search(this.value).draw();
            }));
            webDataTable.loader(table);
            webDataTable.search(table);
            DESCRIPTION.list.bind(table);
        },
        bind(table) {
            $('#dataTable').off('.desc')
                .on('click.desc', '.desc-switch-wrap', function (e) { e.stopPropagation(); })
                .on('click.desc', '.desc-edit', function () {
                    webModal.openGlobalModal({
                        title: trans('Edit') + ' ' + trans('Description'),
                        url: GLOBAL_FN.buildUrl('masters/description/' + $(this).data('id') + '/create'),
                        content: null,
                        size: 'md',
                        callBack: null
                    });
                })
                .on('click.desc', '.desc-delete', function () {
                    const $tr = $(this).closest('tr');
                    deleteRecord(GLOBAL_FN.buildUrl('masters/description/' + $(this).data('id')), function () {
                        table.ajax.reload(null, false);
                    }, {name: $.trim($tr.find('td').eq(1).text())});
                })
                .on('change.desc', '.desc-switch', function () {
                    const box = this, on = box.checked ? 1 : 0;
                    box.disabled = true;
                    $.ajax({
                        url: GLOBAL_FN.buildUrl('masters/description/' + $(box).data('id') + '/status/' + on),
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
                    title: trans('Add Description'),
                    url: GLOBAL_FN.buildUrl('masters/description/create'),
                    content: null,
                    size: 'md',
                    callBack: null,
                });
            })
        },
    },
}
