TAX_SUMMARY = {
    title: 'Tax Summary',
    baseUrl: 'reports/tax-summary',
    actionUrl: 'reports/tax-summary',
    load() {
        CURRENCY.currencyRate();
    },
}
