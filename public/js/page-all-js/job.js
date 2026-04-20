JOB = {
    title: 'Job',
    baseUrl: 'operation/job',
    actionUrl: 'operation/job',
    load() {
        JOB.form.load();
        JOB.filter.load();
        datepicker();
    },
    filter: {
        load: function () {
            JOB.filter.filterBox();
            JOB.filter.shipmentMode();
            JOB.filter.polPodLoad();
        },
        filterBox: function () {
            $('#apply-filter').off().on({
                click: function () {
                    JOB.list.dataTable();
                    /*setFilterCount();*/
                    FILTER.filteredColumn();
                }
            });
        },
        default: function (status = 0) {
            let data = {}, tab = status ?? $("#listTabs").find('li button.active').attr('id');
            //let filterData = $('#list-filter').formSerialize() + '&' + $('#individual-filter').formSerialize() + '&tab=' + tab + "&limit=25&dummy=" + $('#dataTable_length').val();
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
            //JOB.list.dataTable(tab, data);
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

                JOB.filter.polPodLoad();
            })
        },
        polPodLoad(preLoad = null) {
            let port = $('input[name=shipment_mode]:checked').val();
            initTomSelectSearch('#filter-pol', port, 100, preLoad);
            initTomSelectSearch('#filter-pod', port, 100, preLoad);
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
        iframe.src = '/' + JOB.baseUrl + '/' + printId + '/print';
    },
    downloadPDF(printId) {
        fetch('/' + JOB.baseUrl + '/' + printId + '/print')
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
                    filename: `job-${printId}.pdf`,
                };
                html2pdf().set(opt).from(container).save().finally(() => {
                    //document.body.removeChild(container);
                });
            });
    },
    list: {
        load(activeTab) {
            JOB.list.dataTable(activeTab);
        },
        dataTable(activeTab = null, filterData = null) {
            GLOBAL_FN.destroyDataTable();
            //console.log($('#list-filter').serializeArray())
            activeTab = (activeTab && (typeof activeTab !== 'object')) ? activeTab : $("#listTabs").find('li button.active').attr('id');
            let templates = JOB.list.templates;
            let table = $('#dataTable').DataTable({
                processing: false,
                serverSide: true,
                autoWidth: false,
                lengthChange: false,
                pageLength: 25,
                dom: 'rt<"row mt-2"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7 d-flex justify-content-end"p>>',
                /*order: [[1, 'desc']],*/
                ajax: {
                    url: GLOBAL_FN.buildUrl('operation/job/data'),
                    type: 'POST',
                    data: function (d) {
                        // Add tab parameter
                        d.tab = activeTab;
                        d.filterData = JOB.filter.default();
                        // Add filter parameters if they exist
                        /*const fromDate = document.getElementById('fromDate');
                        const toDate = document.getElementById('toDate');
                        const activityType = document.getElementById('activityType');
                        const status = document.getElementById('status');
                        const keyword = document.getElementById('keyword');

                        if (fromDate && fromDate.value) d.fromDate = fromDate.value;
                        if (toDate && toDate.value) d.toDate = toDate.value;
                        if (activityType && activityType.value) d.activityType = activityType.value;
                        if (status && status.value) d.status = status.value;
                        if (keyword && keyword.value) d.keyword = keyword.value;

                        return d;*/
                    },
                    dataSrc: function (json) {
                        //console.log(json);
                        // Remove loader rows when data arrives
                        $('#dataTable tbody').find('.loading-row').remove();
                        GLOBAL_FN.setStatusCounts(json.statusCounts);
                        return json.data;
                    }
                },
                columnDefs: [
                    {targets: [0], searchable: false},
                    {targets: [0, 1, 2, 3, 4, 5, 6], orderable: false},
                ],
                columns: [
                    {
                        data: 'row_no',
                        class: 'ps-4',
                        style: 'border-left: 5px solid #0d6efd; border-top-left-radius: 10px; border-bottom-left-radius: 10px;',
                        render: (data, type, row) => templates.rowInfo(data, row)
                    },
                    {
                        data: 'pol',
                        render: (data, type, row) => templates.polPod(row)
                    },
                    {
                        data: 'row_no',
                        render: (data, type, row) => templates.payload(row)
                    },
                    {
                        data: 'row_no',
                        render: (data, type, row) => templates.tracking(row)
                    },
                    {
                        data: 'invoices',
                        render: (data, type, row) => templates.consignee(row)
                    },
                    {
                        data: 'invoices', class: 'text-end',
                        render: (data, type, row) => templates.invoices(data)
                    },
                    {
                        data: 'posted_at',
                        class: 'small text-muted text-end',
                        /*render: (data, type, row) => function () {
                            console.log(row);
                        }*/
                    },
                    GLOBAL_FN.dataTable.optionButton(activeTab !== 'trashed')

                ],
                language: {
                    search: "" // removes "Search:" label
                },
                deferLoading: 0,

                initComplete: function () {
                    JOB.form.open();
                    webDataTable.actions.menu();
                }
            });
            $('#customSearch').on('keyup', function () {
                table.search(this.value).draw();
            });
            $('#dataTable_filter').closest('div.row').remove();
            webDataTable.loader(table);
            webDataTable.search(table);
            //webDataTable.actions.menu();
        },
        templates: {
            rowInfo: (data, row) => {
                const services = row.services ? row.services.split(',') : [];

                const badges = services.map(service => {
                    const s = service.trim().toLowerCase();
                    let colorClass = 'bg-primary-subtle text-primary border-primary'; // Default
                    style = 'font-size: 0.6rem;';

                    // Define color logic
                    if (s === 'transportation') {
                        colorClass = 'bg-info-subtle text-info border-info';
                    } else if (s === 'freight forwarding') {
                        colorClass = 'bg-purple-subtle text-purple border-purple'; // Ensure your CSS has .text-purple
                        style = 'font-size: 0.65rem; background-color: #f3ebff; color: #6610f2;';
                    } else if (s === 'warehousing') {
                        colorClass = 'bg-success-subtle text-success border-success';
                    }

                    return `
                <span class="badge ${colorClass} border border-opacity-10"
                      style="${style}">
                    ${service.trim()}
                </span>`;
                }).join('');

                return `<div class="fw-semibold">
        <div class="fw-bold text-dark">${data}</div>
        <div class="d-flex flex-wrap gap-1 mt-1">
        ${badges}
        </div>
        <small class="text-muted d-block mt-1" style="font-size: 0.7rem;">Mode: <i class="bi bi-airplane-engines-fill"></i> ${row.activity_id}</small>
        </div>
        <small class="text-muted d-block mt-1" style="font-size: 0.7rem;">Cust: ${row.customer.name_en}</small>`
            },

            polPod: (row) => `<div class="d-flex align-items-center gap-2"><div class="lh-1"><span class="fw-bold d-block">${row.polCode}</span><small class="text-muted" style="font-size: 0.65rem;">${row.etd}</small></div>${row.etd || row.polCode ? '<i class="bi bi-chevron-right text-muted small"></i>' : ''}<div class="lh-1"><span class="fw-bold d-block">${row.podCode}</span><small class="text-muted" style="font-size: 0.65rem;">${row.eta}</small></div></div><div class="mt-1 x-small text-secondary">${row.carrier ? '<i class="bi bi-info-circle me-1"></i>Flight: ' + row.carrier : ''}</div>`,
            payload: (row) => `<div class="small fw-bold text-dark">${row.weight ?? ''}</div><div class="text-muted" style="font-size: 0.65rem;">Vol: ${row.volume ?? '-'} ${row.no_of_pieces ? ' | ' + row.no_of_pieces : ''}</div><div class="text-muted" style="font-size: 0.65rem;">Commodity: ${row.commodity ?? '-'}</div>`,
            tracking: (row) => `<div style="font-size: 0.7rem;"><span class="text-muted text-uppercase">AWB:</span> ${row.awb_no ?? '-'}</div><div style="font-size: 0.7rem;"><div style="font-size: 0.7rem;"><span class="text-muted">Bayan:</span> ${row.clearance?.bayan_no ?? '-'}</div>`,
            consignee: (row) => `<div class="lh-1"><span class="small fw-bold d-block text-truncate" style="max-width: 150px;">${row.shipper ?? ''}</span>${row.shipper ? '<i class="bi bi-arrow-down text-muted" style="font-size: 0.7rem;"></i>' : ''}<span class="small fw-bold d-block text-truncate text-primary" style="max-width: 150px;">${row.consignee ?? ''}</span></div>`,
            invoices: (data) => `<div class="text-end"><span class="text-muted">Draft : ${data.draft}</span></div><div class="text-end"><span class="text-success">Approved : ${data.approved}</span></div>`,

            //<div class="text-info fw-medium" style="font-size: 0.7rem;"><i class="bi bi-person-check me-1"></i>Ops: Anil S.</div>
        },
        extraActions(row) {
            JOB.list.actions.statusChange(row);
            JOB.list.actions.view(row);
            JOB.list.actions.email(row);
            JOB.list.actions.delete(row);
        },
        actions: {
            statusChange(row) {
                $('#row_pending,#row_completed,#row_rejected,#row_trashed').off().on('click', function () {
                    let fd = new FormData();
                    changeCustomerStatus(GLOBAL_FN.buildUrl('operation/job/' + row.attr('data-id') + '/status/' + $(this).attr('data-value')), {
                        method: 'POST',
                        data: fd,
                        callBack: 'datatable'
                    }, $(this).attr('data-value'));
                })
                $('#customer_invoice,#supplier_invoice,#proforma_invoice').off().on('click', function () {
                    location.href = $(this).attr('data-value');
                })
            },
            view(row) {
                $('#row_view').off().on('click', function () {
                    let jobId = row.attr('data-id');

                    // Open drawer

                    let drawer = new bootstrap.Offcanvas(document.getElementById('moduleDrawer'));
                    drawer.show();

                    // Load Overview
                    $('#moduleOverview').html('<p>Loading...</p>');
                    $.get('/operation/job/' + jobId + '/overview', function (data) {
                        $('#moduleOverview').html(data);
                    });
                });
            },
            delete(row) {
                $('#row_delete').off().on('click', function () {
                    let jobId = row.attr('data-id');
                    deleteFn(GLOBAL_FN.buildUrl('operation/job/' + jobId + '/delete'), {
                        method: 'GET',
                        callBack: 'datatable'
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
            JOB.form.open();
            /*let quotationId = localStorage.getItem('convert-quotation');
            if (quotationId) {
                webModal.openGlobalModal({
                    title: 'New Job',
                    url: GLOBAL_FN.buildUrl('operation/job/create'),
                    size: 'xxl',
                    content: {
                        quotationId: quotationId
                    }
                });
                localStorage.removeItem('convert-quotation');
            }*/
        },
        open() {
            $('#new').off().on('click', function () {
                let dataTableData = $('#dataTable');
                let modelSize = dataTableData.data('model-size');
                let minHeight = dataTableData.data('min-height');
                webModal.openGlobalModal({
                    title: 'New Job',
                    url: GLOBAL_FN.buildUrl('operation/job/create'),
                    content: null,
                    minHeight: minHeight,
                    size: modelSize,
                });
            })
        },
        openCallback() {
            JOB.form.addContainer();
            JOB.form.addPackage();
            //JOB.form.removeRow();
            //JOB.form.shipmentMode();
            GLOBAL_FN.activity.activityChange();
            JOB.form.polPodLoad();
            //JOB.form.calculation.package();
        },
        /*calculation: {
            package() {
                $('#tab-packages .quantity,#tab-packages .length,#tab-packages .width,#tab-packages .height,#tab-packages .weight').off().on('change', function () {
                    let element = $(this).closest('tr');
                    let quantity = element.find('.quantity').val();
                    console.log(quantity);
                    let length = element.find('.length').val();
                    let width = element.find('.width').val();
                    let height = element.find('.height').val();
                    let weight = element.find('.weight').val();
                    let total = element.find('.total').val();
                    let volume = ((quantity * (length * width * height)) / 1000000).toFixed(8);
                    let total_weight = (quantity * weight).toFixed(3);
                    let v_weight = ((quantity * (length * width * height)) / 6000).toFixed(3);
                    let chargeable_weight = Math.max(total_weight,v_weight);
                    element.find('.volume').val(volume);
                    element.find('.total_weight').val(total_weight);
                    element.find('.chargeable_weight').val(chargeable_weight);
                })
            }
        },*/
        shipmentMode() {
            //$('#shipment_mode').off().on('change', function () {
            let jobPol = document.querySelector('#pol');
            let jobPod = document.querySelector('#pod');
            //let jobCarrier = document.querySelector('#carrier');

            // If already initialized, destroy first

            jobPol.tomselect.destroy();
            jobPod.tomselect.destroy();
            //jobCarrier.tomselect.destroy();

            JOB.form.polPodLoad();
            //})
        },
        polPodLoad(preLoad = null) {
            let port = $('#activity-id-hidden').val();
            initTomSelectSearch('#pol', port, 100, preLoad);
            initTomSelectSearch('#pod', port, 100, preLoad);
            /*initTomSelectSearch('#carrier', port + 'Lines', 50, preLoad);*/
        },
        addContainer() {
            // Add Container Row
            $('#containerTable').off('click', '.addContainerRow').on('click', '.addContainerRow', function () {
                let $tbody = $(this).closest('tbody');
                let $newRow = $tbody.find('tr:first').clone();

                // Clear values in cloned row
                $newRow.find('input, select, textarea').val('');
                $newRow.find('select').removeClass('tomselected').removeClass('ts-hidden-accessible');
                $newRow.find('div.ts-wrapper').remove();
                initTomSelectForm($newRow);

                $tbody.append($newRow);
            });
        },
        addPackage() {
            // Add Package Row
            $('#packageTable').off('click', '.addPackageRow').on('click', '.addPackageRow', function () {
                let $tbody = $(this).closest('tbody');
                let $newRow = $tbody.find('tr:first').clone();

                // Clear values in cloned row
                $newRow.find('input, select, textarea').val('');
                $newRow.find('select').removeClass('tomselected').removeClass('ts-hidden-accessible');
                $newRow.find('div.ts-wrapper').remove();
                initTomSelectForm($newRow);

                $tbody.append($newRow);
                JOB.form.calculation.package();
            });
        },
        /*removeRow() {
            // Remove Row (for both tables)
            $('#containerTable,#packageTable').off('click', '.remove-row').on('click', '.remove-row', function () {
                let $tbody = $(this).closest('tbody');
                const $tr = $(this).closest('tr');
                if ($tbody.find('tr').length > 1) {
                    $tr.remove();
                } else {
                    // If only one row left, just clear it
                    // $(this).closest('tr').find('input, select').val('');
                    $tr.find('input').val('');
                    $tr.find('select').each(function () {
                        $(this).val('');
                        if ($(this).hasClass('selectpicker')) {
                            $(this).selectpicker('destroy').addClass('selectpicker');
                            selectPicker('#' + $(this).closest('table').attr('id'));
                        }
                    });
                }
            })
        }*/
    },
}
