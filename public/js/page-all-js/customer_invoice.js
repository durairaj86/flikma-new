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
                    {targets: [0, 1, 2, 3, 4, 5, 6, 7, 8], orderable: false},
                    /*{targets: [0, 1, 2, 3, 4, 5, 6, 7, 8], class: 'px-3 py-2 text-nowrap'},*/
                ],
                columns: [
                    /*{
                        data: 'row_no', render: function (data, type, row) {
                            let text = '<span class="badge bg-danger-subtle text-muted me-2 text-xsmall">Cancelled</span>';
                            if (row.status == 1) {
                                $class = 'text-muted';
                                text = ''
                            } else if (row.status == 3) {
                                $class = 'main-text fw-bold';
                                text = '<span class="badge bg-primary-subtle text-success  me-2 text-xsmall">Approved</span>';
                            } else {
                                $class = 'text-danger';
                            }
                            if (row.tax_submit_status == 4) {
                                text = '<span class="badge bg-success-subtle text-success me-2 text-xsmall">Cleared</span>';
                            } else if (row.tax_submit_status == 5) {
                                text = '<span class="badge bg-warning-subtle text-success me-2 text-xsmall">Reported</span>';
                            }
                            return '<div class="' + $class + ' pb-1">' + row.row_no + '</div>' + text + '<!--<span class="badge rounded-pill bg-success-subtle text-success text-xsmall">Paid</span>-->';
                        }
                    },*/
                    {
                        data: 'row_no',
                        class: 'ps-4 ',
                        style: 'border-left: 6px solid #0d6efd; border-top-left-radius: 10px; border-bottom-left-radius: 10px;',
                        render: (data, type, row) => templates.rowInfo(data, row)
                    },
                    {
                        data: 'customer_name', render: (data, type, row) => templates.customer(row)
                    },
                    {
                        data: 'customer_name', render: (data, type, row) => templates.polPod(row)
                    },
                    /*{
                        data: 'customer.name_en', render: function (data, type, row) {
                            return '<div class="fw-bold">' + row.job_no + '</div><div class="text-secondary small">' + row.job_activity + '</div>';
                        }
                    },*/
                    /*{
                        data: 'job.pol', render: function (data, type, row) {
                            let pol_pod = '';
                            if (row.job.pol) {
                                pol_pod = '<span class="text-secondary small me-1">POL:</span><span>' + row.job.pol + '</span>';
                            }
                            if (row.job.pod) {
                                if (pol_pod) {
                                    pol_pod += '<br>';
                                }
                                return pol_pod + '<span class="text-secondary small me-1">POD:</span><span>' + row.job.pod + '</span>';
                            }
                            return pol_pod;
                        }
                    },*/
                    /*{
                        data: 'currency', render: function (data, type, row) {
                            if (row.currency == baseCurrency) {
                                return '<div>' + row.currency + '</div>';
                            } else {
                                return '<div>' + row.currency + ' → SAR</div><small class="text-muted">1 ' + row.currency + ' = ' + row.currency_rate + ' ' + baseCurrency + '</small>';
                            }
                        }
                    },*/
                    /*{
                        data: 'base_total', render: function (data, type, row) {
                            return '<div class="text-end text-secondary">' + row.base_total + '</div><div class="text-end"><small class="text-muted">' + baseCurrency + '</small></div>';
                        }
                    },*/
                    {
                        data: 'sub_total', class: 'text-end', render: function (data, type, row) {
                            return '<div class="fw-bold text-dark">' + amountFormat(row.sub_total) + '</div><div class="text-muted x-small">' + row.currency + '</div>';
                        }
                    },
                    {
                        data: 'tax_total', class: 'text-end', render: function (data, type, row) {
                            return '<div class="fw-medium text-muted">' + amountFormat(row.tax_total) + '</div>';
                        }
                    },
                    {
                        data: 'balance', class: 'text-end', render: function (data, type, row) {
                            return '<div class="fw-bold text-danger fs-6">' + row.balance + '</div>';
                        }
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
            rowInfo: (data, row) => {
                let text = '<span class="badge bg-danger-subtle text-muted me-2 text-xsmall">Cancelled</span>';
                if (row.status == 1) {
                    $class = 'text-muted';
                    text = '<span class="badge bg-secondary-subtle me-2 text-xsmall">Draft</span>';
                } else if (row.status == 3) {
                    $class = 'main-text fw-bold';
                    text = '<span class="badge bg-primary-subtle text-success me-2 text-xsmall">Approved</span>';
                } else {
                    $class = 'text-danger';
                }
                if (row.tax_submit_status == 4) {
                    text = '<span class="badge bg-success-subtle text-success me-2 text-xsmall">Cleared</span>';
                } else if (row.tax_submit_status == 5) {
                    text = '<span class="badge bg-warning-subtle text-success me-2 text-xsmall">Reported</span>';
                }

                return `<div class="fw-bold text-dark mb-0">${row.row_no}</div><div class="text-muted" style="font-size: 0.7rem;">JOB: <span class="text-primary fw-bold">${row.job_no}</span></div><div class="${$class} pb-1">` + text + `</div>`
            },

            customer: (row) => `<div class="lh-sm"><div class="small fw-bold text-dark">${row.customer.name_en}</div><div class="text-muted" style="font-size: 0.7rem;">${row.customer.row_no}</div></div>`,
            polPod: (row) => `<div class="d-flex align-items-center gap-2">
                                    <span class="fw-bold text-dark">${row.job.pol_code}</span>
                                    <i class="bi ${row.job.shipment_mode == 'air' ? 'bi-airplane' : 'bi-truck'} text-primary" style="font-size: 0.8rem;"></i>
                                    <span class="fw-bold text-dark">${row.job.pod_code}</span>
                                </div><div class="x-small text-muted mt-1">Flight: ${row.job.carrier}</div>`,
            invoice: (row) => `<div class="lh-sm">
                                    <div class="x-small text-muted text-uppercase">Inv: <span class="text-dark fw-medium">${row.invoice_date}</span></div>
                                    <div class="x-small text-muted text-uppercase mt-1">Due: <span class="text-danger fw-bold">${row.due_at}</span></div>
                                </div>`,
            aging: (row) => `<div class="badge ${row.due_days.class} border border-opacity-10 px-3 py-2" style="font-size: 0.65rem;">
                                    ${row.due_days.label}
                                </div>`,
            //<div class="text-info fw-medium" style="font-size: 0.7rem;"><i class="bi bi-person-check me-1"></i>Ops: Anil S.</div>
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
