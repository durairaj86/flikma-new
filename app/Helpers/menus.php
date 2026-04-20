<?php

function menus(): array
{
    return [
        [
            'name' => 'Dashboard',
            'icon' => 'bi bi-speedometer',
            'route' => '/dashboard'
        ],
        [
            'name' => 'Customers',
            'icon' => 'bi bi-people',
            'submenus' => [
                ['name' => 'Customer Master', 'route' => 'customers.index'],
                ['name' => 'Customer Statement', 'route' => 'customers.statement'],
            ],
        ],
        [
            'name' => 'Suppliers',
            'icon' => 'bi bi-truck',
            'submenus' => [
                ['name' => 'Supplier Master', 'route' => 'suppliers.index'],
                ['name' => 'Supplier Statement', 'route' => 'suppliers.statement'],
            ],
        ],
        [
            'name' => 'Sales & Enquiries',
            'icon' => 'bi-file-earmark-text',
            'submenus' => [
                ['name' => 'Enquiries', 'route' => 'enquiries.index'],
                ['name' => 'Quotations', 'route' => 'quotations.index'],
                ['name' => 'Jobs / Shipments', 'route' => 'jobs.index'],
                ['name' => 'Job Statement', 'route' => 'jobs.statement'],
            ],
        ],
        [
            'name' => 'Finance',
            'icon' => 'bi-currency-dollar',
            'submenus' => [
                ['name' => 'Opening Balance', 'route' => 'finance.opening-balance'],
                ['name' => 'Customer Invoices', 'route' => 'invoices.customer.index'],
                ['name' => 'Supplier Invoices', 'route' => 'invoices.supplier.index'],
                ['name' => 'Payments', 'route' => 'payments.index'],
                ['name' => 'Collections / Receipts', 'route' => 'collections.index'],
                ['name' => 'Expenses', 'route' => 'expenses.index'],
                ['name' => 'Journal Vouchers', 'route' => 'journals.index'],
            ],
        ],
    ];

}
