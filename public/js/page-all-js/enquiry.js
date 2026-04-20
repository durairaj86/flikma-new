ENQUIRY = {
    title: 'Enquiry',
    baseUrl: 'sales/enquiry',
    actionUrl: 'sales/enquiry',
    load() {
        ENQUIRY.form.open();
        ENQUIRY.filter.load();
        datepicker();
    },
    filter: {
        load: function () {
            ENQUIRY.filter.filterBox();
            ENQUIRY.filter.shipmentMode();
            ENQUIRY.filter.polPodLoad();
        },
        filterBox: function () {
            $('#apply-filter').off().on({
                click: function () {
                    ENQUIRY.list.dataTable();
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
            $('input[name=shipment_mode]').off().on('change', function () {

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

                ENQUIRY.filter.polPodLoad();
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
        iframe.src = '/' + ENQUIRY.baseUrl + '/' + printId + '/print';
    },
    downloadPDF(printId) {
        fetch('/' + ENQUIRY.baseUrl + '/' + printId + '/print')
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
                    filename: `enquiry-${printId}.pdf`,
                };
                html2pdf().set(opt).from(container).save().finally(() => {
                    //document.body.removeChild(container);
                });
            });
    },
    list: {
        load(activeTab) {
            ENQUIRY.list.dataTable(activeTab);
        },
        dataTable(activeTab = null) {
            GLOBAL_FN.destroyDataTable();
            activeTab = (activeTab && (typeof activeTab !== 'object')) ? activeTab : $("#listTabs").find('li button.active').attr('id');
            let table = $('#dataTable').DataTable({
                processing: false,
                serverSide: true,
                autoWidth: false,
                lengthChange: false,
                pageLength: 25,
                dom: 'rt<"row mt-2"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7 d-flex justify-content-end"p>>',
                order: [[1, 'desc']],
                ajax: {
                    url: GLOBAL_FN.buildUrl('sales/enquiry/data'),
                    type: 'POST',
                    data: function (d) {
                        // Add tab parameter
                        d.tab = activeTab;
                        d.filterData = ENQUIRY.filter.default();
                    },
                    dataSrc: function (json) {
                        // Remove loader rows when data arrives
                        $('#dataTable tbody').find('.loading-row').remove();
                        GLOBAL_FN.setStatusCounts(json.statusCounts);
                        return json.data;
                    }
                },
                columnDefs: [
                    {targets: [0], searchable: false},
                    {targets: [0], orderable: false},
                    /*{targets: [1], class: 'hide', visible: false},*/
                ],
                columns: [
                    /*{data: 'DT_RowIndex', class: 'text-center hide-tooltip fav-index'},*/
                    {data: 'row_no', class: 'hide-tooltip fav-index'},
                    {
                        data: 'name',
                        render: function (data, type, row) {
                            return row.name.name + '<br><small class="text-muted">' + row.name.row_no + '</small>';
                        }
                    },
                    {
                        data: 'contact',
                        render: function (data, type, row) {
                            return row.contact.email + '<br><small class="text-muted">' + row.contact.phone + '</small>';
                        }
                    },
                    {data: 'activity.name'},
                    {data: 'pol'},
                    {data: 'pod'},
                    /*{
                        data: 'origin_city',
                        render: function (data, type, row) {
                            let origin = (row.origin_city || row.origin_country)
                                ? `<div class="text-capitalize">${row.origin_city ?? ''}, ${row.origin_country ?? ''}</div>`
                                : '';
                            return origin;
                        }
                    },
                    {
                        data: 'destination_city',
                        render: function (data, type, row) {
                            let dest = (row.destination_city || row.destination_country)
                                ? `<div class="text-capitalize">${row.destination_city ?? ''}, ${row.destination_country ?? ''}</div>`
                                : '';
                            return dest;
                        }
                    },*/
                    {
                        data: 'pickup_date',
                        render: function (data, type, row) {
                            return row.pickup_date ? `<div>${row.pickup_date}</div>` : '';
                        }
                    },
                    /*{
                        data: 'weight',
                        render: function (data, type, row) {
                            let weight = row.weight ? `<div>Weight: ${row.weight} kg</div>` : '';
                            let volume = row.volume ? `<small class="text-muted"><span class="fw-semibold">Volume:</span> ${row.volume} m³</small>` : '';
                            return weight + volume;
                        }
                    },*/
                    {data: 'expiry_date'},
                    {data: 'created_at'},
                    // Actions column
                    GLOBAL_FN.dataTable.optionButton()

                ],
                language: {
                    search: "" // removes "Search:" label
                },
                deferLoading: 0,

                initComplete: function () {
                    ENQUIRY.form.open();
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
        extraActions(row) {
            ENQUIRY.list.actions.statusChange(row);
            ENQUIRY.list.actions.view(row);
            ENQUIRY.list.actions.email(row);
        },
        actions: {
            statusChange(row) {
                $('#row_pending,#row_confirmed,#row_rejected').off().on('click', function () {
                    //GLOBAL_FN.ajaxData.sendData(GLOBAL_FN.buildUrl('ENQUIRY/' + row.attr('data-id') + '/status/' + $(this).attr('data-value')),'datatable',{})
                    let fd = new FormData();
                    changeCustomerStatus(GLOBAL_FN.buildUrl('sales/enquiry/' + row.attr('data-id') + '/status/' + $(this).attr('data-value')), {
                        method: 'POST',
                        data: fd,
                        callBack: 'datatable'
                    }, $(this).attr('data-value'));
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
                    $.get('/sales/enquiry/' + customerId + '/overview', function (data) {
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
    convertToQuotation(enquiryId) {
        // Redirect to the create quotation from enquiry route
        localStorage.setItem('convert-enquiry', enquiryId);
        window.location.href = GLOBAL_FN.buildUrl(`sales/quotations`);
    },

    form: {
        open() {
            $('#new').off().on('click', function () {
                webModal.openGlobalModal({
                    title: 'Add Enquiry',
                    url: GLOBAL_FN.buildUrl('sales/enquiry/create'),
                    content: null,
                    size: 'md',
                    scroll: false,
                    minHeight: '650px',
                });
            })
        },
        openCallback() {
            ENQUIRY.form.shipmentMode();
            ENQUIRY.form.shipmentCategory();
            //ENQUIRY.form.addItem();
            //ENQUIRY.form.initRemoveRow();
            ENQUIRY.form.polPodLoad();
            setTimeout(function () {
                ENQUIRY.form.customerProspectToggle();
            })

            GLOBAL_FN.activity.activityChange();
            //CUSTOMER.form.quick.load();
        },

        customerProspectToggle() {
            // Handle customer select change
            $('#customer').on('change', function () {
                const customerValue = $(this).val();
                const prospectSelect = document.querySelector('#prospect');

                if (customerValue && customerValue !== '') {
                    // Disable prospect select when customer is selected
                    if (prospectSelect && prospectSelect.tomselect) {
                        prospectSelect.tomselect.disable();
                    }
                } else {
                    // Enable prospect select when customer is cleared
                    if (prospectSelect && prospectSelect.tomselect) {
                        prospectSelect.tomselect.enable();
                    }
                }
            });

            // Handle prospect select change
            $('#prospect').on('change', function () {
                const prospectValue = $(this).val();
                const customerSelect = document.querySelector('#customer');

                if (prospectValue && prospectValue !== '') {
                    // Disable customer select when prospect is selected
                    if (customerSelect && customerSelect.tomselect) {
                        customerSelect.tomselect.disable();
                    }
                } else {
                    // Enable customer select when prospect is cleared
                    if (customerSelect && customerSelect.tomselect) {
                        customerSelect.tomselect.enable();
                    }
                }
            });

            // Initial check on page load
            const customerValue = $('#customer').val();
            const prospectValue = $('#prospect').val();
            const customerSelect = document.querySelector('#customer');
            const prospectSelect = document.querySelector('#prospect');

            // Check if we're in edit mode with a prospect
            const isEditMode = $('#data-id').val() && $('#prospect').length > 0;
            const hasProspectId = $('#prospect').data('has-prospect') === true || $('[name="prospect"]').find('option:selected').val() !== '';

            if (customerValue && customerValue !== '') {
                // Disable prospect select if customer is already selected
                if (prospectSelect && prospectSelect.tomselect) {
                    prospectSelect.tomselect.disable();
                }
            } else if (prospectValue && prospectValue !== '' || (isEditMode && hasProspectId)) {
                // Disable customer select if prospect is already selected or we're editing a prospect
                if (customerSelect && customerSelect.tomselect) {
                    customerSelect.tomselect.disable();
                }
            }
        },
        shipmentMode(destroy = null) {
            if (destroy) {
                let enquiryPol = document.querySelector('#pol');
                let enquiryPod = document.querySelector('#pod');

                // If already initialized, destroy first
                enquiryPol.tomselect.destroy();
                enquiryPod.tomselect.destroy();
                ENQUIRY.form.polPodLoad(true);
            }
        },
        shipmentCategory() {
            $('#shipment_category').on('change', function () {
                const category = $(this).val();
                if (category === 'container') {
                    $('.container-fields').removeClass('d-none');
                    $('.package-fields').addClass('d-none');
                } else if (category === 'package') {
                    $('.package-fields').removeClass('d-none');
                    $('.container-fields').addClass('d-none');
                }
            });
        },
        polPodLoad(preLoad = null) {
            let port = $('#activity-id-hidden').val();
            initTomSelectSearch('#pol', port, 50, preLoad);
            initTomSelectSearch('#pod', port, 50, preLoad);
        },
        addItem() {
            $('#addItem').off().on('click', function () {
                const category = $('#shipment_category').val();
                if (!category) return alert("Select Shipment Category first!");

                // Find the first visible template row for the category
                const $template = $('#enquiry-row tr.' + category + '-fields:visible:first');

                // Clone the row (without events)
                const $row = $template.clone(false, false);
                $row.removeClass('d-none');

                // Reset input values
                $row.find('input').val('');
                $row.find('select').val('');
                $row.find('select').removeClass('tomselected').removeClass('ts-hidden-accessible');
                $row.find('div.ts-wrapper').remove();
                // Reset select values and assign unique ids for TomSelect
                /*let i = 1000;
                $row.find('select').each(function () {
                    const t = $(this);
                    t.closest('tr').find('div.tom-select').remove();
                    if (t.hasClass('tom-select')) {
                        const newId = t.attr('id') + '_' + Math.floor(Math.random() * i++);
                        t.attr('id', newId);
                    }
                });*/

                // Initialize TomSelect on the cloned row
                initTomSelectForm($row);

                // Append the row
                $('#enquiry-row').append($row);
            });

        },
        initRemoveRow() {
            $('#enquiry-row').off('click', '.remove-row').on('click', '.remove-row', function () {
                const $tbody = $(this).closest('#enquiry-row');
                const $tr = $(this).closest('tr');

                if ($tbody.find('tr').length > 1) {
                    $tr.remove();
                } else {
                    $tr.find('input').val('');
                    $tr.find('select').each(function () {
                        $(this).val('');
                        if ($(this).hasClass('selectpicker')) {
                            $(this).selectpicker('destroy').addClass('selectpicker');
                            selectPicker('#enquiry-row');
                        }
                    });

                }
            });
        }

    },
}
