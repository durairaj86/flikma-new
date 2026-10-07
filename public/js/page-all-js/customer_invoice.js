CUSTOMER_INVOICE = {
    title: 'Customer Invoice',
    baseUrl: 'invoice/customer',
    actionUrl: 'invoice/customer',
    load() {
        CUSTOMER_INVOICE.form.load();
        CUSTOMER_INVOICE.filter.load();
        datepicker();
    },
    filter: {
        load: function () {
            CUSTOMER_INVOICE.filter.filterBox();
            CUSTOMER_INVOICE.filter.searchBox();
        },
        filterBox: function () {
            $('#apply-filter').off().on({
                click: function () {
                    CUSTOMER_INVOICE.list.dataTable();
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
                        CUSTOMER_INVOICE.list.dataTable();
                        return;
                    }

                    // Otherwise, debounce the search to avoid too many requests
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(function () {
                        CUSTOMER_INVOICE.list.dataTable();
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
        //location.href = '/' + CUSTOMER_INVOICE.baseUrl + '/' + printId + '/print';
        iframe.src = '/' + CUSTOMER_INVOICE.baseUrl + '/' + printId + '/print';
    },
    downloadPDF(printId) {
        fetch('/invoice/customer/' + printId + '/print')
            .then(res => res.text())
            .then(html => {
                const container = document.createElement('div');
                //container.style.display = 'none';
                container.id = 'html-pdf';
                container.className = 'px-4 pt-4';
                container.innerHTML = html;
                //document.body.appendChild(container);
                const opt = {
                    margin: 0.2,
                    filename: `customerInvoice-${printId}.pdf`,
                };
                html2pdf().set(opt).from(container).save().finally(() => {
                    //document.body.removeChild(container);
                });
            });
    },
    list: {
        load(activeTab = null) {
            CUSTOMER_INVOICE.list.dataTable(activeTab);
        },
        dataTable(activeTab = null) {
            GLOBAL_FN.destroyDataTable();
            activeTab = (activeTab && (typeof activeTab !== 'object')) ? activeTab : $("#listTabs").find('li button.active').attr('id');
            let templates = CUSTOMER_INVOICE.list.templates;
            let table = $('#dataTable').DataTable({
                processing: false,
                serverSide: true,
                orderable: false,
                autoWidth: false,
                lengthChange: false,
                pageLength: 25,
                dom: 'rt<"row mt-2"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7 d-flex justify-content-end"p>>',
                /*order: [[1, 'desc']],*/
                ajax: {
                    url: GLOBAL_FN.buildUrl('invoice/customer/data/' + $('#new').attr('data-loader-id')),
                    type: 'POST',
                    data: function (d) {
                        d.tab = activeTab;
                        d.filterData = CUSTOMER_INVOICE.filter.default();
                    },
                    dataSrc: function (json) {
                        $('#dataTable tbody').find('.loading-row').remove();
                        GLOBAL_FN.setStatusCounts(json.statusCounts);
                        CUSTOMER_INVOICE.list.cardSummary(json.salesSummary, json.statusCounts);
                        return json.data;
                    }
                },
                columnDefs: [
                    {targets: [0], searchable: false},
                    {targets: [0, 1, 2, 3, 4, 5, 6, 7, 8], orderable: false},
                ],
                columns: [
                    {
                        data: 'row_no', render: (data, type, row) => templates.invoiceNumber(row)
                    },
                    {
                        data: 'job_no', render: (data, type, row) => templates.job(row)
                    },
                    {
                        data: 'customer_name', render: (data, type, row) => templates.customer(row)
                    },
                    {
                        data: 'sub_total', class: 'text-end', render: function (data, type, row) {
                            return '<div class="cell-primary">' + amountFormat(row.sub_total) + '</div><div class="cell-secondary">' + row.currency + '</div>';
                        }
                    },
                    {
                        data: 'tax_total', class: 'text-end', render: function (data, type, row) {
                            return '<div class="cell-primary">' + amountFormat(row.tax_total) + '</div>';
                        }
                    },
                    {
                        data: 'balance', class: 'text-end', render: (data, type, row) => templates.balance(row)
                    },
                    {
                        data: 'invoice_date', class: 'text-end', render: (data, type, row) => templates.invoice(row)
                    },
                    {
                        data: 'due_status', class: 'text-end', render: (data, type, row) => templates.aging(row)
                    },
                    /*{
                        data: 'due_status', render: function (data, type, row) {
                            if (row.status !== 'unpaid') {
                                return '<div class="text-sm text-gray-500">Due: 21-09-2025</div><small class="text-xs font-bold text-red-600 block">74 days overdue</small>';
                            }
                            return '<div class="text-sm text-gray-500">Due: 21-12-2025</div><small class="text-xs font-bold text-green-600 block">On Time</small>';
                        }
                    },*/
                    GLOBAL_FN.dataTable.optionButton()

                ],
                language: {
                    search: ""
                },
                deferLoading: 0,

                initComplete: function () {
                    CUSTOMER_INVOICE.form.open();
                    webDataTable.actions.menu();
                }
            });
            $('#customSearch').on('keyup input', window.debounceSearch(function () {
                table.search(this.value).draw();
            }));
            $('#dataTable_filter').closest('div.row').remove();
            webDataTable.loader(table);
            webDataTable.search(table);

            $('#dataTable tbody').off('click', '.customer-invoice-no-link').on('click', '.customer-invoice-no-link', function (e) {
                e.preventDefault();
                e.stopPropagation();
                let $row = $(this).closest('tr');
                CUSTOMER_INVOICE.list.openDrawer($row.attr('data-id'), $row.attr('data-name'));
            });
        },
        openDrawer(customerId, invoiceNo) {
            $('#drawerSubtitle').text(invoiceNo || '');

            let drawer = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('moduleDrawer'));
            drawer.show();

            $('#moduleOverview').html('<div class="text-center py-5 text-muted"><div class="spinner-border spinner-border-sm me-2" role="status"></div> Loading...</div>');
            $.get('/invoice/customer/' + customerId + '/overview-drawer', function (data) {
                $('#moduleOverview').html(data);
            }).fail(function () {
                $('#moduleOverview').html('<div class="alert alert-danger m-3">Failed to load invoice details.</div>');
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
                $('#cardApprovedCount').text(counts.approved ?? 0);
                $('#cardDraftCount').text(counts.draft ?? 0);
            }
        },
        templates: {
            // Every cell below follows the same 2-line convention: a bold
            // primary line, and one small muted caption underneath.
            statusBadge: {
                1: '<span class="badge bg-secondary-subtle text-secondary-emphasis">Draft</span>',
                2: '<span class="badge bg-info-subtle text-info-emphasis">Sent</span>',
                3: '<span class="badge bg-success-subtle text-success-emphasis">Approved</span>',
                4: '<span class="badge bg-danger-subtle text-danger-emphasis">Rejected</span>',
                5: '<span class="badge bg-danger-subtle text-danger-emphasis">Cancelled</span>',
                6: '<span class="badge bg-primary-subtle text-primary-emphasis">Converted</span>',
            },
            invoiceNumber: (row) => `<div class="cell-primary fw-bold text-primary customer-invoice-no-link" style="cursor:pointer;">${row.row_no ?? ''}</div><div class="mt-1">${CUSTOMER_INVOICE.list.templates.statusBadge[row.status] ?? ''}</div>`,

            job: (row) => `<div class="cell-primary">${row.job_no ?? '—'}</div><div class="cell-secondary">${row.job_activity ?? ''}</div>`,

            customer: (row) => `<div class="cell-primary">${row.customer?.name_en ?? ''}</div><div class="cell-secondary">${row.customer?.row_no ?? ''}</div>`,

            invoice: (row) => `<div class="cell-primary">${row.invoice_date}</div><div class="cell-secondary">Due ${row.due_at}</div>`,

            aging: (row) => {
                const grand = parseFloat(String(row.grand_total).replace(/,/g, '')) || 0;
                const paid = parseFloat(row.paid_amount) || 0;
                // Overdue/due-days aging is meaningless once an invoice is fully
                // settled — show a plain "Paid" indicator instead.
                if (grand > 0 && paid >= grand) {
                    return `<span class="badge bg-success-subtle text-success border border-opacity-10 px-2 py-1" style="font-size: 0.65rem;">PAID</span>`;
                }
                const badge = `<span class="badge ${row.due_days.class} border border-opacity-10 px-2 py-1" style="font-size: 0.65rem;">${row.due_days.label}</span>`;
                // Settlement rate only makes sense for approved invoices — draft/cancelled
                // invoices have no meaningful collection progress to show.
                if (row.status !== 3) return badge;
                return badge + '<div class="mt-1 d-flex justify-content-end">' + CUSTOMER_INVOICE.list.templates.settlementRate(row) + '</div>';
            },

            // due_status from the API is a hardcoded stub (always "unpaid"),
            // so paid/unpaid is computed here from the real totals instead.
            balance: (row) => {
                const grand = parseFloat(String(row.grand_total).replace(/,/g, '')) || 0;
                const paid = parseFloat(row.paid_amount) || 0;
                const isPaid = grand > 0 && paid >= grand;
                return `<div class="cell-primary">${row.balance}</div><div class="cell-secondary ${isPaid ? 'text-success' : 'text-danger'}">${isPaid ? 'Paid' : 'Unpaid'}</div>`;
            },

            settlementRate: (row) => {
                const grand = parseFloat(String(row.grand_total).replace(/,/g, '')) || 0;
                const paid = parseFloat(row.paid_amount) || 0;
                const rate = grand > 0 ? Math.min(100, (paid / grand) * 100) : 0;
                const color = rate >= 100 ? '#16a34a' : rate > 0 ? '#f59e0b' : '#dc2626';
                return `<div class="d-flex align-items-center gap-2">
                            <div class="progress" style="height:5px;width:50px;">
                                <div class="progress-bar" role="progressbar" style="width:${rate.toFixed(0)}%;background:${color};"></div>
                            </div>
                            <small class="fw-semibold" style="min-width:30px;color:${color};font-size:0.65rem;">${rate.toFixed(0)}%</small>
                        </div>`;
            },
        },
        extraActions(row) {
            CUSTOMER_INVOICE.list.actions.statusChange(row);
            CUSTOMER_INVOICE.list.actions.view(row);
            CUSTOMER_INVOICE.list.actions.email(row);
            CUSTOMER_INVOICE.list.actions.recordPayment(row);
            CUSTOMER_INVOICE.list.actions.paymentHistory(row);
        },
        actions: {
            statusChange(row) {
                $('#row_pending,#row_approved,#row_rejected').off().on('click', function () {
                    let fd = new FormData();
                    changeCustomerStatus(GLOBAL_FN.buildUrl('invoice/customer/' + row.attr('data-id') + '/status/' + $(this).attr('data-value')), {
                        method: 'POST',
                        data: fd,
                        callBack: 'datatable'
                    }, $(this).attr('data-value'));
                })
                $('#row_converted').off().on('click', function () {
                    alert("convert to invoice");
                })
            },
            view(row) {
                $('#row_view').off().on('click', function () {
                    CUSTOMER_INVOICE.list.openDrawer(row.attr('data-id'), row.attr('data-name'));
                });
            },
            email(row) {
                $('#row_email').off().on('click', function () {
                    let drawer = new bootstrap.Offcanvas(document.getElementById('sendEmailDrawer'));
                    drawer.show();
                });
            },
            recordPayment(row) {
                $('#row_record_payment').off().on('click', function () {
                    CUSTOMER_INVOICE.recordPayment(row.attr('data-id'));
                });
            },
            paymentHistory(row) {
                $('#row_payment_history').off().on('click', function () {
                    let invoiceId = row.attr('data-id');

                    let drawer = new bootstrap.Offcanvas(document.getElementById('paymentHistoryDrawer'));
                    drawer.show();

                    $('#paymentHistoryBody').html('<p>Loading...</p>');
                    $.get('/invoice/customer/' + invoiceId + '/payment-history', function (data) {
                        $('#paymentHistoryBody').html(data);
                    });
                });
            }
        }
    },
    /**
     * Opens the Collection ("Record Payment") modal pre-selected for this
     * invoice's customer, with this invoice pre-checked at its outstanding
     * balance. Reuses the existing Collection create modal/controller as-is —
     * this only supplies the invoiceId query param it reads.
     */
    recordPayment(invoiceId) {
        // Close the drawer first if this was triggered from within it
        const historyDrawerEl = document.getElementById('paymentHistoryDrawer');
        const historyDrawer = bootstrap.Offcanvas.getInstance(historyDrawerEl);
        if (historyDrawer) historyDrawer.hide();

        webModal.openGlobalModal({
            title: 'Record Payment',
            url: GLOBAL_FN.buildUrl('transaction/collections/create'),
            content: {
                invoiceId: invoiceId
            },
            size: 'xl',
            scroll: true,
            minHeight: 'min-height:70vh;',
        });
    },
    form: {
        load() {
            CUSTOMER_INVOICE.form.open();
        },
        open() {
            $('#new').off().on('click', function () {
                webModal.openGlobalModal({
                    title: 'New Customer Invoice',
                    url: GLOBAL_FN.buildUrl('invoice/customer/create'),
                    content: {
                        jobId: $(this).attr('data-loader-id')
                    },
                    size: 'xl',
                    scroll: false,
                });
            })
        },
        openCallback() {
            CUSTOMER_INVOICE.form.addRow();
            CUSTOMER_INVOICE.form.removeRow();
            CUSTOMER_INVOICE.form.customer.change();
            CUSTOMER_INVOICE.form.customer.invoiceDateChange();
            CUSTOMER_INVOICE.form.jobCost.bind();
            CALCULATION.load();
            CALCULATION.finalTotals();
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
                //PROFORMA_INVOICE.form.removeRow();
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
                    // $(this).closest('tr').find('input, select').val('');
                    $tr.find('input,textarea').val('');
                    $tr.find('select').each(function () {
                        $(this).val('');
                        if ($(this).hasClass('selectpicker')) {
                            $(this).selectpicker('destroy').addClass('selectpicker');
                            console.log($(this).attr('id'));
                            selectPicker('#' + $(this).closest('table').attr('id'));
                        }
                    });
                }
                CALCULATION.finalTotals();
            })
        },
        // Job cost: the supplier invoices raised against the selected job. Choosing one copies its
        // lines into the invoice rows (editable — add your margin on the price).
        jobCost: {
            bills: {},
            added: new Set(),
            bind() {
                const self = CUSTOMER_INVOICE.form.jobCost;
                const $job = $('select[name=job_id]');
                self.added = new Set();
                $job.off('change.jobCost').on('change.jobCost', function () {
                    self.added = new Set();
                    self.load($(this).val());
                });
                $('#jobCostSelect').off('change.jobCost').on('change.jobCost', function () {
                    const id = $(this).val();
                    if (!id) return;
                    self.addBill(id);
                    const ts = this.tomselect;
                    if (ts) ts.clear(true);
                });
                if ($job.val()) self.load($job.val());
            },
            load(jobId) {
                const self = CUSTOMER_INVOICE.form.jobCost;
                const wrap = $('#jobCostWrap');
                const sel = document.getElementById('jobCostSelect');
                self.bills = {};
                if (sel && sel.tomselect) { sel.tomselect.clear(true); sel.tomselect.clearOptions(); }
                if (!jobId) { wrap.addClass('d-none'); return; }
                $.get(GLOBAL_FN.buildUrl('invoice/customer/job/' + jobId + '/costs'), function (list) {
                    if (!list.length) { wrap.addClass('d-none'); return; }
                    list.forEach(b => { self.bills[b.id] = b; });
                    if (sel && sel.tomselect) {
                        list.forEach(b => {
                            sel.tomselect.addOption({
                                value: String(b.id),
                                text: b.row_no + ' — ' + (b.supplier || '') + ' — ' + b.grand_total.toFixed(2) + ' ' + (b.currency || '') + (b.status === 1 ? ' (draft)' : '')
                            });
                        });
                        sel.tomselect.refreshOptions(false);
                    }
                    wrap.removeClass('d-none');
                });
            },
            addBill(id) {
                const self = CUSTOMER_INVOICE.form.jobCost;
                const bill = self.bills[id];
                if (!bill) return;
                if (self.added.has(String(id))) {
                    toastr.warning(trans('This supplier invoice is already added.'));
                    return;
                }
                const $tbody = $('#' + MODULE + '-tbody');
                const blank = ($tr) => !$tr.find('select[name="description_id[]"]').val() && !(parseFloat(String($tr.find('.unit_price').val() || '').replace(/,/g, '')) > 0);
                bill.lines.forEach(line => {
                    let $row;
                    const $first = $tbody.find('tr:first');
                    if ($tbody.find('tr').length === 1 && blank($first)) {
                        $row = $first;                       // reuse the empty starter row
                    } else {
                        $row = $first.clone();
                        $row.find('input, select, textarea').val('');
                        $row.find('select').removeClass('tomselected').removeClass('ts-hidden-accessible');
                        $row.find('div.ts-wrapper').remove();
                        initTomSelectForm($row);
                        $tbody.append($row);
                    }
                    const setSel = (name, val) => {
                        const el = $row.find('select[name="' + name + '"]')[0];
                        if (el && el.tomselect && val !== null && val !== undefined) el.tomselect.setValue(String(val), false);
                    };
                    setSel('description_id[]', line.description_id);
                    setSel('account[]', line.account_id);
                    setSel('unit_id[]', line.unit_id);
                    setSel('tax[]', line.tax_code);
                    $row.find('textarea[name="comment[]"]').val((bill.row_no + (line.comment ? ' — ' + line.comment : '')));
                    $row.find('.quantity').val(line.quantity).trigger('input');
                    $row.find('.unit_price').val(line.unit_price).trigger('input');
                });
                self.added.add(String(id));
                CALCULATION.finalTotals();
                toastr.success(bill.row_no + ' ' + trans('added to the invoice lines.'));
            },
        },
        customer: {
            // Recomputes Due Date = Invoice Date + (selected customer's credit
            // days). Shared by both the customer-selection handler (job pick
            // fills #customer, which fires this) and the invoice-date handler
            // below (so editing the invoice date after a customer is already
            // selected keeps the due date in sync instead of leaving it stale).
            recalcDueDate() {
                const selectedOption = $('#customer').find('option:selected');
                let creditDays = parseInt(selectedOption.data('credit-days'), 10) || 0;
                const invoiceInput = document.getElementById('invoice_date');

                if (invoiceInput && invoiceInput._flatpickr) {
                    const invoiceDate = invoiceInput._flatpickr.selectedDates[0];
                    if (invoiceDate) {
                        let dueDate = new Date(invoiceDate);
                        dueDate.setDate(dueDate.getDate() + creditDays);

                        const dueDateInput = document.getElementById('due_date');
                        if (dueDateInput && dueDateInput._flatpickr) {
                            dueDateInput._flatpickr.setDate(dueDate);
                        }
                    }
                }
            },
            invoiceDateChange() {
                // #invoice_date is initialized by the generic datepicker()
                // (public/js/startup.js), whose onChange is a shared no-op —
                // flatpickr still fires a native 'change' on the original
                // input for compatibility, so listen there instead of
                // touching the shared initializer.
                $('#invoice_date').off('change.dueDate').on('change.dueDate', function () {
                    if ($('#customer').find('option:selected').val()) {
                        CUSTOMER_INVOICE.form.customer.recalcDueDate();
                    }
                });
            },
            change() {
                $('#customer').change(function () {
                    const selectedOption = $(this).find('option:selected');

                    // --- 1. HANDLE DUE DATE CALCULATION ---
                    CUSTOMER_INVOICE.form.customer.recalcDueDate();

                    // --- 2. HANDLE CURRENCY UPDATE & DISABLE ---
                    // --- 2. HANDLE CURRENCY UPDATE & DISABLE ---
                    let customerCurrency = selectedOption.data('currency');
                    let currencySelect = document.querySelector('#currency-code');

                    if (currencySelect && customerCurrency) {
                        // 1. Destroy TomSelect instance to allow manipulation
                        if (currencySelect.tomselect) {
                            currencySelect.tomselect.destroy();
                        }

                        // 2. Set the value
                        $(currencySelect).val(customerCurrency);

                        // 3. TRIGGER THE CHANGE EVENT
                        // This will execute any code bound to $('#currency-code').change(...)
                        $(currencySelect).trigger('change');

                        // 4. Disable the element
                        currencySelect.disabled = true;

                        // 5. Re-initialize TomSelect (it will inherit the disabled state)
                        if (typeof initTomSelectSearch === "function") {
                            initTomSelectSearch('#currency-code');
                        }
                    }
                });
            }
        }
    },
}
