CREDIT_NOTE = {
    title: 'Credit Note',
    baseUrl: 'adjustment/credit-note',
    actionUrl: 'adjustment/credit-note',
    load() {
        CREDIT_NOTE.form.load();
        CREDIT_NOTE.filter.load();
        CREDIT_NOTE.list.load('all');
    },
    filter: {
        load: function () {
            CREDIT_NOTE.filter.filterBox();
            CREDIT_NOTE.filter.searchBox();
            CREDIT_NOTE.filter.tabClick();
        },
        filterBox: function () {
            $('#apply-filter').off().on({
                click: function () {
                    CREDIT_NOTE.list.dataTable();
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
                        CREDIT_NOTE.list.dataTable();
                        return;
                    }
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(function() {
                        CREDIT_NOTE.list.dataTable();
                    }, 500);
                }
            });
        },
        tabClick: function () {
            $('#listTabs .status-btn').off().on('click', function () {
                CREDIT_NOTE.list.dataTable();
            });
        },
        default: function () {
            let data = {}, tab = $("#listTabs").find('li button.active').attr('id');
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
        iframe.src = '/' + CREDIT_NOTE.baseUrl + '/' + printId + '/print';
    },
    list: {
        load(activeTab) {
            CREDIT_NOTE.list.dataTable(activeTab);
        },
        dataTable(activeTab = null) {
            GLOBAL_FN.destroyDataTable();
            activeTab = (activeTab && (typeof activeTab !== 'object')) ? activeTab : $("#listTabs").find('li button.active').attr('id');
            let templates = CREDIT_NOTE.list.templates;
            let table = $('#dataTable').DataTable({
                processing: false,
                serverSide: true,
                orderable: false,
                autoWidth: false,
                lengthChange: false,
                pageLength: 25,
                dom: 'rt<"row mt-2"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7 d-flex justify-content-end"p>>',
                ajax: {
                    url: GLOBAL_FN.buildUrl('adjustment/credit-note/data'),
                    type: 'POST',
                    data: function(d) {
                        d.tab = activeTab;
                        d.filterData = CREDIT_NOTE.filter.default();
                    },
                    dataSrc: function (json) {
                        $('#dataTable tbody').find('.loading-row').remove();
                        // Updates the KPI cards (#draftCount/#approvedCount/#cancelledCount/#allCount) —
                        // left untouched, do not repoint these ids to the tabs.
                        GLOBAL_FN.setStatusCounts(json.statusCounts);

                        // Update KPI summary
                        if (json.salesSummary) {
                            var s = json.salesSummary;
                            $('#overall_sales').text(CREDIT_NOTE.list.formatNumber(s.overall_sales || 0));
                            $('#draftTotal').text(CREDIT_NOTE.list.formatNumber(s.total_draft_grand || 0));
                            $('#approvedTotal').text(CREDIT_NOTE.list.formatNumber(s.total_approved_grand || 0));
                        }

                        // Update the tab counts — distinct ids from the KPI
                        // cards above (tabXCount vs XCount) so the two don't collide.
                        if (json.statusCounts) {
                            var c = json.statusCounts;
                            $('#tabDraftCount').text(c.DRAFT || 0);
                            $('#tabApprovedCount').text(c.APPROVED || 0);
                            $('#tabCancelledCount').text(c.CANCELLED || 0);
                            $('#tabAllCount').text(c.all ?? ((c.DRAFT || 0) + (c.APPROVED || 0) + (c.CANCELLED || 0)));
                        }

                        return json.data;
                    }
                },
                columnDefs: [
                    {targets: [0, 1, 2, 3, 4, 5, 6, 7, 8], orderable: false},
                ],
                columns: [
                    {
                        data: 'row_no', render: (data, type, row) => templates.creditNoteNumber(row)
                    },
                    {
                        data: 'customer', render: (data, type, row) => templates.customer(row)
                    },
                    {
                        data: 'job_no', render: (data, type, row) => templates.job(row)
                    },
                    {
                        data: 'invoice_no', render: function (data, type, row) {
                            return '<div class="cell-primary">' + (row.invoice_no ?? '—') + '</div>';
                        }
                    },
                    {
                        // sub_total/tax_total/grand_total are already
                        // number_format()-ed server-side via editColumn, so
                        // they must not be re-formatted client-side — the
                        // old formatNumber() ran parseFloat() on an already
                        // comma-formatted string like "10,434.78", which
                        // JS truncates at the first comma (silently wrong
                        // amounts on any row ≥ 1,000, not just "NaN").
                        data: 'sub_total', class: 'text-end', render: function (data, type, row) {
                            return '<div class="cell-primary">' + (row.sub_total ?? '0.00') + '</div><div class="cell-secondary">' + (row.currency ?? '') + '</div>';
                        }
                    },
                    {
                        data: 'tax_total', class: 'text-end', render: function (data, type, row) {
                            return '<div class="cell-primary">' + (row.tax_total ?? '0.00') + '</div>';
                        }
                    },
                    {
                        data: 'grand_total', class: 'text-end', render: function (data, type, row) {
                            return '<div class="cell-primary fw-semibold">' + (row.grand_total ?? '0.00') + '</div>';
                        }
                    },
                    {
                        data: 'posted_at', render: function (data, type, row) {
                            return '<div class="cell-primary">' + (row.posted_at ?? '') + '</div>';
                        }
                    },
                    GLOBAL_FN.dataTable.optionButton()
                ],
                language: {
                    search: ""
                },
                initComplete: function () {
                    CREDIT_NOTE.form.open();
                    webDataTable.actions.menu();
                }
            });
            $('#dataTable_filter').closest('div.row').remove();
            webDataTable.loader(table);
            webDataTable.search(table);
        },
        formatNumber(num) {
            return parseFloat(num || 0).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        },
        templates: {
            // Every cell below follows the same 2-line convention used on
            // the customer/supplier/proforma invoice lists: a bold primary
            // line, and one small muted caption underneath.
            statusBadge: {
                1: '<span class="badge bg-secondary-subtle text-secondary-emphasis">Draft</span>',
                2: '<span class="badge bg-success-subtle text-success-emphasis">Approved</span>',
                3: '<span class="badge bg-danger-subtle text-danger-emphasis">Cancelled</span>',
            },
            creditNoteNumber: (row) => `<div class="cell-primary">#${row.row_no ?? ''}</div><div class="mt-1">${CREDIT_NOTE.list.templates.statusBadge[row.status] ?? ''}</div>`,

            customer: (row) => `<div class="cell-primary">${row.customer?.name_en ?? '—'}</div><div class="cell-secondary">${row.customer?.row_no ?? ''}</div>`,

            job: (row) => `<div class="cell-primary">${row.job_no ?? '—'}</div><div class="cell-secondary">${row.job?.shipment_mode ?? ''}</div>`,
        },
        extraActions(row) {
            CREDIT_NOTE.list.actions.statusChange(row);
            CREDIT_NOTE.list.actions.view(row);
            CREDIT_NOTE.list.actions.email(row);
        },
        actions: {
            statusChange(row) {
                $('#row_pending,#row_approved,#row_rejected').off().on('click', function () {
                    let fd = new FormData();
                    changeCustomerStatus(GLOBAL_FN.buildUrl('adjustment/credit-note/' + row.attr('data-id') + '/status/' + $(this).attr('data-value')), {
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
                    let recordId = row.attr('data-id');
                    let recordName = row.attr('data-name') || 'Credit Note';

                    // Update drawer subtitle
                    $('#drawerSubtitle').text(recordName);

                    // Reset to overview tab
                    let overviewTab = document.getElementById('cn-overview-tab');
                    if (overviewTab) {
                        bootstrap.Tab.getOrCreateInstance(overviewTab).show();
                    }

                    // Clear other tabs
                    $('#cnItemsContent, #cnDocumentsContent').html('');

                    // Show drawer
                    let drawer = new bootstrap.Offcanvas(document.getElementById('moduleDrawer'));
                    drawer.show();

                    // Load overview content
                    $('#moduleOverview').html('<div class="text-center py-5 text-muted"><div class="spinner-border spinner-border-sm me-2" role="status"></div> Loading...</div>');
                    $.get('/adjustment/credit-note/' + recordId + '/overview', function (data) {
                        $('#moduleOverview').html(data);
                    }).fail(function () {
                        $('#moduleOverview').html('<div class="alert alert-danger m-3">Failed to load credit note details.</div>');
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
            CREDIT_NOTE.form.open();
        },
        open() {
            $('#new').off().on('click', function () {
                webModal.openGlobalModal({
                    title: 'New Credit Note',
                    url: GLOBAL_FN.buildUrl('adjustment/credit-note/create'),
                    content: null,
                    size: 'xl',
                    scroll: false,
                });
            })
        },
        openCallback() {
            CREDIT_NOTE.form.addRow();
            CREDIT_NOTE.form.removeRow();
            CALCULATION.load();
            CALCULATION.finalTotals();
        },
        addRow() {
            $('#' + MODULE + '-tbody').off('click', '.add-row').on('click', '.add-row', function () {
                let $tbody = $(this).closest('tbody');
                let $newRow = $tbody.find('tr:first').clone();
                $newRow.find('input, select, textarea').val('');
                $newRow.find('select').removeClass('tomselected').removeClass('ts-hidden-accessible');
                $newRow.find('div.ts-wrapper').remove();
                initTomSelectForm($newRow);
                $tbody.append($newRow);
            });
        },
        removeRow() {
            $('#' + MODULE + '-tbody').off('click', '.remove-row').on('click', '.remove-row', function () {
                let $tbody = $(this).closest('tbody');
                const $tr = $(this).closest('tr');
                if ($tbody.find('tr').length > 1) {
                    $tr.remove();
                } else {
                    $tr.find('input,textarea').val('');
                    $tr.find('select').each(function () {
                        $(this).val('');
                        if ($(this).hasClass('selectpicker')) {
                            $(this).selectpicker('destroy').addClass('selectpicker');
                            selectPicker('#' + $(this).closest('table').attr('id'));
                        }
                    });
                }
                CALCULATION.finalTotals();
            })
        },
    },
}
