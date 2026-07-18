PROFORMA_INVOICE = {
    title: 'Proforma Invoice',
    baseUrl: 'invoice/proforma',
    actionUrl: 'invoice/proforma',
    load() {
        PROFORMA_INVOICE.form.load();
        PROFORMA_INVOICE.filter.load();
        datepicker();
    },
    filter: {
        load: function () {
            PROFORMA_INVOICE.filter.filterBox();
            PROFORMA_INVOICE.filter.shipmentMode();
            PROFORMA_INVOICE.filter.polPodLoad();
            PROFORMA_INVOICE.filter.jobLoad();
        },
        filterBox: function () {
            $('#apply-filter').off().on({
                click: function () {
                    PROFORMA_INVOICE.list.dataTable();
                    FILTER.filteredColumn();
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
        },
        shipmentMode() {
            $('input[name=shipment_mode],input[name=shipment_mode_2]').off().on('change', function () {
                let shipmentSelect = $('.pol-pod-select');
                if ($(this).hasClass('sync-sea')) {
                    shipmentSelect.find('.sync-sea').prop('checked', true);
                    shipmentSelect.find('.sync-air').prop('checked', false);
                } else if ($(this).hasClass('sync-air')) {
                    shipmentSelect.find('.sync-sea').prop('checked', false);
                    shipmentSelect.find('.sync-air').prop('checked', true);
                }

                let filterPol = document.querySelector('#filter-pol');
                let filterPod = document.querySelector('#filter-pod');

                filterPol.tomselect.destroy();
                filterPod.tomselect.destroy();

                PROFORMA_INVOICE.filter.polPodLoad();
            })
        },
        polPodLoad(preLoad = null) {
            initTomSelectSearch('#filter-pol', 'sea', 100, preLoad);
            initTomSelectSearch('#filter-pod', 'sea', 100, preLoad);
        },
        // Same search-as-you-type UX as POL/POD, but the submitted filter
        // value must be the plain job number text (not POL's "code - name"
        // combined value), since the backend matches it against job_no with
        // a LIKE, not against a port code/name pair.
        jobLoad() {
            const selector = '#filter-job';
            const el = document.querySelector(selector);
            if (!el) return;
            if (el.tomselect) el.tomselect.destroy();

            new TomSelect(selector, {
                valueField: 'name',
                labelField: 'name',
                searchField: 'name',
                create: false,
                placeholder: $(selector).data('placeholder') ?? '',
                maxOptions: 50,
                openOnFocus: false,
                maxItems: 1,
                allowEmptyOption: true,
                preload: false,
                hideSelected: false,
                plugins: ['dropdown_input'],
                load: function (query, callback) {
                    if (!query.length) return callback();
                    fetch(`/dropdown/search?query=${encodeURIComponent(query)}&db=job`)
                        .then(res => res.json())
                        .then(json => {
                            const data = (Array.isArray(json) ? json : [])
                                .filter(item => item && item.id !== null && item.id !== undefined)
                                .map(item => ({id: String(item.id), name: item.name, code: item.code}));
                            callback(data);
                        })
                        .catch(() => callback());
                },
                render: {
                    option: function (data, escape) {
                        const subtext = data.code ? `<div class="ts-subtext">${escape(data.code)}</div>` : '';
                        return `<div class="option" data-selectable data-value="${escape(data.id)}">${escape(data.name)}${subtext}</div>`;
                    },
                    item: function (data, escape) {
                        return `<div class="item">${escape(data.name)}</div>`;
                    }
                },
            });
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
        iframe.src = '/' + PROFORMA_INVOICE.baseUrl + '/' + printId + '/print';
    },
    downloadPDF(printId) {
        fetch('/invoice/proforma/' + printId + '/print')
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
                    filename: `proformaInvoice-${printId}.pdf`,
                };
                html2pdf().set(opt).from(container).save().finally(() => {
                    //document.body.removeChild(container);
                });
            });
    },
    list: {
        load(activeTab) {
            PROFORMA_INVOICE.list.dataTable(activeTab);
        },
        dataTable(activeTab = null) {
            GLOBAL_FN.destroyDataTable();
            activeTab = (activeTab && (typeof activeTab !== 'object')) ? activeTab : $("#listTabs").find('li button.active').attr('id');
            let templates = PROFORMA_INVOICE.list.templates;
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
                    url: GLOBAL_FN.buildUrl('invoice/proforma/data/'+ $('#new').attr('data-loader-id')),
                    type: 'POST',
                    data: function (d) {
                        // Add tab parameter
                        d.tab = activeTab;
                        d.filterData = PROFORMA_INVOICE.filter.default();
                    },
                    dataSrc: function (json) {
                        $('#dataTable tbody').find('.loading-row').remove();
                        GLOBAL_FN.setStatusCounts(json.statusCounts);
                        PROFORMA_INVOICE.list.cardSummary(json.salesSummary, json.statusCounts);
                        return json.data;
                    }
                },
                columnDefs: [
                    {targets: [0], searchable: false},
                    {targets: [0, 1, 2, 3, 4, 5, 6, 7], orderable: false},
                ],
                columns: [
                    {
                        data: 'row_no', render: (data, type, row) => templates.invoiceNumber(row)
                    },
                    {
                        data: 'job_no', render: (data, type, row) => templates.job(row)
                    },
                    {
                        data: 'customer', render: (data, type, row) => templates.customer(row)
                    },
                    {
                        // sub_total/tax_total/grand_total are already
                        // number_format()-ed server-side via editColumn (with
                        // thousands commas), so they must not be re-wrapped
                        // with amountFormat() — Intl.NumberFormat can't parse
                        // an already comma-formatted string and returns NaN.
                        data: 'sub_total', class: 'text-end', render: function (data, type, row) {
                            return '<div class="cell-primary">' + row.sub_total + '</div><div class="cell-secondary">' + row.currency + '</div>';
                        }
                    },
                    {
                        data: 'tax_total', class: 'text-end', render: function (data, type, row) {
                            return '<div class="cell-primary">' + row.tax_total + '</div>';
                        }
                    },
                    {
                        data: 'grand_total', class: 'text-end', render: function (data, type, row) {
                            return '<div class="cell-primary fw-semibold">' + row.grand_total + '</div>';
                        }
                    },
                    {
                        data: 'posted_at', render: (data, type, row) => templates.date(row)
                    },
                    GLOBAL_FN.dataTable.optionButton()

                ],
                language: {
                    search: ""
                },
                deferLoading: 0,

                initComplete: function () {
                    PROFORMA_INVOICE.form.open();
                    webDataTable.actions.menu();
                }
            });
            $('#customSearch').on('keyup', function () {
                table.search(this.value).draw();
            });
            $('#dataTable_filter').closest('div.row').remove();
            webDataTable.loader(table);
            webDataTable.search(table);

            $('#dataTable tbody').off('click', '.proforma-invoice-no-link').on('click', '.proforma-invoice-no-link', function (e) {
                e.preventDefault();
                e.stopPropagation();
                let $row = $(this).closest('tr');
                PROFORMA_INVOICE.list.openDrawer($row.attr('data-id'), $row.attr('data-name'));
            });
        },
        openDrawer(proformaId, invoiceNo) {
            $('#drawerSubtitle').text(invoiceNo || '');

            let drawer = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('moduleDrawer'));
            drawer.show();

            $('#moduleOverview').html('<div class="text-center py-5 text-muted"><div class="spinner-border spinner-border-sm me-2" role="status"></div> Loading...</div>');
            $.get('/invoice/proforma/' + proformaId + '/overview-drawer', function (data) {
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
            invoiceNumber: (row) => `<div class="cell-primary fw-bold text-primary proforma-invoice-no-link" style="cursor:pointer;">${row.row_no ?? ''}</div><div class="mt-1">${PROFORMA_INVOICE.list.templates.statusBadge[row.status] ?? ''}</div>`,

            job: (row) => `<div class="cell-primary">${row.job_no ?? '—'}</div><div class="cell-secondary">${row.job_activity ?? ''}</div>`,

            // A proforma invoice's customer_id is only populated when it was
            // created against a job (store() only sets it `if ($job)`), so
            // row.customer can be null — guard every access.
            customer: (row) => `<div class="cell-primary">${row.customer?.name_en ?? '—'}</div><div class="cell-secondary">${row.customer?.row_no ?? ''}</div>`,

            date: (row) => `<div class="cell-primary">${row.posted_at ?? ''}</div>`,
        },
        extraActions(row) {
            PROFORMA_INVOICE.list.actions.statusChange(row);
            PROFORMA_INVOICE.list.actions.view(row);
            PROFORMA_INVOICE.list.actions.email(row);
        },
        actions: {
            statusChange(row) {
                $('#row_pending,#row_approved,#row_rejected').off().on('click', function () {
                    let fd = new FormData();
                    changeCustomerStatus(GLOBAL_FN.buildUrl('invoice/proforma/' + row.attr('data-id') + '/status/' + $(this).attr('data-value')), {
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
                    PROFORMA_INVOICE.list.openDrawer(row.attr('data-id'), row.attr('data-name'));
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
            PROFORMA_INVOICE.form.open();
        },
        open() {
            $('#new').off().on('click', function () {
                webModal.openGlobalModal({
                    title: 'New Proforma Invoice',
                    url: GLOBAL_FN.buildUrl('invoice/proforma/create'),
                    content: {
                        jobId: $(this).attr('data-loader-id')
                    },
                    size: 'xl',
                });
            })
        },
        openCallback() {
            PROFORMA_INVOICE.form.addRow();
            PROFORMA_INVOICE.form.removeRow();
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
