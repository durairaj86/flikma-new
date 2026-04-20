COMPANY = {
    title: 'Company',
    baseUrl: 'settings/company',
    actionUrl: 'settings/company',
    load() {
        let logoInput = document.getElementById('logoInput');
        let signatureInput = document.getElementById('signatureInput');

        $('#logoUploadBox').on('click', function () {
            logoInput.click();
        });
        $('#signatureUploadBox').on('click', function () {
            signatureInput.click();
        });
        $(logoInput).on('change', function (event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    $('#logoPreview').attr("src", e.target.result);
                    $('#logoPreview').removeClass('d-none');
                    $('#logoUploadBox .upload-text').addClass('d-none');
                };
                reader.readAsDataURL(file);
            }
        });
        $(signatureInput).on('change', function (event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    $('#signaturePreview').attr("src", e.target.result);
                    $('#signaturePreview').removeClass('d-none');
                    $('#signatureUploadBox .upload-text').addClass('d-none');
                };
                reader.readAsDataURL(file);
            }
        });

        function toggleVatFields() {
            const complianceGroup = $('.vat-compliance-group');
            if ($('#vat_status').val() === '1') {
                complianceGroup.slideDown(200);
                $('#vatNumber').attr('required',true);
                $('#crNumber').attr('required',true);
            } else {
                complianceGroup.slideUp(200);
                $('#vatNumber').removeAttr('required');
                $('#crNumber').removeAttr('required');
            }
        }

        $('#vat_status').on('change', toggleVatFields);

        $('#submit').off().on('click', function (e) {
            e.preventDefault();
            const form = $('#company-form');
            const action = form.attr('action');
            const method = form.attr('method') || 'POST';
            // Reset validation
            /*form.removeClass("was-validated");

            if (!form[0].checkValidity()) {
                e.stopPropagation();
                form.addClass("was-validated");
                return;
            }*/
            if (form.valid()) {

                // Build form data
                const formData = new FormData(form[0]);
                // const formData = new FormData(this);

                const submitBtn = form.find('button[type="submit"]');
                submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Submitting');

                $.ajax({
                    url: action,
                    type: method,
                    data: formData,
                    //context: modal,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        console.log(response);
                        if (response.status === 'success') {
                            toastr.success(response.message);

                            // Reset form
                            $('#customerForm')[0].reset();
                            console.log(response, callback);
                            if (callback) callback(response);
                        } else {
                            if (response.status === 'error') {
                                toastr.error(response.message);
                            }
                        }
                    },
                    error: function (xhr) {
                        let errors = xhr.responseJSON.errors;
                        if (errors) {
                            $.each(errors, function (key, value) {
                                toastr.error(value[0]);
                            });
                        } else {
                            toastr.error("Something went wrong!");
                        }
                    },
                    /*error: function(xhr){
                        $('#globalModalBody').html(xhr.responseText);
                        setupModalFooter(); // Re-add footer buttons after validation errors
                    },*/
                    complete: function () {
                        submitBtn.prop('disabled', false).html('Submit');
                    }
                });
            }
        })
    },
    list: {
        load() {
        },
    },
}
