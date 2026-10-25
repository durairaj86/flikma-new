<ol class="breadcrumb">
    <li class="breadcrumb-item text-muted {{ $page1 == 'account' ? 'active' : 'link' }}" data-url="/settings/account">
        {{ __('Account') }}
    </li>
    <li class="breadcrumb-item text-muted {{ $page1 == 'company' ? 'active' : 'link' }}" data-url="/settings/company">
        {{ __('Manage Business') }}
    </li>
    <li class="breadcrumb-item text-muted {{ $page1 == 'invoice' ? 'active' : 'link' }}" data-url="/settings/invoice">
        {{ __('Invoice Setting') }}
    </li>
    <li class="breadcrumb-item text-muted {{ $page1 == 'company' ? 'active' : 'link' }}" data-url="/settings/tax">{{ __('Tax') }}
    </li>
</ol>
