SUPPLIER_AGING = {
    title: 'Supplier Aging Report',
    baseUrl: 'reports/supplier-aging',
    actionUrl: 'reports/supplier-aging',
    load() {
        CURRENCY.currencyRate();
        SUPPLIER_AGING.initDatepickers();
    },
    initDatepickers() {
        function syncToLivewire(el, dateStr) {
            el.value = dateStr;
            el.dispatchEvent(new Event('input', { bubbles: true }));
        }

        const asOfEl = document.getElementById('sa-as-of-date');
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
