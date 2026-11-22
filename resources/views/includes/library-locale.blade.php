{{-- shared by the app and the customer portal: each library is only touched when the page loaded it --}}
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
