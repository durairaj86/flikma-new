<?php

// Plan catalogue backing the Billing & Subscription page.
//
// Shape mirrors the reference build: numeric ids (1..4) so plan selection can
// round-trip through the URL, plus `monthly_price` / `yearly_price` and a
// `feature_labels` list the Alpine plan picker can read directly.
//
// Feature keys are the real module keys from config/modules.php, so entitlements
// can never drift from the actual application modules. Prices are in SAR.
return [

    // The plan a company lands on when it has never chosen one.
    'default_package_id' => 2,

    'by_id' => [

        1 => [
            'id' => 1,
            'key' => 'free',
            'name' => 'free',
            'label' => 'Free',
            'tagline' => 'Try the core workflow at no cost.',
            'monthly_price' => 0,
            'yearly_price' => 0,
            'is_active' => false,
            'features' => [
                'customers' => 'Customer directory and statements',
                'suppliers' => 'Supplier / agent directory',
            ],
        ],

        2 => [
            'id' => 2,
            'key' => 'starter',
            'name' => 'starter',
            'label' => 'Starter',
            'tagline' => 'A simple start for growing freight operations.',
            'monthly_price' => 199,
            'yearly_price' => 1990,
            'is_active' => true,
            'features' => [
                'customers' => 'Customer directory and statements',
                'suppliers' => 'Supplier / agent directory',
                'sales' => 'Enquiries and quotations',
                'operations' => 'Job tracking and milestones',
                'inventory' => 'Items and stock levels',
            ],
        ],

        3 => [
            'id' => 3,
            'key' => 'professional',
            'name' => 'professional',
            'label' => 'Professional',
            'tagline' => 'For teams that need the full finance picture.',
            'monthly_price' => 449,
            'yearly_price' => 4490,
            'is_active' => true,
            'features' => [
                'customers' => 'Customer directory and statements',
                'suppliers' => 'Supplier / agent directory',
                'sales' => 'Enquiries and quotations',
                'operations' => 'Job tracking and milestones',
                'inventory' => 'Items and stock levels',
                'finances' => 'Invoices, adjustments and accounts',
                'transactions' => 'Payments and collections',
                'bl' => 'Bill of lading',
                'payroll' => 'Payroll and employee loans',
                'reports' => 'Full reporting suite',
                'masters' => 'Reference data and users',
            ],
        ],

        4 => [
            'id' => 4,
            'key' => 'enterprise',
            'name' => 'enterprise',
            'label' => 'Enterprise',
            'tagline' => 'Maximum scale for larger, multi-branch teams.',
            'monthly_price' => 0,
            'yearly_price' => 0,
            'is_active' => true,
            'features' => [
                'customers' => 'Customer directory and statements',
                'suppliers' => 'Supplier / agent directory',
                'sales' => 'Enquiries and quotations',
                'operations' => 'Job tracking and milestones',
                'inventory' => 'Items and stock levels',
                'finances' => 'Invoices, adjustments and accounts',
                'transactions' => 'Payments and collections',
                'bl' => 'Bill of lading',
                'payroll' => 'Payroll and employee loans',
                'reports' => 'Full reporting suite',
                'masters' => 'Reference data and users',
                'settings' => 'Company, ZATCA and system settings',
            ],
        ],

    ],

    // Savings badge shown on the annual option and next to yearly prices.
    'annual_discount_percent' => 17,

];
