CUSTOMER_STATEMENT = {
    title: trans('Customer Statement'),
    baseUrl: 'reports/customer-statement',
    actionUrl: 'reports/customer-statement',
    load() {
        CURRENCY.currencyRate();
    },
}
