COLLECTION = {
    title: 'Collection',
    baseUrl: 'transaction/collections',
    actionUrl: 'transaction/collections',
    currentTab: 'all',
    load() {
        COLLECTION.form.load();
    },

    list: {
        load(activeTab) {
            COLLECTION.list.dataTable(activeTab);

            // Tab change event
            /*$('#collectionTabs button').off().on('click', function(e) {
                COLLECTION.currentTab = $(this).attr('id').replace('-tab', '');
                COLLECTION.list.dataTable(COLLECTION.currentTab);
            });*/
        },

        dataTable(activeTab = null) {
            GLOBAL_FN.destroyDataTable();
            activeTab = (activeTab && (typeof activeTab !== 'object')) ? activeTab : $("#listTabs").find('li button.active').attr('id');
            console.log(activeTab);
            let table = $('#dataTable').DataTable({
                processing: false,
                serverSide: true,
                autoWidth: false,
                lengthChange: false,
                pageLength: 25,
                dom: 'rt<"row mt-2"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7 d-flex justify-content-end"p>>',
                /*order: [[1, 'desc']],*/
                ajax: {
                    url: GLOBAL_FN.buildUrl('transaction/collections/data'),
                    type: 'POST',
                    data: {
                        'tab': activeTab
                    },
                    dataSrc: function (json) {
                        // Update status counts
                        $('#dataTable tbody').find('.loading-row').remove();
                        GLOBAL_FN.setStatusCounts(json.statusCounts);
                        return json.data;
                    }
                },
                columnDefs: [
                    {targets: [0], searchable: false},
                    {targets: [0], orderable: false},
                ],
                columns: [
                    {data: 'DT_RowIndex', class: 'hide-tooltip fav-index'},
                    {data: 'row_no', name: 'row_no'},
                    {data: 'customer_name', name: 'customer_name'},
                    /*{data: 'job_no', name: 'job_no'},*/
                    {data: 'collection_date', name: 'collection_date'},
                    {data: 'account', name: 'account'},
                    /*{data: 'collection_method', name: 'collection_method'},*/
                    {data: 'reference_no', name: 'reference_no'},
                    {data: 'currency', name: 'currency'},
                    {data: 'grand_total', name: 'grand_total'},
                    {data: 'status', name: 'status'},
                    // Actions column
                    GLOBAL_FN.dataTable.optionButton()
                ],
                language: {
                    search: "" // removes "Search:" label
                },
                deferLoading: 0, // don't load immediately
                initComplete: function () {
                    COLLECTION.form.open();
                    webDataTable.actions.menu();
                },
                createdRow: function (row, data, dataIndex) {
                    $(row).addClass('row-item');
                    $(row).attr('data-id', data.id);
                    $(row).attr('data-name', 'Collection #' + data.row_no);
                    $(row).attr('id', 'collection-' + data.id);
                }
            });

            $('#customSearch').off('keyup').on('keyup', function () {
                table.search(this.value).draw();
            });

            // Initialize table utilities
            webDataTable.loader(table);
            webDataTable.search(table);
        },

        extraActions(row) {
            COLLECTION.list.actions.statusChange(row);
            COLLECTION.list.actions.view(row);
            COLLECTION.list.actions.print(row);
            COLLECTION.list.actions.download(row);
        },

        actions: {
            statusChange(row) {
                $('#row_approved, #row_draft, #row_cancelled').off().on('click', function () {
                    const id = row.attr('data-id');
                    const status = $(this).attr('data-value');

                    if ($(this).attr('id') === 'row_cancelled_later') {
                        $('#collection_id').val(id);
                        $('#disapprovalReasonModal').modal('show');
                    } else {
                        COLLECTION.actions.updateStatus(id, status);
                    }
                });
            },

            view(row) {
                $('#row_view').off().on('click', function () {
                    const id = row.attr('data-id');
                    window.location.href = `/${COLLECTION.baseUrl}/${id}`;
                });
            },

            /*edit(row) {
                $('#row_edit').off().on('click', function () {
                    const id = row.attr('data-id');
                    COLLECTION.form.edit(id);
                });
            },*/

            delete(row) {
                $('#row_delete').off().on('click', function () {
                    const id = row.attr('data-id');
                    const name = row.attr('data-name');

                    if (confirm(`Are you sure you want to delete ${name}?`)) {
                        $.ajax({
                            url: GLOBAL_FN.buildUrl(`${COLLECTION.baseUrl}/${id}`),
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function (response) {
                                toastr.success(response.message);
                                GLOBAL_FN.refreshDataTable();
                            },
                            error: function (xhr) {
                                toastr.error(xhr.responseJSON?.message || 'Error deleting collection');
                            }
                        });
                    }
                });
            },

            print(row) {
                $('#row_print').off().on('click', function () {
                    const id = row.attr('data-id');
                    COLLECTION.printPreview(id);
                });
            },

            download(row) {
                $('#row_download').off().on('click', function () {
                    const id = row.attr('data-id');
                    COLLECTION.downloadPDF(id);
                });
            }
        }
    },

    actions: {
        updateStatus: function (id, status) {
            $.ajax({
                url: GLOBAL_FN.buildUrl(`${COLLECTION.baseUrl}/${id}/status/${status}`),
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    toastr.success(response.message);
                    loadJs('list.dataTable');
                },
                error: function (xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Error updating status');
                }
            });
        },

        submitDisapprovalReason: function () {
            const id = $('#collection_id').val();
            const reason = $('#reason').val();

            if (!reason) {
                toastr.error('Please provide a reason for disapproval');
                return;
            }

            $.ajax({
                url: GLOBAL_FN.buildUrl(`${COLLECTION.baseUrl}/${id}/disapprove`),
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    reason: reason
                },
                success: function (response) {
                    $('#disapprovalReasonModal').modal('hide');
                    $('#reason').val('');
                    toastr.success(response.message);
                    GLOBAL_FN.refreshDataTable();
                },
                error: function (xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Error disapproving collection');
                }
            });
        }
    },
    before: {
        submit() {
            if ($('input[name="customer_invoice_ids[]"]:checked').length === 0) {
                toastr.error('Please select at least one invoice');
                return false;
            }
            return true;
        }
    },

    form: {
        load() {
            COLLECTION.form.open();
        },
        open() {
            $('#new').off().on('click', function () {
                webModal.openGlobalModal({
                    title: 'Create Collection',
                    url: GLOBAL_FN.buildUrl(`${COLLECTION.baseUrl}/create`),
                    content: null,
                    size: 'xl',
                    scroll: true,
                    minHeight: 'min-height:70vh;',
                });
            });
        },

        openCallback() {
            // Initialize customer select
            $('#customer').off('change').on('change', function () {
                const customerId = $(this).val();
                if (customerId) {
                    COLLECTION.form.loadCustomerInvoices(customerId);
                } else {
                    $('#no-invoices-message').show();
                    $('#invoices-table-container').hide();
                }
            });

            // Select/deselect all invoices
            $('#select-all-invoices').off('change').on('change', function () {
                const isChecked = $(this).is(':checked');
                $('.invoice-checkbox').prop('checked', isChecked).trigger('change');
            });

            // Initialize if customer is already selected
            if ($('#customer').val()) {
                $('.invoice-checkbox').off('change').on('change', function () {
                    COLLECTION.form.handleInvoiceCheckboxChange($(this));
                });

                // Update total when amount changes
                $('.invoice-amount').off('input').on('input', function () {
                    COLLECTION.form.handleAmountChange($(this));
                });

                // Calculate initial total
                COLLECTION.form.calculateTotal();
            }

            // Form submission
            /*$('#moduleForm').off('submit').on('submit', function (e) {
                return COLLECTION.form.prepareFormData($(this));
            });*/
        },

        loadCustomerInvoices(customerId) {
            $.ajax({
                url: GLOBAL_FN.buildUrl(`${COLLECTION.baseUrl}/customer/${customerId}/invoices`),
                method: 'GET',
                success: function (response) {
                    if (response.length === 0) {
                        $('#no-invoices-message').text('No approved invoices found for this customer.').show();
                        $('#invoices-table-container').hide();
                        return;
                    }

                    $('#no-invoices-message').hide();
                    $('#invoices-table-container').show();

                    let html = '';
                    response.forEach(function (invoice) {
                        // Check if this invoice is already selected
                        /* const existingInvoice = COLLECTION.form.selectedInvoices ?
                             COLLECTION.form.selectedInvoices.find(inv => inv.id === invoice.id) : null;
                         const isChecked = existingInvoice !== undefined;*/

                        const isChecked = false;

                        // Use the balance amount from the server, or default to the current amount if it's already selected
                        const balanceAmount = invoice.balance_amount || 0;
                        //const amount = existingInvoice ? existingInvoice.amount : (balanceAmount > 0 ? balanceAmount : 0);
                        const amount = balanceAmount > 0 ? balanceAmount : 0;

                        html += `
                            <tr>
                                <td>
                                    <input type="checkbox" class="invoice-checkbox" data-id="${invoice.id}" name="customer_invoice_ids[]" value="${invoice.id}" data-amount="${invoice.grand_total}" data-paid="${invoice.paid_amount || 0}" ${isChecked ? 'checked' : ''}>
                                </td>
                                <td>${invoice.row_no}</td>
                                <td>${invoice.job_no}</td>
                                <td>${invoice.invoice_date}</td>
                                <td>${invoice.due_at}</td>
                                <td class="text-end">${COLLECTION.form.formatNumber(invoice.grand_total)}</td>
                                <td class="text-end">
                                    <input type="text" step="0.01" class="form-control invoice-amount float text-end" name="invoice_amounts[${invoice.id}]" data-id="${invoice.id}" value="${amount}" min="0" max="${balanceAmount}" data-balance="${balanceAmount}" ${!isChecked ? 'disabled' : ''}>
                                </td>
                                <td class="text-end">${COLLECTION.form.formatNumber(balanceAmount)}</td>
                            </tr>
                        `;
                    });

                    $('#invoices-body').html(html);

                    // Enable/disable amount inputs based on checkbox
                    $('.invoice-checkbox').off('change').on('change', function () {
                        COLLECTION.form.handleInvoiceCheckboxChange($(this));
                    });

                    // Update total when amount changes
                    $('.invoice-amount').off('input').on('input', function () {
                        COLLECTION.form.handleAmountChange($(this));
                    });

                    // Calculate initial total
                    COLLECTION.form.calculateTotal();
                },
                error: function (xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Error loading customer invoices');
                }
            });
        },

        selectedInvoices: [],

        handleInvoiceCheckboxChange($checkbox) {
            const isChecked = $checkbox.is(':checked');
            const amountInput = $checkbox.closest('tr').find('.invoice-amount');
            const id = $checkbox.data('id');

            amountInput.prop('disabled', !isChecked);
            if (isChecked) {
                amountInput.addClass('bg-white');
            } else {
                amountInput.removeClass('bg-white');
            }

            if (isChecked) {
                // Add to selected invoices
                COLLECTION.form.selectedInvoices.push({
                    id: id,
                    amount: parseFloat(amountInput.val())
                });
            } else {
                // Remove from selected invoices
                COLLECTION.form.selectedInvoices = COLLECTION.form.selectedInvoices.filter(inv => inv.id !== id);
            }

            COLLECTION.form.calculateTotal();
        },

        handleAmountChange($input) {
            const id = $input.data('id');
            const amount = parseFloat($input.val() || 0);
            const balanceAmount = parseFloat($input.attr('max') || 0);

            // Ensure amount doesn't exceed the balance amount
            if (amount > balanceAmount) {
                $input.val(balanceAmount);
                toastr.error('Collection amount cannot exceed the balance amount');
                return;
            }

            // Update in selected invoices
            const index = COLLECTION.form.selectedInvoices.findIndex(inv => inv.id === id);
            if (index !== -1) {
                COLLECTION.form.selectedInvoices[index].amount = amount;
            }

            // Update the balance amount display
            const $row = $input.closest('tr');
            const $balanceCell = $row.find('td:last');
            const newBalance = balanceAmount - amount;
            $balanceCell.text(COLLECTION.form.formatNumber(newBalance));

            // Update the data-balance attribute for future validations
            $input.data('balance', newBalance);

            COLLECTION.form.calculateTotal();
        },

        calculateTotal() {
            let total = 0;
            $('.invoice-amount').each(function () {
                if ($(this).closest('tr').find('.invoice-checkbox').is(':checked')) {
                    total += parseFloat($(this).val() || 0);
                }
            });
            $('#total-collection-amount').text(COLLECTION.form.formatNumber(total));
        },

        formatNumber(num) {
            return parseFloat(num).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        },

        /*prepareFormData($form) {
            // Validate form
            if (!$('#customer').val()) {
                toastr.error('Please select a customer');
                return false;
            }

            if ($('input[name="customer_invoice_ids[]"]:checked').length === 0) {
                toastr.error('Please select at least one invoice');
                return false;
            }

            // We don't need to modify the form, as the checkboxes and amount inputs are already in the form
            // and will be submitted correctly

            return true;
        }*/
    },

    printPreview: function (id) {
        window.open(`/${COLLECTION.baseUrl}/${id}/print`, '_blank');
    },

    downloadPDF: function (id) {
        window.open(`/${COLLECTION.baseUrl}/${id}/download`, '_blank');
    }
};
