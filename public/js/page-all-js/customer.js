CUSTOMER = {
    title: 'Customer',
    baseUrl: 'customer',
    actionUrl: 'customer',
    load() {
        CUSTOMER.form.open();
        CUSTOMER.import.open();
    },
    list: {
        load(activeTab) {
            CUSTOMER.list.dataTable(activeTab);
        },
        dataTable(activeTab = null) {
            // ⚠️ CRUCIAL: Destroy the old instance before creating a new one
            GLOBAL_FN.destroyDataTable();

            activeTab = (activeTab && (typeof activeTab !== 'object')) ? activeTab : $("#listTabs").find('li button.active').attr('id');

            // ⚠️ CRUCIAL: Wrap the DataTables creation in a try...catch
            // If the DataTables library failed to load, this prevents the script from crashing.
            try {
                let table = $('#dataTable').DataTable({
                    processing: false,
                    serverSide: true,
                    autoWidth: false,
                    lengthChange: false,
                    pageLength: 25,
                    dom: 'rt<"row mt-2"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7 d-flex justify-content-end"p>>',
                    order: [[1, 'desc']],
                    ajax: {
                        url: GLOBAL_FN.buildUrl(CUSTOMER.baseUrl + '/data'),
                        type: 'POST',
                        data: {
                            'tab': activeTab
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
                        {data: 'DT_RowIndex', class: 'ps-4 text-muted small'},
                        {
                            data: 'name_en', render: function (data, type, row) {
                                return '<div class="fw-bold text-dark">' + row.name_en + '</div><div class="text-primary x-small fw-semibold">' + row.row_no + '</div>';
                            }
                        },
                        {
                            data: 'email', render: function (data, type, row) {
                                return '<div class="d-flex flex-column"><span class="small">' + row.email + '</span><span class="small text-muted">' + row.phone + '</span></div>';
                            }
                        },
                        {
                            data: 'country', render: function (data, type, row) {
                                return '<span class="badge bg-light text-dark border fw-normal"><i class="bi bi-geo-alt me-1"></i>' + (row.city_en ? (row.city_en + ', ') : '') + row.country + '</span>';
                            }
                        },
                        {
                            data: 'currency', render: function (data, type, row) {
                                return '<span class="fw-medium">' + row.currency + '</span>';
                            }
                        },
                        {
                            data: 'vat_number', class: 'small font-monospace', render: function (data, type, row) {
                                return row.vat_number;
                            }
                        },
                        {
                            data: 'credit_limit', render: function (data, type, row) {
                                return '<div class="fw-bold">' + (row.credit_limit ?? '') + '</div><div class="x-small text-danger">' + (row.credit_days ? row.credit_days + ' Days' : '') + '</div>';
                            }
                        },
                        {
                            data: 'salesperson.name', render: function (data, type, row) {
                                return '<div class="d-flex align-items-center">' + data ? data : '' + '</div>';
                            }
                        },
                        {data: 'created_at', name: 'created_at', class: 'small text-muted'},
                        // Actions column
                        GLOBAL_FN.dataTable.optionButton()

                    ],
                    language: {
                        search: "" // removes "Search:" label
                    },

                    deferLoading: 0, // don't load immediately
                    /*preDrawCallback: function (settings) {
                        let api = this.api();
                        let cols = api.columns().nodes().length;
                        let pageLen = api.page.len(); // rows per page

                        // clear old rows & insert loader rows
                        let loaderRows = '';
                        for (let i = 0; i < pageLen; i++) {
                            loaderRows += '<tr class="loading-row">';
                            for (let j = 0; j < cols; j++) {
                                loaderRows += `<td><div class="loader-td"></div></td>`;
                            }
                            loaderRows += '</tr>';
                        }
                        $('#dataTable tbody').html(loaderRows);
                    }*/

                    initComplete: function () {
                        CUSTOMER.form.open();
                        webDataTable.actions.menu();
                    }
                });

                $('#customSearch').on('keyup', function () {
                    table.search(this.value).draw();
                });
                // Assuming webDataTable object is defined elsewhere
                webDataTable.loader(table);
                webDataTable.search(table);
                //webDataTable.actions.menu();
            } catch (e) {
                console.error("DataTables Initialization Error:", e);
                // Gracefully degrade the table to a basic HTML table if the plugin fails.
            }
        },
        extraActions(row) {
            CUSTOMER.list.actions.statusChange(row);
            CUSTOMER.list.actions.view(row);
            CUSTOMER.list.actions.email(row);
        },
        actions: {
            statusChange(row) {
                $('#row_pending,#row_confirm,#row_blocked,#row_rejected').off().on('click', function () {
                    //GLOBAL_FN.ajaxData.sendData(GLOBAL_FN.buildUrl('customer/' + row.attr('data-id') + '/status/' + $(this).attr('data-value')),'datatable',{})
                    let fd = new FormData();
                    changeCustomerStatus(GLOBAL_FN.buildUrl('customer/' + row.attr('data-id') + '/status/' + $(this).attr('data-value')), {
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

                    let drawer = new bootstrap.Offcanvas(document.getElementById('customerDrawer'));
                    drawer.show();

                    // Load Overview
                    $('#customerOverview').html('<p>Loading...</p>');
                    $.get('/customer/' + customerId + '/overview', function (data) {
                        $('#customerOverview').html(data);
                    });

                    // Clear other tabs
                    $('#customerInvoices').html('');
                    $('#customerTransactions').html('');
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
    import: {
        open() {
            $('#import').off().on('click', function () {
                // Show the import modal
                const importModal = new bootstrap.Modal(document.getElementById('importModal'));
                importModal.show();

                // Reset the import modal to step 1
                $('.import-step').addClass('d-none');
                $('#step1').removeClass('d-none');
                $('#uploadForm').trigger('reset');
                //$('#columnMappingContainer').empty();
                $('#errorList').empty();
                $('#importSuccess, #importError').addClass('d-none');
            });

            // Handle file upload
            $('#uploadForm').off().on('submit', function (e) {
                e.preventDefault();

                const fileInput = $('#excelFile')[0];
                if (fileInput.files.length === 0) {
                    alert('Please select a file to upload');
                    return;
                }

                const formData = new FormData(this);

                // Show loading state
                const submitBtn = $(this).find('button[type="submit"]');
                const originalText = submitBtn.text();
                submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Uploading...');

                // Send the file to the server
                $.ajax({
                    url: GLOBAL_FN.buildUrl('customer/import/upload'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        // Move to step 2 (column mapping)
                        $('.import-step').addClass('d-none');
                        $('#step2').removeClass('d-none');
                        $('#import-step-2').removeClass('text-muted').addClass('text-primary');
                        $('#import-step-3').removeClass('text-primary').addClass('text-muted');
                        $('#importProgressBar').css('width', '66%');

                        // Get the Excel columns from the response
                        const columns = response.columns;

                        // Populate the Excel column dropdowns
                        $('.excel-column-select').each(function () {
                            const select = $(this);
                            // Clear existing options except the first one
                            select.find('option:not(:first)').remove();

                            // Add Excel columns as options
                            columns.forEach((column, index) => {
                                select.append(`<option value="${index}">${column}</option>`);
                            });
                        });
                    },
                    error: function (xhr) {
                        let errorMessage = 'An error occurred while uploading the file.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }

                        // Show error in step 3
                        $('.import-step').addClass('d-none');
                        $('#step3').removeClass('d-none');
                        $('#importSuccess').addClass('d-none');
                        $('#importError').removeClass('d-none');
                        $('#import-step-3').removeClass('text-muted').addClass('text-primary');
                        $('#importProgressBar').css('width', '100%');
                        $('#errorList').html(`<li>${errorMessage}</li>`);
                    },
                    complete: function () {
                        // Reset button state
                        submitBtn.prop('disabled', false).text(originalText);
                    }
                });
            });

            // Handle back button in step 2
            $('#backToUpload').off().on('click', function () {
                $('.import-step').addClass('d-none');
                $('#step1').removeClass('d-none');
                $('#import-step-2').removeClass('text-primary').addClass('text-muted');
                $('#importProgressBar').css('width', '33%');
            });

            // Handle column mapping form submission
            $('#mappingForm').off().on('submit', function (e) {
                e.preventDefault();

                const $form = $(this);
                const submitBtn = $form.find('button[type="submit"]');
                const originalText = submitBtn.text();

                submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Importing...');

                // 1. Check if at least one mapping is selected
                let hasMapping = false;
                $('.excel-column-select').each(function () {
                    if ($(this).val() !== "") {
                        hasMapping = true;
                        return false; // break loop
                    }
                });

                if (!hasMapping) {
                    alert('Please map at least one column.');
                    submitBtn.prop('disabled', false).text(originalText);
                    return;
                }

                // 2. Use serialize() to get all named inputs
                const formDataString = $form.serialize();

                $.ajax({
                    url: GLOBAL_FN.buildUrl('customer/import/process'),
                    type: 'POST',
                    data: formDataString, // Standard URL-encoded string
                    success: function (response) {
                        $('.import-step').addClass('d-none');
                        $('#step3').removeClass('d-none');
                        $('#importSuccess').removeClass('d-none');
                        $('#successMessage').text(`Successfully imported ${response.imported} customers.`);

                        $('#importProgressBar').css('width', '100%').addClass('bg-success');

                        if ($('#dataTable').length) $('#dataTable').DataTable().ajax.reload();
                    },
                    error: function (xhr) {
                        $('.import-step').addClass('d-none');
                        $('#step3').removeClass('d-none');
                        $('#importError').removeClass('d-none');

                        let errorHtml = '';
                        const resp = xhr.responseJSON;
                        if (resp && resp.errors) {
                            Object.values(resp.errors).flat().forEach(err => {
                                errorHtml += `<li>${err}</li>`;
                            });
                        } else {
                            errorHtml = `<li>${resp.message || 'Import failed.'}</li>`;
                        }
                        $('#errorList').html(errorHtml);
                    },
                    complete: function () {
                        submitBtn.prop('disabled', false).text(originalText);
                    }
                });
            });
        }
    },
    form: {
        open() {
            $('#new').off().on('click', function () {
                let dataTableData = $('#dataTable');
                let modelSize = dataTableData.data('model-size');
                let minHeight = dataTableData.data('min-height');
                webModal.openGlobalModal({
                    title: 'Add Customer',
                    url: GLOBAL_FN.buildUrl('customer/create'),
                    content: null,
                    size: modelSize,
                    scroll: false,
                    //callBack: CUSTOMER.form.openCallback,
                    minHeight: minHeight,
                });
            })
        },
        openCallback() {
            const mandatoryFields = [
                'cr_number',
                'vat_number',
                'address1_en',
                'city_en',
                'building_number',
                'plot_no',
                'postal_code'
            ];

            const toggleField = (fieldId, isRequired) => {
                const $field = $(`#${fieldId}`);

                // 1. Set/Remove the required attribute
                $field.prop('required', isRequired);
                if (isRequired) {
                    $field.closest('div.form-group').find('label').addClass('required');
                } else {
                    $field.closest('div.form-group').find('label').removeClass('required');
                }

                // 2. Set/Remove the disabled property
                $('#cr_number').prop('disabled', !isRequired);
                $('#vat_number').prop('disabled', !isRequired);

                // 3. Clear the value if the field is disabled (unregistered)
                if (!isRequired && (fieldId == 'cr_number' || fieldId == 'vat_number')) {
                    $field.val('');
                }

                // 4. Optional: If you use the ARIA attribute for enhanced accessibility
                $field.attr('aria-required', isRequired);

                // 5. Update the label's class to show the required star (if your CSS relies on it)
                // This is optional if your required styling is tied to the input[required] attribute.
                const $label = $field.closest('.form-group').find('.is-invalid');
                const $errorTooltip = $field.closest('.form-group').find('.error-tooltip-top');
                if ($label.length) {
                    $label.removeClass('is-invalid', isRequired);
                    $errorTooltip.remove();
                }
            };

            // Set initial state and listen for changes
            $('input[name="business_type"]').on('change', function () {
                const isRegistered = ($(this).val() === 'registered');

                // Loop through all conditionally mandatory fields
                mandatoryFields.forEach(fieldId => {
                    toggleField(fieldId, isRegistered);
                });

            })/*.trigger('change')*/; // Trigger change on load to set the initial state

            const isRegisteredCheck = $('input[name="business_type"]:checked').val() === 'registered';
            //console.log(isRegisteredCheck);
            mandatoryFields.forEach(fieldId => {
                toggleField(fieldId, isRegisteredCheck);
            });
        },
        quick: {
            open() {
                let dataTableData = $('#dataTable');
                let modelSize = dataTableData.data('model-size');
                webModal.quickModal.open({
                    title: 'Add Quick Customer',
                    url: GLOBAL_FN.buildUrl('customer/create/quick'),
                    content: null,
                    size: modelSize,
                    callBack: null,
                    module: 'CUSTOMER'
                });
            },
            after: {
                save(data) {
                    let ts = document.querySelector('#customer').tomselect;

                    // If TomSelect already exists → add dynamically
                    if (ts) {
                        ts.addOption({
                            value: data.id,
                            text: data.name,
                            subtext: data.code
                        });
                        ts.addItem(data.id); // select it
                    } else {
                        // If not initialized yet
                        $('#customer').prepend(
                            `<option value="${data.id}" data-subtext="${data.code}" selected>${data.name}</option>`
                        );
                    }
                }
            }
        }
    },
}
