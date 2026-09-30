<!-- Send Email Offcanvas -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="sendEmailDrawer" style="width: 600px;">
    <div class="offcanvas-header border-bottom">
        <h5 id="sendEmailDrawerLabel">{{ __('Send Email') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>

    <div class="offcanvas-body p-3">
        <form id="sendEmailForm" enctype="multipart/form-data">
            <!-- To / CC -->
            <div class="mb-3">
                <label for="emailTo" class="form-label">{{ __('To') }}</label>
                <input type="email" class="form-control" id="emailTo" name="to"
                       placeholder="{{ __('Enter recipient email') }}" required>
            </div>
            <div class="mb-3">
                <label for="emailCc" class="form-label">{{ __('CC') }}</label>
                <input type="email" class="form-control" id="emailCc" name="cc"
                       placeholder="{{ __('Enter CC email (optional)') }}">
            </div>

            <!-- Subject -->
            <div class="mb-3">
                <label for="emailSubject" class="form-label">{{ __('Subject') }}</label>
                <input type="text" class="form-control" id="emailSubject" name="subject"
                       placeholder="{{ __('Email subject') }}" required>
            </div>

            <!-- Template Dropdown -->
            <div class="mb-3">
                <label for="emailTemplate" class="form-label">{{ __('Template') }}</label>
                <select class="tom-select" id="emailTemplate" name="template">
                    <option value="">{{ __('-- Select Template --') }}</option>
                    <option value="welcome">{{ __('Welcome Message') }}</option>
                    <option value="payment_reminder">{{ __('Payment Reminder') }}</option>
                    <option value="invoice">{{ __('Invoice Notification') }}</option>
                    <!-- Add more templates here -->
                </select>
            </div>

            <!-- Message Editor -->
            <div class="mb-3">
                <label for="emailBody" class="form-label">{{ __('Message') }}</label>
                <textarea class="form-control" style="min-height: 10rem" id="emailBody" name="body" rows="8"
                          placeholder="{{ __('Write your message...') }}"></textarea>
            </div>

            <!-- Attachments -->
            <div class="mb-3">
                <label for="emailAttachment" class="form-label">{{ __('Attachment') }}</label>
                <input type="file" class="form-control" id="emailAttachment" name="attachment[]" multiple>
                <small class="text-muted">{{ __('You can attach multiple files') }}</small>
            </div>

            <!-- Send Button -->
            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-send me-1"></i> {{ __('Send Email') }}
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    #sendEmailDrawer .form-label {
        font-weight: 600;
    }

    #sendEmailDrawer input,
    #sendEmailDrawer textarea,
    #sendEmailDrawer select {
        border-radius: 0.4rem;
    }

    #sendEmailDrawer button.btn-primary {
        min-width: 120px;
    }

    #sendEmailDrawer .offcanvas-body {
        max-height: calc(100vh - 70px);
        overflow-y: auto;
    }
</style>

<script>
    // Populate email body when template is selected
    document.getElementById('emailTemplate').addEventListener('change', function () {
        const template = this.value;
        const bodyField = document.getElementById('emailBody');

        switch (template) {
            case 'welcome':
                bodyField.value = "{{ __('Hello [Name],') }}\n\n{{ __('Welcome to our platform!') }}";
                break;
            case 'payment_reminder':
                bodyField.value = "{{ __('Dear [Name],') }}\n\n{{ __('This is a friendly reminder for your pending payment.') }}";
                break;
            case 'invoice':
                bodyField.value = "{{ __('Hello [Name],') }}\n\n{{ __('Please find attached your latest invoice.') }}";
                break;
            default:
                bodyField.value = "";
        }
    });
</script>
