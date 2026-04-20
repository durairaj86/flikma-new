ITEM = {
    title: 'Item',
    baseUrl: 'inventory/items',
    actionUrl: 'inventory/items',
    load() {
        ITEM.form.open();
    },
    list: {
        load(activeTab) {
            ITEM.list.dataTable(activeTab);
        },
        dataTable(activeTab = null) {
            // ⚠️ CRUCIAL: Destroy the old instance before creating a new one
            GLOBAL_FN.destroyDataTable();

            activeTab = (activeTab && (typeof activeTab !== 'object')) ? activeTab : $("#listTabs").find('li button.active').attr('id');

            // ⚠️ CRUCIAL: Wrap the DataTables creation in a try...catch
            // If the DataTables library failed to load, this prevents the script from crashing.
            try {
                let table = $('#dataTable').DataTable({
                    processing: false,
                    serverSide: true,
                    autoWidth: false,
                    lengthChange: false,
                    pageLength: 25,
                    dom: 'rt<"row mt-2"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7 d-flex justify-content-end"p>>',
                    order: [[1, 'desc']],
                    ajax: {
                        url: GLOBAL_FN.buildUrl('inventory/items/data'),
                        type: 'POST',
                        data: {
                            'tab': activeTab
                        },
                        dataSrc: function (json) {
                            // Remove loader rows when data arrives
                            $('#dataTable tbody').find('.loading-row').remove();
                            // Set the count of all items
                            $('#allCount').text(json.data.length);
                            return json.data;
                        }
                    },
                    columnDefs: [
                        {targets: [0], searchable: false},
                        {targets: [0], orderable: false},
                    ],
                    columns: [
                        {data: 'DT_RowIndex', class: 'hide-tooltip fav-index'},
                        {
                            data: 'sku_code', render: function (data, type, row) {
                                return row.sku_code;
                            }
                        },
                        {
                            data: 'name_en', render: function (data, type, row) {
                                return row.name_en;
                            }
                        },
                        {
                            data: 'name_ar', render: function (data, type, row) {
                                return row.name_ar;
                            }
                        },
                        {
                            data: 'account_type', render: function (data, type, row) {
                                return row.account_type.charAt(0).toUpperCase() + row.account_type.slice(1);
                            }
                        },
                        {
                            data: 'cost_price', render: function (data, type, row) {
                                return row.cost_price ?? 'N/A';
                            }
                        },
                        {
                            data: 'selling_price', render: function (data, type, row) {
                                return row.selling_price ?? 'N/A';
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
                        ITEM.form.open();
                        webDataTable.actions.menu();
                    }
                });

                $('#customSearch').on('keyup', function () {
                    table.search(this.value).draw();
                });
                // Assuming webDataTable object is defined elsewhere
                webDataTable.loader(table);
                webDataTable.search(table);
            } catch (e) {
                console.error("DataTables Initialization Error:", e);
                // Gracefully degrade the table to a basic HTML table if the plugin fails.
            }
        },
        extraActions(row) {
            ITEM.list.actions.view(row);
            ITEM.list.actions.edit(row);
        },
        actions: {
            view(row) {
                $('#row_view').off().on('click', function () {
                    let itemId = row.attr('data-id');

                    // Open drawer
                    let drawer = new bootstrap.Offcanvas(document.getElementById('itemDrawer'));
                    drawer.show();

                    // Load Overview
                    $('#itemOverview').html('<p>Loading...</p>');
                    $.get('/inventory/items/' + itemId + '/view', function (data) {
                        $('#itemOverview').html(data);
                    });
                });
            },
            edit(row) {
                $('#row_edit').off().on('click', function () {
                    let itemId = row.attr('data-id');

                    webModal.openGlobalModal({
                        title: 'Edit Item',
                        url: GLOBAL_FN.buildUrl('inventory/items/' + itemId + '/edit'),
                        content: null,
                        size: 'md',
                        scroll: false,
                        minHeight: 'min-height:51vh;',
                    });
                });
            }
        }
    },
    form: {
        open() {
            $('#new').off().on('click', function () {
                webModal.openGlobalModal({
                    title: 'Add Item',
                    url: GLOBAL_FN.buildUrl('inventory/items/create'),
                    content: null,
                    size: 'md',
                    scroll: false,
                    minHeight: 'min-height:51vh;',
                });
            })
        }
    }
}
