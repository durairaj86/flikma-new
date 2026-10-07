<script src="{{ asset('fontawesome/js/all.js') }}"></script>
<!--end::Third Party Plugin(OverlayScrollbars)--><!--begin::Required Plugin(popperjs for Bootstrap 5)-->
<script src="{{ asset('js/popper.min.js') }}" crossorigin="anonymous"></script>
<!--end::Required Plugin(popperjs for Bootstrap 5)--><!--begin::Required Plugin(Bootstrap 5)-->

<script src="{{ asset('js/jquery-3.7.1.min.js') }}"></script>
<script src="{{ asset('js/bootstrap.min.js') }}" crossorigin="anonymous"></script>

<!-- DataTables JS -->
<script src="{{ asset('js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('js/dataTables.bootstrap5.min.js') }}"></script>

<script src="{{ asset('js/adminlte.js') }}"></script>{{--for turbo no need here--}}

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
<script src="{{ asset('js/toastr.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/form-validation.js?v='.appVersion()) }}"></script>
<script type="text/javascript" src="{{ asset('js/jquery-confirm.js') }}"></script>
<script src="{{ asset('js/bootstrap-select.js') }}"></script>
<script src="{{ asset('js/flatpickr/flatpickr.js') }}"></script>
<script src="{{ asset('js/quill/quill.js') }}"></script>

<script src="{{ asset('js/html2pdf/html2pdf.js') }}"></script>


<script>
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
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
