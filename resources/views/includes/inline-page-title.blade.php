{{--
    Page title shown inside the page body when the top header is off and the slim topbar is dropped
    (@section('hide-topbar')). With the header on, the header already carries the title, so this stays hidden.
--}}
<style>
    .inline-page-title { display: none; }
    body:not(.has-top-header) .inline-page-title { display: block; }
</style>
<div class="inline-page-title container-fluid px-0 pt-4 pb-0">
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <h4 class="fw-bold text-dark mb-0">@yield('page-title')</h4>
        {{-- the page's own title action (e.g. "How it works"), which normally sits next to the title in the bar --}}
        @stack('page-title-action')
    </div>
    @hasSection('page-subtitle')
        <div class="text-muted small mt-1">@yield('page-subtitle')</div>
    @endif
</div>
