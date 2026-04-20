@section('js','user')
@section('page-title','Users')
<x-app-layout>
    <main class="gmail-content bg-white d-flex">
        @include('includes.master-navigation')
        <section class="flex-grow-1 px-4 d-flex flex-column">
            @include('includes.master-header')
            <div class="align-items-center flex-shrink-0">
                <div class="gap-4">
                    <ul class="nav status-tabs align-items-center border-bottom m-0" id="listTabs" role="tablist"
                        aria-label="Navigation 13">
                        <li class="nav-item me-2">
                            <button
                                class="nav-link px-3 py-2 d-flex align-items-center justify-content-between status-btn active"
                                data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="user">
                                <span><i class="bi bi-person-check text-warning me-1"></i> Active Users -</span>
                                <span class="status-count ms-2" id="userCount">0</span>
                            </button>
                        </li>
                        <li class="nav-item me-2">
                            <button
                                class="nav-link py-2 d-flex align-items-center justify-content-between status-btn"
                                data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="employee">
                                <span><i class="bi bi-people text-success me-1"></i> Employees -</span>
                                <span class="status-count ms-2" id="employeeCount">0</span>
                            </button>
                        </li>
                        <li class="nav-item me-2">
                            <button
                                class="nav-link py-2 d-flex align-items-center justify-content-between status-btn"
                                data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="terminated">
                                <span><i class="bi bi-person-x text-secondary me-1"></i> Terminated -</span>
                                <span class="status-count ms-2" id="terminatedCount">0</span>
                            </button>
                        </li>
                        <li class="nav-item">
                            <button
                                class="nav-link py-2 d-flex align-items-center justify-content-between status-btn"
                                data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="allUsers">
                                <span><i class="bi bi-people-fill text-danger me-1"></i> All -</span>
                                <span class="status-count ms-2" id="allUsersCount">0</span>
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="d-flex justify-content-between py-3">
                <div class="align-items-center gap-2">
                    <div class="search-box position-relative me-2">
                        <!-- Search icon -->
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>

                        <input type="text" id="customSearch" class="form-control rounded-pill ps-5"
                               placeholder="Search..." aria-label="Search...">
                    </div>
                </div>
                <button class="btn btn-primary rounded-pill px-4" id="new">New User</button>
            </div>
            <div class="shadow bdr-r-10 py-3 flex-grow-1">
                <!-- Table with scroll -->
                <div class="flex-grow-1">
                    <table class="table align-middle dataTable" id="dataTable">
                        <thead class="table-light sticky-top bg-white">
                        <tr>
                            <th style="width: 10px">#</th>
                            <th>Name</th>
                            <th>Contact</th>
                            <th>Department</th>
                            <th>Last Login</th>
                            <th>Joined</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>
    @include('modules.master.user.user-view')
</x-app-layout>
