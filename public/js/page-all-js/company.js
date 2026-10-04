COMPANY = {
    title: 'Company',
    baseUrl: 'settings/company',
    actionUrl: 'settings/company',
    load() {
        // This page's form is #company-form (not #moduleForm), so style its selects with Tom Select explicitly.
        if (typeof initTomSelectForm === 'function') initTomSelectForm($('#company-form'));
        let logoInput = document.getElementById('logoInput');
        let signatureInput = document.getElementById('signatureInput');

        $('#logoUploadBox').on('click', function () {
            logoInput.click();
        });
        $('#signatureUploadBox').on('click', function () {
            signatureInput.click();
        });
        // Logo and signature: choosing a file opens one shared crop modal; the cropped image replaces the file in the form.
        const targets = {
            logo: { input: logoInput, preview: '#logoPreview', box: '#logoUploadBox', title: 'Crop your logo', max: 1200 },
            signature: { input: signatureInput, preview: '#signaturePreview', box: '#signatureUploadBox', title: 'Crop your signature', max: 800 },
        };
        let cropper = null, cropFile = null, cropTarget = null;
        const cropModalEl = document.getElementById('logoCropModal');
        const cropImg = document.getElementById('logoCropImage');
        const cropModal = cropModalEl ? new bootstrap.Modal(cropModalEl) : null;

        Object.keys(targets).forEach(function (name) {
            const t = targets[name];
            if (!t.input) return;
            $(t.input).on('change', function (event) {
                const file = event.target.files[0];
                if (!file) return;
                if (!window.Cropper || !cropModal) { return COMPANY.setPreview(t, file); }
                cropFile = file;
                cropTarget = t;
                $('#logoCropTitle').text(t.title);
                const reader = new FileReader();
                reader.onload = function (e) {
                    cropImg.src = e.target.result;
                    cropModal.show();
                };
                reader.readAsDataURL(file);
            });
        });

        if (cropModalEl) {
            cropModalEl.addEventListener('shown.bs.modal', function () {
                if (cropper) cropper.destroy();
                $('#logoCropRatio [data-ratio]').removeClass('active').first().addClass('active');
                cropper = new Cropper(cropImg, { viewMode: 1, autoCropArea: 1, responsive: true, background: false, dragMode: 'move' });
            });
            cropModalEl.addEventListener('hidden.bs.modal', function () {
                if (cropper) { cropper.destroy(); cropper = null; }
                // Cancelled (not saved): forget the chosen file so the current image stays.
                if (cropModalEl.dataset.saved !== '1' && cropTarget) cropTarget.input.value = '';
                cropModalEl.dataset.saved = '';
            });
            $('#logoCropRatio').on('click', '[data-ratio]', function () {
                $('#logoCropRatio [data-ratio]').removeClass('active');
                $(this).addClass('active');
                if (cropper) cropper.setAspectRatio(parseFloat($(this).data('ratio')) || NaN);
            });
            $('#logoCropRotate').on('click', function () { if (cropper) cropper.rotate(90); });
            $('#logoCropReset').on('click', function () { if (cropper) cropper.reset(); });
            $('#logoCropSave').on('click', function () {
                if (!cropper || !cropTarget) return;
                const t = cropTarget;
                const isPng = cropFile.type === 'image/png';
                const canvas = cropper.getCroppedCanvas({ maxWidth: t.max, maxHeight: t.max, imageSmoothingQuality: 'high', fillColor: isPng ? undefined : '#ffffff' });
                canvas.toBlob(function (blob) {
                    if (!blob) return;
                    const base = cropFile.name.replace(/\.[^.]+$/, '');
                    const out = new File([blob], base + (isPng ? '.png' : '.jpg'), { type: blob.type });
                    const dt = new DataTransfer();
                    dt.items.add(out);
                    t.input.files = dt.files;
                    COMPANY.setPreview(t, out);
                    cropModalEl.dataset.saved = '1';
                    cropModal.hide();
                }, isPng ? 'image/png' : 'image/jpeg', 0.92);
            });
        }

        // CR and VAT numbers are always visible; the VAT number is only mandatory when VAT registered.
        function toggleVatFields() {
            const on = $('#vat_status').val() === '1';
            $('#vatNumber, #crNumber').prop('required', on);
            $('.vat-req').toggleClass('d-none', !on);
        }
        toggleVatFields();

        $('#vat_status').on('change', toggleVatFields);

        $('#submit').off().on('click', function (e) {
            e.preventDefault();
            const form = $('#company-form');
            const action = form.attr('action');
            const method = form.attr('method') || 'POST';
            const submitBtn = $('#submit');

            // Build form data
            const formData = new FormData(form[0]);

            submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

            $.ajax({
                url: action,
                type: method,
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.status === 'success') {
                        toastr.success(response.message);
                    } else {
                        toastr.error(response.message || 'Something went wrong!');
                    }
                },
                error: function (xhr) {
                    let errors = xhr.responseJSON && xhr.responseJSON.errors;
                    if (errors) {
                        $.each(errors, function (key, value) {
                            toastr.error(value[0]);
                        });
                    } else {
                        toastr.error('Something went wrong!');
                    }
                },
                complete: function () {
                    submitBtn.prop('disabled', false).html('<i class="bi bi-save me-2"></i> Update');
                }
            });
        })
    },
    setPreview(t, file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            $(t.preview).attr('src', e.target.result).removeClass('d-none');
            $(t.box + ' .upload-text').addClass('d-none');
        };
        reader.readAsDataURL(file);
    },
    list: {
        load() {
        },
    },
}
