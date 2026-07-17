JOB_INCOME_REPORT = {
    title: 'Job Income Report',
    baseUrl: 'reports/job-income-report',
    actionUrl: 'reports/job-income-report',
    load() {
        CURRENCY.currencyRate();
    },
}
