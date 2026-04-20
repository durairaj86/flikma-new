CREDIT_NOTE = {
    title: 'Credit Note',
    baseUrl: 'adjustment/credit-note',
    actionUrl: 'adjustment/credit-note',
    load() {
        CREDIT_NOTE.form.load();
        CREDIT_NOTE.filter.load();
        datepicker();
    },
    filter: {
        load: function () {
            CREDIT_NOTE.filter.filterBox();
            CREDIT_NOTE.filter.searchBox();
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
                    // If Enter key is pressed, search immediately
                    if (e.key === 'Enter') {
                        clearTimeout(searchTimeout);
                        CREDIT_NOTE.list.dataTable();
                        return;
                    }

                    // Otherwise, debounce the search to avoid too many requests
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(function() {
                        CREDIT_NOTE.list.dataTable();
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
        iframe.src = '/' + CREDIT_NOTE.baseUrl + '/' + printId + '/print';
    },
    downloadPDF(printId) {
        fetch('/adjustment/credit-note/' + printId + '/print')
            .then(res => res.text())
            .then(html => {
                const container = document.createElement('div');
                //container.style.display = 'none';
                container.id = 'html-pdf';
                container.className = 'px-4 pt-4';
                container.innerHTML = html;
                console.log(container);
                //document.body.appendChild(container);
                const opt = {
                    margin: 0.2,
                    filename: `creditNote-${printId}.pdf`,
                };
                html2pdf().set(opt).from(container).save().finally(() => {
                    //document.body.removeChild(container);
                });
            });
    },
    list: {
        load(activeTab) {
            CREDIT_NOTE.list.dataTable(activeTab);
        },
        dataTable(activeTab = null) {
            GLOBAL_FN.destroyDataTable();
            activeTab = (activeTab && (typeof activeTab !== 'object')) ? activeTab : $("#listTabs").find('li button.active').attr('id');
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
                    url: GLOBAL_FN.buildUrl('adjustment/credit-note/data'),
                    type: 'POST',
                    data: function(d) {
                        d.tab = activeTab;
                        d.filterData = CREDIT_NOTE.filter.default();
                    },
                    dataSrc: function (json) {
                        $('#dataTable tbody').find('.loading-row').remove();
                        GLOBAL_FN.setStatusCounts(json.statusCounts);

                        // Update summary data
                        if (json.salesSummary) {
                            $('#overall_sales').text(CREDIT_NOTE.list.formatNumber(json.salesSummary.overall_sales || 0));
                            $('#total_draft_grand').text(CREDIT_NOTE.list.formatNumber(json.salesSummary.total_draft_grand || 0));
                            $('#total_draft_sub').text(CREDIT_NOTE.list.formatNumber(json.salesSummary.total_draft_sub || 0));
                            $('#total_draft_tax').text(CREDIT_NOTE.list.formatNumber(json.salesSummary.total_draft_tax || 0));
                            $('#total_approved_grand').text(CREDIT_NOTE.list.formatNumber(json.salesSummary.total_approved_grand || 0));
                            $('#total_approved_sub').text(CREDIT_NOTE.list.formatNumber(json.salesSummary.total_approved_sub || 0));
                            $('#total_approved_tax').text(CREDIT_NOTE.list.formatNumber(json.salesSummary.total_approved_tax || 0));
                        }

                        return json.data;
                    }
                },
                columnDefs: [
                    {targets: [0], searchable: false},
                    {targets: [0, 1, 2, 3, 4, 5, 6, 7, 8], orderable: false},
                ],
                columns: [
                    {
                        data: 'row_no', render: function (data, type, row) {
                            return '<strong>' + row.row_no + '</strong>';
                        }
                    },
                    {
                        data: 'customer.name_en', render: function (data, type, row) {
                            return '<div>' + row.customer.name_en + '</div><div class="small text-muted">Code: ' + row.customer.row_no + '</div>';
                        }
                    },
                    {data: 'job_no'},
                    {data: 'invoice_no'},
                    {
                        data: 'sub_total', render: function (data, type, row) {
                            return '<div class="text-end text-secondary">' + row.sub_total + '</div>';
                        }
                    },
                    {
                        data: 'tax_total', render: function (data, type, row) {
                            return '<div class="text-end text-secondary">' + row.tax_total + '</div>';
                        }
                    },
                    {
                        data: 'grand_total', render: function (data, type, row) {
                            return '<div class="text-end fw-semibold">' + row.grand_total + '</div><div class="text-end"><small>' + row.currency + '</small></div>';
                        }
                    },
                    {data: 'posted_at'},
                    GLOBAL_FN.dataTable.optionButton()

                ],
                language: {
                    search: ""
                },
                deferLoading: 0,

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
            return parseFloat(num).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
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
                    let customerId = row.attr('data-id');

                    // Open drawer

                    let drawer = new bootstrap.Offcanvas(document.getElementById('moduleDrawer'));
                    drawer.show();

                    // Load Overview
                    $('#moduleOverview').html('<p>Loading...</p>');
                    $.get('/adjustment/credit-note/' + customerId + '/overview', function (data) {
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
    },
}
