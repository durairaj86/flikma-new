SUPPLIER = {
    title: trans('Supplier'),
    baseUrl: 'supplier',
    actionUrl: 'supplier',
    load() {
        SUPPLIER.form.open();
    },
    list: {
        load(activeTab) {
            SUPPLIER.list.dataTable(activeTab);
        },
        filters() {
            return {
                joined_from: $('#filter-joined-from').val() || '',
                joined_to: $('#filter-joined-to').val() || ''
            };
        },
        resetFilterInputs() {
            $('#filter-joined-from, #filter-joined-to').each(function () {
                if (this._flatpickr) this._flatpickr.clear(); else $(this).val('');
            });
        },
        chips() {
            const f = SUPPLIER.list.filters();
            let html = '';
            if (f.joined_from || f.joined_to) {
                html = '<div class="d-inline-flex align-items-center bg-light border rounded-pill px-2 py-1 me-2 mb-2 small" style="font-size:0.8rem;">' +
                    '<span class="me-2">Joined :&nbsp;<b>' + (f.joined_from || '…') + ' / ' + (f.joined_to || '…') + '</b></span>' +
                    '<button type="button" class="btn btn-sm btn-light p-0 border-0 d-flex align-items-center justify-content-center supplier-chip-remove" style="width:16px;height:16px;line-height:1;" aria-label="Close">&times;</button></div>';
            }
            $('#supplierFilterChips').html(html);
            $('.supplier-chip-remove').off().on('click', function () {
                SUPPLIER.list.resetFilterInputs();
                $('#supplier-filter-apply').trigger('click');
            });
        },
        bindFilter() {
            const panel = $('#supplierFilterForm');
            if (!panel.data('ready')) {
                panel.data('ready', true);
                datepicker();
                // The calendar renders outside the menu; don't let clicks on it close the menu.
                document.getElementById('supplierFilterBtn').addEventListener('hide.bs.dropdown', function (e) {
                    const t = e.clickEvent && e.clickEvent.target;
                    if (t && $(t).closest('.flatpickr-calendar').length) e.preventDefault();
                });
            }
            const refresh = () => {
                const n = Object.values(SUPPLIER.list.filters()).filter(v => v).length;
                $('#supplierFilterBadge').text(n).toggleClass('d-none', n === 0);
                SUPPLIER.list.chips();
                SUPPLIER.list.load();
            };
            $('#supplier-filter-apply').off().on('click', function () {
                refresh();
                bootstrap.Dropdown.getOrCreateInstance(document.getElementById('supplierFilterBtn')).hide();
            });
            $('#supplier-filter-clear').off().on('click', function () {
                SUPPLIER.list.resetFilterInputs();
                refresh();
            });
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
                    url: GLOBAL_FN.buildUrl(SUPPLIER.baseUrl + '/data'),
                    type: 'POST',
                    data: {
                        'tab': activeTab
                    },
                    beforeSend: function (xhr, settings) {
                        settings.data += '&' + $.param(SUPPLIER.list.filters());
                    },
                    dataSrc: function (json) {
                        // Remove loader rows when data arrives
                        $('#dataTable tbody').find('.loading-row').remove();
                        GLOBAL_FN.setStatusCounts(json.statusCounts);
                        return json.data;
                    }
                },
                columnDefs: [
                    {targets: [0], searchable: false},
                    {targets: [8], searchable: false, orderable: false},
                    {targets: [0], orderable: false},
                    /*{targets: [1], class: 'hide', visible: false},*/
                ],
                columns: [
                    {data: 'DT_RowIndex', class: 'ps-4 text-muted small'},
                    {
                        data: 'name_en', render: function (data, type, row) {
                            return '<a href="' + GLOBAL_FN.buildUrl('suppliers/' + row.id) + '" class="fw-bold text-primary text-decoration-none d-block">' + $('<span>').text(row.name_en).html() + '</a>' +
                                   '<div class="text-muted x-small fw-semibold">' + row.row_no + '</div>';
                        }
                    },
                    {
                        data: 'contact_person', render: function (data) {
                            return data ? '<span class="small">' + $('<span>').text(data).html() + '</span>' : '<span class="text-muted">—</span>';
                        }
                    },
                    {
                        data: 'email', render: function (data, type, row) {
                            return '<div class="d-flex flex-column">' +
                                   '<span class="small">' + (row.email ?? '') + '</span>' +
                                   '<span class="small text-muted">' + (row.phone ?? '') + '</span>' +
                                   '</div>';
                        }
                    },
                    {
                        data: 'country', render: function (data, type, row) {
                            return '<span class="badge bg-light text-dark border fw-normal">' +
                                   '<i class="bi bi-geo-alt me-1"></i>' +
                                   (row.city_en ? (row.city_en + ', ') : '') + (row.country ?? '') +
                                   '</span>';
                        }
                    },
                    {
                        data: 'currency', render: function (data, type, row) {
                            return '<span class="fw-medium">' + (row.currency ?? '') + '</span>';
                        }
                    },
                    {
                        data: 'vat_number', class: 'small font-monospace', render: function (data, type, row) {
                            return row.vat_number ?? '';
                        }
                    },
                    {
                        data: 'credit_limit', render: function (data, type, row) {
                            return '<div class="fw-bold">' + (row.credit_limit ?? '') + '</div>' +
                                   '<div class="x-small text-danger">' + (row.credit_days ? row.credit_days + ' ' + trans('Days') : '') + '</div>';
                        }
                    },
                    {
                        data: 'due_amount', render: function (data, type, row) {
                            const due = parseFloat(row.due_amount) || 0;
                            const overdueCount = parseInt(row.overdue_count) || 0;
                            if (due <= 0) {
                                return '<div class="text-muted">0.00</div>';
                            }
                            const badge = overdueCount > 0
                                ? '<span class="badge bg-danger-subtle text-danger border border-opacity-10 px-2 py-1" style="font-size:0.65rem;">' + overdueCount + ' ' + trans('Overdue') + '</span>'
                                : '<span class="badge bg-success-subtle text-success border border-opacity-10 px-2 py-1" style="font-size:0.65rem;">' + trans('On Time') + '</span>';
                            return '<div class="fw-bold">' + amountFormat(due) + '</div><div class="mt-1">' + badge + '</div>';
                        }
                    },
                    {data: 'created_at', name: 'created_at', class: 'small text-muted'},
                    // Actions column
                    GLOBAL_FN.dataTable.optionButton()
                ],
                language: {
                    search: "" // removes "Search:" label
                },
                deferLoading: 0, // don't load immediately
                /*preDrawCallback: function (settings) {
                    let api = this.api();
                    let cols = api.columns().nodes().length;
                    let pageLen = api.page.len(); // rows per page

                    // clear old rows & insert loader rows
                    let loaderRows = '';
                    for (let i = 0; i < pageLen; i++) {
                        loaderRows += '<tr class="loading-row">';
                        for (let j = 0; j < cols; j++) {
                            loaderRows += `<td><div class="loader-td"></div></td>`;
                        }
                        loaderRows += '</tr>';
                    }
                    $('#dataTable tbody').html(loaderRows);
                }*/

                initComplete: function () {
                    SUPPLIER.form.open();
                    webDataTable.actions.menu();
                    SUPPLIER.list.bindFilter();
                }
            });
            $('#customSearch').on('keyup input', window.debounceSearch(function () {
                table.search(this.value).draw();
            }));
            $('#dataTable_filter').closest('div.row').remove();
            webDataTable.loader(table);
            webDataTable.search(table);
            //webDataTable.actions.menu();
        },
        extraActions(row) {
            SUPPLIER.list.actions.statusChange(row);
            SUPPLIER.list.actions.view(row);
            $('#row_delete').off().on('click', function () {
                deleteRecord(GLOBAL_FN.buildUrl('supplier/' + row.attr('data-id')), function () { SUPPLIER.list.load(); });
            });
            $('#row_statement').off().on('click', function () {
                window.location.href = GLOBAL_FN.buildUrl('reports/supplier-statement', {supplier: row.attr('data-id')});
            });
            $('#row_search').off().on('click', function () {
                window.location.href = GLOBAL_FN.buildUrl('invoice/supplier', {supplier: row.attr('data-id')});
            });
        },
        actions: {
            statusChange(row) {
                $('#row_confirm,#row_blocked').off().on('click', function () {
                    //GLOBAL_FN.ajaxData.sendData(GLOBAL_FN.buildUrl('customer/' + row.attr('data-id') + '/status/' + $(this).attr('data-value')),'datatable',{})
                    let fd = new FormData();
                    changeCustomerStatus(GLOBAL_FN.buildUrl('supplier/' + row.attr('data-id') + '/status/' + $(this).attr('data-value')), {
                        method: 'POST',
                        data: fd,
                        callBack: 'datatable'
                    }, $(this).attr('data-value'));
                })
            },
            view(row) {
                $('#row_view').off().on('click', function () {
                    window.location.href = GLOBAL_FN.buildUrl('suppliers/' + row.attr('data-id'));
                });
            },
        }
    },
    form: {
        open() {
            $('#new').off().on('click', function () {
                webModal.openGlobalModal({
                    title: trans('Add Supplier'),
                    url: GLOBAL_FN.buildUrl('supplier/create'),
                    content: null,
                    size: 'md',
                    callBack: null,
                    minHeight: 'min-height:51vh;',
                });
            })
        },
    },
}
