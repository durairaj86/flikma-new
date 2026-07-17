CUSTOMER_AGING = {
    title: 'Customer Aging Report',
    baseUrl: 'reports/customer-aging',
    actionUrl: 'reports/customer-aging',
    load() {
        CURRENCY.currencyRate();
        CUSTOMER_AGING.initDatepickers();
    },
    initDatepickers() {
        function syncToLivewire(el, dateStr) {
            el.value = dateStr;
            el.dispatchEvent(new Event('input', { bubbles: true }));
        }

        const asOfEl = document.getElementById('ca-as-of-date');
        if (asOfEl) {
            flatpickr(asOfEl, {
                dateFormat:    'Y-m-d',
                altInput:      true,
                altFormat:     'd-m-Y',
                allowInput:    true,
                disableMobile: true,
                defaultDate:   asOfEl.value || null,
                onChange(selectedDates, dateStr) {
                    syncToLivewire(asOfEl, dateStr);
                },
            });
        }
    },
}
