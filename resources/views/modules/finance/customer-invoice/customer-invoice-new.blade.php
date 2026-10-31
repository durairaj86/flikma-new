@section('page-title', __('New Customer Invoice'))
@section('hide-topbar', true)
<x-app-layout>
    <main class="gmail-content bg-white px-3 pb-5">
        @include('modules.finance.customer-invoice._gp-form', ['inModal' => false])
    </main>
</x-app-layout>
