JOB_REPORT = {
    title: trans('Job Report'),
    baseUrl: 'reports/job-report',
    actionUrl: 'reports/job-report',
    load() {
        CURRENCY.currencyRate();
    },
}
