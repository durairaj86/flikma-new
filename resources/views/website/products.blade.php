@extends('website.layout')

@section('title', 'Products — Flikma Logistics ERP for Saudi Arabia, Bahrain & Dubai')
@section('meta_description', 'Flikma products: Operations, Finance, Compliance and Payroll modules for logistics companies in Saudi Arabia, Bahrain and Dubai — work together as one platform or independently.')
@section('meta_keywords', 'logistics ERP modules Saudi Arabia, freight operations software Bahrain, finance module logistics Dubai, ZATCA compliance software, payroll software logistics GCC')

@section('content')

    <section class="fk-gradient-bg pt-5 pb-4">
        <div class="container pt-5 pb-4 text-center">
            <span class="fk-badge mb-3"><i class="bi bi-boxes"></i> Products</span>
            <h1 class="display-5 mb-3">One platform. Four connected products.</h1>
            <p class="fs-5 text-fk-muted mx-auto" style="max-width:640px;">Start with what you need today and switch on more as you grow — every product shares the same customers, jobs and chart of accounts.</p>
        </div>
    </section>

    @php
        $products = [
            [
                'tag' => 'Product 01',
                'name' => 'Flikma Operations',
                'icon' => 'bi-truck',
                'desc' => 'The command center for your sales and shipment workflow — from first enquiry to final delivery.',
                'items' => ['Enquiries, quotations & revisions', 'Job management with milestones', 'Airway Bill, Seaway Bill & Waybill', 'Container & package tracking'],
                'reverse' => false,
            ],
            [
                'tag' => 'Product 02',
                'name' => 'Flikma Finance',
                'icon' => 'bi-cash-stack',
                'desc' => 'A complete double-entry accounting engine purpose-built for freight forwarding revenue and cost structures.',
                'items' => ['Customer & supplier invoicing', 'Payments, collections & credit notes', 'Chart of accounts & journal vouchers', 'Trial balance, P&L & balance sheet'],
                'reverse' => true,
            ],
            [
                'tag' => 'Product 03',
                'name' => 'Flikma Compliance',
                'icon' => 'bi-shield-check',
                'desc' => 'Stay ahead of tax authority requirements without adding headcount to your finance team.',
                'items' => ['ZATCA e-invoicing (Saudi Arabia)', 'Input & output VAT reporting', 'Multi-currency exchange handling', 'Full audit trail on every document'],
                'reverse' => false,
            ],
            [
                'tag' => 'Product 04',
                'name' => 'Flikma Payroll',
                'icon' => 'bi-people-fill',
                'desc' => 'Keep your operations and admin staff paid accurately, with payroll tied straight into the same ledger.',
                'items' => ['Attendance tracking', 'Basic & monthly salary runs', 'Employee loans & deductions', 'Payroll postings to the GL'],
                'reverse' => true,
            ],
        ];
    @endphp

    @foreach($products as $p)
        <section class="fk-section {{ $loop->odd ? '' : 'bg-light' }}">
            <div class="container">
                <div class="row align-items-center g-5 {{ $p['reverse'] ? 'flex-row-reverse' : '' }}">
                    <div class="col-lg-6">
                        <div class="fk-eyebrow mb-2">{{ $p['tag'] }}</div>
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="fk-icon-box"><i class="bi {{ $p['icon'] }}"></i></div>
                            <h2 class="h2 mb-0">{{ $p['name'] }}</h2>
                        </div>
                        <p class="fs-5 text-fk-muted">{{ $p['desc'] }}</p>
                        <ul class="list-unstyled mt-4">
                            @foreach($p['items'] as $item)
                                <li class="mb-2"><i class="bi bi-check-circle-fill text-primary me-2"></i>{{ $item }}</li>
                            @endforeach
                        </ul>
                        <a href="{{ route('website.features') }}" class="fk-btn-outline mt-2">Explore Features <i class="bi bi-arrow-right ms-1"></i></a>
                    </div>
                    <div class="col-lg-6">
                        <div class="fk-card fk-shadow-lg p-5 text-center" style="background: linear-gradient(135deg, #eff4ff 0%, #f8fafc 100%);">
                            <div class="fk-icon-box mx-auto mb-3" style="width:72px;height:72px;font-size:2rem;"><i class="bi {{ $p['icon'] }}"></i></div>
                            <h4 class="mb-1">{{ $p['name'] }}</h4>
                            <p class="text-fk-muted small mb-0">Included in every Flikma plan</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endforeach

    <section class="container pb-5">
        <div class="fk-cta-band text-white p-5 text-center">
            <h2 class="display-6 text-white mb-3">All four products. One subscription.</h2>
            <p class="text-white-50 mb-4">See how the pricing works for your team size.</p>
            <a href="{{ route('website.pricing') }}" class="btn btn-light fw-bold px-4 py-2">View Pricing</a>
        </div>
    </section>

@endsection
