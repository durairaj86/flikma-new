CUSTOMER_STATEMENT = {
    title: 'Customer Statement',
    baseUrl: 'reports/customer-statement',
    actionUrl: 'reports/customer-statement',
    load() {
        CURRENCY.currencyRate();
        CUSTOMER_STATEMENT.initDatepickers();
    },
    initDatepickers() {
        // Fire a native 'input' event so wire:model picks up the new date
        function syncToLivewire(el, dateStr) {
            el.value = dateStr;
            el.dispatchEvent(new Event('input', { bubbles: true }));
        }

        const startEl = document.getElementById('cs-start-date');
        const endEl   = document.getElementById('cs-end-date');

        if (startEl) {
            flatpickr(startEl, {
                dateFormat:    'Y-m-d',
                altInput:      true,
                altFormat:     'd-m-Y',
                allowInput:    true,
                disableMobile: true,
                defaultDate:   startEl.value || null,
                onChange(selectedDates, dateStr) {
                    syncToLivewire(startEl, dateStr);
                },
            });
        }

        if (endEl) {
            flatpickr(endEl, {
                dateFormat:    'Y-m-d',
                altInput:      true,
                altFormat:     'd-m-Y',
                allowInput:    true,
                disableMobile: true,
                defaultDate:   endEl.value || null,
                onChange(selectedDates, dateStr) {
                    syncToLivewire(endEl, dateStr);
                },
            });
        }
    },
}
