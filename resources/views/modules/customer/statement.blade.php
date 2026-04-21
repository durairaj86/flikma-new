@section('js','customer_statement')
@section('page-title','Customer Statement')
<x-app-layout>
    @livewire('report.finance.customer-statement')
</x-app-layout>

<style>
    :root {
        --finance-primary: #0d9488;
        --finance-dark: #0f172a;
    }

    .bg-finance { background-color: var(--finance-primary); }
    .bg-finance-subtle { background-color: #e6fffa; }
    .text-finance { color: var(--finance-primary); }
    .btn-finance {
        background-color: var(--finance-primary);
        color: white;
        border: none;
    }
    .btn-finance:hover {
        background-color: #0f766e;
        color: white;
    }

    .btn-white { background: white; }

    .table-info { background-color: #e0f2fe !important; }

    .form-select, .form-control {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 0.5rem 0.75rem;
    }

    .form-select:focus, .form-control:focus {
        border-color: var(--finance-primary);
        box-shadow: 0 0 0 0.25rem rgba(13, 148, 136, 0.1);
    }

    .card {
        border-radius: 12px;
        overflow: hidden;
    }

    .shadow-sm {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
    }

    .badge {
        font-weight: 500;
    }

    @media print {
        body {
            background: #fff !important;
            color: #000 !important;
        }
        .card {
            box-shadow: none !important;
            border: 1px solid #dee2e6 !important;
        }
        .btn-group, .card-header .d-flex {
            display: none !important;
        }
    }
</style>
