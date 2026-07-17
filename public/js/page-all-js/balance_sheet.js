BALANCE_SHEET = {
    title: 'Balance Sheet',
    baseUrl: 'reports/balance-sheet',
    actionUrl: 'reports/balance-sheet',
    load() {
        CURRENCY.currencyRate();
    },
}
