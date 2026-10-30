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
                        data: 'is_active', render: function (data, type, row) {
                            return Number(row.is_active) === 1
                                ? '<span class="badge bg-success-subtle text-success-emphasis">' + trans('Active') + '</span>'
                                : '<span class="badge bg-secondary-subtle text-secondary-emphasis">' + trans('In-Active') + '</span>';
                        }
                    },
                    {data: 'created_at', name: 'created_at'},
                    // Actions column
                    GLOBAL_FN.dataTable.optionButton()

                ],
                language: {
                    search: "" // removes "Search:" label
                },

                deferLoading: 0, // don't load immediately
                initComplete: function () {
                    DESCRIPTION.form.open();
                    webDataTable.actions.menu();
                }
            });

            $('#customSearch').on('keyup input', window.debounceSearch(function () {
                table.search(this.value).draw();
            }));
            webDataTable.loader(table);
            webDataTable.search(table);
            //webDataTable.actions.menu();
        },
        extraActions(row) {
            DESCRIPTION.list.actions.statusChange(row);
        },
        actions: {
            // Active / In-Active instead of Delete: a description may be on documents already
            statusChange(row) {
                $('#row_active,#row_inactive').off().on('click', function () {
                    changeCustomerStatus(GLOBAL_FN.buildUrl('masters/description/' + row.attr('data-id') + '/status/' + $(this).attr('data-value')), {
                        method: 'POST',
                        data: new FormData(),
                        callBack: 'datatable'
                    }, $(this).attr('data-value'));
                });
            }
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
