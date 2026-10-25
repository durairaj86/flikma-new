<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Flikma Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    @stack('styles')
    <style>
        :root { --sidebar-width: 248px; --primary: #4f46e5; --primary-hover: #4338ca; --line: #e5e7eb; }
        * { box-sizing: border-box; }
        body { font-family: 'Figtree', system-ui, -apple-system, 'Segoe UI', sans-serif; background: #f9fafb; overflow-x: hidden; color: #1f2937; margin: 0; }
        .wrapper { display: flex; min-height: 100vh; }

        .sidebar { width: var(--sidebar-width); position: fixed; left: 0; top: 0; height: 100vh; background: #fff; border-right: 1px solid var(--line); z-index: 1040; transition: transform .25s ease; overflow-y: auto; }
        .sidebar .logo { height: 60px; padding: 0 14px 0 18px; display: flex; align-items: center; }
        .sidebar .logo img { height: 30px; width: auto; }
        .sidebar .logo .admin-chip { font-size: .62rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: var(--primary); background: #eef2ff; border-radius: 5px; padding: 2px 6px; }
        .sidebar .nav-item { margin: 1px 10px; }
        .sidebar .nav-link { color: #374151; padding: .5rem .75rem; border-radius: 8px; font-size: .875rem; display: flex; align-items: center; gap: .7rem; transition: background .15s, color .15s; text-decoration: none; }
        .sidebar .nav-link:hover, .sidebar .nav-link:focus-visible { background: #f3f4f6; color: #111827; outline: none; }
        .sidebar .nav-link.active { background: #eef2ff; color: var(--primary); font-weight: 600; box-shadow: inset 3px 0 0 var(--primary); }
        .sidebar .nav-link i { font-size: 1.05rem; width: 20px; text-align: center; color: #6b7280; }
        .sidebar .nav-link.active i, .sidebar .nav-link:hover i { color: inherit; }
        .sidebar .nav-link .badge { margin-left: auto; }
        .sidebar .nav-section { padding: 1.1rem 1.4rem .35rem; color: #9ca3af; font-size: .66rem; text-transform: uppercase; letter-spacing: .08em; font-weight: 700; }

        .main-content { margin-left: var(--sidebar-width); flex: 1; min-width: 0; display: flex; flex-direction: column; transition: margin-left .25s ease; }
        .topbar { background: #fff; height: 60px; padding: 0 1.5rem; border-bottom: 1px solid var(--line); display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 1030; }
        .topbar .breadcrumb { margin: 0; font-size: .875rem; }
        .topbar .breadcrumb a { color: #6b7280; text-decoration: none; }
        .topbar .breadcrumb a:hover { color: var(--primary); }
        .topbar .breadcrumb .active { color: #111827; font-weight: 600; }
        .topbar .breadcrumb-item + .breadcrumb-item::before { content: '›'; color: #9ca3af; }
        .topbar-icon { width: 36px; height: 36px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; color: #4b5563; background: transparent; border: 0; position: relative; }
        .topbar-icon:hover { background: #f3f4f6; color: #111827; }
        .content { padding: 1.75rem 2rem; flex: 1; }
        body.sb-collapsed .sidebar { transform: translateX(-100%); }
        body.sb-collapsed .main-content { margin-left: 0; }

        .card { border: none; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,.03); background: #fff; }
        .btn { border-radius: 8px; font-size: .875rem; font-weight: 500; }
        .btn-primary { background: var(--primary); border-color: var(--primary); }
        .btn-primary:hover { background: var(--primary-hover); border-color: var(--primary-hover); }
        .form-control, .form-select { border-radius: 8px; border: 1.5px solid #e2e8f0; font-size: .875rem; }
        .form-control:focus, .form-select:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(79,70,229,.1); }
        .table th { font-size: .72rem; text-transform: uppercase; letter-spacing: .05em; color: #64748b; font-weight: 600; background-color: #f8fafc !important; }
        .table td { font-size: .875rem; vertical-align: middle; color: #334155; }
        .badge { font-weight: 500; border-radius: 6px; }
        .fs-7 { font-size: .8rem !important; }
        .modal-content { border: none; border-radius: 12px; }
        .dropdown-menu { border: none; border-radius: 10px; box-shadow: 0 10px 30px rgba(0,0,0,.08); padding: .5rem; }
        .dropdown-item { border-radius: 6px; padding: .4rem .75rem; font-size: .85rem; }
        .avatar { width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: .75rem; }
        .min-w-0 { min-width: 0; }
        /* shared: stat cards that double as filters, filter box, page head */
        .ad-stats { display: grid; grid-template-columns: repeat(var(--cols, 6), minmax(0, 1fr)); gap: 12px; }
        @media (max-width: 1199.98px) { .ad-stats { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        @media (max-width: 575.98px) { .ad-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        .ad-stat { display: block; background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 16px 18px; text-decoration: none; transition: border-color .15s, box-shadow .15s; }
        .ad-stat:hover { border-color: #c7d2fe; box-shadow: 0 4px 14px rgba(79,70,229,.08); }
        .ad-stat.is-on { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(79,70,229,.12); }
        .ad-stat-lbl { font-size: .72rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: #6b7280; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .ad-stat-num { font-size: 1.6rem; font-weight: 700; margin-top: 4px; font-variant-numeric: tabular-nums; }
        .ad-box { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; }
        .ad-filters { padding: 18px 20px; }
        .pagination { margin: 0; gap: 4px; }
        .pagination .page-link { border-radius: 8px !important; border: 1px solid #e5e7eb; color: #374151; min-width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; font-size: .875rem; }
        .pagination .page-item.active .page-link { background: var(--primary); border-color: var(--primary); color: #fff; }
        nav[role="navigation"] p.small.text-muted { margin: 0; }
        @media (max-width: 768px) {
            .topbar { padding: 0 .75rem; gap: .5rem; }
            .topbar .breadcrumb-item:not(.active) { display: none; }
            .topbar .breadcrumb-item.active::before { display: none; }
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .content { padding: 1rem; }
            .mobile-overlay { display: block; position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 1035; }
        }
        @media (min-width: 769px) { .mobile-overlay { display: none; } }
    </style>
</head>
<body>
<script>try { if (localStorage.getItem('adminSidebarCollapsed') === '1' && window.innerWidth > 768) document.body.classList.add('sb-collapsed'); } catch (e) {}</script>
<div class="wrapper">
    @include('admin.layouts.sidebar')
    <div class="mobile-overlay d-md-none d-none" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <div class="main-content">
        @include('admin.layouts.topbar')
        <div class="content">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3">
                    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ $errors->first() }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // row menus sit inside cards that clip overflow; fixed positioning lets them escape
    bootstrap.Dropdown.Default.popperConfig = { strategy: 'fixed' };

    function toggleSidebar() {
        // phone: slide-in drawer; desktop: collapse/expand and remember it
        if (window.innerWidth <= 768) {
            document.querySelector('.sidebar').classList.toggle('show');
            document.getElementById('sidebarOverlay').classList.toggle('d-none');
            return;
        }
        const collapsed = document.body.classList.toggle('sb-collapsed');
        try { localStorage.setItem('adminSidebarCollapsed', collapsed ? '1' : '0'); } catch (e) {}
    }
</script>
@stack('scripts')
</body>
</html>
