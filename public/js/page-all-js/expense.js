EXPENSE = {
    title: 'Expense',
    baseUrl: 'finance/expense',
    actionUrl: 'finance/expense',
    load() {
        EXPENSE.form.load();
        EXPENSE.filter.load();
        datepicker();
    },
    filter: {
        load: function () {
            EXPENSE.filter.filterBox();
        },
        filterBox: function () {
            $('#apply-filter').off().on({
                click: function () {
                    EXPENSE.list.dataTable();
                    FILTER.filteredColumn();
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
        iframe.src = '/' + EXPENSE.baseUrl + '/' + printId + '/print';
    },
    downloadPDF(printId) {
        fetch('/' + EXPENSE.baseUrl + '/' + printId + '/print')
            .then(res => res.text())
            .then(html => {
                const container = document.createElement('div');
                container.id = 'html-pdf';
                container.className = 'px-4 pt-4';
                container.innerHTML = html;
                const opt = {
                    margin: 0.2,
                    filename: `expense-${printId}.pdf`,
                };
                html2pdf().set(opt).from(container).save();
            });
    },
    list: {
        load(activeTab) {
            EXPENSE.list.dataTable(activeTab);
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
                    url: GLOBAL_FN.buildUrl(EXPENSE.baseUrl + '/data'),
                    type: 'POST',
                    data: function (d) {
                        // Add tab parameter
                        d.tab = activeTab;
                        d.filterData = EXPENSE.filter.default();
                    },
                    dataSrc: function (json) {
                        $('#dataTable tbody').find('.loading-row').remove();
                        GLOBAL_FN.setStatusCounts(json.statusCounts);
                        return json.data;
                    }
                },
                columnDefs: [
                    {targets: [4,5,6], searchable: false},
                    {targets: [0, 1, 2, 3, 4, 5, 6], orderable: false},
                ],
                columns: [
                    {
                        data: 'row_no', render: function (data, type, row) {
                            return '<strong>' + row.row_no + '</strong>';
                        }
                    },
                    {data: 'posted_at'},
                    {
                        data: 'vendor.name_en', render: function (data, type, row) {
                            if (row.vendor) {
                                return '<div>' + row.vendor.name_en + '</div><div class="small text-muted">Code: ' + row.vendor.row_no + '</div>';
                            }
                            return '<div class="text-muted">-</div>';
                        }
                    },
                    {
                        data: 'customer.name_en', render: function (data, type, row) {
                            if (row.customer) {
                                return '<div>' + row.customer.name_en + '</div><div class="small text-muted">Code: ' + row.customer.row_no + '</div>';
                            }
                            return '<div class="text-muted">-</div>';
                        }
                    },
                    {
                        data: 'base_total', render: function (data, type, row) {
                            return '<div class="text-end text-secondary">' + row.base_total + '</div><div class="text-end"><small class="text-muted">' + baseCurrency + '</small></div>';
                        }
                    },
                    {
                        data: 'grand_total', render: function (data, type, row) {
                            return '<div class="text-end fw-semibold">' + row.grand_total + '</div><div class="text-end"><small>' + row.currency + '</small></div>';
                        }
                    },
                    GLOBAL_FN.dataTable.optionButton()
                ],
                language: {
                    search: ""
                },
                deferLoading: 0,

                initComplete: function () {
                    EXPENSE.form.open();
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
            EXPENSE.list.actions.statusChange(row);
            EXPENSE.list.actions.view(row);
            EXPENSE.list.actions.delete(row);
        },
        actions: {
            statusChange(row) {
                $('#row_draft,#row_approved,#row_rejected').off().on('click', function () {
                    let fd = new FormData();
                    changeCustomerStatus(GLOBAL_FN.buildUrl(EXPENSE.baseUrl + '/' + row.attr('data-id') + '/status/' + $(this).attr('data-value')), {
                        method: 'POST',
                        data: fd,
                        callBack: 'datatable'
                    }, $(this).attr('data-value'));
                })
            },
            view(row) {
                $('#row_view').off().on('click', function () {
                    let expenseId = row.attr('data-id');

                    // Open drawer
                    let drawer = new bootstrap.Offcanvas(document.getElementById('moduleDrawer'));
                    drawer.show();

                    // Load Overview
                    $('#moduleOverview').html('<p>Loading...</p>');
                    $.get('/finance/expense/' + expenseId + '/overview', function (data) {
                        $('#moduleOverview').html(data);
                    });
                });
            },
            delete(row) {
                $('#row_delete').off().on('click', function () {
                    if (confirm('Are you sure you want to delete this expense?')) {
                        $.ajax({
                            url: '/finance/expense/' + row.attr('data-id'),
                            type: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function (response) {
                                if (response.success) {
                                    toastr.success(response.message);
                                    $('#dataTable').DataTable().ajax.reload();
                                } else {
                                    toastr.error(response.message);
                                }
                            },
                            error: function (xhr) {
                                toastr.error('Error deleting expense');
                            }
                        });
                    }
                });
            }
        }
    },
    form: {
        load() {
            EXPENSE.form.open();
        },
        open() {
            $('#new').off().on('click', function () {
                webModal.openGlobalModal({
                    title: 'New Expense',
                    url: GLOBAL_FN.buildUrl(EXPENSE.baseUrl + '/create'),
                    content: null,
                    size: 'lg',
                    scroll: false,
                });
            })
        },
        openCallback() {
            EXPENSE.form.addRow();
            EXPENSE.form.removeRow();
            CALCULATION.load();
            CALCULATION.finalTotals();
            setTimeout(function () {
                EXPENSE.form.customerProspectToggle();
            })
        },
        customerProspectToggle() {
            // Handle customer select change
            $('#customer').on('change', function () {
                const customerValue = $(this).val();
                const supplierSelect = document.querySelector('#supplier');

                if (customerValue && customerValue !== '') {
                    // Disable prospect select when customer is selected
                    if (supplierSelect && supplierSelect.tomselect) {
                        supplierSelect.tomselect.disable();
                    }
                } else {
                    // Enable prospect select when customer is cleared
                    if (supplierSelect && supplierSelect.tomselect) {
                        supplierSelect.tomselect.enable();
                    }
                }
            });

            // Handle supplier select change
            $('#supplier').on('change', function () {
                const supplierValue = $(this).val();
                const customerSelect = document.querySelector('#customer');

                if (supplierValue && supplierValue !== '') {
                    // Disable customer select when prospect is selected
                    if (customerSelect && customerSelect.tomselect) {
                        customerSelect.tomselect.disable();
                    }
                } else {
                    // Enable customer select when prospect is cleared
                    if (customerSelect && customerSelect.tomselect) {
                        customerSelect.tomselect.enable();
                    }
                }
            });

            // Initial check on page load
            const customerValue = $('#customer').val();
            const supplierValue = $('#supplier').val();
            const customerSelect = document.querySelector('#customer');
            const supplierSelect = document.querySelector('#supplier');

            // Check if we're in edit mode with a supplier
            const isEditMode = $('#data-id').val() && $('#supplier').length > 0;
            const hasSupplierId = $('#supplier').data('has-supplier') === true || $('[name="supplier"]').find('option:selected').val() !== '';

            if (customerValue && customerValue !== '') {
                // Disable supplier select if customer is already selected
                if (supplierSelect && supplierSelect.tomselect) {
                    supplierSelect.tomselect.disable();
                }
            } else if (supplierValue && supplierValue !== '' || (isEditMode && hasSupplierId)) {
                // Disable customer select if supplier is already selected or we're editing a supplier
                if (customerSelect && customerSelect.tomselect) {
                    customerSelect.tomselect.disable();
                }
            }
        },
        addRow() {
            $('#' + MODULE + '-tbody').off('click', '.add-row').on('click', '.add-row', function () {
                let $tbody = $(this).closest('tbody');
                let $newRow = $tbody.find('tr:first').clone();

                // Clear values in cloned row
                $newRow.find('input, select, textarea').val('');
                $newRow.find('select').removeClass('tomselected').removeClass('ts-hidden-accessible');
                $newRow.find('div.ts-wrapper').remove();
                initTomSelectForm($newRow);

                $tbody.append($newRow);
            });
        },
        removeRow() {
            $('#' + MODULE + '-tbody').off('click', '.remove-row').on('click', '.remove-row', function () {
                let $tbody = $(this).closest('tbody');
                const $tr = $(this).closest('tr');
                if ($tbody.find('tr').length > 1) {
                    $tr.remove();
                } else {
                    // If only one row left, just clear it
                    $tr.find('input,textarea').val('');
                    $tr.find('select').each(function () {
                        $(this).val('');
                        if ($(this).hasClass('selectpicker')) {
                            $(this).selectpicker('destroy').addClass('selectpicker');
                            selectPicker('#' + $(this).closest('table').attr('id'));
                        }
                    });
                }
                CALCULATION.finalTotals();
            })
        }
    },
}
