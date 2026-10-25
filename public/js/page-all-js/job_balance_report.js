JOB_BALANCE_REPORT = {
    title: trans('Job Balance Report'),
    baseUrl: 'reports/job-balance-report',
    actionUrl: 'reports/job-balance-report',
    load() {
        CURRENCY.currencyRate();
    },
}
