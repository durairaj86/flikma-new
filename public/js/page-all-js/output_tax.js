OUTPUT_TAX = {
    title: 'Output Tax Report',
    baseUrl: 'reports/output-tax',
    actionUrl: 'reports/output-tax',
    load() {
        CURRENCY.currencyRate();
    },
}
