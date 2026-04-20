ZATCA = {
    load() {
        let form = $("#form"),
            form2 = $('#simulation-form'),
            form3 = $('#production-form');
        /*selectPicker(form);
        formValidation(form);
        initMaxLength(form);
        formValidation(form2)
        formValidation(form3)*/
        ZATCA.submit();
        ZATCA.formLoad();
        ZATCA.registerClick();
        ZATCA.production();
        ZATCA.edit();
        ZATCA.testRun();
    },
    formLoad() {

        /*$('#register').on('click', function () {
            let switchStatus = '';
            if ($(this).is(':checked')) {
                switchStatus = $(this).is(':checked');
                if (switchStatus === true) {
                    $('#register-details').removeClass('d-none');
                    $('.save').removeClass('d-none');
                }
            } else {
                $('#register-details').addClass('d-none');
            }
        })*/
    }, submit() {
        $("#submit-details").on('click', function () {
            if ($('#form').valid()) {
                MODEL.options({
                    type: 'send',
                    url: DEFAULT.initUrl('settings/register'),
                    //callBack: ZATCA.register,
                    callBack: ZATCA.register,
                });
            }
        })
    }, register() {
        /*$('.register').removeClass('d-none');
        $('.edit').removeClass('d-none');
        $('#register-details').addClass('d-none');
        $('#register-detail').removeClass('d-none');
        $('#toggle').addClass('d-none');
        $('.save').addClass('d-none');*/
    },

    edit() {
        /*$('#edit-details').on('click', function () {
            $('#register-details').removeClass('d-none');
            $('.save').removeClass('d-none');
            $('.register').addClass('d-none');
            $('#register-detail').addClass('d-none');
            $('.edit').addClass('d-none');
        })*/
    },

    testRun() {
        $('.test-run').on('click', function () {
            let btnId = $(this).attr('data-type');
            stickLoader('Testing', __('Testing ' + (btnId).toUpperCase() + ' invoices...'));
            $('#' + btnId).text('Testing...');
            $('#label-' + btnId).empty();
            MODEL.options({
                type: 'fetch',
                url: DEFAULT.initUrl('settings/test/' + btnId + '/zatca'),
                callBack: ZATCA.testResponse,
                options: {
                    data: btnId
                }
            });
        })
    },

    registerClick() {
        $("#submit-simulation-validate,#submit-core-validate").on('click', function () {
            let mode = 'core',
                btnId = $(this).attr('id'),
                form;

            if (btnId === 'submit-simulation-validate') {
                mode = 'simulation';
                form = $('#simulation-form');
            } else {
                form = $('#production-form');
            }

            if ($('#' + mode + '_otp').val() > 0 || mode === 'developer-portal') {
                //stickLoader('Installing', __('Installing Zatca Please wait...'));
                $('#' + btnId).text('Validating...');
                if ($('#simulation-form').valid()) {

                    const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
                    const simulationFormBtn = document.getElementById('submit-simulation-validate');
                    const simulationCardBody = document.querySelector('#simulation-card .card-body');

                    const titleElement = document.getElementById('loadingModalLabel');
                    const delay = 2000; // 2 seconds

                    // Phase 1: Initial State
                    titleElement.textContent = 'Validating OTP';

                    // Phase 2: Switch to "Verifying..." after 2 seconds
                    setTimeout(function() {
                        titleElement.textContent = 'Verifying...';

                        // Phase 3: Switch to "Installing please wait..." after another 2 seconds
                        setTimeout(function() {
                            titleElement.textContent = 'Installing please wait...';

                            // Note: The text will remain "Installing please wait..."
                            // until your actual success or error handling code changes it.

                        }, delay); // 2 seconds delay for Phase 3

                    }, delay); // 2 seconds delay for Phase 2
                    /*MODEL.options({
                        type: 'send',
                        url: DEFAULT.initUrl('settings/' + mode + '/register/zatca'),
                        callBack: ZATCA.response,
                        options: {
                            form: 'simulation-form',
                        }
                    });*/
                    //const formData = new FormData(this);
                    const formData = new FormData(form[0]);

                    $.ajax({
                        url: '/settings/zatca/' + mode + '/register/zatca',
                        type: 'POST',
                        data: formData,
                        /*context: modal,*/
                        processData: false,
                        contentType: false,
                        beforeSend: function () {
                            loadingModal.show();
                        },
                        success: function (response) {
                            const success = true; // Assume success for this flow

                            // 3. Hide the Loader Modal
                            loadingModal.hide();
                            const successContent = `
                        <div id="simulation-success-message-container" class="text-center p-4 rounded bg-success-subtle">
                            <i class="bi bi-check-circle-fill display-4 text-success mb-3"></i>
                            <h5 class="fw-bold text-success">STAGE 1: ACTIVATED</h5>
                            <p class="small text-success mb-0">
                                The Compliance CSID has been successfully issued and registered.
                                **You may now proceed to Stage 2: Production Activation.**
                            </p>
                        </div>
                    `;

                            // Replace the inner content of the simulation card body
                            simulationCardBody.innerHTML = `
                        <span class="badge bg-warning text-dark mb-3 fs-6">STAGE 1: SIMULATION</span>
                        <h4 class="card-title fw-bold text-warning-emphasis mb-3 me-3">Compliance Certification</h4>
                        <p class="small text-muted mb-4">
                            This phase validates your system's technical compatibility with ZATCA's standards. Successful validation generates the **Compliance CSID**.
                        </p>
                        ${successContent}
                    `;
                            if (response.status === 'success') {
                                toastr.success(response.message);
                            } else {
                                if (response.status === 'error') {
                                    toastr.error(response.message);
                                }
                            }
                        },
                        error: function (xhr) {
                            loadingModal.hide();
                            let error = xhr.responseJSON.message;
                            if (error) {
                                toastr.error(error);
                            } else {
                                toastr.error("Something went wrong!");
                            }
                        },
                        /*error: function(xhr){
                            $('#globalModalBody').html(xhr.responseText);
                            setupModalFooter(); // Re-add footer buttons after validation errors
                        },*/
                        /*complete: function (xhr) {
                            console.log("responsemess",xhr.responseTex);
                        }*/
                    });
                }
            } else {
                alert("Enter valid otp");
                //showToastr('Invalid', 'Enter valid otp', 'error');
            }

        })
    },

    production() {
        $("#submit-core-validate").on('click', function () {
            let mode = 'core', btnId = $(this).attr('id');
            if (btnId === 'submit-simulation-validate') {
                mode = 'developer-portal';
            }
            if ($('#' + mode + '_otp').val() > 0 || mode === 'developer-portal') {
                //stickLoader('Installing', __('Installing Zatca Please wait...'));
                $('#' + btnId).text('Validating...');
                if ($('#production-form').valid()) {
                    MODEL.options({
                        type: 'send',
                        url: DEFAULT.initUrl('settings/' + mode + '/register/zatca'),
                        callBack: ZATCA.response,
                        options: {
                            form: 'production-form',
                        }
                    });
                }
            } else {
                alert("Enter valid otp");
                //showToastr('Invalid', 'Enter valid otp', 'error');
            }

        })
    },

    response(data) {
        if (data.title === "Success") {
            /*$('#submit-simulation-validate,#submit-core-validate').remove();*/
            setTimeout(function () {
                location.reload();
                //pageReload()
            }, 500)
        } else {
            $('#submit-simulation-validate,#submit-core-validate').text('Validate');
        }
    },
    testResponse(data, btnId) {
        $('#' + btnId.data).text('Test Again');
        $('#label-' + btnId.data).html('<i class="fa fa-check-circle"></i>' + ' Sample Invoice ' + data.message);
        showToastr('Success', data.message);
    }
}
