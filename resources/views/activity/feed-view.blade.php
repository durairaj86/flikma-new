{{--<div class="offcanvas offcanvas-end customer-drawer" tabindex="-1" id="moduleFeedViewDrawer" style="width: 30%;">--}}
    <div
        class="offcanvas offcanvas-end"
        tabindex="-1"
        id="moduleFeedViewDrawer"
        aria-labelledby="activityOffcanvasLabel" style="width: 30%;"
    >
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title fw-semibold text-dark" id="activityOffcanvasLabel">
            <svg class="w-6 h-6 me-2 text-primary" width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
            Activity Feed (Timeline)
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body px-0 py-0">
        <!-- Tab Content -->
        <div class="tab-pane fade show active" id="feedViewTab">
            <div id="feedOverview">
                @include('activity.feed')
            </div>
        </div>
    </div>
</div>
<style>
    /* Add this to your custom application stylesheet (e.g., app.css) */

    .timeline-list {
        position: relative;
        /*padding-left: 30px;*/ /* Space for the line and points */
    }

    /*.timeline-list::before {
        content: '';
        position: absolute;
        top: 0;
        left: 10px; !* Position the vertical line *!
        height: 100%;
        width: 2px;
        background-color: #dee2e6; !* Bootstrap border color *!
    }*/

    .timeline-item {
        position: relative;
        margin-bottom: 20px;
    }

    .timeline-pin {
        position: absolute;
        top: 5px; /* Adjust pin position */
        left: -20px;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        z-index: 20;
        border: 3px solid #fff; /* White border to lift it off the line */
    }

    .timeline-item:last-child {
        margin-bottom: 0;
    }

    /* Force the inner container to scroll and take full height */
    #activity-feed-scroll-container {
        height: calc(100vh - 60px); /* Use viewport height minus header height */
        overflow-y: auto; /* THIS FORCES THE SCROLLBAR HERE */
        -webkit-overflow-scrolling: touch;
        padding: 1rem; /* Re-apply the padding lost from offcanvas-body p-0 */
    }

    /* Ensure the offcanvas body takes full height */
    /*.offcanvas-body {
        height: calc(100% - 60px); !* Subtract header height *!
        overflow: hidden; !* Let the inner container handle scrolling *!
    }*/

    /* Ensure the tab content takes full height */
    #feedViewTab, #feedOverview {
        height: 100%;
    }
</style>
{{--
@push('scripts')
    <script>
        // Extend the PAYMENT object with edit functionality
        if (typeof PAYMENT === 'undefined') {
            PAYMENT = {
                baseUrl: 'transaction/payments'
            };
        }

        PAYMENT.edit = {
            selectedInvoices: [],
            invoicesData: [],
            paymentStatus: {{ $payment->status }},
            isDraft: {{ $payment->status }} === 1,

            init: function () {
                // Initialize selected invoices from existing data
                @foreach($payment->paymentInvoices as $paymentInvoice)
                PAYMENT.edit.selectedInvoices.push({
                    id: {{ $paymentInvoice->supplier_invoice_id }},
                    amount: {{ $paymentInvoice->amount }}
                });
                @endforeach

                // Only enable these events if payment is in draft status
                if (PAYMENT.edit.isDraft) {
                    // Select/deselect all invoices
                    $('#select-all-invoices').on('change', function () {
                        const isChecked = $(this).is(':checked');
                        $('.invoice-checkbox').prop('checked', isChecked).trigger('change');
                    });

                    // Load invoices when supplier changes
                    $('#supplier_id').on('change', function () {
                        const supplierId = $(this).val();
                        PAYMENT.edit.selectedInvoices = [];
                        PAYMENT.edit.loadSupplierInvoices(supplierId);
                    });

                    // Form submission
                    $('#payment-form').on('submit', function (e) {
                        e.preventDefault();
                        PAYMENT.edit.submitForm($(this));
                    });
                }

                // Initialize event handlers for existing invoice checkboxes and amount inputs
                PAYMENT.edit.bindExistingInvoiceEvents();

                // Add event listeners for bank charges and other charges
                $('#bank_charges, #other_charges').on('input', function () {
                    PAYMENT.edit.calculateTotal();
                });

                // Calculate initial total
                PAYMENT.edit.calculateTotal();
            },

            // Format number with 2 decimal places
            formatNumber: function (num) {
                return parseFloat(num).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
            },

            // Calculate total payment amount
            calculateTotal: function () {
                let total = 0;
                $('.invoice-amount').each(function () {
                    if ($(this).closest('tr').find('.invoice-checkbox').is(':checked')) {
                        total += parseFloat($(this).val() || 0);
                    }
                });

                // Add bank charges and other charges
                let bankCharges = parseFloat($('#bank_charges').val() || 0);
                let otherCharges = parseFloat($('#other_charges').val() || 0);
                total += bankCharges + otherCharges;

                $('#total-payment-amount').text(PAYMENT.edit.formatNumber(total));
            },

            // Load supplier invoices
            loadSupplierInvoices: function (supplierId) {
                if (!supplierId) {
                    $('#no-invoices-message').show();
                    $('#invoices-table-container').hide();
                    return;
                }

                $.ajax({
                    url: `/${PAYMENT.baseUrl}/supplier/${supplierId}/invoices`,
                    method: 'GET',
                    success: function (response) {
                        PAYMENT.edit.invoicesData = response;

                        if (response.length === 0) {
                            $('#no-invoices-message').text('No approved invoices found for this supplier.').show();
                            $('#invoices-table-container').hide();
                            return;
                        }

                        $('#no-invoices-message').hide();
                        $('#invoices-table-container').show();

                        let html = '';
                        response.forEach(function (invoice) {
                            // Check if this invoice is already selected
                            const existingInvoice = PAYMENT.edit.selectedInvoices.find(inv => inv.id === invoice.id);
                            const isChecked = existingInvoice !== undefined;
                            const amount = existingInvoice ? existingInvoice.amount : invoice.grand_total;

                            html += '<tr>' +
                                '<td>' +
                                '<input type="checkbox" class="invoice-checkbox" data-id="' + invoice.id + '" data-amount="' + invoice.grand_total + '"' +
                                (isChecked ? ' checked' : '') +
                                (!PAYMENT.edit.isDraft ? ' disabled' : '') + '>' +
                                '</td>' +
                                '<td>' + invoice.row_no + '</td>' +
                                '<td>' + invoice.invoice_date + '</td>' +
                                '<td>' + invoice.due_at + '</td>' +
                                '<td>' + PAYMENT.edit.formatNumber(invoice.grand_total) + '</td>' +
                                '<td>' +
                                '<input type="number" step="0.01" class="form-control invoice-amount" data-id="' + invoice.id + '" value="' + amount + '" min="0" max="' + invoice.grand_total + '"' +
                                (!isChecked || !PAYMENT.edit.isDraft ? ' disabled' : '') + '>' +
                                '</td>' +
                                '</tr>';
                        });

                        $('#invoices-body').html(html);

                        // Enable/disable amount inputs based on checkbox
                        $('.invoice-checkbox').on('change', function () {
                            PAYMENT.edit.handleInvoiceCheckboxChange($(this));
                        });

                        // Update total when amount changes
                        $('.invoice-amount').on('input', function () {
                            PAYMENT.edit.handleAmountChange($(this));
                        });

                        PAYMENT.edit.calculateTotal();
                    },
                    error: function (xhr) {
                        toastr.error(xhr.responseJSON?.message || 'Error loading supplier invoices');
                    }
                });
            },

            // Handle invoice checkbox change
            handleInvoiceCheckboxChange: function ($checkbox) {
                if (!PAYMENT.edit.isDraft) return;

                const isChecked = $checkbox.is(':checked');
                const amountInput = $checkbox.closest('tr').find('.invoice-amount');
                const id = $checkbox.data('id');

                amountInput.prop('disabled', !isChecked);

                if (isChecked) {
                    // Add to selected invoices
                    PAYMENT.edit.selectedInvoices.push({
                        id: id,
                        amount: parseFloat(amountInput.val())
                    });
                } else {
                    // Remove from selected invoices
                    PAYMENT.edit.selectedInvoices = PAYMENT.edit.selectedInvoices.filter(inv => inv.id !== id);
                }

                PAYMENT.edit.calculateTotal();
            },

            // Handle amount change
            handleAmountChange: function ($input) {
                if (!PAYMENT.edit.isDraft) return;

                const id = $input.data('id');
                const amount = parseFloat($input.val() || 0);

                // Update in selected invoices
                const index = PAYMENT.edit.selectedInvoices.findIndex(inv => inv.id === id);
                if (index !== -1) {
                    PAYMENT.edit.selectedInvoices[index].amount = amount;
                }

                PAYMENT.edit.calculateTotal();
            },

            // Bind events for existing invoice checkboxes and amount inputs
            bindExistingInvoiceEvents: function () {
                $('.invoice-checkbox').on('change', function () {
                    if (!PAYMENT.edit.isDraft) return;

                    const isChecked = $(this).is(':checked');
                    const amountInput = $(this).closest('tr').find('.invoice-amount');
                    const id = $(this).data('id');

                    amountInput.prop('disabled', !isChecked);

                    if (isChecked) {
                        // Add to selected invoices if not already there
                        if (!PAYMENT.edit.selectedInvoices.some(inv => inv.id === id)) {
                            PAYMENT.edit.selectedInvoices.push({
                                id: id,
                                amount: parseFloat(amountInput.val())
                            });
                        }
                    } else {
                        // Remove from selected invoices
                        PAYMENT.edit.selectedInvoices = PAYMENT.edit.selectedInvoices.filter(inv => inv.id !== id);
                    }

                    PAYMENT.edit.calculateTotal();
                });

                $('.invoice-amount').on('input', function () {
                    if (!PAYMENT.edit.isDraft) return;

                    const id = $(this).data('id');
                    const amount = parseFloat($(this).val() || 0);

                    // Update in selected invoices
                    const index = PAYMENT.edit.selectedInvoices.findIndex(inv => inv.id === id);
                    if (index !== -1) {
                        PAYMENT.edit.selectedInvoices[index].amount = amount;
                    }

                    PAYMENT.edit.calculateTotal();
                });
            },

            // Submit form
            submitForm: function ($form) {
                // Validate form
                if (!$('#supplier_id').val()) {
                    toastr.error('Please select a supplier');
                    return;
                }

                if (PAYMENT.edit.selectedInvoices.length === 0) {
                    toastr.error('Please select at least one invoice');
                    return;
                }

                // Prepare form data
                const formData = $form.serializeArray();

                // Add selected invoices
                formData.push({
                    name: 'supplier_invoice_ids',
                    value: JSON.stringify(PAYMENT.edit.selectedInvoices.map(inv => inv.id))
                });
                formData.push({
                    name: 'invoice_amounts',
                    value: JSON.stringify(PAYMENT.edit.selectedInvoices.map(inv => inv.amount))
                });

                // Submit form
                $.ajax({
                    url: $form.attr('action'),
                    method: 'POST',
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        toastr.success(response.message);
                        window.location.href = '{{ route("transaction.payments.index") }}';
                    },
                    error: function (xhr) {
                        toastr.error(xhr.responseJSON?.message || 'Error updating payment');
                    }
                });
            }
        };

        // Initialize the payment edit module
        $(function () {
            PAYMENT.edit.init();
        });
    </script>
@endpush--}}
