TAX_SUMMARY = {
    title: trans('Tax Summary'),
    baseUrl: 'reports/tax-summary',
    actionUrl: 'reports/tax-summary',
    load() {
        CURRENCY.currencyRate();
    },
}
