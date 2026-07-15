PROVISIONAL_REPORT = {
    title: 'Provisional Report',
    baseUrl: 'reports/provisional-report',
    actionUrl: 'reports/provisional-report',
    load() {
        CURRENCY.currencyRate();
    },
}
