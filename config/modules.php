<?php

use App\Enums\BookingEnum;
use App\Enums\CollectionEnum;
use App\Enums\CreditNoteEnum;
use App\Enums\CustomerInvoiceEnum;
use App\Enums\DebitNoteEnum;
use App\Enums\EnquiryEnum;
use App\Enums\ExpenseEnum;
use App\Enums\JournalVoucherStatusEnum;
use App\Enums\PaymentEnum;
use App\Enums\ProformaInvoiceEnum;
use App\Enums\SupplierInvoiceEnum;

// Registry of application modules used for department-based user rights.
// Each module maps to the URL path prefixes (relative to the app root,
// no leading slash) that belong to it, and lists its sub menus as
// 'children' with their own right ("module.child"). A path belongs to the
// entry with the longest matching prefix, so a sub menu wins over its
// module. A sub menu with no saved permission row takes its module's row,
// so departments saved before the sub menus existed keep their rights.
// 'status_enum': the enum behind "/status/{value}"; moving a document to
// APPROVED / REJECTED / DISAPPROVED needs the approve right, CONFIRMED the
// confirm right (any other status needs edit).
// The rights screen offers only the actions a section really has, found from
// its routes (rightActions()); 'actions' => [...] fixes the list by hand.
// 'custom_actions' => ['close' => 'approve']: a POST ending in "/close" (and the menu item "row_close") needs that right;
// 'action_labels' names the box on the rights screen (e.g. Approve shown as "Close").
// 'without_view' => true: the rights screen offers only the listed 'actions' (a right checked in code, with no page of its own).
// 'inherits' => 'module.child': with no saved row of its own the section takes that section's row (before its module's row),
// so a right added later starts as what the department already had there.
return [
    'customers' => [
        'label' => 'Customers',
        'prefixes' => [],
        'children' => [
            'list' => ['label' => 'Customers', 'prefixes' => ['customers', 'customer']],
            'reports' => ['label' => 'Customer Reports', 'prefixes' => [
                'reports/customer-statement', 'reports/customer-balance-summary', 'reports/customer-activity-report',
                'reports/customer-aging', 'reports/customer-aging-all',
            ]],
            'prospects' => ['label' => 'Prospects', 'prefixes' => ['prospects', 'prospect']],
        ],
    ],
    'suppliers' => [
        'label' => 'Suppliers / Agents',
        'prefixes' => [],
        'children' => [
            'list' => ['label' => 'Suppliers / Agents', 'prefixes' => ['suppliers', 'supplier']],
            'reports' => ['label' => 'Supplier Reports', 'prefixes' => [
                'reports/supplier-statement', 'reports/supplier-balance-summary', 'reports/supplier-aging', 'reports/supplier-aging-all',
            ]],
        ],
    ],
    'sales' => [
        'label' => 'Sales (Enquiry & Quotation)',
        'prefixes' => ['sales'],
        'children' => [
            'pipeline' => ['label' => 'Pipeline', 'prefixes' => ['sales/pipeline']],
            'activities' => ['label' => 'Activities & Follow-ups', 'prefixes' => ['sales/follow-ups', 'sales/follow-up', 'sales/activity']],
            'tracker' => ['label' => 'Customer Tracker', 'prefixes' => ['sales/tracker']],
            'crm-dashboard' => ['label' => 'CRM Dashboard', 'prefixes' => ['sales/crm-dashboard']],
            'enquiries' => ['label' => 'Enquiries', 'prefixes' => ['sales/enquiries', 'sales/enquiry'], 'status_enum' => EnquiryEnum::class],
            'quotations' => ['label' => 'Quotations', 'prefixes' => ['sales/quotations', 'sales/quotation']],
            'rate-sheets' => ['label' => 'Rate Sheets', 'prefixes' => ['sales/rate-sheets', 'sales/rate-sheet']],
            'reports' => ['label' => 'Sales Reports', 'prefixes' => [
                'reports/sale-report', 'reports/shipment-volume', 'reports/sales-by-salesperson', 'reports/sales-commission',
                'reports/sales-funnel', 'reports/win-loss', 'reports/crm-activity-summary', 'reports/pipeline-value', 'reports/salesperson-performance',
                'reports/customer-wise-sales', 'reports/description-wise', 'reports/invoice-wise-sales', 'reports/customer-wise-commission',
            ]],
        ],
    ],
    'operations' => [
        'label' => 'Operations (Jobs)',
        'prefixes' => ['operation'],
        'children' => [
            'jobs' => ['label' => 'Jobs', 'prefixes' => ['operation/jobs', 'operation/job']],
            'bookings' => ['label' => 'Bookings', 'prefixes' => ['operation/bookings', 'operation/booking'], 'status_enum' => BookingEnum::class],
            'arrival-notices' => ['label' => 'Arrival Notices', 'prefixes' => ['operation/arrival-notices', 'operation/arrival-notice']],
            'delivery-orders' => ['label' => 'Delivery Orders', 'prefixes' => ['operation/delivery-orders', 'operation/delivery-order']],
            'trips' => ['label' => 'Trips', 'prefixes' => ['operation/trips', 'operation/trip']],
            'tracking' => ['label' => 'Tracking', 'prefixes' => ['operation/tracking']],
            'demurrage' => ['label' => 'Demurrage', 'prefixes' => ['operation/demurrage']],
            'documents' => ['label' => 'Documents', 'prefixes' => ['operation/documents', 'operation/document']],
            'customs' => ['label' => 'Customs', 'prefixes' => ['operation/customs']],
            'job-overview' => ['label' => 'Job Overview', 'prefixes' => ['operation/job-overview']],
        ],
    ],
    'finances' => [
        'label' => 'Finances (Invoices, Adjustments, Accounts)',
        'prefixes' => ['invoice', 'adjustment', 'finance'],
        'children' => [
            'proforma' => ['label' => 'Proforma Invoices', 'prefixes' => ['invoice/proforma'], 'status_enum' => ProformaInvoiceEnum::class],
            'supplier-invoices' => ['label' => 'Supplier Invoices', 'prefixes' => ['invoice/supplier'], 'status_enum' => SupplierInvoiceEnum::class],
            'customer-invoices' => ['label' => 'Customer Invoices', 'prefixes' => ['invoice/customer'], 'status_enum' => CustomerInvoiceEnum::class],
            'credit-notes' => ['label' => 'Credit Notes', 'prefixes' => ['adjustment/credit-note'], 'status_enum' => CreditNoteEnum::class],
            'debit-notes' => ['label' => 'Debit Notes', 'prefixes' => ['adjustment/debit-note'], 'status_enum' => DebitNoteEnum::class],
            'expenses' => ['label' => 'Expenses', 'prefixes' => ['finance/expense'], 'status_enum' => ExpenseEnum::class],
            'journal-vouchers' => ['label' => 'Journal Vouchers', 'prefixes' => ['finance/journal-vouchers'], 'status_enum' => JournalVoucherStatusEnum::class],
            'transfer-vouchers' => ['label' => 'Transfer Vouchers', 'prefixes' => ['finance/transfer-vouchers'], 'status_enum' => JournalVoucherStatusEnum::class],
            'credit-approvals' => ['label' => 'Credit Approval', 'prefixes' => ['finance/credit-approvals'], 'actions' => ['view', 'create', 'approve']],
            // approving a customer invoice that the credit check (Settings › Finance: Block) refuses; given only explicitly, see CreditCheck::mayOverride
            'credit-override' => ['label' => 'Credit Limit Override', 'prefixes' => [], 'actions' => ['approve'], 'without_view' => true],
            'assets' => ['label' => 'Assets', 'prefixes' => ['finance/asset']],
            'reconciliation' => ['label' => 'Bank Reconciliation', 'prefixes' => ['finance/reconciliation']],
            'accounts' => ['label' => 'Chart of Accounts', 'prefixes' => ['finance/accounts', 'finance/account']],
        ],
    ],
    'transactions' => [
        'label' => 'Transactions (Payments & Collections)',
        'prefixes' => ['transaction'],
        'children' => [
            'payments' => ['label' => 'Payments', 'prefixes' => ['transaction/payments'], 'status_enum' => PaymentEnum::class],
            'collections' => ['label' => 'Collections', 'prefixes' => ['transaction/collections'], 'status_enum' => CollectionEnum::class],
            // the Advance / Refund buttons: checked in Payment/CollectionController (no URL of their own); approving them is the
            // Payments / Collections Approve right
            'payment-advances' => ['label' => 'Payment Advances', 'prefixes' => [], 'actions' => ['create'], 'without_view' => true, 'inherits' => 'transactions.payments'],
            'payment-refunds' => ['label' => 'Payment Refunds', 'prefixes' => [], 'actions' => ['create'], 'without_view' => true, 'inherits' => 'transactions.payments'],
            'collection-advances' => ['label' => 'Collection Advances', 'prefixes' => [], 'actions' => ['create'], 'without_view' => true, 'inherits' => 'transactions.collections'],
            'collection-refunds' => ['label' => 'Collection Refunds', 'prefixes' => [], 'actions' => ['create'], 'without_view' => true, 'inherits' => 'transactions.collections'],
            'overview' => ['label' => 'Overview', 'prefixes' => ['transaction/overview']],
        ],
    ],
    'bl' => [
        'label' => 'Bill of Lading',
        'prefixes' => ['bl'],
        'children' => [
            'master-bl' => ['label' => 'Master B/L', 'prefixes' => ['bl/master-bl']],
            'airway-bill' => ['label' => 'Airway Bill', 'prefixes' => ['bl/airway-bill']],
            'seaway' => ['label' => 'Seaway Bill', 'prefixes' => ['bl/seaway']],
            'waybill' => ['label' => 'Waybill', 'prefixes' => ['bl/waybill']],
        ],
    ],
    'payroll' => [
        'label' => 'Payroll',
        'prefixes' => ['payroll'],
        'children' => [
            'dashboard' => ['label' => 'Payroll Dashboard', 'prefixes' => ['payroll'], 'exact' => true],
            'attendance' => ['label' => 'Attendance', 'prefixes' => ['payroll/attendance']],
            'punch-log' => ['label' => 'Punch Log', 'prefixes' => ['payroll/punch-log']],
            'runs' => ['label' => 'Payroll Runs', 'prefixes' => ['payroll/runs']],
            'loans' => ['label' => 'Employee Loans', 'prefixes' => ['employee-loans']],
            'shifts' => ['label' => 'Shifts', 'prefixes' => ['payroll/shifts']],
            'salary-structures' => ['label' => 'Salary Structures', 'prefixes' => ['payroll/salary-structures']],
            'employees' => ['label' => 'Employee Profiles', 'prefixes' => ['payroll/employees']],
            'devices' => ['label' => 'Attendance Devices', 'prefixes' => ['payroll/devices']],
        ],
    ],
    'reports' => [
        'label' => 'Reports',
        'prefixes' => ['reports'],
        'children' => [
            'jobs' => ['label' => 'Job Reports', 'prefixes' => [
                'reports/job-report', 'reports/job-balance-report', 'reports/job-income-report', 'reports/provisional-report',
            ]],
            'finance' => ['label' => 'Finance Reports', 'prefixes' => [
                'reports/trial-balance', 'reports/account-postings', 'reports/balance-sheet', 'reports/profit-and-loss',
                'reports/general-ledger', 'reports/supplier-ledger', 'reports/tax-summary', 'reports/input-tax', 'reports/output-tax',
            ]],
            'waybill' => ['label' => 'Waybill Report', 'prefixes' => ['reports/waybill-report']],
        ],
    ],
    // Masters is for Admins (within their department rights) and the Super User; a User or Employee never gets it
    'masters' => [
        'label' => 'Master Data',
        'prefixes' => ['masters'],
        'children' => [
            // owner_only: the Super User alone, never given through department rights (not on the rights screen)
            'users' => ['label' => 'Employees', 'prefixes' => ['masters/users', 'masters/user'], 'owner_only' => true],
            'departments' => ['label' => 'Departments', 'prefixes' => ['masters/departments', 'masters/department'], 'owner_only' => true],
            'quotation-terms' => ['label' => 'Quotation Terms', 'prefixes' => ['masters/quotation-terms', 'masters/quotation-term']],
            'lost-reasons' => ['label' => 'Lost Reasons', 'prefixes' => ['masters/lost-reasons', 'masters/lost-reason']],
            'crm-actions' => ['label' => 'CRM Actions', 'prefixes' => ['masters/crm-actions', 'masters/crm-action']],
            'hs-tariffs' => ['label' => 'HS Tariff', 'prefixes' => ['masters/hs-tariffs', 'masters/hs-tariff']],
            // closing locks every transaction up to the closing date and reopening unlocks them, so each has its own box:
            // POST ".../close" and the menu's row_close need Approve (shown as "Close"), ".../reopen" / row_reopen need Confirm ("Reopen")
            'period-closing' => ['label' => 'Period Closing', 'prefixes' => ['masters/period-closing'], 'owner_only' => true,
                'custom_actions' => ['close' => 'approve', 'reopen' => 'confirm'],
                'action_labels' => ['approve' => 'Close', 'confirm' => 'Reopen']],
            'seaports' => ['label' => 'Seaports', 'prefixes' => ['masters/transport/directories/seaports', 'masters/transport/directories/seaport']],
            'airports' => ['label' => 'Airports', 'prefixes' => ['masters/transport/directories/airports', 'masters/transport/directories/airport']],
            'services' => ['label' => 'Logistic Departments', 'prefixes' => ['masters/services', 'masters/activities']],
            'package-codes' => ['label' => 'Package Codes', 'prefixes' => ['masters/package']],
            'container-types' => ['label' => 'Container Types', 'prefixes' => ['masters/container']],
            'incoterms' => ['label' => 'Incoterms', 'prefixes' => ['masters/incoterms', 'masters/incoterm']],
            'currencies' => ['label' => 'Currencies', 'prefixes' => ['masters/currencies', 'masters/currency']],
            'banks' => ['label' => 'Banks', 'prefixes' => ['masters/banks', 'masters/bank']],
            'descriptions' => ['label' => 'Descriptions', 'prefixes' => ['masters/descriptions', 'masters/description']],
            'salesperson' => ['label' => 'Salesperson', 'prefixes' => ['masters/salesperson']],
            'units' => ['label' => 'Units', 'prefixes' => ['masters/units', 'masters/unit']],
            'drivers' => ['label' => 'Drivers', 'prefixes' => ['masters/drivers', 'masters/driver']],
            'vehicles' => ['label' => 'Vehicles', 'prefixes' => ['masters/vehicles', 'masters/vehicle']],
            'opening-balance' => ['label' => 'Opening Balance', 'prefixes' => ['masters/finance/opening-balance'], 'owner_only' => true],
        ],
    ],
    // the whole Settings menu is the Super User's; a user's own account (settings/account) is outside the rights
    'settings' => [
        'label' => 'Settings',
        'prefixes' => ['settings'],
        'owner_only' => true,
        'children' => [
            // saving these pages updates the one settings record (the route is named "store"): Edit, not Create
            'company' => ['label' => 'Company', 'prefixes' => ['settings/company'], 'actions' => ['edit']],
            'invoice' => ['label' => 'Invoice Settings', 'prefixes' => ['settings/invoice'], 'actions' => ['edit']],
            'tax' => ['label' => 'Tax', 'prefixes' => ['settings/tax']],
            'crm' => ['label' => 'CRM Settings', 'prefixes' => ['settings/crm'], 'actions' => ['edit']],
            'finance' => ['label' => 'Finance Settings', 'prefixes' => ['settings/finance'], 'actions' => ['edit']],
            'zatca' => ['label' => 'ZATCA', 'prefixes' => ['settings/zatca']],
        ],
    ],
];
