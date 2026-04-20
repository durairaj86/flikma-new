@php
    $appendBreadCrumbUrl = '';
    if(isset($segment4) && filled($segment4)){
        $appendBreadCrumbUrl = '/list/'.$segment4;
    }
@endphp
<ol class="breadcrumb">
    <li class="breadcrumb-item text-muted {{ $page1 == 'proforma' ? 'active' : 'link' }}" data-url="/invoice/proforma{{ $appendBreadCrumbUrl }}">
        Proforma Invoices
    </li>
    <li class="breadcrumb-item text-muted {{ $page1 == 'supplier' ? 'active' : 'link' }}" data-url="/invoice/supplier{{ $appendBreadCrumbUrl }}">
        Supplier Invoices
    </li>
    <li class="breadcrumb-item text-muted {{ $page1 == 'customer' ? 'active' : 'link' }}" data-url="/invoice/customer{{ $appendBreadCrumbUrl }}">Customer Invoices</li>
</ol>

