INPUT_TAX = {
    title: 'Input Tax Report',
    baseUrl: 'reports/input-tax',
    actionUrl: 'reports/input-tax',
    load() {
        CURRENCY.currencyRate();
    },
}
