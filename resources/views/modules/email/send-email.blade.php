<!-- Send Email Offcanvas -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="sendEmailDrawer" style="width: 600px;">
    <div class="offcanvas-header border-bottom">
        <h5 id="sendEmailDrawerLabel">Send Email</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>

    <div class="offcanvas-body p-3">
        <form id="sendEmailForm" enctype="multipart/form-data">
            <!-- To / CC -->
            <div class="mb-3">
                <label for="emailTo" class="form-label">To</label>
                <input type="email" class="form-control" id="emailTo" name="to"
                       placeholder="Enter recipient email" required>
            </div>
            <div class="mb-3">
                <label for="emailCc" class="form-label">CC</label>
                <input type="email" class="form-control" id="emailCc" name="cc"
                       placeholder="Enter CC email (optional)">
            </div>

            <!-- Subject -->
            <div class="mb-3">
                <label for="emailSubject" class="form-label">Subject</label>
                <input type="text" class="form-control" id="emailSubject" name="subject"
                       placeholder="Email subject" required>
            </div>

            <!-- Template Dropdown -->
            <div class="mb-3">
                <label for="emailTemplate" class="form-label">Template</label>
                <select class="tom-select" id="emailTemplate" name="template">
                    <option value="">-- Select Template --</option>
                    <option value="welcome">Welcome Message</option>
                    <option value="payment_reminder">Payment Reminder</option>
                    <option value="invoice">Invoice Notification</option>
                    <!-- Add more templates here -->
                </select>
            </div>

            <!-- Message Editor -->
            <div class="mb-3">
                <label for="emailBody" class="form-label">Message</label>
                <textarea class="form-control" style="min-height: 10rem" id="emailBody" name="body" rows="8"
                          placeholder="Write your message..."></textarea>
            </div>

            <!-- Attachments -->
            <div class="mb-3">
                <label for="emailAttachment" class="form-label">Attachment</label>
                <input type="file" class="form-control" id="emailAttachment" name="attachment[]" multiple>
                <small class="text-muted">You can attach multiple files</small>
            </div>

            <!-- Send Button -->
            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-send me-1"></i> Send Email
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
                bodyField.value = "Hello [Name],\n\nWelcome to our platform!";
                break;
            case 'payment_reminder':
                bodyField.value = "Dear [Name],\n\nThis is a friendly reminder for your pending payment.";
                break;
            case 'invoice':
                bodyField.value = "Hello [Name],\n\nPlease find attached your latest invoice.";
                break;
            default:
                bodyField.value = "";
        }
    });
</script>
