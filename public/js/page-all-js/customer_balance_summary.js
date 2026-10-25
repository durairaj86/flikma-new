CUSTOMER_BALANCE_SUMMARY = {
    title: trans('Customer Balance Summary'),
    baseUrl: 'reports/customer-balance-summary',
    actionUrl: 'reports/customer-balance-summary',
    load() {
        CURRENCY.currencyRate();
    },
}
