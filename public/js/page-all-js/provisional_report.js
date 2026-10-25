PROVISIONAL_REPORT = {
    title: trans('Provisional Report'),
    baseUrl: 'reports/provisional-report',
    actionUrl: 'reports/provisional-report',
    load() {
        CURRENCY.currencyRate();
    },
}
