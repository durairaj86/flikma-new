CUSTOMER_STATEMENT = {
    title: trans('Customer Statement'),
    baseUrl: 'reports/customer-statement',
    actionUrl: 'reports/customer-statement',
    load() {
        CUSTOMER_STATEMENT.recordHere();
        CURRENCY.currencyRate();
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
    // the modal form is the collection form: its hooks run from here
    form: {
        openCallback() { COLLECTION.form.openCallback(); },
    },
    before: {
        submit() { return COLLECTION.before.submit(); },
    },
}
