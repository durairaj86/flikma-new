CUSTOMER_BALANCE_SUMMARY = {
    title: 'Customer Balance Summary',
    baseUrl: 'reports/customer-balance-summary',
    actionUrl: 'reports/customer-balance-summary',
    load() {
        CURRENCY.currencyRate();
    },
}
