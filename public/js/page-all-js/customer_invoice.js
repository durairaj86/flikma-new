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
                        CUSTOMER_INVOICE.list.cardSummary(json.salesSummary);
                        return json.data;
                    }
                },
                columnDefs: [
                    {targets: [0], searchable: false},
                    {targets: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9], orderable: false},
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
                        data: 'customer_name', render: (data, type, row) => templates.polPod(row)
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
            $('#customSearch').on('keyup', function () {
                table.search(this.value).draw();
            });
            $('#dataTable_filter').closest('div.row').remove();
            webDataTable.loader(table);
            webDataTable.search(table);
        },
        cardSummary(data) {
            $('#overall_sales').text(amountFormat(data.overall_sales));
            $('#total_draft_grand').text(amountFormat(data.total_draft_grand));
            $('#total_draft_sub').text(amountFormat(data.total_draft_sub));
            $('#total_draft_tax').text(amountFormat(data.total_draft_tax));
            $('#total_approved_grand').text(amountFormat(data.total_approved_grand));
            $('#total_approved_sub').text(amountFormat(data.total_approved_sub));
            $('#total_approved_tax').text(amountFormat(data.total_approved_tax));
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
            invoiceNumber: (row) => `<div class="cell-primary">${row.row_no ?? ''}</div><div class="mt-1">${CUSTOMER_INVOICE.list.templates.statusBadge[row.status] ?? ''}</div>`,

            job: (row) => `<div class="cell-primary">${row.job_no ?? '—'}</div><div class="cell-secondary">${row.job_activity ?? ''}</div>`,

            customer: (row) => `<div class="cell-primary">${row.customer?.name_en ?? ''}</div><div class="cell-secondary">${row.customer?.row_no ?? ''}</div>`,

            polPod: (row) => {
                // Not every customer invoice is tied to a job (e.g. those
                // created directly, not from a job), so row.job can be null.
                if (!row.job || (!row.job.pol_code && !row.job.pod_code)) return '<span class="cell-secondary">—</span>';
                return `<div class="cell-primary d-flex align-items-center gap-1">
                            <span>${row.job.pol_code ?? ''}</span>
                            <i class="bi ${row.job.shipment_mode == 'air' ? 'bi-airplane' : 'bi-truck'} text-primary" style="font-size: 0.7rem;"></i>
                            <span>${row.job.pod_code ?? ''}</span>
                        </div><div class="cell-secondary">${row.job.carrier ?? ''}</div>`;
            },

            invoice: (row) => `<div class="cell-primary">${row.invoice_date}</div><div class="cell-secondary">Due ${row.due_at}</div>`,

            aging: (row) => `<span class="badge ${row.due_days.class} border border-opacity-10 px-2 py-1" style="font-size: 0.65rem;">${row.due_days.label}</span>`,

            // due_status from the API is a hardcoded stub (always "unpaid"),
            // so paid/unpaid is computed here from the real totals instead.
            balance: (row) => {
                const grand = parseFloat(String(row.grand_total).replace(/,/g, '')) || 0;
                const paid = parseFloat(row.paid_amount) || 0;
                const isPaid = grand > 0 && paid >= grand;
                return `<div class="cell-primary">${row.balance}</div><div class="cell-secondary ${isPaid ? 'text-success' : 'text-danger'}">${isPaid ? 'Paid' : 'Unpaid'}</div>`;
            },
        },
        extraActions(row) {
            CUSTOMER_INVOICE.list.actions.statusChange(row);
            CUSTOMER_INVOICE.list.actions.view(row);
            CUSTOMER_INVOICE.list.actions.email(row);
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
                    let customerId = row.attr('data-id');

                    // Open drawer

                    let drawer = new bootstrap.Offcanvas(document.getElementById('moduleDrawer'));
                    drawer.show();

                    // Load Overview
                    $('#moduleOverview').html('<p>Loading...</p>');
                    $.get('/invoice/customer/' + customerId + '/overview', function (data) {
                        $('#moduleOverview').html(data);
                    });
                });
            },
            email(row) {
                $('#row_email').off().on('click', function () {
                    let drawer = new bootstrap.Offcanvas(document.getElementById('sendEmailDrawer'));
                    drawer.show();
                });
            }
        }
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
        customer: {
            change() {
                $('#customer').change(function () {
                    const selectedOption = $(this).find('option:selected');

                    // --- 1. HANDLE DUE DATE CALCULATION ---
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
