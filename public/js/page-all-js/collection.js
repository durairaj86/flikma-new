COLLECTION = {
    title: 'Collection',
    baseUrl: 'transaction/collections',
    actionUrl: 'transaction/collections',
    currentTab: 'all',
    load() {
        COLLECTION.form.load();
        datepicker();
        COLLECTION.filter.load();
        FILTER.filteredColumn();
    },
    filter: {
        load() {
            $('#apply-filter').off().on('click', function () {
                COLLECTION.list.dataTable();
                FILTER.filteredColumn();
            });
        },
        default() {
            let data = {};
            new URLSearchParams($('#list-filter').serialize()).forEach((value, key) => {
                data[key] = data[key] ? [].concat(data[key], value) : value;
            });
            return data;
        },
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
                    data: function (d) {
                        d.tab = activeTab;
                        d.filterData = COLLECTION.filter.default();
                    },
                    dataSrc: function (json) {
                        // Update status counts
                        $('#dataTable tbody').find('.loading-row').remove();
                        GLOBAL_FN.setStatusCounts(json.statusCounts);
                        COLLECTION.list.cardSummary(json.salesSummary, json.statusCounts);
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
                        data: 'row_no', name: 'row_no', render: function (data, type, row) {
                            return '<span class="fw-bold text-primary collection-no-link" style="cursor:pointer;">' + (row.row_no ?? '') + '</span>';
                        }
                    },
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

            $('#dataTable tbody').off('click', '.collection-no-link').on('click', '.collection-no-link', function (e) {
                e.preventDefault();
                e.stopPropagation();
                let $row = $(this).closest('tr');
                COLLECTION.list.openDrawer($row.attr('data-id'), $row.attr('data-name'));
            });
        },

        cardSummary(data, counts) {
            if (data) {
                ['draft', 'approved'].forEach(k => {
                    ['grand', 'sub', 'tax'].forEach(t => $('#total_' + k + '_' + t).text(amountFormat(data['total_' + k + '_' + t] || 0)));
                });
            }
            if (counts) {
                $('#cardAllCount').text(counts.all ?? 0);
                $('#cardApprovedCount').text(counts.APPROVED ?? 0);
                $('#cardDraftCount').text(counts.DRAFT ?? 0);
            }
        },

        openDrawer(collectionId, collectionNo) {
            $('#drawerSubtitle').text(collectionNo || '');

            let drawer = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('moduleDrawer'));
            drawer.show();

            $('#moduleOverview').html('<div class="text-center py-5 text-muted"><div class="spinner-border spinner-border-sm me-2" role="status"></div> Loading...</div>');
            $.get('/' + COLLECTION.baseUrl + '/' + collectionId + '/overview-drawer', function (data) {
                $('#moduleOverview').html(data);
            }).fail(function () {
                $('#moduleOverview').html('<div class="alert alert-danger m-3">Failed to load collection details.</div>');
            });
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
                    COLLECTION.list.openDrawer(row.attr('data-id'), row.attr('data-name'));
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
                toastr.error(trans('Please provide a reason for disapproval'));
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
                toastr.error(trans('Please select at least one invoice'));
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
                    COLLECTION.form.loadCustomerCredit(customerId);
                } else {
                    $('#credit-block').hide();
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

            // Customer credit: typing an amount (or "Use all") lowers the cash to collect on the ticked invoices by that credit
            $(document).off('input.credit', '.credit-apply').on('input.credit', '.credit-apply', function () {
                const max = parseFloat($(this).data('max')) || 0;
                let v = parseFloat(String($(this).val()).replace(/,/g, '')) || 0;
                if (v > max) { v = max; $(this).val(v.toFixed(2)); }
                COLLECTION.form.applyCreditToAmounts();
            });
            $(document).off('click.credit', '#credit-use-all').on('click.credit', '#credit-use-all', function () {
                $('.credit-apply').each(function () { $(this).val((parseFloat($(this).data('max')) || 0).toFixed(2)); });
                COLLECTION.form.applyCreditToAmounts();
            });
            COLLECTION.form.refreshCreditTotal();

            // Form submission
            /*$('#moduleForm').off('submit').on('submit', function (e) {
                return COLLECTION.form.prepareFormData($(this));
            });*/
        },

        creditTotal() {
            let t = 0;
            $('.credit-apply').each(function () { t += parseFloat(String($(this).val()).replace(/,/g, '')) || 0; });
            return t;
        },
        refreshCreditTotal() {
            $('#credit-applied-total').text(COLLECTION.form.formatNumber(COLLECTION.form.creditTotal()));
        },
        // Spread the chosen credit over the ticked invoices (in order): each one's cash amount = its balance less its share of the credit.
        applyCreditToAmounts() {
            let credit = COLLECTION.form.creditTotal();
            $('.invoice-checkbox:checked').each(function () {
                const $amt = $(this).closest('tr').find('.invoice-amount');
                // the invoice's real balance is the input's max (data('balance') is overwritten as amounts are typed)
                const balance = parseFloat($amt.attr('max')) || parseFloat($amt.attr('data-balance')) || 0;
                const share = Math.min(credit, balance);
                credit -= share;
                $amt.val((balance - share).toFixed(2)).trigger('input');
                // what is left to collect after cash AND credit
                $amt.closest('tr').find('td:last').text(COLLECTION.form.formatNumber(balance - (balance - share) - share));
            });
            COLLECTION.form.refreshCreditTotal();
            COLLECTION.form.calculateTotal();
        },
        // The customer's credit list follows the customer picked on the form.
        loadCustomerCredit(customerId) {
            $.get(GLOBAL_FN.buildUrl(`${COLLECTION.baseUrl}/customer/${customerId}/credit`), {collection: $('#collection_id').val() || ''}, function (rows) {
                const $body = $('#credit-body').empty();
                (rows || []).forEach(function (r) {
                    $body.append('<tr><td>' + r.row_no + '</td><td class="text-end">' + COLLECTION.form.formatNumber(r.available) + '</td><td><input type="text" class="form-control text-end float credit-apply" name="credit_apply[' + r.invoice_id + ']" data-max="' + r.available + '" placeholder="0.00" autocomplete="off"></td></tr>');
                });
                $('#credit-block').toggle((rows || []).length > 0);
                COLLECTION.form.refreshCreditTotal();
            });
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

            // customer credit chosen: re-spread it over whatever is ticked now
            if (COLLECTION.form.creditTotal() > 0) {
                COLLECTION.form.applyCreditToAmounts();
                return;
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
                toastr.error(trans('Collection amount cannot exceed the balance amount'));
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
            COLLECTION.form.toggleCashFields(total);
        },
        // Paying only with customer credit (no cash): there is no account to collect through and no reference to give.
        toggleCashFields(cash) {
            const charges = (parseFloat(String($('#bank_charges').val()).replace(/,/g, '')) || 0) + (parseFloat(String($('#other_charges').val()).replace(/,/g, '')) || 0);
            const creditOnly = (cash + charges) <= 0.004 && COLLECTION.form.creditTotal() > 0;
            const $acct = $('select[name="account[]"], select[name="account"]').first(), $ref = $('#reference_no');
            const $labels = $acct.closest('.mb-3').find('label .text-danger').add($ref.closest('.mb-3').find('label .text-danger'));
            [$acct, $ref].forEach(function ($f) {
                if (!$f.length) return;
                $f.prop('required', !creditOnly);
                if (creditOnly) {
                    $f.removeClass('is-invalid');
                    const ts = $f[0].tomselect; if (ts) ts.wrapper.classList.remove('is-invalid');
                }
            });
            $labels.toggle(!creditOnly);
            $('#credit-only-note').toggle(creditOnly);
        },

        formatNumber(num) {
            return parseFloat(num).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        },

        /*prepareFormData($form) {
            // Validate form
            if (!$('#customer').val()) {
                toastr.error(trans('Please select a customer'));
                return false;
            }

            if ($('input[name="customer_invoice_ids[]"]:checked').length === 0) {
                toastr.error(trans('Please select at least one invoice'));
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
