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
                        SUPPLIER_INVOICE.list.cardSummary(json.salesSummary, json.statusCounts);
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
                        data: 'supplier.name_en', render: (data, type, row) => templates.supplier(row)
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
                        data: 'due_at', class: 'text-end', render: (data, type, row) => templates.aging(row)
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

            $('#dataTable tbody').off('click', '.supplier-invoice-no-link').on('click', '.supplier-invoice-no-link', function (e) {
                e.preventDefault();
                e.stopPropagation();
                let $row = $(this).closest('tr');
                SUPPLIER_INVOICE.list.openDrawer($row.attr('data-id'), $row.attr('data-name'));
            });
        },
        openDrawer(supplierInvoiceId, invoiceNo) {
            $('#drawerSubtitle').text(invoiceNo || '');

            let drawer = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('moduleDrawer'));
            drawer.show();

            $('#moduleOverview').html('<div class="text-center py-5 text-muted"><div class="spinner-border spinner-border-sm me-2" role="status"></div> Loading...</div>');
            $.get('/invoice/supplier/' + supplierInvoiceId + '/overview-drawer', function (data) {
                $('#moduleOverview').html(data);
            }).fail(function () {
                $('#moduleOverview').html('<div class="alert alert-danger m-3">Failed to load invoice details.</div>');
            });
        },
        cardSummary(data, counts) {
            if (data) {
                $('#total_draft_grand').text(amountFormat(data.total_draft_grand));
                $('#total_draft_sub').text(amountFormat(data.total_draft_sub));
                $('#total_draft_tax').text(amountFormat(data.total_draft_tax));
                $('#total_approved_grand').text(amountFormat(data.total_approved_grand));
                $('#total_approved_sub').text(amountFormat(data.total_approved_sub));
                $('#total_approved_tax').text(amountFormat(data.total_approved_tax));
            }
            if (counts) {
                $('#cardAllCount').text(counts.all ?? Object.values(counts).reduce((a, b) => a + (parseInt(b) || 0), 0));
                $('#cardApprovedCount').text(counts.APPROVED ?? 0);
                $('#cardDraftCount').text(counts.DRAFT ?? 0);
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
            invoiceNumber: (row) => `<div class="cell-primary fw-bold text-primary supplier-invoice-no-link" style="cursor:pointer;">${row.row_no ?? ''}</div><div class="mt-1">${SUPPLIER_INVOICE.list.templates.statusBadge[row.status] ?? ''}</div>`,

            job: (row) => `<div class="cell-primary">${row.job_no ?? '—'}</div><div class="cell-secondary">${row.job_activity ?? ''}</div>`,

            supplier: (row) => `<div class="cell-primary">${row.supplier?.name_en ?? ''}</div><div class="cell-secondary">${row.supplier?.row_no ?? ''}</div>`,

            // due_status isn't provided by this endpoint, so paid/unpaid is
            // computed here from the real totals (same as customer invoice).
            balance: (row) => {
                const grand = parseFloat(String(row.grand_total).replace(/,/g, '')) || 0;
                const paid = parseFloat(row.paid_amount) || 0;
                const isPaid = grand > 0 && paid >= grand;
                return `<div class="cell-primary">${amountFormat(grand - paid)}</div><div class="cell-secondary ${isPaid ? 'text-success' : 'text-danger'}">${isPaid ? 'Paid' : 'Unpaid'}</div>`;
            },

            invoice: (row) => `<div class="cell-primary">${row.invoice_date ?? ''}</div><div class="cell-secondary">Due ${row.due_at ?? ''}</div>`,

            // Settlement rate only makes sense for approved invoices — draft/cancelled
            // invoices have no meaningful payment progress to show.
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

            aging: (row) => {
                const grand = parseFloat(String(row.grand_total).replace(/,/g, '')) || 0;
                const paid = parseFloat(row.paid_amount) || 0;
                // Overdue/due-days aging is meaningless once an invoice is fully
                // settled — show a plain "Paid" indicator instead.
                if (grand > 0 && paid >= grand) {
                    return `<div class="badge bg-success-subtle text-success border border-opacity-10 px-3 py-2" style="font-size: 0.65rem;">PAID</div>`;
                }
                // Prefer server-provided aging (parity with customer list)
                if (row.due_days && row.due_days.label) {
                    const cls = row.due_days.class || 'bg-secondary-subtle text-muted';
                    const label = row.due_days.label;
                    const badge = `<div class="badge ${cls} border border-opacity-10 px-3 py-2" style="font-size: 0.65rem;">${label}</div>`;
                    if (row.status !== 3) return badge;
                    return badge + '<div class="mt-1 d-flex justify-content-end">' + SUPPLIER_INVOICE.list.templates.settlementRate(row) + '</div>';
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
                    SUPPLIER_INVOICE.list.openDrawer(row.attr('data-id'), row.attr('data-name'));
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
