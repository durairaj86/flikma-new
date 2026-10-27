<script src="{{ asset('fontawesome/js/all.js') }}"></script>
<!--end::Third Party Plugin(OverlayScrollbars)--><!--begin::Required Plugin(popperjs for Bootstrap 5)-->
<script src="{{ asset('js/popper.min.js') }}" crossorigin="anonymous"></script>
<!--end::Required Plugin(popperjs for Bootstrap 5)--><!--begin::Required Plugin(Bootstrap 5)-->

<script src="{{ asset('js/jquery-3.7.1.min.js') }}"></script>
<script src="{{ asset('js/bootstrap.min.js') }}" crossorigin="anonymous"></script>

<!-- DataTables JS -->
<script src="{{ asset('js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('js/dataTables.bootstrap5.min.js') }}"></script>

<script src="{{ asset('js/adminlte.js') }}?v={{ appVersion() }}"></script>{{--for turbo no need here--}}

{{--<script src="{{ asset('js/turbo@8.0.4/turbo.es2017-umd.js') }}"></script>--}}

{{--

<script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>
--}}

<script src="{{ asset('js/tom-select@2.4.3/tom-select.complete.min.js') }}"></script>


{{--@if(env('APP_JS') !== 'local')
    <script src="{{ asset('js/all.js') }}"></script>
@endif--}}
@php
    $i18nKeys = [
        'Convert to invoice?', 'A draft customer invoice is created with these lines. Check it and approve it to post it.', 'Convert',
        'required',
        'Any carrier', 'days', 'from',
        'Due',
        'Done', 'Started', 'Cost', 'Margin', 'Days', 'Overdue', ':n days ago', ':n days left',
        'Cus Inv.', 'CN', 'SI', 'CI', 'Company currency',
        'Skip to main content', 'Skip to navigation', 'Show the unpaid invoices', 'Show the detail',
        'AWB',
        'AWB:',
        'Accounts',
        'Active',
        'Activity',
        'Add Airport',
        'Add Bank',
        'Add Customer',
        'Add Department',
        'Add Description',
        'Add Employee',
        'Add Enquiry',
        'Add Package Code',
        'Add Prospect',
        'Add Quick Customer',
        'Add Quick Prospect',
        'Add Salesperson',
        'Add Seaport',
        'Add Service',
        'Add Supplier',
        'Add Unit',
        'Add to charges',
        'Advance',
        'Airway Bill',
        'Already used on collections',
        'Amount',
        'An error occurred during import.',
        'An error occurred while deleting the opening balance entry.',
        'Applied to',
        'Are you sure you want to delete :name?',
        'Are you sure you want to delete this HS Tariff?',
        'Are you sure you want to delete this period?',
        'Are you sure you want to delete this quotation term?',
        'Arrival Notice',
        'Asset',
        'Balance',
        'Balance Sheet',
        'Bayan filed',
        'Bayan:',
        'Booking',
        'Cleared',
        'Client',
        'Close Period',
        'Closed',
        'Closing this period LOCKS every transaction dated on or before its closing date. This cannot be undone by regular users. Continue?',
        'Column',
        'Company',
        'Compliance Certification',
        'Confirmed',
        'Converted',
        'Create Collection',
        'Create Journal Voucher',
        'Create Payment',
        'Created At',
        'Credit',
        'Credit notes',
        'Crop your logo',
        'Crop your signature',
        'Cus Inv./CN',
        'Customer Activity Report',
        'Customer Aging Report',
        'Customer Balance Summary',
        'Customer Invoice',
        'Customer Statement',
        'Customer credit',
        'Customs Clearance',
        'D.O Collected',
        'D.O issued',
        'Date',
        'Debit',
        'Debit Note',
        'Delivery Order',
        'Department',
        'Description',
        'Destination',
        'Destination (POD)',
        'Destination:',
        'Dispatched',
        'Document',
        'Driver',
        'Due Amount',
        'Due date',
        'ETA',
        'ETD',
        'Edit Journal Voucher',
        'Employee',
        'Enquiry',
        'Enquiry No',
        'Expense',
        'Expired',
        'Expiring',
        'Failed to load arrival notice details.',
        'Failed to load booking details.',
        'Failed to load clearance details.',
        'Failed to load collection details.',
        'Failed to load credit note details.',
        'Failed to load debit note details.',
        'Failed to load delivery order details.',
        'Failed to load details.',
        'Failed to load enquiry details.',
        'Failed to load expense details.',
        'Failed to load invoice details.',
        'Failed to load voucher details.',
        'Failed to load job details.',
        'Failed to load master B/L details.',
        'Failed to load payment details.',
        'Failed to load quotation details.',
        'Failed to load rate details.',
        'Failed to load.',
        'Fixed',
        'General Ledger',
        'HS Tariff',
        'INCO Term',
        'In Transit',
        'Inactive',
        'Input Tax Report',
        'Invalid date',
        'Invoice',
        'Invoice total',
        'Issued',
        'Job Balance Report',
        'Job Date',
        'Job Income Report',
        'Job No',
        'Job Report',
        'Journal Voucher',
        'Loading...',
        'Maintenance',
        'Master B/L',
        'Mode:',
        'New Account',
        'New Airway Bill',
        'New Arrival Notice',
        'New Asset',
        'New Booking',
        'New Customer Invoice',
        'New Debit Note',
        'New Delivery Order',
        'New Department',
        'New Document',
        'New Driver',
        'New Expense',
        'New HS Tariff',
        'New Job',
        'New Master B/L',
        'New Period',
        'New Proforma Invoice',
        'New Quotation',
        'New Quotation Term',
        'New Rate',
        'New Seaway Bill',
        'New Supplier Invoice',
        'New Trip',
        'New Vehicle',
        'New Waybill',
        'No approved invoices found for this customer.',
        'No approved invoices found for this supplier.',
        'No columns selected. Click fields on the left to add them.',
        'No due date',
        'No expiry',
        'No free house bills for this mode.',
        'No unused amount.',
        'Nothing is due.',
        'Notified',
        'On Hold',
        'On Time',
        'On time',
        'Origin',
        'Origin (POL)',
        'Origin:',
        'Output Tax Report',
        'Overdue invoices',
        'PAID',
        'POD:',
        'POL:',
        'Package Code',
        'Payment',
        'Pending',
        'Period Closing',
        'Planned',
        'Profit and Loss',
        'Proforma Invoice',
        'Prospect',
        'Provisional Report',
        'Quotation Term',
        'Quote No',
        'Rate',
        'Rate :no added to charges',
        'Rejected',
        'Reopen Period',
        'Reopening this period unlocks every transaction dated on or before its closing date for editing again. Continue?',
        'STAGE 1: ACTIVATED',
        'STAGE 1: SIMULATION',
        'Sales Person',
        'Sales Report',
        'Save',
        'Save & New',
        'Seaway Bill',
        'Select Account',
        'Select Customer',
        'Select Supplier',
        'Select Type',
        'Sent',
        'Services',
        'Settled',
        'Shipment Mode',
        'Shipped',
        'Some information has been modified. Please save your changes or continue without saving.',
        'Started',
        'Status',
        'Sub',
        'Sub-columns',
        'Successfully imported :n customers.',
        'Successfully imported :n suppliers.',
        'Supplier',
        'Supplier Aging Report',
        'Supplier Balance Summary',
        'Supplier Invoice',
        'Supplier Ledger',
        'Supplier Statement',
        'Taken from',
        'Tax Summary',
        'Test Again',
        'Testing...',
        'The customer paid / was credited more than these invoices were worth. Use it by choosing Credit Applied on another invoice when you record a collection, or refund it.',
        'Total due',
        'Total used',
        'Trip',
        'Under Process',
        'Unused',
        'Unused Amount',
        'Upcoming',
        'Update Tracking',
        'Valid To',
        'Validate',
        'Validating...',
        'Vehicle',
        'Waybill',
        'Waybill Report',
        'cash beyond the invoice',
        'convert to invoice',
        'expiring',
        'Validation Failed', 'First', 'Collection History', 'New Credit Note', 'billed in', 'Quotation', 'selling price', 'Supplier invoices', 'cost', 'lines', 'is already billed in', 'Add it again?', 'Only', 'of credit can be used on this invoice.', 'Each attachment must be 2 MB or less.',
        'View details',
        'quotations',
        'Do you want to change the job?',
        'The lines already added will be cleared.',
        'Yes, change',
        'Change job?',
        'Already billed',
        'Changing the job will clear the lines already added. Do you want to change the job?',
        'Invoice Amount',
        'Due tomorrow',
        'Due today',
        'days left',
        'days overdue',
        'Unpaid',
        'Part paid',
        'Paid',
        'VAT',
        'Excl. VAT',
        'free left',
        'In detention',
        'Due',
        'Continue?',
        'Collection',
        'Credit applied',
        'Revenue',
        'Cost',
        'Profit',
        'Ref',
        'Credit Note',
        'Total',
        'Draft',
        'Approved',
        'Credit Notes',
        'Customer Inv.',
        'Supplier Inv.',
        'Not started',
        'Stage updated',
        'Stage',
        'Skip this step (not needed for this job)',
        'Skip',
        'skipped',
        'Trashed',
        'Cancelled',
        'Completed',
        'draft',
        'approved',
        'Incoterm',
        'Value',
        'Invoices',
        'Carrier',
        'Open',
        'Complete job',
        'D/O released',
        'Documents received',
        'Done',
        'Cargo',
        'Customs',
        'containers',
        'pcs',
        'Deliver',
        'Update',
        'Mark the next step done today',
        'In transit',
        'Picked up',
        'Flight arrived',
        'Flight departed',
        'Delivered',
        'Customs cleared',
        'Vessel arrived',
        'Vessel departed',
        'Loaded on vessel',
        'Cargo received',
        'Booking confirmed',
        'Job',
        'Route',
        'Dates',
        'Progress',
        'Billing',
        'Next',
        'Complete',
        'Invoiced',
        'Draft invoice',
        'Not invoiced',
        'Thinking...',
        'Arrived',
        'Today',
        'No ETA',
        'jobs are past their ETA',
        'arrive within a week',
        'have no invoice yet',
        'are in customs clearance',
        'Everything looks on track.',
        'Delete :name? This cannot be undone.',
        'Cannot delete :name',
        'Cannot delete',
        'Close',
        'Confirm',
        'Delete record?',
        'This cannot be undone.',
        'Cancel',
        'Terminate',
        'Reactivate',
        'Terminate user?',
        'Reactivate user?',
        'This user will no longer be able to log in. Their history stays.',
        'This user will be able to log in again.',
        'selected',
        'Select a job to choose its containers.',
        'This job has no containers.',
        'Are you sure you want to change status?',
        'Are you sure you want to convert this customer to Confirmed?',
        'Why do you want to reject this customer?',
        'Why do you want to block this customer?',
        'Confirm!',
        'Enter reason...',
        'No',
        'Yes',
        'Are you sure you want to delete?',
        'Modal',
        'Close main modal?',
        'Save as Draft',
        'Save and Approve',
        'Save and open new form',
        'Submit the form',
        'Are you sure you want to close the modal?',
        'Are you sure you want to exit?',
        'Exit',
        'Confirm',
        'Failed to load actions',
        'Something went wrong!',
        'Edit',
        'Submit',
        'Saved, but approving it failed — it is still saved as a draft.',
        'An error occurred while deleting the record.',
        'An error occurred while sending the email.',
        'Asset not identified',
        'Charge deleted.',
        'Collection amount cannot exceed the balance amount',
        'Could not reset column settings.',
        'Could not save column settings.',
        'Deleted!',
        'Enter valid otp',
        'Error deleting expense',
        'Error deleting record.',
        'Error!',
        'Failed to delete this HS Tariff.',
        'Failed to delete this quotation term.',
        'Failed to fetch customer list',
        'Failed to fetch exchange rate',
        'Failed to finalise quotation.',
        'Nothing to export yet — apply filters to load the report first.',
        'Payment amount cannot exceed the balance amount',
        'Please map at least one column.',
        'Please provide a reason before proceeding.',
        'Please provide a reason for disapproval',
        'Please select a customer',
        'Please select a file to upload',
        'Please select a supplier',
        'Please select an employee',
        'Please select at least one invoice',
        'Please select at least one row to delete.',
        'Select Shipment Category first!',
        'Server error',
        'Success!',
        'Terms & Conditions text is required.',
        'Confirm Delete',
        'Are you sure you want to delete this record?',
        'Delete',
        'This supplier invoice is already added.',
        'added to the invoice lines.',
        'Mark as Confirmed',
        'Convert to Quotation',
        'Mark as Cancelled',
        'Print',
        'Are you sure you want to mark this enquiry as Confirmed?',
        'Are you sure you want to cancel this enquiry?',
        'Are you sure you want to move this enquiry back to Pending?',
        'Please fill in the required field',
        'has no invoices for the selected period',
        'No invoices found for the selected period',
        'Try a different period or customer, or create a new invoice.',
        'Change filters',
        'Create Invoice',
        'Try a different period or supplier, or create a new invoice.',
        'has no :items for the selected period',
        'No :items found for the selected period',
        'Try a different period or filter, or create a new one.',
        'In other tabs:',
        'Already credited',
        'Still creditable',
        'this invoice is fully credited.',
        'Customer',
    ];
    $i18nMap = collect($i18nKeys)->mapWithKeys(fn($key) => [$key => __($key)]);
@endphp
<script>
    /* startup.js can't run through Blade's __(), so the confirm/toast text
       it hardcodes is looked up here instead: trans(key) returns the
       translation for the current locale, or the English key itself when
       none exists (English) or a key is missing from lang/ar.json. */
    window.i18n = @json($i18nMap);

    window.trans = function (key) {
        return (window.i18n && window.i18n[key]) || key;
    };
</script>
<script type="text/javascript" src="{{ asset('js/startup.js?v='.appVersion()) }}" defer></script>
<script src="{{ asset('js/number-input.js') }}?v={{ appVersion() }}"></script>
<script src="{{ asset('js/keyboard.js') }}?v={{ appVersion() }}"></script>
<script src="{{ asset('js/input-lengths.js') }}?v={{ appVersion() }}"></script>
<script src="{{ asset('js/input-maxlength.js') }}?v={{ appVersion() }}"></script>
<script src="{{ asset('js/toastr.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/form-validation.js?v='.appVersion()) }}"></script>
<script type="text/javascript" src="{{ asset('js/jquery-confirm.js') }}"></script>
<script src="{{ asset('js/bootstrap-select.js') }}"></script>
<script src="{{ asset('js/flatpickr/flatpickr.js') }}"></script>
@if(app()->getLocale() === 'ar')
    {{-- library defaults (DataTables, flatpickr, tom-select) speak Arabic too; pages only override a key or two --}}
    <script>
        (function () {
            if (window.jQuery && jQuery.fn.dataTable) {
                jQuery.extend(true, jQuery.fn.dataTable.defaults, {
                    language: {
                        emptyTable: @json(__('No data available')),
                        info: @json(__('Showing _START_ to _END_ of _TOTAL_ entries')),
                        infoEmpty: @json(__('Showing 0 to 0 of 0 entries')),
                        infoFiltered: @json(__('(filtered from _MAX_ total entries)')),
                        lengthMenu: @json(__('Show _MENU_ entries')),
                        loadingRecords: @json(__('Loading...')),
                        processing: @json(__('Processing...')),
                        zeroRecords: @json(__('No matching records found')),
                        paginate: {first: @json(__('First')), last: @json(__('Last')), next: @json(__('Next')), previous: @json(__('Previous'))},
                    },
                });
            }
            if (window.flatpickr) {
                flatpickr.localize({
                    weekdays: {
                        shorthand: ['أحد', 'اثنين', 'ثلاثاء', 'أربعاء', 'خميس', 'جمعة', 'سبت'],
                        longhand: ['الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'],
                    },
                    months: {
                        shorthand: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'],
                        longhand: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'],
                    },
                    firstDayOfWeek: 0, rangeSeparator: ' - ', weekAbbreviation: 'أسبوع', amPM: ['ص', 'م'], yearAriaLabel: 'سنة', time_24hr: false,
                });
            }
            if (window.TomSelect) {
                const Base = window.TomSelect;
                const noResults = @json(__('No results found'));
                window.TomSelect = class extends Base {
                    constructor(el, opts = {}) {
                        opts.render = Object.assign({no_results: () => '<div class="no-results">' + noResults + '</div>'}, opts.render || {});
                        super(el, opts);
                    }
                };
            }
        })();
    </script>
@endif
<script src="{{ asset('js/quill/quill.js') }}"></script>

<script src="{{ asset('js/html2pdf/html2pdf.js') }}"></script>


<script>
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-bottom-center",
        "timeOut": "3000"
    };
</script>

<!--begin::Third Party Plugin(OverlayScrollbars)-->
{{--<script
    src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"
    crossorigin="anonymous"
></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Initialize OverlayScrollbars on the body
        OverlayScrollbars(document.body, {
            scrollbars: {
                theme: 'os-theme-thin',
                autoHide: 'leave',
                size: 'thin'
            }
        });

        // Apply OverlayScrollbars to all elements with overflow-y: auto or scroll
        document.querySelectorAll('[style*="overflow-y: auto"], [style*="overflow-y:auto"], [style*="overflow-y: scroll"], [style*="overflow-y:scroll"], .overflow-y-auto, .overflow-y-scroll').forEach(function(element) {
            OverlayScrollbars(element, {
                scrollbars: {
                    theme: 'os-theme-thin',
                    autoHide: 'leave',
                    size: 'thin'
                }
            });
        });
    });
</script>--}}
