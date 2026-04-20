PAYMENT = {
    title: 'Payment',
    baseUrl: 'transaction/payments',
    actionUrl: 'transaction/payments',
    currentTab: 'all',
    load() {
        PAYMENT.form.load();
    },

    /*load() {
        // Initialize list functionality
        PAYMENT.list.load(this.currentTab);

        // Initialize form functionality
        PAYMENT.form.open();

        // Initialize disapproval reason submission
        $('#submitDisapprovalReason').off().on('click', function() {
            PAYMENT.actions.submitDisapprovalReason();
        });
    },*/
    list: {
        load(activeTab) {
            PAYMENT.list.dataTable(activeTab);

            // Tab change event
            /*$('#paymentTabs button').off().on('click', function(e) {
                PAYMENT.currentTab = $(this).attr('id').replace('-tab', '');
                PAYMENT.list.dataTable(PAYMENT.currentTab);
            });*/

            // Row click for view
            /*$(document).off('click', '.row-item').on('click', '.row-item', function() {
                const id = $(this).data('id');
                window.location.href = `/${PAYMENT.baseUrl}/${id}`;
            });*/

            // Context menu for row actions
            /*$(document).off('contextmenu', '.row-item').on('contextmenu', '.row-item', function(e) {
                e.preventDefault();
                const id = $(this).data('id');

                $.ajax({
                    url: GLOBAL_FN.buildUrl(`${PAYMENT.baseUrl}/${id}/actions`),
                    method: 'GET',
                    success: function(response) {
                        showContextMenu(e, response);
                    }
                });
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
                    url: GLOBAL_FN.buildUrl('transaction/payments/data'),
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
                    {data: 'supplier_name', name: 'supplier_name'},
                    /*{data: 'job_no', name: 'job_no'},*/
                    {data: 'payment_date', name: 'payment_date'},
                    {data: 'account', name: 'account'},
                    /*{data: 'payment_method', name: 'payment_method'},*/
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
                    PAYMENT.form.open();
                    webDataTable.actions.menu();
                },
                createdRow: function (row, data, dataIndex) {
                    $(row).addClass('row-item');
                    $(row).attr('data-id', data.id);
                    $(row).attr('data-name', 'Payment #' + data.row_no);
                    $(row).attr('id', 'payment-' + data.id);
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
            PAYMENT.list.actions.statusChange(row);
            PAYMENT.list.actions.view(row);
            PAYMENT.list.actions.print(row);
            PAYMENT.list.actions.download(row);
        },

        actions: {
            statusChange(row) {
                $('#row_approved, #row_draft, #row_cancelled').off().on('click', function () {
                    const id = row.attr('data-id');
                    const status = $(this).attr('data-value');

                    if ($(this).attr('id') === 'row_cancelled_later') {//later right now no time
                        $('#payment_id').val(id);
                        $('#disapprovalReasonModal').modal('show');
                    } else {
                        PAYMENT.actions.updateStatus(id, status);
                    }
                });
            },

            view(row) {
                $('#row_view').off().on('click', function () {
                    const id = row.attr('data-id');
                    window.location.href = `/${PAYMENT.baseUrl}/${id}`;
                });
            },

            /*edit(row) {
                $('#row_edit').off().on('click', function () {
                    const id = row.attr('data-id');
                    PAYMENT.form.edit(id);
                });
            },*/

            delete(row) {
                $('#row_delete').off().on('click', function () {
                    const id = row.attr('data-id');
                    const name = row.attr('data-name');

                    if (confirm(`Are you sure you want to delete ${name}?`)) {
                        $.ajax({
                            url: GLOBAL_FN.buildUrl(`${PAYMENT.baseUrl}/${id}`),
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function (response) {
                                toastr.success(response.message);
                                GLOBAL_FN.refreshDataTable();
                            },
                            error: function (xhr) {
                                toastr.error(xhr.responseJSON?.message || 'Error deleting payment');
                            }
                        });
                    }
                });
            },

            print(row) {
                $('#row_print').off().on('click', function () {
                    const id = row.attr('data-id');
                    PAYMENT.printPreview(id);
                });
            },

            download(row) {
                $('#row_download').off().on('click', function () {
                    const id = row.attr('data-id');
                    PAYMENT.downloadPDF(id);
                });
            }
        }
    },

    actions: {
        updateStatus: function (id, status) {
            $.ajax({
                url: GLOBAL_FN.buildUrl(`${PAYMENT.baseUrl}/${id}/status/${status}`),
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
            const id = $('#payment_id').val();
            const reason = $('#reason').val();

            if (!reason) {
                toastr.error('Please provide a reason for disapproval');
                return;
            }

            $.ajax({
                url: GLOBAL_FN.buildUrl(`${PAYMENT.baseUrl}/${id}/disapprove`),
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
                    toastr.error(xhr.responseJSON?.message || 'Error disapproving payment');
                }
            });
        }
    },
    before: {
        submit() {
            if ($('input[name="supplier_invoice_ids[]"]:checked').length === 0) {
                toastr.error('Please select at least one invoice');
                return false;
            }
            return true;
        }
    },

    form: {
        load() {
            PAYMENT.form.open();
        },

        addTransaction() {
            const newRow = `
                <tr class="additional-transaction-row">
                    <td>
                        <select class="form-select account-select" name="additional_transaction_accounts[]" required>
                            <option value="">Select Account</option>
                            ${PAYMENT.form.getAccountOptions()}
                        </select>
                    </td>
                    <td>
                        <input type="text" class="form-control" name="additional_transaction_descriptions[]" placeholder="Description">
                    </td>
                    <td>
                        <input type="text" class="form-control float additional-transaction-amount" name="additional_transaction_amounts[]" placeholder="0.00" required>
                    </td>
                    <td>
                        <select class="form-select" name="additional_transaction_types[]" required>
                            <option value="debit">Debit</option>
                            <option value="credit">Credit</option>
                        </select>
                    </td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm remove-transaction"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
            `;
            $('#additional-transactions-body').append(newRow);

            // Initialize any plugins for the new row
            $('.float').inputmask({
                alias: 'numeric',
                groupSeparator: ',',
                autoGroup: true,
                digits: 2,
                digitsOptional: false,
                placeholder: '0.00',
                rightAlign: false,
                allowMinus: false,
                removeMaskOnSubmit: true
            });
        },

        getAccountOptions() {
            let options = '';
            $('#additional-transactions-table select.account-select:first option').each(function() {
                options += `<option value="${$(this).val()}">${$(this).text()}</option>`;
            });
            return options;
        },
        open() {
            $('#new').off().on('click', function () {
                webModal.openGlobalModal({
                    title: 'Create Payment',
                    url: GLOBAL_FN.buildUrl(`${PAYMENT.baseUrl}/create`),
                    content: null,
                    size: 'xl',
                    scroll: true,
                    //callBack: PAYMENT.form.openCallback,
                    minHeight: 'min-height:70vh;',
                });
            });
        },

        /*edit(id) {
            webModal.openGlobalModal({
                title: 'Edit Payment',
                url: GLOBAL_FN.buildUrl(`${PAYMENT.baseUrl}/${id}/edit`),
                content: null,
                size: 'xl',
                scroll: true,
                minHeight: 'min-height:70vh;',
            });
        },*/

        openCallback() {
            // Initialize supplier select
            $('#supplier').off('change').on('change', function () {
                const supplierId = $(this).val();
                if (supplierId) {
                    PAYMENT.form.loadSupplierInvoices(supplierId);
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

            // Initialize if supplier is already selected
            if ($('#supplier').val()) {
                //PAYMENT.form.loadSupplierInvoices($('#supplier').val());
                $('.invoice-checkbox').off('change').on('change', function () {
                    PAYMENT.form.handleInvoiceCheckboxChange($(this));
                });

                // Update total when amount changes
                $('.invoice-amount').off('input').on('input', function () {
                    PAYMENT.form.handleAmountChange($(this));
                });

                // Calculate initial total
                PAYMENT.form.calculateTotal();
            }

            // Handle adding additional transactions
            $('#add-transaction').off('click').on('click', function() {
                PAYMENT.form.addTransaction();
            });

            // Handle removing additional transactions
            $(document).off('click', '.remove-transaction').on('click', '.remove-transaction', function() {
                $(this).closest('tr').remove();
                PAYMENT.form.calculateTotal();
            });

            // Handle additional transaction amount changes
            $(document).off('input', '.additional-transaction-amount').on('input', '.additional-transaction-amount', function() {
                PAYMENT.form.calculateTotal();
            });

            // Form submission
            /*$('#moduleForm').off('submit').on('submit', function (e) {
                return PAYMENT.form.prepareFormData($(this));
            });*/
        },

        loadSupplierInvoices(supplierId) {
            $.ajax({
                url: GLOBAL_FN.buildUrl(`${PAYMENT.baseUrl}/supplier/${supplierId}/invoices`),
                method: 'GET',
                success: function (response) {
                    if (response.length === 0) {
                        $('#no-invoices-message').text('No approved invoices found for this supplier.').show();
                        $('#invoices-table-container').hide();
                        return;
                    }

                    $('#no-invoices-message').hide();
                    $('#invoices-table-container').show();

                    let html = '';
                    response.forEach(function (invoice) {
                        // Check if this invoice is already selected
                        /*const existingInvoice = PAYMENT.form.selectedInvoices ?
                            PAYMENT.form.selectedInvoices.find(inv => inv.id === invoice.id) : null;
                        const isChecked = existingInvoice !== undefined;*/

                        //const existingInvoice = PAYMENT.form.selectedInvoices.find(inv => inv.id === invoice.id);
                        const isChecked = false;

                        // Use the balance amount from the server, or default to the current amount if it's already selected
                        const balanceAmount = invoice.balance_amount || 0;
                        const amount = balanceAmount > 0 ? balanceAmount : 0;

                        html += `
                            <tr>
                                <td>
                                    <input type="checkbox" class="invoice-checkbox" data-id="${invoice.id}" name="supplier_invoice_ids[]" value="${invoice.id}" data-amount="${invoice.grand_total}" data-paid="${invoice.paid_amount || 0}" ${isChecked ? 'checked' : ''}>
                                </td>
                                <td>${invoice.row_no}</td>
                                <td>${invoice.job_no}</td>
                                <td>${invoice.invoice_date}</td>
                                <td>${invoice.due_at}</td>
                                <td class="text-end">${PAYMENT.form.formatNumber(invoice.grand_total)}</td>
                                <td class="text-end">
                                    <input type="text" step="0.01" class="form-control invoice-amount float text-end" name="invoice_amounts[${invoice.id}]" data-id="${invoice.id}" value="${amount}" min="0" max="${balanceAmount}" data-balance="${balanceAmount}" ${!isChecked ? 'disabled' : ''}>
                                </td>
                                <td class="text-end">${PAYMENT.form.formatNumber(balanceAmount)}</td>
                            </tr>
                        `;
                    });

                    $('#invoices-body').html(html);

                    // Enable/disable amount inputs based on checkbox
                    $('.invoice-checkbox').off('change').on('change', function () {
                        PAYMENT.form.handleInvoiceCheckboxChange($(this));
                    });

                    // Update total when amount changes
                    $('.invoice-amount').off('input').on('input', function () {
                        PAYMENT.form.handleAmountChange($(this));
                    });

                    // Calculate initial total
                    PAYMENT.form.calculateTotal();
                },
                error: function (xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Error loading supplier invoices');
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
                PAYMENT.form.selectedInvoices.push({
                    id: id,
                    amount: parseFloat(amountInput.val())
                });
            } else {
                // Remove from selected invoices
                PAYMENT.form.selectedInvoices = PAYMENT.form.selectedInvoices.filter(inv => inv.id !== id);
            }

            PAYMENT.form.calculateTotal();
        },

        handleAmountChange($input) {
            const id = $input.data('id');
            const amount = parseFloat($input.val() || 0);
            const balanceAmount = parseFloat($input.attr('max') || 0);

            // Ensure amount doesn't exceed the balance amount
            if (amount > balanceAmount) {
                $input.val(balanceAmount);
                toastr.error('Payment amount cannot exceed the balance amount');
                return;
            }

            // Update in selected invoices
            const index = PAYMENT.form.selectedInvoices.findIndex(inv => inv.id === id);
            if (index !== -1) {
                PAYMENT.form.selectedInvoices[index].amount = amount;
            }

            // Update the balance amount display
            const $row = $input.closest('tr');
            const $balanceCell = $row.find('td:last');
            const newBalance = balanceAmount - amount;
            $balanceCell.text(PAYMENT.form.formatNumber(newBalance));

            // Update the data-balance attribute for future validations
            $input.data('balance', newBalance);

            PAYMENT.form.calculateTotal();
        },

        calculateTotal() {
            let total = 0;

            // Sum invoice amounts
            $('.invoice-amount').each(function () {
                if ($(this).closest('tr').find('.invoice-checkbox').is(':checked')) {
                    total += parseFloat($(this).val() || 0);
                }
            });

            // Add additional transaction amounts
            $('.additional-transaction-amount').each(function() {
                const amount = parseFloat($(this).val());
                if (!isNaN(amount)) {
                    total += amount;
                }
            });

            $('#total-payment-amount').text(PAYMENT.form.formatNumber(total));
        },

        formatNumber(num) {
            return parseFloat(num).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        },

        /* prepareFormData($form) {
             // Validate form
             if (!$('#supplier').val()) {
                 toastr.error('Please select a supplier');
                 return false;
             }

             if (PAYMENT.form.selectedInvoices.length === 0) {
                 toastr.error('Please select at least one invoice');
                 return false;
             }

             // Remove any existing hidden fields for supplier_invoice_ids and invoice_amounts
             $form.find('input[name="supplier_invoice_ids[]"]').remove();
             $form.find('input[name="invoice_amounts[]"]').remove();

             // Add hidden fields for each selected invoice
             PAYMENT.form.selectedInvoices.forEach(function(invoice) {
                 $form.append(`<input type="hidden" name="supplier_invoice_ids[]" value="${invoice.id}">`);
                 $form.append(`<input type="hidden" name="invoice_amounts[]" value="${invoice.amount}">`);
             });

             return true;
         }*/
    },

    printPreview: function (id) {
        window.open(`/${PAYMENT.baseUrl}/${id}/print`, '_blank');
    },

    downloadPDF: function (id) {
        window.open(`/${PAYMENT.baseUrl}/${id}/download`, '_blank');
    }
};
