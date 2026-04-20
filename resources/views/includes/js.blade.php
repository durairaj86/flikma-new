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
<script type="text/javascript" src="{{ asset('js/startup.js?v='.appVersion()) }}" defer></script>
<script src="{{ asset('js/toastr.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/form-validation.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/jquery-confirm.js') }}"></script>
<script src="{{ asset('js/bootstrap-select.js') }}"></script>
<script src="{{ asset('js/flatpickr/flatpickr.js') }}"></script>

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
