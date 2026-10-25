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
                    {
                        // Amount = everything that settled the invoices (cash + customer credit). With credit applied, the split is shown underneath.
                        data: 'grand_total', name: 'grand_total', render: function (data, type, row) {
                            const cash = parseFloat(String(data == null ? '' : data).replace(/,/g, '')) || 0;
                            const credit = parseFloat(row.credit_applied) || 0;
                            if (credit <= 0.004 || type !== 'display') return data;
                            let h = '<div class="fw-bold">' + amountFormat(cash + credit) + '</div>';
                            if (cash > 0.004) h += '<div class="small text-success">' + trans('Collection') + ' ' + amountFormat(cash) + '</div>';
                            h += '<div class="small" style="color:#0891b2;">' + trans('Credit applied') + ' ' + amountFormat(credit) + '</div>';
                            return h;
                        }
                    },
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
                // the split bar of the Summary card: approved vs draft share of the documents that are one or the other
                const ap = Number(counts.APPROVED ?? 0), dr = Number(counts.DRAFT ?? 0), sum = ap + dr;
                $('#cardApprovedBar').css('width', sum ? (ap / sum * 100) + '%' : '0');
                $('#cardDraftBar').css('width', sum ? (dr / sum * 100) + '%' : '0');
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

            // Customer credit, per invoice: typing credit on an invoice lowers its cash by that credit
            $(document).off('input.creditTo', '.credit-to').on('input.creditTo', '.credit-to', function () {
                COLLECTION.form.creditInput($(this));
            });
            // cash typed by hand can never push cash + credit above the invoice's balance
            $(document).off('input.creditCash', '.invoice-amount').on('input.creditCash', '.invoice-amount', function () {
                if (COLLECTION.form._busy) return;
                const $row = $(this).closest('tr'), credit = COLLECTION.form.num($row.find('.credit-to').val());
                if (credit <= 0) return;
                const balance = COLLECTION.form.rowBalance($row);
                if (COLLECTION.form.num($(this).val()) + credit > balance + 0.004) {
                    COLLECTION.form.setRowCash($row, balance - credit, credit);
                } else {
                    $row.find('td:last').text(COLLECTION.form.formatNumber(Math.max(0, balance - COLLECTION.form.num($(this).val()) - credit)));
                }
                COLLECTION.form.calculateTotal();
            });
            $(document).off('click.credit', '#credit-use-all').on('click.credit', '#credit-use-all', function () {
                COLLECTION.form.useAllCredit();
            });
            COLLECTION.form.refreshCreditTotal();

            // Form submission
            /*$('#moduleForm').off('submit').on('submit', function (e) {
                return COLLECTION.form.prepareFormData($(this));
            });*/
        },

        // ---- customer credit, chosen PER INVOICE (the "Credit Applied" column) ----
        num(v) { return parseFloat(String(v == null ? '' : v).replace(/,/g, '')) || 0; },
        // money arithmetic in whole cents: 151.80 - 50 is 101.80, never 101.80000000000001
        money(v) { return Math.round((Number(v) + Number.EPSILON) * 100) / 100; },
        creditPool() {
            let t = 0;
            $('#credit-body tr').each(function () { t += parseFloat($(this).data('available')) || 0; });
            return t;
        },
        // credit typed on the ticked invoices
        creditTotal() {
            let t = 0;
            $('.invoice-checkbox:checked').each(function () { t += COLLECTION.form.num($(this).closest('tr').find('.credit-to').val()); });
            return t;
        },
        refreshCreditTotal() {
            const pool = COLLECTION.form.creditPool(), used = COLLECTION.form.creditTotal();
            $('#credit-available-total').text(COLLECTION.form.formatNumber(pool));
            $('#credit-applied-total').text(COLLECTION.form.formatNumber(used));
            $('#credit-left-total').text(COLLECTION.form.formatNumber(Math.max(0, pool - used)));
        },
        // the invoice's real balance is the amount input's max (data('balance') is overwritten as amounts are typed)
        rowBalance($row) {
            const $a = $row.find('.invoice-amount');
            return parseFloat($a.attr('max')) || parseFloat($a.attr('data-balance')) || 0;
        },
        // Credit typed on one invoice: capped to what that invoice owes and to the credit still available; the cash to collect is the rest.
        creditInput($input) {
            const $row = $input.closest('tr'), balance = COLLECTION.form.rowBalance($row);
            let v = COLLECTION.form.num($input.val());
            let others = 0;
            $('.credit-to').not($input).each(function () {
                if ($(this).closest('tr').find('.invoice-checkbox').is(':checked')) others += COLLECTION.form.num($(this).val());
            });
            const room = Math.max(0, COLLECTION.form.creditPool() - others);
            const cap = Math.min(balance, room);
            if (v > cap + 0.004) {
                v = COLLECTION.form.money(cap);
                $input.val(v > 0 ? v.toFixed(2) : '');
                toastr.warning(trans('Only') + ' ' + COLLECTION.form.formatNumber(cap) + ' ' + trans('of credit can be used on this invoice.'));
            }
            COLLECTION.form.setRowCash($row, balance - v, v);
            COLLECTION.form.refreshCreditTotal();
            COLLECTION.form.calculateTotal();
        },
        // set the cash amount of a row, and show what is left to collect after cash and credit
        setRowCash($row, cash, credit) {
            const $amt = $row.find('.invoice-amount'), balance = COLLECTION.form.rowBalance($row);
            COLLECTION.form._busy = true;
            $amt.val(COLLECTION.form.money(Math.max(0, cash)).toFixed(2)).trigger('input');
            COLLECTION.form._busy = false;
            $row.find('td:last').text(COLLECTION.form.formatNumber(Math.max(0, COLLECTION.form.money(balance - Math.max(0, cash) - credit))));
        },
        // "Use all credit": hand the available credit to the ticked invoices, in order
        useAllCredit() {
            let left = COLLECTION.form.creditPool();
            $('.invoice-checkbox').each(function () {
                const $row = $(this).closest('tr'), $c = $row.find('.credit-to');
                if (!this.checked) { $c.val(''); return; }
                const take = Math.min(left, COLLECTION.form.rowBalance($row));
                left = COLLECTION.form.money(left - take);
                $c.val(take > 0 ? COLLECTION.form.money(take).toFixed(2) : '');
                COLLECTION.form.setRowCash($row, COLLECTION.form.rowBalance($row) - take, take);
            });
            COLLECTION.form.refreshCreditTotal();
            COLLECTION.form.calculateTotal();
        },
        // Paying only with customer credit (no cash): there is no account to collect through and no reference to give.
        toggleCashFields(cash) {
            const charges = (parseFloat(String($('#bank_charges').val()).replace(/,/g, '')) || 0) + (parseFloat(String($('#other_charges').val()).replace(/,/g, '')) || 0);
            const creditOnly = (cash + charges) <= 0.004 && COLLECTION.form.creditTotal() > 0 && $('.invoice-checkbox:checked').length > 0;
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
        // The customer's credit follows the customer picked on the form.
        loadCustomerCredit(customerId) {
            $.get(GLOBAL_FN.buildUrl(`${COLLECTION.baseUrl}/customer/${customerId}/credit`), {collection: $('#collection_id').val() || ''}, function (rows) {
                const $body = $('#credit-body').empty();
                (rows || []).forEach(function (r) {
                    $body.append('<tr data-available="' + r.available + '"><td>' + r.row_no + '</td><td class="text-end">' + COLLECTION.form.formatNumber(r.available) + '</td></tr>');
                });
                $('#credit-block').toggle((rows || []).length > 0);
                $('#invoices-table').toggleClass('has-credit', (rows || []).length > 0);
                $('.credit-to').val('');
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
                        const amount = balanceAmount > 0 ? Number(balanceAmount).toFixed(2) : '0.00';   // money: two decimals

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
                                    <input type="text" step="0.01" data-decimal="2" class="form-control invoice-amount float text-end" name="invoice_amounts[${invoice.id}]" data-id="${invoice.id}" value="${amount}" min="0" max="${Number(balanceAmount).toFixed(2)}" data-balance="${Number(balanceAmount).toFixed(2)}" ${!isChecked ? 'disabled' : ''}>
                                </td>
                                <td class="text-end credit-col">
                                    <input type="text" step="0.01" data-decimal="2" class="form-control credit-to float text-end" name="credit_to[${invoice.id}]" data-id="${invoice.id}" placeholder="0.00" autocomplete="off" disabled>
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

            // the credit box of a row only works while the invoice is ticked; unticking gives the credit back
            const $credit = $checkbox.closest('tr').find('.credit-to');
            $credit.prop('disabled', !isChecked);
            if (!isChecked && $credit.val() !== '') {
                $credit.val('');
                amountInput.val(COLLECTION.form.rowBalance($checkbox.closest('tr')).toFixed(2));
                $checkbox.closest('tr').find('td:last').text(COLLECTION.form.formatNumber(COLLECTION.form.rowBalance($checkbox.closest('tr'))));
            }
            COLLECTION.form.refreshCreditTotal();
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
            const creditOnly = (cash + charges) <= 0.004 && COLLECTION.form.creditTotal() > 0 && $('.invoice-checkbox:checked').length > 0;
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

    // Print opens the browser's print preview straight away (hidden frame), instead of opening the receipt in a new tab.
    printPreview: function (id) {
        let frame = document.getElementById('print-frame');
        if (!frame) {
            frame = document.createElement('iframe');
            frame.id = 'print-frame';
            frame.style.cssText = 'display:none;';
            document.body.appendChild(frame);
        }
        frame.onload = function () {
            try {
                frame.contentWindow.focus();
                frame.contentWindow.print();
            } catch (e) {
                window.open(`/${COLLECTION.baseUrl}/${id}/print`, '_blank');   // the frame cannot be printed here: fall back to a tab
            }
        };
        frame.src = `/${COLLECTION.baseUrl}/${id}/print`;
    },

    downloadPDF: function (id) {
        window.open(`/${COLLECTION.baseUrl}/${id}/download`, '_blank');
    }
};
