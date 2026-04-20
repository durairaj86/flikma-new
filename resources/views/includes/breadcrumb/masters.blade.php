<ol class="breadcrumb">
    <li class="breadcrumb-item text-muted {{ $page1 == 'users' ? 'active' : 'link' }}" data-url="/masters/users">Users
    </li>
    <li class="breadcrumb-item text-muted {{ ($page1 == 'transport' && in_array($page3,['seaports','airports'])) ? 'active' : 'link' }}"
        data-url="/masters/transport/directories/seaports">Transport Directory
    </li>
    <li class="breadcrumb-item text-muted {{ in_array($page1,['services','package','container','currencies']) ? 'active' : 'link' }}"
        data-url="/masters/services">Predefined Data
    </li>
    {{--<li class="breadcrumb-item text-muted"><a href="#">Finance Data</a></li>--}}
    <li class="breadcrumb-item text-muted {{ $page1 == 'banks' ? 'active' : 'link' }}" data-url="/masters/banks">Banks
    </li>
</ol>
