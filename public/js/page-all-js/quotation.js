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

            // Non-orderable/non-searchable column indices (0-based)
            // 1=Client(computed), 2=Branch, 4=Status, 5=LatestComments,
            // 6=OperationalActivity(computed), 11=UserName, 12=SalesPerson(computed),
            // 15=Remarks, 16=ShipmentNo, 17=JobNo, 18=NoOfPcs, 19=GWeight,
            // 20=Volume, 21=P.Sale, 22=P.Cost, 23=GP, 24=GP%, 25=ShipperName,
            // 26=ConsigneeName, 27=ShipmentStatus, 28=ETD, 29=ETA,
            // 30=OriginAgent, 31=DestAgent, 32=EnquiryNo, 33=ContainerType,
            // 34=VesselName, 35=VoyageNo, 37=Actions
            let noSort = [1, 2, 4, 5, 6, 11, 12, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 37];

            let actionBtn = GLOBAL_FN.dataTable.optionButton();
            actionBtn.className = (actionBtn.className ? actionBtn.className + ' ' : '') + 'text-center';

            let table = $('#dataTable').DataTable({
                processing: false,
                serverSide: true,
                autoWidth: false,
                lengthChange: false,
                paging: false,
                dom: 'rt',
                order: [[3, 'desc']],
                ajax: {
                    url: GLOBAL_FN.buildUrl('sales/quotation/data'),
                    type: 'POST',
                    data: function (d) {
                        d.tab = activeTab;
                        d.filterData = QUOTATION.filter.default();
                    },
                    dataSrc: function (json) {
                        $('#dataTable tbody').find('.loading-row').remove();
                        GLOBAL_FN.setStatusCounts(json.statusCounts);
                        return json.data;
                    }
                },
                columnDefs: [
                    {targets: noSort, orderable: false, searchable: false},
                ],
                columns: [
                    // 0 - Quote No (left sticky via CSS)
                    {data: 'row_no', defaultContent: '', className: 'fw-semibold'},
                    // 1 - Client
                    {data: 'client_name', defaultContent: ''},
                    // 2 - Branch (not in quotation module)
                    {data: null, defaultContent: ''},
                    // 3 - Date
                    {data: 'posted_at', defaultContent: ''},
                    // 4 - Status
                    {
                        data: 'status',
                        render(data) {
                            const map = {
                                'pending':   ['Pending',   'warning'],
                                'accepted':  ['Accepted',  'success'],
                                'converted': ['Converted', 'info'],
                                'cancelled': ['Cancelled', 'danger'],
                                'expired':   ['Expired',   'secondary'],
                                'draft':     ['Draft',     'light'],
                                'sent':      ['Sent',      'primary'],
                                'rejected':  ['Rejected',  'danger'],
                                'confirmed': ['Confirmed', 'success'],
                            };
                            let key = (data ?? '').toString().toLowerCase();
                            let [label, color] = map[key] ?? ['—', 'secondary'];
                            return `<span class="badge bg-${color} text-capitalize">${label}</span>`;
                        }
                    },
                    // 5 - Latest Comments
                    {data: null, defaultContent: ''},
                    // 6 - Operational Activity
                    {data: 'activity_name', defaultContent: ''},
                    // 7 - Origin (POL)
                    {data: 'pol', defaultContent: ''},
                    // 8 - Destination (POD)
                    {data: 'pod', defaultContent: ''},
                    // 9 - Valid From (same as posted_at for quotations)
                    {data: 'posted_at', defaultContent: ''},
                    // 10 - Valid To
                    {data: 'valid_until', defaultContent: ''},
                    // 11 - User Name
                    {data: null, defaultContent: ''},
                    // 12 - Sales Person
                    {data: 'salesperson_name', defaultContent: ''},
                    // 13 - INCO Term
                    {data: 'incoterm', defaultContent: ''},
                    // 14 - Carrier
                    {data: 'carrier', defaultContent: ''},
                    // 15 - Remarks
                    {data: null, defaultContent: ''},
                    // 16 - Shipment No.
                    {data: null, defaultContent: ''},
                    // 17 - Job No.
                    {data: null, defaultContent: ''},
                    // 18 - No.of Pcs
                    {data: null, defaultContent: ''},
                    // 19 - G.Weight
                    {data: null, defaultContent: ''},
                    // 20 - Volume
                    {data: null, defaultContent: ''},
                    // 21 - P.Sale
                    {data: null, defaultContent: ''},
                    // 22 - P.Cost
                    {data: null, defaultContent: ''},
                    // 23 - GP
                    {data: null, defaultContent: ''},
                    // 24 - GP%
                    {data: null, defaultContent: ''},
                    // 25 - Shipper Name
                    {data: null, defaultContent: ''},
                    // 26 - Consignee Name
                    {data: null, defaultContent: ''},
                    // 27 - Shipment Status
                    {data: null, defaultContent: ''},
                    // 28 - ETD
                    {data: null, defaultContent: ''},
                    // 29 - ETA
                    {data: null, defaultContent: ''},
                    // 30 - Origin Agent
                    {data: null, defaultContent: ''},
                    // 31 - Destination Agent
                    {data: null, defaultContent: ''},
                    // 32 - Enquiry No
                    {data: null, defaultContent: ''},
                    // 33 - Container Type
                    {data: null, defaultContent: ''},
                    // 34 - Vessel/Flight Name
                    {data: null, defaultContent: ''},
                    // 35 - Voyage/Flight No
                    {data: null, defaultContent: ''},
                    // 36 - Place Of Delivery
                    {data: 'place_of_delivery', defaultContent: ''},
                    // 37 - Edit (right sticky via CSS)
                    actionBtn,
                ],
                language: {
                    search: '',
                    emptyTable: ' ',
                    zeroRecords: ' ',
                },
                deferLoading: 0,

                drawCallback: function () {
                    const info = this.api().page.info();
                    const noData    = info.recordsTotal === 0;
                    const noResults = !noData && info.recordsDisplay === 0;
                    const hasRows   = info.recordsDisplay > 0;

                    $('#tableWrapper').toggleClass('d-none', !hasRows);
                    $('#quotationEmptyState').toggleClass('d-none', hasRows);
                    $('#emptyStateNoData').toggleClass('d-none', !noData);
                    $('#emptyStateNoResults').toggleClass('d-none', !noResults);
                },

                initComplete: function () {
                    QUOTATION.form.open();
                    webDataTable.actions.menu();

                    // Dropdowns inside overflow:auto get clipped by the scroll container.
                    // Re-initialise each one with Popper's fixed strategy so they
                    // escape the overflow boundary and always appear above the layout.
                    table.on('draw.dt', function () {
                        $('#dataTable [data-bs-toggle="dropdown"]').each(function () {
                            const inst = bootstrap.Dropdown.getInstance(this);
                            if (inst) inst.dispose();
                            new bootstrap.Dropdown(this, {
                                popperConfig: { strategy: 'fixed' }
                            });
                        });
                    });
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
            $('#new,#new-first').off().on('click', function (enquiryId = null) {
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
