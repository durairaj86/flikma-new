QUOTATION = {
    title: 'Quotation',
    baseUrl: 'sales/quotation',
    actionUrl: 'sales/quotation',
    load() {
        QUOTATION.form.load();
        QUOTATION.filter.load();
        datepicker();
    },
    filter: {
        load: function () {
            QUOTATION.filter.filterBox();
            QUOTATION.filter.shipmentMode();
            QUOTATION.filter.polPodLoad();
        },
        filterBox: function () {
            $('#apply-filter').off().on({
                click: function () {
                    QUOTATION.list.dataTable();
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

                JOB.filter.polPodLoad();
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
        iframe.src = '/' + QUOTATION.baseUrl + '/' + printId + '/print';
    },
    downloadPDF(printId) {
        fetch('/' + QUOTATION.baseUrl + '/' + printId + '/print')
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
                    filename: `quotation-${printId}.pdf`,
                };
                html2pdf().set(opt).from(container).save().finally(() => {
                    //document.body.removeChild(container);
                });
            });
    },
    list: {
        load(activeTab) {
            QUOTATION.list.dataTable(activeTab);
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
                    url: GLOBAL_FN.buildUrl('sales/quotation/data'),
                    type: 'POST',
                    data: function (d) {
                        // Add tab parameter
                        d.tab = activeTab;
                        d.filterData = QUOTATION.filter.default();
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
                    {data: 'services'},
                    {data: 'activity.name'},
                    {
                        data: 'pol',
                        render: function (data, type, row) {
                            return `<div class="text-capitalize">${row.pol} -> ${row.pod}</div>`;
                        }
                    },
                    {data: 'posted_at'},
                    {data: 'valid_until'},
                    {data: 'salesperson.name'},
                    /*{
                        data: 'shipment_type',
                        render: function (data, type, row) {
                            let icon = '';
                            switch (row.shipment_type) {
                                case 'air':
                                    icon = '<i class="bi bi-airplane text-secondary me-1"></i>'; // free Font Awesome
                                    break;
                                case 'sea':
                                    icon = '<i class="fa fa-ship text-secondary me-1"></i>';
                                    break;
                                case 'road':
                                    icon = '<i class="bi bi-truck text-secondary me-1"></i>';
                                    break;
                                case 'rail':
                                    icon = '<i class="bi bi-train-front text-secondary me-1"></i>';
                                    break;
                            }

                            let typeText = row.shipment_type ? `<div>${(row.shipment_type)}</div>` : '';
                            let categoryText = row.shipment_category ? `<div>${row.shipment_category}</div>` : '';

                            return `<div class="d-flex align-items-start">
                                    <div class="me-2" style="width:16px;">${icon}</div>
                                    <div class="d-flex flex-column text-capitalize">
                                        ${typeText}
                                        ${categoryText}
                                    </div>
                                </div>`;
                        }
                    },
                    {
                        data: 'origin_city',
                        render: function (data, type, row) {
                            let origin = (row.origin_city || row.origin_country)
                                ? `<div class="text-capitalize"><i class="fa fa-map-marker-alt text-success me-1"></i>${row.origin_city ?? ''}, ${row.origin_country ?? ''}</div>`
                                : '';
                            return origin;
                        }
                    },
                    {
                        data: 'destination_city',
                        render: function (data, type, row) {
                            let dest = (row.destination_city || row.destination_country)
                                ? `<div class="text-capitalize"><i class="fa fa-map-marker-alt text-danger me-1"></i>${row.destination_city ?? ''}, ${row.destination_country ?? ''}</div>`
                                : '';
                            return dest;
                        }
                    },
                    {
                        data: 'pickup_date',
                        render: function (data, type, row) {
                            let pickup = row.pickup_date ? `<div><i class="bi bi-calendar-event text-primary me-1"></i>${row.pickup_date}</div>` : '';
                            let delivery = row.delivery_date ? `<div><i class="bi bi-calendar-check text-success me-1"></i>${row.delivery_date}</div>` : '';
                            return pickup + delivery;
                        }
                    },
                    {
                        data: 'weight',
                        render: function (data, type, row) {
                            let weight = row.weight ? `<div><span class="fw-semibold">Weight:</span> ${row.weight} kg</div>` : '';
                            let volume = row.volume ? `<div><span class="fw-semibold">Volume:</span> ${row.volume} m³</div>` : '';
                            return weight + volume;
                        }
                    },
                    {data: 'expiry_date'},
                    {data: 'created_at'},*/
                    // Actions column
                    GLOBAL_FN.dataTable.optionButton()

                ],
                language: {
                    search: "" // removes "Search:" label
                },
                deferLoading: 0,

                initComplete: function () {
                    QUOTATION.form.open();
                    webDataTable.actions.menu();
                }
            });
            $('#customSearch').on('keyup', function () {
                table.search(this.value).draw();
            });
            //$('#dataTable_filter').closest('div.row').remove();
            webDataTable.loader(table);
            webDataTable.search(table);
            //webDataTable.actions.menu();
        },
        extraActions(row) {
            QUOTATION.list.actions.statusChange(row);
            QUOTATION.list.actions.view(row);
            QUOTATION.list.actions.email(row);
            //QUOTATION.list.actions.convertToJob(row);
        },
        actions: {
            statusChange(row) {
                $('#row_pending,#row_accepted,#row_rejected,#row_convert_to_job').off().on('click', function () {
                    let fd = new FormData();
                    changeCustomerStatus(GLOBAL_FN.buildUrl('sales/quotation/' + row.attr('data-id') + '/status/' + $(this).attr('data-value')), {
                        method: 'POST',
                        data: fd,
                        callBack: 'datatable'
                    }, $(this).attr('data-value'));
                })
            },
            /*convertToJob(row) {
                $('#row_convert_to_job').off().on('click', function () {
                    localStorage.setItem('convert-quotation', row.attr('data-id'));
                    window.location.href = GLOBAL_FN.buildUrl(`operation/jobs`);
                });
            },*/
            view(row) {
                $('#row_view').off().on('click', function () {
                    let customerId = row.attr('data-id');

                    // Open drawer

                    let drawer = new bootstrap.Offcanvas(document.getElementById('moduleDrawer'));
                    drawer.show();

                    // Load Overview
                    $('#moduleOverview').html('<p>Loading...</p>');
                    $.get('/sales/quotation/' + customerId + '/overview', function (data) {
                        $('#moduleOverview').html(data);
                    });
                });
            },
            email(row) {
                $('#row_email').off().on('click', function () {
                    let quotationId = row.attr('data-id');

                    // Fetch email data from server
                    $.get('/sales/quotation/' + quotationId + '/email-data', function (data) {
                        // Populate the email form
                        $('#emailTo').val(data.to);
                        $('#emailCc').val(data.cc);
                        $('#emailSubject').val('Quotation #' + data.id);

                        // Show the drawer
                        let drawer = new bootstrap.Offcanvas(document.getElementById('sendEmailDrawer'));
                        drawer.show();

                        // Handle form submission
                        $('#sendEmailForm').off('submit').on('submit', function (e) {
                            e.preventDefault();

                            // Create FormData object
                            let formData = new FormData(this);

                            // Show loading state
                            const submitBtn = $(this).find('button[type="submit"]');
                            const originalBtnText = submitBtn.html();
                            submitBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Sending...');
                            submitBtn.prop('disabled', true);

                            // Send the email
                            $.ajax({
                                url: '/sales/quotation/send-email',
                                type: 'POST',
                                data: formData,
                                processData: false,
                                contentType: false,
                                success: function (response) {
                                    // Close the drawer
                                    bootstrap.Offcanvas.getInstance(document.getElementById('sendEmailDrawer')).hide();

                                    // Show success message
                                    toastr.success(response.message);

                                    // Reset form
                                    $('#sendEmailForm')[0].reset();
                                },
                                error: function (xhr) {
                                    // Show error message
                                    if (xhr.responseJSON && xhr.responseJSON.message) {
                                        toastr.error(xhr.responseJSON.message);
                                    } else {
                                        toastr.error('An error occurred while sending the email.');
                                    }
                                },
                                complete: function () {
                                    // Reset button state
                                    submitBtn.html(originalBtnText);
                                    submitBtn.prop('disabled', false);
                                }
                            });
                        });
                    });
                });
            }
        }
    },
    form: {
        load() {
            QUOTATION.form.open();
            let enquiryId = localStorage.getItem('convert-enquiry');
            if (enquiryId) {
                webModal.openGlobalModal({
                    title: 'New Quotation',
                    url: GLOBAL_FN.buildUrl('sales/quotation/create'),
                    size: 'xxl',
                    scroll: false,
                    minHeight: '700px',
                    content: {
                        enquiryId: enquiryId
                    }
                });
                localStorage.removeItem('convert-enquiry');
            }
        },
        open() {
            $('#new').off().on('click', function (enquiryId = null) {
                webModal.openGlobalModal({
                    title: 'New Quotation',
                    url: GLOBAL_FN.buildUrl('sales/quotation/create'),
                    content: null,
                    size: 'md',
                    scroll: false,
                    minHeight: 'min-height:70vh;',
                });
            })
        },
        openCallback() {
            QUOTATION.form.addContainer();
            QUOTATION.form.addPackage();
            QUOTATION.form.removeRow();
            QUOTATION.form.shipmentMode();
            setTimeout(function () {
                QUOTATION.form.customerProspectToggle();
            })
            GLOBAL_FN.activity.activityChange();
            QUOTATION.form.polPodLoad();
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
                let quotationPol = document.querySelector('#pol');
                let quotationPod = document.querySelector('#pod');

                // If already initialized, destroy first
                quotationPol.tomselect.destroy();
                quotationPod.tomselect.destroy();
                QUOTATION.form.polPodLoad(true);
            }
        },
        polPodLoad(preLoad = null) {
            let port = $('#activity-id-hidden').val();
            initTomSelectSearch('#pol', port, 50, preLoad);
            initTomSelectSearch('#pod', port, 50, preLoad);
            initTomSelectSearch('#carrier', port + 'Lines', 50, preLoad);
        },
        addContainer() {
            // Add Container Row
            $('#addContainerRow').off().on('click', function () {
                let $table = $('#containerTable tbody');
                let $newRow = $table.find('tr:first').clone();

                // Clear values in cloned row
                $newRow.find('input, select').val('');
                //$newRow.find('.bootstrap-select button').remove();
                $newRow.find('select').removeClass('tomselected').removeClass('ts-hidden-accessible');
                $newRow.find('div.ts-wrapper').remove();
                initTomSelectForm($newRow);
                //selectPicker($newRow);

                $table.append($newRow);
                QUOTATION.form.removeRow();
            });
        },
        addPackage() {
            // Add Package Row
            $('#addPackageRow').off().on('click', function () {
                let $table = $('#packageTable tbody');
                let $newRow = $table.find('tr:first').clone();


                // Clear values in cloned row
                $newRow.find('input, select').val('');
                $newRow.find('select').removeClass('tomselected').removeClass('ts-hidden-accessible');
                $newRow.find('div.ts-wrapper').remove();
                initTomSelectForm($newRow);

                $table.append($newRow);
                QUOTATION.form.removeRow();
            });
        },
        removeRow() {
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
                            console.log($(this).attr('id'));
                            selectPicker('#' + $(this).closest('table').attr('id'));
                        }
                    });
                }
            })
        }
    },
}
