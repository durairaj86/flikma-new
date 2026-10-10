@switch($key)
    @case('sales') <livewire:widgets.sales size="medium" /> @break
    @case('sales-small') <livewire:widgets.sales size="small" /> @break
    @case('invoices-small') <livewire:widgets.invoices size="small" /> @break
    @case('customers-small') <livewire:widgets.customers size="small" /> @break
    @case('profit-small') <livewire:widgets.profit size="small" /> @break
    @case('quotation-medium') <livewire:widgets.quotation size="medium" /> @break
    @case('payments-medium') <livewire:widgets.payments size="medium" /> @break
    @case('collection-medium') <livewire:widgets.collection size="medium" /> @break
    @case('enquiry-small') <livewire:widgets.enquiry size="small" /> @break
    @case('job-small') <livewire:widgets.job size="small" /> @break
    @case('invoices') <livewire:widgets.invoices size="medium" /> @break
    @case('customers') <livewire:widgets.customers size="medium" /> @break
    @case('profit') <livewire:widgets.profit size="medium" /> @break
    @case('expenses') <livewire:widgets.expenses size="medium" /> @break
    @case('revenue-summary') @include('dashboard.widgets.revenue-summary-medium') @break
    @case('quotation') <livewire:widgets.quotation size="small" /> @break
    @case('payments') <livewire:widgets.payments size="small" /> @break
    @case('collection') <livewire:widgets.collection size="small" /> @break
    @case('eta-etd') @include('dashboard.widgets.eta-etd-medium') @break
    @case('ata-atd') @include('dashboard.widgets.ata-atd-medium') @break
    @case('eta-etd-small') @include('dashboard.widgets.eta-etd-small') @break
    @case('ata-atd-small') @include('dashboard.widgets.ata-atd-small') @break
    @case('recent-transactions') @include('dashboard.widgets.recent-transactions-tall') @break
    @case('job-status') @include('dashboard.widgets.job-status-medium') @break
    @case('to-collect-pay') @include('dashboard.widgets.to-collect-pay-medium') @break
    @case('revenue-expenses') @include('dashboard.widgets.revenue-expenses-medium') @break
    @case('revenue-trend') @include('dashboard.widgets.revenue-trend-medium') @break
    @case('outstanding') @include('dashboard.widgets.outstanding-medium') @break
    @case('awaiting-approval') @include('dashboard.widgets.awaiting-approval-medium') @break
    @case('payroll-small') @include('dashboard.widgets.payroll-small') @break
    @case('payroll-medium') @include('dashboard.widgets.payroll-medium') @break
    @case('payroll') @include('dashboard.widgets.payroll-tall') @break
    @case('attendance') @include('dashboard.widgets.attendance-tall') @break
    @case('punching-small') @include('dashboard.widgets.punching-small') @break
    @case('punching-medium') @include('dashboard.widgets.punching-medium') @break
    @case('punching') @include('dashboard.widgets.punching-tall') @break
    @case('cost-summary') @include('dashboard.widgets.cost-summary-medium') @break
@endswitch
