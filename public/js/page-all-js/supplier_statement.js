SUPPLIER_STATEMENT = {
    title: 'Supplier Statement',
    baseUrl: 'reports/supplier-statement',
    actionUrl: 'reports/supplier-statement',
    load() {
        CURRENCY.currencyRate();
        SUPPLIER_STATEMENT.initDatepickers();
    },
    initDatepickers() {
        // Fire a native 'input' event so wire:model picks up the new date
        function syncToLivewire(el, dateStr) {
            el.value = dateStr;
            el.dispatchEvent(new Event('input', { bubbles: true }));
        }

        const startEl = document.getElementById('ss-start-date');
        const endEl   = document.getElementById('ss-end-date');

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
