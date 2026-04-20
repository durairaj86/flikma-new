SEAWAY_BILL = {
    title: 'Seaway Bill',
    baseUrl: 'bl/seaway',
    actionUrl: 'bl/seaway',
    load() {
        SEAWAY_BILL.form.load();
        SEAWAY_BILL.filter.load();
        datepicker();
    },
    filter: {
        load: function () {
            SEAWAY_BILL.filter.filterBox();
            SEAWAY_BILL.filter.searchBox();
        },
        filterBox: function () {
            $('#apply-filter').off().on({
                click: function () {
                    SEAWAY_BILL.list.dataTable();
                    FILTER.filteredColumn();
                }
            });
        },
        searchBox: function () {
            let searchTimeout;
            $('#customSearch').off().on({
                keyup: function (e) {
                    // If Enter key is pressed, search immediately
                    if (e.key === 'Enter') {
                        clearTimeout(searchTimeout);
                        SEAWAY_BILL.list.dataTable();
                        return;
                    }

                    // Otherwise, debounce the search to avoid too many requests
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(function () {
                        SEAWAY_BILL.list.dataTable();
                    }, 500); // Wait 500ms after user stops typing
                }
            });
        },
        default: function (status = 0) {
            let data = {}, tab = status ?? $("#listTabs").find('li button.active').attr('id');
            let params = new URLSearchParams($('#list-filter').serialize());

            params.forEach((value, key) => {
                if (data[key]) {
                    data[key] = [].concat(data[key], value);
                } else {
                    data[key] = value;
                }
            });
            data['tab'] = tab;
            data['limit'] = 25;
            data['customSearch'] = $('#customSearch').val();
            return data;
        }
    },
    printPreview(printId) {
        const iframe = document.getElementById('print-frame');

        iframe.onload = function () {
            try {
                iframe.contentWindow.focus();
                iframe.contentWindow.print();
                const doc = iframe.contentDocument || iframe.contentWindow.document;
                iframe.style.height = doc.body.scrollHeight + 'px';
            } catch (e) {
                console.error('Cannot print iframe content. Cross-origin issue?', e);
            }
        };
        iframe.src = '/' + SEAWAY_BILL.baseUrl + '/' + printId + '/print';
    },
    list: {
        load(activeTab = null) {
            SEAWAY_BILL.list.dataTable(activeTab);
        },
        dataTable(activeTab = null) {
            GLOBAL_FN.destroyDataTable();
            activeTab = (activeTab && (typeof activeTab !== 'object')) ? activeTab : $("#listTabs").find('li button.active').attr('id');
            let table = $('#dataTable').DataTable({
                processing: false,
                serverSide: true,
                orderable: false,
                autoWidth: false,
                lengthChange: false,
                pageLength: 25,
                dom: 'rt<"row mt-2"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7 d-flex justify-content-end"p>>',
                ajax: {
                    url: GLOBAL_FN.buildUrl('bl/seaway/data'),
                    type: 'POST',
                    data: function (d) {
                        d.tab = activeTab;
                        d.filterData = SEAWAY_BILL.filter.default();
                    },
                    dataSrc: function (json) {
                        $('#dataTable tbody').find('.loading-row').remove();
                        return json.data;
                    }
                },
                columnDefs: [
                    {targets: [0], searchable: false},
                    {targets: [0, 1, 2, 3, 4, 5, 6, 7, 8], orderable: false},
                    {targets: [0, 1, 2, 3, 4, 5, 6, 7, 8], class: 'px-3 py-2 text-nowrap'},
                ],
                columns: [
                    {
                        data: 'row_no', render: function (data, type, row) {
                            let statusClass = 'text-muted';
                            let statusBadge = '';

                            if (row.status === 'delivered') {
                                statusClass = 'text-success';
                                statusBadge = '<span class="badge bg-success-subtle text-success me-2 text-xsmall">Delivered</span>';
                            } else if (row.status === 'in_transit') {
                                statusClass = 'text-primary';
                                statusBadge = '<span class="badge bg-primary-subtle text-primary me-2 text-xsmall">In Transit</span>';
                            } else if (row.status === 'pending') {
                                statusClass = 'text-warning';
                                statusBadge = '<span class="badge bg-warning-subtle text-warning me-2 text-xsmall">Pending</span>';
                            } else if (row.status === 'cancelled') {
                                statusClass = 'text-danger';
                                statusBadge = '<span class="badge bg-danger-subtle text-danger me-2 text-xsmall">Cancelled</span>';
                            }

                            return '<div class="' + statusClass + ' pb-1">' + row.row_no + '</div>' + statusBadge;
                        }
                    },
                    {
                        data: 'customer_name', render: function (data, type, row) {
                            return '<div>' + row.customer_name + '</div>';
                        }
                    },
                    {
                        data: 'job_no', render: function (data, type, row) {
                            return '<div class="fw-bold">' + row.job_no + '</div>';
                        }
                    },
                    {
                        data: 'origin_destination', render: function (data, type, row) {
                            let origin_destination = '';
                            if (row.origin_port) {
                                origin_destination = '<span class="text-secondary small me-1">Origin:</span><span>' + row.origin_port + '</span>';
                            }
                            if (row.destination_port) {
                                if (origin_destination) {
                                    origin_destination += '<br>';
                                }
                                return origin_destination + '<span class="text-secondary small me-1">Destination:</span><span>' + row.destination_port + '</span>';
                            }
                            return origin_destination;
                        }
                    },
                    {
                        data: 'delivery_address', render: function (data, type, row) {
                            return '<div>' + row.delivery_address + '</div>';
                        }
                    },
                    {data: 'delivery_date'},
                    {
                        data: 'status', render: function (data, type, row) {
                            let statusClass = '';
                            let statusText = '';

                            if (row.status === 'delivered') {
                                statusClass = 'status-delivered';
                                statusText = 'Delivered';
                            } else if (row.status === 'in_transit') {
                                statusClass = 'status-in-transit';
                                statusText = 'In Transit';
                            } else if (row.status === 'pending') {
                                statusClass = 'status-pending';
                                statusText = 'Pending';
                            } else if (row.status === 'cancelled') {
                                statusClass = 'status-cancelled';
                                statusText = 'Cancelled';
                            }

                            return '<span class="status-pill ' + statusClass + '">' + statusText + '</span>';
                        }
                    },
                    {data: 'seaway_bill_date'},
                    GLOBAL_FN.dataTable.optionButton()
                ],
                language: {
                    search: ""
                },
                deferLoading: 0,

                initComplete: function () {
                    SEAWAY_BILL.form.open();
                    webDataTable.actions.menu();
                }
            });
            $('#customSearch').on('keyup', function () {
                table.search(this.value).draw();
            });
            $('#dataTable_filter').closest('div.row').remove();
            webDataTable.loader(table);
            webDataTable.search(table);
        },
        extraActions(row) {
            SEAWAY_BILL.list.actions.statusChange(row);
            SEAWAY_BILL.list.actions.view(row);
            SEAWAY_BILL.list.actions.delete(row);
        },
        actions: {
            statusChange(row) {
                $('#row_pending,#row_delivered,#row_cancelled,#row_in_transit').off().on('click', function () {
                    let fd = new FormData();
                    changeCustomerStatus(GLOBAL_FN.buildUrl('bl/seaway/' + row.attr('data-id') + '/status/' + $(this).attr('data-value')), {
                        method: 'POST',
                        data: fd,
                        callBack: 'datatable'
                    }, $(this).attr('data-value'));
                })
            },
            view(row) {
                $('#row_view').off().on('click', function () {
                    let SEAWAY_BILLId = row.attr('data-id');

                    // Open drawer
                    let drawer = new bootstrap.Offcanvas(document.getElementById('moduleDrawer'));
                    drawer.show();

                    // Load Overview
                    $('#moduleOverview').html('<p>Loading...</p>');
                    $.get('/bl/seaway/' + SEAWAY_BILLId + '/overview', function (data) {
                        $('#moduleOverview').html(data);
                    });
                });
            },
            print(row) {
                $('#row_print').off().on('click', function () {
                    let SEAWAY_BILLId = row.attr('data-id');
                    SEAWAY_BILL.printPreview(SEAWAY_BILLId);
                });
            }
        }
    },
    form: {
        load() {
            SEAWAY_BILL.form.open();
        },
        open() {
            $('#new').off().on('click', function () {
                webModal.openGlobalModal({
                    title: 'New Seaway Bill',
                    url: GLOBAL_FN.buildUrl('bl/seaway/create'),
                    content: {
                        jobId: $(this).attr('data-loader-id')
                    },
                    size: 'lg',
                    scroll: false,
                });
            })
        },
        openCallback() {
            SEAWAY_BILL.form.addRow();
            SEAWAY_BILL.form.removeRow();
            SEAWAY_BILL.form.customer.change();
            SEAWAY_BILL.form.polPodLoad();
        },
        polPodLoad(preLoad = null) {
            initTomSelectSearch('#origin_port', 'sea', 100, preLoad);
            initTomSelectSearch('#destination_port', 'sea', 100, preLoad);
        },
        addRow() {
            $('#SEAWAYBILL-tbody').off('click', '.add-row').on('click', '.add-row', function () {
                let $tbody = $(this).closest('tbody');
                let $newRow = $tbody.find('tr:first').clone();

                // Clear values in cloned row
                $newRow.find('input, select, textarea').val('');
                $newRow.find('input[type="checkbox"]').prop('checked', false);
                $newRow.find('select').removeClass('tomselected').removeClass('ts-hidden-accessible');
                $newRow.find('div.ts-wrapper').remove();
                initTomSelectForm($newRow);

                $tbody.append($newRow);
            });
        },
        removeRow() {
            $('#SEAWAYBILL-tbody').off('click', '.remove-row').on('click', '.remove-row', function () {
                let $tbody = $(this).closest('tbody');
                const $tr = $(this).closest('tr');
                if ($tbody.find('tr').length > 1) {
                    $tr.remove();
                } else {
                    // If only one row left, just clear it
                    $tr.find('input,textarea').val('');
                    $tr.find('input[type="checkbox"]').prop('checked', false);
                    $tr.find('select').each(function () {
                        $(this).val('');
                        if ($(this).hasClass('selectpicker')) {
                            $(this).selectpicker('destroy').addClass('selectpicker');
                            selectPicker('#' + $(this).closest('table').attr('id'));
                        }
                    });
                }
            })
        },
        customer: {
            change() {
                $('#customer').change(function () {
                    // Handle customer change if needed
                });
            }
        }
    },
}
