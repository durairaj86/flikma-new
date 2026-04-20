@php
    $segments = request()->segments();
    $segment1 = $segments[0] ?? '';
    $breadcrumb = $segment1;
$page1 = $segments[1] ?? '';
$page2 = $segments[2] ?? '';
$page3 = $segments[3] ?? '';
@endphp
<div class="py-3">
    <h5 class="fw-bold mb-0 pb-1">@yield('page-title')</h5>
    @if(isset($breadcrumb))
        @include('includes.breadcrumb.'.$breadcrumb,['page1'=>$page1??'','page2'=>$page2 ?? '','page3'=>$page3 ?? ''])
    @endif
</div>
