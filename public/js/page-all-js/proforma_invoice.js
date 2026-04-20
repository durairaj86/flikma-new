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
                            return '<strong>' + row.row_no + '</strong>';
                        }
                    },
                    {data: 'job_no'},
                    /*{
                        data: 'job_no', render: function (data, type, row) {
                            return '<div>' + row.job_no + '</div><div class="small text-muted">Code: ' + row.job.shipment_mode + '</div>';
                        }
                    },*/
                    {
                        data: 'customer.name_en', render: function (data, type, row) {
                            return '<div>' + row.customer.name_en + '</div><div class="small text-muted">Code: ' + row.customer.row_no + '</div>';
                        }
                    },
                    {
                        data: 'currency', render: function (data, type, row) {
                            if (row.currency == baseCurrency) {
                                return '<div>' + row.currency + '</div>';
                            } else {
                                return '<div>' + row.currency + ' → SAR</div><small class="text-muted">1 ' + row.currency + ' = ' + row.currency_rate + ' ' + baseCurrency + '</small>';
                            }
                        }
                    },
                    {
                        data: 'base_sub_total', render: function (data, type, row) {
                            return '<div class="text-end text-secondary">' + row.base_sub_total + '</div><div class="text-end"><small class="text-muted">' + baseCurrency + '</small></div>';
                        }
                    },
                    {
                        data: 'base_tax_total', render: function (data, type, row) {
                            return '<div class="text-end text-secondary">' + row.base_tax_total + '</div><div class="text-end"><small class="text-muted">' + baseCurrency + '</small></div>';
                        }
                    },
                    {
                        data: 'sub_total', render: function (data, type, row) {
                            return '<div class="text-end">' + row.sub_total + '</div><div class="text-end"><small>' + row.currency + '</small></div>';
                        }
                    },
                    {
                        data: 'tax_total', render: function (data, type, row) {
                            return '<div class="text-end">' + row.tax_total + '</div><div class="text-end"><small>' + row.currency + '</small></div>';
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
                    let customerId = row.attr('data-id');

                    // Open drawer

                    let drawer = new bootstrap.Offcanvas(document.getElementById('moduleDrawer'));
                    drawer.show();

                    // Load Overview
                    $('#moduleOverview').html('<p>Loading...</p>');
                    $.get('/invoice/proforma/' + customerId + '/overview', function (data) {
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
