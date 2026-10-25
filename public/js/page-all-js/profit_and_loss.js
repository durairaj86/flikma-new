PROFIT_AND_LOSS = {
    title: trans('Profit and Loss'),
    baseUrl: 'reports/profit-and-loss',
    actionUrl: 'reports/profit-and-loss',
    load() {
        CURRENCY.currencyRate();
    },
}
