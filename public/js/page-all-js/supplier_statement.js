SUPPLIER_STATEMENT = {
    title: trans('Supplier Statement'),
    baseUrl: 'reports/supplier-statement',
    actionUrl: 'reports/supplier-statement',
    load() {
        SUPPLIER_STATEMENT.recordHere();
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
    // Record Collection / Payment opens its form in the modal here, on the statement, and the statement reloads once it is saved
    recordHere() {
        $(document).off('click.stmtRecord').on('click.stmtRecord', '#stmtRecordOpen', function (e) {
            e.preventDefault();
            window.afterModalSave = () => setTimeout(() => window.location.reload(), 600);
            webModal.openGlobalModal({
                title: $(this).data('title'),
                url: $(this).data('url'),
                content: null,
                size: 'xl',
                scroll: true,
                minHeight: 'min-height:70vh;',
            });
        });
    },
    // the modal form is the payment form: its hooks run from here
    form: {
        openCallback() { PAYMENT.form.openCallback(); },
    },
    before: {
        submit() { return PAYMENT.before.submit(); },
    },
}
