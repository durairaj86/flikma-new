<?php

// Registry of application modules used for department-based user rights.
// Each module maps to the URL path prefixes (relative to the app root,
// no leading slash) that belong to it. Order matters: more specific
// prefixes (e.g. customer statement/aging reports) must be listed in a
// module that is resolved before the generic "reports" module.
return [
    'customers' => [
        'label' => 'Customers',
        'prefixes' => [
            'customers', 'customer', 'prospects', 'prospect',
            'reports/customer-statement', 'reports/customer-aging',
        ],
    ],
    'suppliers' => [
        'label' => 'Suppliers / Agents',
        'prefixes' => [
            'suppliers', 'supplier',
            'reports/supplier-statement', 'reports/supplier-aging',
        ],
    ],
    'sales' => [
        'label' => 'Sales (Enquiry & Quotation)',
        'prefixes' => ['sales'],
    ],
    'operations' => [
        'label' => 'Operations (Jobs)',
        'prefixes' => ['operation'],
    ],
    'finances' => [
        'label' => 'Finances (Invoices, Adjustments, Accounts)',
        'prefixes' => ['invoice', 'adjustment', 'finance'],
    ],
    'transactions' => [
        'label' => 'Transactions (Payments & Collections)',
        'prefixes' => ['transaction'],
    ],
    'bl' => [
        'label' => 'Bill of Lading',
        'prefixes' => ['bl'],
    ],
    'payroll' => [
        'label' => 'Payroll',
        'prefixes' => ['payroll'],
    ],
    'inventory' => [
        'label' => 'Inventory (Items)',
        'prefixes' => ['inventory'],
    ],
    'reports' => [
        'label' => 'Reports',
        'prefixes' => ['reports'],
    ],
    'masters' => [
        'label' => 'Master Data',
        'prefixes' => ['masters'],
    ],
    'settings' => [
        'label' => 'Settings',
        'prefixes' => ['settings'],
    ],
];
