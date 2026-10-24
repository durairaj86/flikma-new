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
            const jobScoped = $('#new').attr('data-loader-id') !== 'list';
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
                columnDefs: [{targets: '_all', orderable: false}, {targets: [0], searchable: false}],
                columns: [
                    {data: 'row_no', render: (data, type, row) => templates.invoiceNumber(row)},
                    // The Job column is only useful on the all-invoices list; opened from a job the job is already named above.
                    ...(jobScoped ? [] : [{data: 'job_no', render: (data, type, row) => templates.job(row)}]),
                    {data: 'customer_name', render: (data, type, row) => templates.customer(row)},
                    {data: 'grand_total', class: 'text-end', render: (data, type, row) => templates.amount(row)},
                    {data: 'balance', class: 'text-end', render: (data, type, row) => templates.balance(row)},
                    {data: 'due_at', class: 'text-end', render: (data, type, row) => templates.due(row)},
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
            $('#dataTable').attr('data-empty-custom', '1');
            // Empty list: say who/what was searched instead of a bare "No data available in table".
            $('#dataTable').off('draw.dt.empty').on('draw.dt.empty', function () {
                const cell = $(this).find('td.dataTables_empty');
                if (!cell.length || cell.find('.ci-empty').length) return;
                const names = $('#customers option:selected').map(function () { return $.trim($(this).text()); }).get().filter(n => n && n !== '--Select--');
                const from = $('#filter-from-date').val(), to = $('#filter-to-date').val();
                const esc = t => $('<span>').text(t).html();
                const period = (from && to) ? ' <span class="ci-empty-period">(' + esc(from) + ' &ndash; ' + esc(to) + ')</span>' : '';
                const title = names.length
                    ? '<b>' + esc(names.join(', ')) + '</b> ' + trans('has no invoices for the selected period')
                    : trans('No invoices found for the selected period');
                cell.html('<div class="ci-empty">' +
                    '<span class="ci-empty-icon"><i class="bi bi-receipt-cutoff"></i></span>' +
                    '<h6>' + title + period + '</h6>' +
                    '<p>' + trans('Try a different period or customer, or create a new invoice.') + '</p>' +
                    '<div class="d-flex justify-content-center gap-2 flex-wrap">' +
                    '<button type="button" class="btn btn-outline-secondary rounded-pill px-3 ci-empty-filter"><i class="bi bi-funnel me-1"></i>' + trans('Change filters') + '</button>' +
                    '<button type="button" class="btn btn-primary rounded-pill px-3 ci-empty-new"><i class="bi bi-plus-lg me-1"></i>' + trans('Create Invoice') + '</button>' +
                    '</div></div>');
            });
            $(document).off('click.ciEmpty').on('click.ciEmpty', '.ci-empty-new', function () { $('#new').trigger('click'); })
                .on('click.ciEmpty', '.ci-empty-filter', function () { $('#filter-box').trigger('click'); });
            $('#dataTable_filter').closest('div.row').remove();
            webDataTable.loader(table);
            webDataTable.search(table);

            $('#dataTable tbody').off('click', '.ci-history-link').on('click', '.ci-history-link', function (e) {
                e.preventDefault();
                e.stopPropagation();
                CUSTOMER_INVOICE.list.openHistory($(this).closest('tr').attr('data-id'));
            });

            $('#dataTable tbody').off('click', '.customer-invoice-no-link').on('click', '.customer-invoice-no-link', function (e) {
                e.preventDefault();
                e.stopPropagation();
                let $row = $(this).closest('tr');
                CUSTOMER_INVOICE.list.openDrawer($row.attr('data-id'), $row.attr('data-name'));
            });
        },
        /** The collection history drawer (same one the row menu opens). */
        openHistory(invoiceId) {
            bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('paymentHistoryDrawer')).show();
            $('#paymentHistoryBody').html('<p>Loading...</p>');
            $.get('/invoice/customer/' + invoiceId + '/payment-history', function (data) {
                $('#paymentHistoryBody').html(data);
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
                // the split bar of the Summary card: approved vs draft share of the invoices that are one or the other
                const ap = Number(counts.approved ?? 0), dr = Number(counts.draft ?? 0), sum = ap + dr;
                $('#cardApprovedBar').css('width', sum ? (ap / sum * 100) + '%' : '0');
                $('#cardDraftBar').css('width', sum ? (dr / sum * 100) + '%' : '0');
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
            // One idea per column, each with a bold main line and a small grey line.
            // a draft has no real number yet: it just says "Draft" (no number, no second Draft badge)
            invoiceNumber: (row) => `<div class="cell-primary fw-bold text-primary customer-invoice-no-link" style="cursor:pointer;">${Number(row.status) === 1 ? trans('Draft') : (row.row_no ?? '')}</div>
                <div class="d-flex align-items-center gap-2 mt-1">${Number(row.status) === 1 ? '' : (CUSTOMER_INVOICE.list.templates.statusBadge[row.status] ?? '')}<span class="cell-secondary">${row.invoice_date ?? ''}</span></div>`,

            job: (row) => row.job_no
                ? `<div class="cell-primary">${row.job_no}</div><div class="cell-secondary">${row.job_activity && row.job_activity !== '-' ? row.job_activity : ''}</div>`
                : '<span class="text-muted">—</span>',

            customer: (row) => `<div class="cell-primary">${row.customer?.name_en ?? ''}</div><div class="cell-secondary">${row.customer?.row_no ?? ''}</div>`,

            // Total to pay, with how it is made up underneath.
            amount: (row) => `<div class="cell-primary">${row.grand_total} <span class="cell-secondary">${row.currency ?? ''}</span></div>
                <div class="cell-secondary">${trans('Excl. VAT')} ${amountFormat(row.sub_total)} + ${trans('VAT')} ${amountFormat(row.tax_total)}</div>`,

            // What is still owed, and whether anything has been paid.
            balance: (row) => {
                const grand = parseFloat(String(row.grand_total).replace(/,/g, '')) || 0;
                const paid = parseFloat(row.paid_amount) || 0;
                if (row.status !== 3) return '<span class="text-muted">—</span>';
                const settled = grand > 0 && paid >= grand;
                const part = paid > 0 && !settled;
                const label = settled ? trans('Paid') : (part ? trans('Part paid') : trans('Unpaid'));
                const color = settled ? 'text-success' : (part ? 'text-warning-emphasis' : 'text-danger');
                // Paid / Part paid: click to see the collections behind it.
                const caption = (settled || part)
                    ? `<a href="#" class="cell-secondary ${color} ci-history-link" title="${trans('Collection History')}"><i class="bi bi-clock-history me-1"></i>${label}</a>`
                    : `<span class="cell-secondary ${color}">${label}</span>`;
                return `<div class="cell-primary ${settled ? 'text-success' : ''}">${settled ? '0.00' : row.balance}</div><div>${caption}</div>`;
            },

            // Due date, and how late or how soon, in plain words. Only approved, unpaid invoices have a due status.
            due: (row) => {
                const grand = parseFloat(String(row.grand_total).replace(/,/g, '')) || 0;
                const paid = parseFloat(row.paid_amount) || 0;
                if (row.status !== 3) return `<div class="cell-secondary">${row.due_at ?? ''}</div>`;
                if (grand > 0 && paid >= grand) return `<div class="cell-secondary">${row.due_at ?? ''}</div><span class="badge bg-success-subtle text-success-emphasis">${trans('Paid')}</span>`;
                const d = row.due_days || {};
                const text = String(d.label || '').toLowerCase().replace(/^overdue: (\d+) days?$/, (m, n) => n + ' ' + trans('days overdue')).replace(/^open: (\d+) days left$/, (m, n) => n + ' ' + trans('days left'))
                    .replace('due: today', trans('Due today')).replace('due: tomorrow', trans('Due tomorrow'));
                return `<div class="cell-primary">${row.due_at ?? ''}</div><span class="badge ${d.class || ''}" style="font-size:.68rem;">${text}</span>`;
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
                    CUSTOMER_INVOICE.list.openHistory(row.attr('data-id'));
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
        // the normal Collection form in the sub floatbar beside the invoice (like a linked record opens)
        window.openLinkedForm({
            title: trans('Collection'),
            url: GLOBAL_FN.buildUrl('transaction/collections/create'),
            params: {invoiceId: invoiceId},
            onReady: function () { if (typeof COLLECTION !== 'undefined') COLLECTION.form.openCallback(); },
            afterSave: function () { CUSTOMER_INVOICE.refreshOpenDrawer(invoiceId); },
        });
    },
    // after a collection / credit note is saved from the drawer: show the invoice again with its new balance
    refreshOpenDrawer(invoiceId) {
        const el = document.getElementById('moduleDrawer');
        if (el && el.classList.contains('show')) {
            $.get('/invoice/customer/' + invoiceId + '/overview-drawer', function (data) { $('#moduleOverview').html(data); });
        }
    },
    // the side drawers (invoice details, payment history) must not sit over a form that opens from them
    closeDrawers() {
        document.querySelectorAll('.offcanvas.show').forEach(function (el) {
            const inst = bootstrap.Offcanvas.getInstance(el);
            if (inst) inst.hide();
        });
    },
    /**
     * "Credit Note" button of the invoice drawer: opens the New Credit Note form on top of this page, already pointing at
     * this invoice (customer, job and all its lines filled in). Uses the credit note form as it is.
     */
    newCreditNote(invoiceId) {
        window.openLinkedForm({
            title: trans('New Credit Note'),
            url: GLOBAL_FN.buildUrl('adjustment/credit-note/create'),
            params: {invoiceId: invoiceId},
            onReady: function () { if (typeof CREDIT_NOTE !== 'undefined') CREDIT_NOTE.form.openCallback(); },
            afterSave: function () { CUSTOMER_INVOICE.refreshOpenDrawer(invoiceId); },
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
                $newRow.find('.cost-hint').addClass('d-none').text('');
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
        // Pick up from the job: its quotation (selling lines, provisional) and its supplier invoices (cost lines, actual).
        // Choosing one copies its lines into the invoice rows; every copied line remembers its source and, for supplier
        // lines, its cost, so the invoice can show the margin and the reports can compare cost with revenue.
        jobCost: {
            bills: {},
            quotes: {},
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
                    const v = $(this).val();
                    if (!v) return;
                    self.add(v);
                    const ts = this.tomselect;
                    if (ts) ts.clear(true);
                });
                // keep the cost hints and the margin line in step with what is typed
                const $tb = $('#' + MODULE + '-tbody');
                $tb.off('input.margin change.margin').on('input.margin change.margin', '.quantity, .unit_price', self.refresh);
                $tb.off('click.margin').on('click.margin', '.remove-row', function () { setTimeout(self.refresh, 60); });
                $(document).off('input.marginRate').on('input.marginRate', '#currency-rate', self.refresh);
                self.refresh();
                if ($job.val()) self.load($job.val());
            },
            load(jobId) {
                const self = CUSTOMER_INVOICE.form.jobCost;
                const wrap = $('#jobCostWrap');
                const sel = document.getElementById('jobCostSelect');
                self.bills = {};
                self.quotes = {};
                if (sel && sel.tomselect) { sel.tomselect.clear(true); sel.tomselect.clearOptions(); sel.tomselect.clearOptionGroups && sel.tomselect.clearOptionGroups(); }
                if (!jobId) { wrap.addClass('d-none'); return; }
                const invoiceId = $('input[name="data-id"]').val() || '';
                $.get(GLOBAL_FN.buildUrl('invoice/customer/job/' + jobId + '/costs'), {invoice: invoiceId}, function (res) {
                    const quotes = res.quotations || [], bills = res.bills || [];
                    if (!quotes.length && !bills.length) { wrap.addClass('d-none'); return; }
                    const money = (n) => Number(n).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    const billed = (x) => x.billed_in && x.billed_in.length ? ' · ' + trans('billed in') + ' ' + x.billed_in.join(', ') : '';
                    if (sel && sel.tomselect) {
                        const ts = sel.tomselect;
                        if (quotes.length) ts.addOptionGroup('q', {label: trans('Quotation') + ' (' + trans('selling price') + ')'});
                        if (bills.length) ts.addOptionGroup('s', {label: trans('Supplier invoices') + ' (' + trans('cost') + ')'});
                        quotes.forEach(q => {
                            self.quotes[q.id] = q;
                            ts.addOption({value: 'q:' + q.id, optgroup: 'q', text: q.row_no + ' — ' + money(q.total) + ' SAR', subtext: (q.lines.length + ' ' + trans('lines')) + billed(q)});
                        });
                        bills.forEach(b => {
                            self.bills[b.id] = b;
                            ts.addOption({value: 's:' + b.id, optgroup: 's', text: b.row_no + ' — ' + (b.supplier || '') + ' — ' + money(b.grand_total) + ' ' + (b.currency || '') + (b.status === 1 ? ' (draft)' : ''), subtext: billed(b).replace(/^ · /, '')});
                        });
                        ts.refreshOptions(false);
                    }
                    wrap.removeClass('d-none');
                });
            },
            add(v) {
                const self = CUSTOMER_INVOICE.form.jobCost;
                const kind = v.charAt(0), id = v.slice(2);
                const doc = kind === 'q' ? self.quotes[id] : self.bills[id];
                if (!doc) return;
                const key = v;
                if (self.added.has(key)) {
                    toastr.warning(trans(kind === 'q' ? 'This quotation is already added.' : 'This supplier invoice is already added.'));
                    return;
                }
                if (doc.billed_in && doc.billed_in.length &&
                    !window.confirm(doc.row_no + ' ' + trans('is already billed in') + ' ' + doc.billed_in.join(', ') + '.\n' + trans('Add it again?'))) {
                    return;
                }
                const $tbody = $('#' + MODULE + '-tbody');
                const rate = parseFloat(String($('#currency-rate').val() || '1').replace(/,/g, '')) || 1;
                const blank = ($tr) => !$tr.find('select[name="description_id[]"]').val() && !(parseFloat(String($tr.find('.unit_price').val() || '').replace(/,/g, '')) > 0);
                doc.lines.forEach(line => {
                    let $row;
                    const $first = $tbody.find('tr:first');
                    if ($tbody.find('tr').length === 1 && blank($first)) {
                        $row = $first;                       // reuse the empty starter row
                    } else {
                        $row = $first.clone();
                        $row.find('input, select, textarea').val('');
                        $row.find('.cost-hint').addClass('d-none').text('');
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
                    const note = kind === 'q' ? (line.comment || '') : (doc.row_no + (line.comment ? ' — ' + line.comment : ''));
                    $row.find('textarea[name="comment[]"]').val(note);
                    const price = kind === 'q' ? (line.unit_price_local / rate) : line.unit_price;
                    $row.find('.quantity').val(line.quantity).trigger('input');
                    $row.find('.unit_price').val(Number(price).toFixed(2)).trigger('input');
                    // remember the source (and, for a supplier line, the cost behind it)
                    $row.find('[name="source_type[]"]').val(kind === 'q' ? 'quotation' : 'supplier_invoice');
                    $row.find('[name="source_id[]"]').val(doc.id);
                    $row.find('[name="source_sub_id[]"]').val(line.source_sub_id);
                    $row.find('[name="cost_base_amount[]"]').val(kind === 'q' ? '' : line.cost_base);
                });
                self.added.add(key);
                CALCULATION.finalTotals();
                self.refresh();
                toastr.success(doc.row_no + ' ' + trans('added to the invoice lines.'));
            },
            // cost hint under each picked supplier line + the margin line under the totals
            refresh() {
                const rate = parseFloat(String($('#currency-rate').val() || '1').replace(/,/g, '')) || 1;
                const num = (v) => parseFloat(String(v || '').replace(/,/g, '')) || 0;
                const fmt = (n) => Number(n).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                let cost = 0, billed = 0;
                $('#' + MODULE + '-tbody tr').each(function () {
                    const $r = $(this), c = $r.find('[name="cost_base_amount[]"]').val();
                    const $h = $r.find('.cost-hint');
                    if (c === '' || c === undefined || $r.find('[name="source_type[]"]').val() !== 'supplier_invoice') { $h.addClass('d-none').text(''); return; }
                    const line = num($r.find('.quantity').val()) * num($r.find('.unit_price').val()) * rate;   // company currency
                    const cb = num(c);
                    cost += cb; billed += line;
                    const pct = cb > 0 ? ((line - cb) / cb * 100) : null;
                    const cls = line - cb < 0 ? 'text-danger' : 'text-success';
                    // "2 × 300.00 = 600.00": what the supplier charged per unit, in company currency, and the total behind this line
                    const q = num($r.find('.quantity').val());
                    const calc = q > 0 ? (fmt(q).replace(/\.00$/, '') + ' × ' + fmt(cb / q) + ' = ' + fmt(cb)) : fmt(cb);
                    $h.removeClass('d-none').html('<span class="text-muted text-nowrap" title="' + trans('Cost') + '">' + calc + '</span> <span class="text-nowrap ' + cls + '">' + (pct === null ? '' : (pct >= 0 ? '+' : '') + pct.toFixed(0) + '%') + '</span>');
                });
                const $m = $('#marginRow');
                if (cost > 0) {
                    const margin = billed - cost;
                    $('#marginCost').text(fmt(cost));
                    $('#marginBilled').text(fmt(billed));
                    $('#marginValue').text(fmt(margin)).toggleClass('text-danger', margin < 0).toggleClass('text-success', margin >= 0);
                    $m.removeClass('d-none');
                } else { $m.addClass('d-none'); }
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
