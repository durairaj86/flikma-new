{{--
    Breadcrumb + page title/subtitle + the page-title action slot.

    Shared by the full header (includes/header.blade.php) and the slim
    header-off topbar (layouts/topbar.blade.php) so a page looks the same in
    either layout mode — turning the top header off should drop the action
    icons into the rail, not lose the page's own heading.

    Uses a local $phCrumb rather than $breadcrumb so the including view's
    $breadcrumb (which includes/header.blade.php reads for its own class)
    is not affected.
--}}
@php
    $phCrumb = null;
    $phPage1 = $phPage2 = $phPage3 = '';
    if ($segment1 == 'dashboard1') { $phCrumb = 'customer'; }
    elseif (in_array($segment1, ['customers', 'customer', 'prospects'])) { $phCrumb = 'customer'; }
    elseif (in_array($segment1, ['suppliers', 'supplier'])) { $phCrumb = 'supplier'; }
    elseif (in_array($segment1, ['masters', 'settings'])) {
        $phCrumb = $segment1; $phPage1 = $segment2; $phPage2 = $segment3; $phPage3 = $segment4;
    }
    elseif (in_array($segment1, ['sales', 'operation', 'invoice'])) {
        if ($segment2 != 'overview') { $phCrumb = $segment1; }
        $phPage1 = $segment2;
    }
    elseif ($segment1 == 'finance') { $phCrumb = 'invoice'; $phPage1 = 'proforma'; }
    elseif ($segment1 == 'reports') { $phCrumb = 'reports'; $phPage1 = 'reports'; }
    elseif ($segment1 == 'bl') { $phCrumb = 'bl'; $phPage1 = $segment2; }
@endphp

<div class="lh-sm overflow-hidden">
    @if ($phCrumb !== 'reports')
        <nav aria-label="breadcrumb" class="mb-0 d-none d-sm-block">
            @if ($phCrumb)
                @include('includes.breadcrumb.' . $phCrumb, ['page1' => $phPage1, 'page2' => $phPage2, 'page3' => $phPage3])
            @endif
        </nav>
    @endif
    <div class="d-flex align-items-center gap-2">
        <div class="lh-sm overflow-hidden">
            <h5 class="fw-bold text-dark mb-0 text-truncate" style="max-width: min(420px, 38vw);">@yield('page-title')</h5>
            @hasSection('page-subtitle')
                <div class="small text-muted text-truncate" style="max-width: min(420px, 38vw);">@yield('page-subtitle')</div>
            @endif
        </div>
        @stack('page-title-action')
    </div>
</div>
