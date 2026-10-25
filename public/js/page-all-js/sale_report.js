SALE_REPORT = {
    title: trans('Sales Report'),
    baseUrl: 'reports/sale-report',
    actionUrl: 'reports/sale-report',
    load() {
        CURRENCY.currencyRate();
    },
}
