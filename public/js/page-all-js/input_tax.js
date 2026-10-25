INPUT_TAX = {
    title: trans('Input Tax Report'),
    baseUrl: 'reports/input-tax',
    actionUrl: 'reports/input-tax',
    load() {
        CURRENCY.currencyRate();
    },
}
