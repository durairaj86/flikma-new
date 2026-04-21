CUSTOMER_STATEMENT = {
    title: 'Customer Statement',
    baseUrl: 'reports/customer-statement',
    actionUrl: 'reports/customer-statement',
    load() {
        // datepicker();
        CURRENCY.currencyRate();
    },
}
