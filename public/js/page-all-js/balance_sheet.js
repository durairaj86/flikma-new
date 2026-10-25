BALANCE_SHEET = {
    title: trans('Balance Sheet'),
    baseUrl: 'reports/balance-sheet',
    actionUrl: 'reports/balance-sheet',
    load() {
        CURRENCY.currencyRate();
    },
}
