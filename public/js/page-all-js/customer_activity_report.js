CUSTOMER_ACTIVITY_REPORT = {
    title: 'Customer Activity Report',
    baseUrl: 'reports/customer-activity-report',
    actionUrl: 'reports/customer-activity-report',
    load() {
        CURRENCY.currencyRate();
    },
}
