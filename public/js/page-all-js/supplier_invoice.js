SUPPLIER_INVOICE = {
    title: 'Supplier Invoice',
    baseUrl: 'invoice/supplier',
    actionUrl: 'invoice/supplier',
    load() {
        SUPPLIER_INVOICE.form.load();
        SUPPLIER_INVOICE.filter.load();
        datepicker();
    },
    filter: {
        load: function () {
            SUPPLIER_INVOICE.filter.filterBox();
            SUPPLIER_INVOICE.filter.searchBox();
        },
        filterBox: function () {
            $('#apply-filter').off().on({
                click: function () {
                    SUPPLIER_INVOICE.list.dataTable();
                    FILTER.filteredColumn();
                }
            });
        },
        searchBox: function () {
            let searchTimeout;
            $('#customSearch').off().on({
                keyup: function (e) {
                    if (e.key === 'Enter') {
                        clearTimeout(searchTimeout);
                        SUPPLIER_INVOICE.list.dataTable();
                        return;
                    }
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(function () {
                        SUPPLIER_INVOICE.list.dataTable();
                    }, 500);
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
        iframe.src = '/' + SUPPLIER_INVOICE.baseUrl + '/' + printId + '/print';
    },
    downloadPDF(printId) {
        fetch('/invoice/supplier/' + printId + '/print')
            .then(res => res.text())
            .then(html => {
                const container = document.createElement('div');
                //container.style.display = 'none';
                container.id = 'html-pdf';
                container.className = 'px-4';
                container.innerHTML = html;
                console.log(container);
                //document.body.appendChild(container);
                const opt = {
                    margin: 0.2,
                    filename: `supplierInvoice-${printId}.pdf`,
                };
                html2pdf().set(opt).from(container).save().finally(() => {
                    //document.body.removeChild(container);
                });
            });
    },
    list: {
        load(activeTab) {
            SUPPLIER_INVOICE.list.dataTable(activeTab);
        },
        dataTable(activeTab = null) {
            GLOBAL_FN.destroyDataTable();
            activeTab = (activeTab && (typeof activeTab !== 'object')) ? activeTab : $("#listTabs").find('li button.active').attr('id');
            let templates = SUPPLIER_INVOICE.list.templates;
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
                    url: GLOBAL_FN.buildUrl('invoice/supplier/data/' + $('#new').attr('data-loader-id')),
                    type: 'POST',
                    data: function (d) {
                        // Add tab parameter
                        d.tab = activeTab;
                        d.filterData = SUPPLIER_INVOICE.filter.default();
                    },
                    dataSrc: function (json) {
                        $('#dataTable tbody').find('.loading-row').remove();
                        GLOBAL_FN.setStatusCounts(json.statusCounts);
                        return json.data;
                    }
                },
                columnDefs: [
                    {targets: [0], searchable: false},
                    {targets: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9], orderable: false},
                ],
                columns: [
                    {
                        data: 'row_no', render: function (data, type, row) {
                            return templates.rowInfo(data, row);
                        }
                    },
                    {data: 'invoice_number'},
                    {
                        data: 'job_no', render: function (data, type, row) {
                            return templates.jobInfo(row);
                        }
                    },
                    {
                        data: 'supplier.name_en', render: function (data, type, row) {
                            return templates.supplier(row);
                        }
                    },
                    {
                        data: 'base_total', class: 'text-end', render: function (data, type, row) {
                            return '<div class="text-end text-secondary">' + amountFormat(row.base_tax_total + row.base_sub_total) + '</div><div class="text-end"><small class="text-muted">' + baseCurrency + '</small></div>';
                        }
                    },
                    {
                        data: 'grand_total', class: 'text-end', render: function (data, type, row) {
                            return '<div class="text-end fw-semibold">' + amountFormat(row.grand_total) + '</div><div class="text-end"><small>' + row.currency + '</small></div>';
                        }
                    },
                    {
                        data: 'balance', class: 'text-end', render: function (data, type, row) {
                            return '<div class="fw-bold text-danger fs-6">' + amountFormat(parseFloat(row.grand_total) - parseFloat(row.paid_amount || 0)) + '</div>';
                        }
                    },
                    {data: 'invoice_date', class: 'text-end', render: function (data, type, row) { return templates.invoice(row); } },
                    {
                        data: 'due_at', class: 'text-end', render: function (data, type, row) {
                            return templates.aging(row);
                        }
                    },
                    GLOBAL_FN.dataTable.optionButton()

                ],
                language: {
                    search: ""
                },
                deferLoading: 0,

                initComplete: function () {
                    SUPPLIER_INVOICE.form.open();
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
        templates: {
            rowInfo: (data, row) => {
                return `<div class="fw-bold text-dark mb-0">${row.row_no}</div><div class="text-muted" style="font-size: 0.7rem;">JOB: <span class="text-primary fw-bold">${row.job_no ?? '-'}</span></div>`;
            },
            supplier: (row) => {
                const name = row.supplier && row.supplier.name_en ? row.supplier.name_en : '-';
                const code = row.supplier && row.supplier.row_no ? row.supplier.row_no : '';
                return `<div class="lh-sm"><div class="small fw-bold text-dark">${name}</div><div class="text-muted" style="font-size: 0.7rem;">${code}</div></div>`;
            },
            jobInfo: (row) => {
                const mode = row.job && row.job.shipment_mode ? row.job.shipment_mode : null;
                const icon = mode === 'air' ? 'bi-airplane' : 'bi-truck';
                const hasMode = !!mode;
                const jobNo = row.job_no ?? '-';
                return `<div class="d-flex align-items-center gap-2">${hasMode ? `<i class="bi ${icon} text-primary" style="font-size: 0.8rem;"></i>` : ''}<span class="fw-bold text-dark">${jobNo}</span></div>`;
            },
            invoice: (row) => {
                const inv = row.invoice_date ?? '';
                const due = row.due_at ?? '';
                return `<div class="lh-sm"><div class="x-small text-muted text-uppercase">Inv: <span class="text-dark fw-medium">${inv}</span></div><div class="x-small text-muted text-uppercase mt-1">Due: <span class="text-danger fw-bold">${due}</span></div></div>`;
            },
            aging: (row) => {
                // Prefer server-provided aging (parity with customer list)
                if (row.due_days && row.due_days.label) {
                    const cls = row.due_days.class || 'bg-secondary-subtle text-muted';
                    const label = row.due_days.label;
                    return `<div class="badge ${cls} border border-opacity-10 px-3 py-2" style="font-size: 0.65rem;">${label}</div>`;
                }
                // Fallback to client-side computation
                const rawDue = row.due_at;
                if (!rawDue) {
                    return `<div class="badge bg-secondary-subtle border border-opacity-10 px-3 py-2" style="font-size: 0.65rem;">No due date</div>`;
                }
                const parseDate = (str) => {
                    if (!str) return null;
                    // Try DD-MM-YYYY
                    const parts = str.split('-');
                    if (parts.length === 3) {
                        if (parts[0].length === 2 && parts[2].length === 4) {
                            return new Date(parseInt(parts[2], 10), parseInt(parts[1], 10) - 1, parseInt(parts[0], 10));
                        }
                        if (parts[0].length === 4) {
                            return new Date(str);
                        }
                    }
                    const d = new Date(str);
                    return isNaN(d.getTime()) ? null : d;
                };
                const dueDate = parseDate(rawDue);
                if (!dueDate) {
                    return `<div class=\"badge bg-secondary-subtle border border-opacity-10 px-3 py-2\" style=\"font-size: 0.65rem;\">Invalid date</div>`;
                }
                const today = new Date();
                // Reset times to midnight for diff
                dueDate.setHours(0,0,0,0);
                today.setHours(0,0,0,0);
                const diffDays = Math.round((dueDate - today) / (1000 * 60 * 60 * 24));
                let cls = 'bg-success-subtle text-success';
                let label = 'On time';
                if (diffDays < 0) {
                    cls = 'bg-danger-subtle text-danger';
                    label = `${Math.abs(diffDays)} day${Math.abs(diffDays) === 1 ? '' : 's'} overdue`;
                } else if (diffDays === 0) {
                    cls = 'bg-warning-subtle text-warning';
                    label = 'Due today';
                } else if (diffDays <= 7) {
                    cls = 'bg-warning-subtle text-warning';
                    label = `Due in ${diffDays} day${diffDays === 1 ? '' : 's'}`;
                }
                return `<div class=\"badge ${cls} border border-opacity-10 px-3 py-2\" style=\"font-size: 0.65rem;\">${label}</div>`;
            }
        },
        extraActions(row) {
            SUPPLIER_INVOICE.list.actions.statusChange(row);
            SUPPLIER_INVOICE.list.actions.view(row);
            SUPPLIER_INVOICE.list.actions.email(row);
        },
        actions: {
            statusChange(row) {
                $('#row_pending,#row_approved,#row_rejected').off().on('click', function () {
                    let fd = new FormData();
                    changeCustomerStatus(GLOBAL_FN.buildUrl('invoice/supplier/' + row.attr('data-id') + '/status/' + $(this).attr('data-value')), {
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
                    $.get('/invoice/supplier/' + customerId + '/overview', function (data) {
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
            SUPPLIER_INVOICE.form.open();
        },
        open() {
            $('#new').off().on('click', function () {
                webModal.openGlobalModal({
                    title: 'New Supplier Invoice',
                    url: GLOBAL_FN.buildUrl('invoice/supplier/create'),
                    content: {
                        jobId: $(this).attr('data-loader-id')
                    },
                    size: 'xl',
                });
            })
        },
        openCallback() {
            SUPPLIER_INVOICE.form.addRow();
            SUPPLIER_INVOICE.form.removeRow();
            CALCULATION.load();
            CALCULATION.finalTotals();
        },
        addRow() {
            // Add Package Row
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
        }
    },
}
