@section('js','customer')
@section('page-title','Customers')
<x-app-layout>
    <div class="shadow m-4 bdr-r-10">
        <div class="p-4 pt-0 pb-0">
            <div class="d-flex justify-content-between align-items-center">
                <!-- Tabs -->
                <div class="d-inline-block d-none">
                    <ul class="nav status-tabs" id="listTabs" role="tablist" aria-label="Navigation 13">
                        <li class="nav-item">
                            <button class="nav-link px-3 py-2 d-flex align-items-center justify-content-between status-btn"
                                    data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="pending">
                            <span class="d-flex align-items-center">
                                <i class="bi bi-play-circle me-1"></i> Pending -
                            </span>
                                <small class="status-count d-flex align-items-center justify-content-center"
                                       id="pendingCount">0</small>
                            </button>
                        </li>


                        <li class="nav-item">
                            <button class="nav-link py-2 d-flex align-items-center justify-content-between active status-btn"
                                    data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="confirmed">
                                <span><i class="bi bi-play-circle me-1"></i> Confirmed -</span>
                                <span class="status-count d-flex align-items-center justify-content-center"
                                      id="confirmedCount">0</span>
                            </button>
                        </li>

                        <li class="nav-item">
                            <button class="nav-link py-2 d-flex align-items-center justify-content-between status-btn"
                                    data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="blocked">
                                <span><i class="bi bi-play-circle me-1"></i> Blocked -</span>
                                <span class="status-count d-flex align-items-center justify-content-center"
                                      id="blockedCount">0</span>
                            </button>
                        </li>

                        <li class="nav-item">
                            <button class="nav-link py-2 d-flex align-items-center justify-content-between status-btn"
                                    data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="rejected">
                                <span><i class="bi bi-play-circle me-1"></i> Rejected -</span>
                                <span class="status-count d-flex align-items-center justify-content-center"
                                      id="rejectedCount">0</span>
                            </button>
                        </li>


                    </ul>
                    <!-- Indicator -->
                    <div class="tab-indicator"></div>
                </div>

                <style>
                    .status-tabs {
                        display: flex;
                        gap: 1.5rem;
                        border-bottom: 1px solid #e0e0e0;
                        padding-bottom: 0.25rem;
                        font-family: Arial, sans-serif;
                    }

                    .status-btn {
                        background: none;
                        border: none;
                        padding: 0.5rem 0;
                        font-size: 0.95rem;
                        color: #5f6368;
                        cursor: pointer;
                        position: relative;
                        display: flex;
                        align-items: center;
                        gap: 0.4rem;
                        transition: color 0.2s;
                    }

                    .status-btn:hover {
                        color: #202124;
                    }

                    .status-btn.active {
                        color: #202124;
                        font-weight: 500;
                    }

                    .status-btn.active::after {
                        content: '';
                        position: absolute;
                        bottom: -2px;
                        left: 0;
                        width: 100%;
                        height: 3px;
                        background-color: #1a73e8; /* Gmail blue underline */
                        border-radius: 2px;
                    }

                    .status-btn i {
                        font-size: 1rem;
                    }

                    .status-count {
                        font-size: 0.8rem;
                        color: #5f6368;
                        background: #f1f3f4;
                        padding: 0 0.4rem;
                        border-radius: 12px;
                        min-width: 20px;
                        text-align: center;
                    }
                </style>

                <div class="status-tabs">
                    <button class="status-btn active" id="pending">
                        <i class="bi bi-clock"></i> Pending <span class="status-count" id="pendingCount">3</span>
                    </button>
                    <button class="status-btn" id="confirmed">
                        <i class="bi bi-check-circle"></i> Confirmed <span class="status-count" id="confirmedCount">5</span>
                    </button>
                    <button class="status-btn" id="blocked">
                        <i class="bi bi-slash-circle"></i> Blocked <span class="status-count" id="blockedCount">2</span>
                    </button>
                    <button class="status-btn" id="rejected">
                        <i class="bi bi-x-circle"></i> Rejected <span class="status-count" id="rejectedCount">1</span>
                    </button>
                </div>


                {{--<div class="status-tabs">
                    <button class="status-btn active">
                        <i class="bi bi-clock"></i> Pending <span class="count">3</span>
                    </button>
                    <button class="status-btn">
                        <i class="bi bi-check-circle"></i> Confirmed <span class="count">5</span>
                    </button>
                    <button class="status-btn">
                        <i class="bi bi-slash-circle"></i> Blocked <span class="count">2</span>
                    </button>
                    <button class="status-btn">
                        <i class="bi bi-x-circle"></i> Rejected <span class="count">1</span>
                    </button>
                </div>--}}



                <!-- Button -->
                <button class="btn btn-primary btn-round" id="new">New Customer</button>
            </div>
        </div>
        <div class="pt-0">
            <div class=" pb-3 bdr-r-10">
                <table class="table dataTable" id="dataTable" data-module-url="customer">
                    <thead>
                    <tr>
                        <th style="width: 10px">#</th>
                        <th>Customer</th>
                        <th>Contact</th>
                        <th>Location</th>
                        <th>Currency</th>
                        <th>VAT #</th>
                        <th>Credit</th>
                        <th>Salesperson</th>
                        <th>Joined</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    {{--<tr>
                        <td colspan="4">
                            <div id="loading-spinner" class="text-center p-4">
                                <div class="spinner-border text-primary" role="status"></div>
                                <p class="mt-2">Loading data...</p>
                            </div>
                        </td>
                    </tr>--}}
                    {{--<tr class="align-middle">
                        <td>1.</td>
                        <td>Update software</td>
                        <td>
                            <div class="progress progress-xs">
                                <div
                                    class="progress-bar progress-bar-danger"
                                    style="width: 55%"
                                ></div>
                            </div>
                        </td>
                        <td><span class="badge text-bg-danger">55%</span></td>
                    </tr>
                    <tr class="align-middle">
                        <td>2.</td>
                        <td>Clean database</td>
                        <td>
                            <div class="progress progress-xs">
                                <div class="progress-bar text-bg-warning" style="width: 70%"></div>
                            </div>
                        </td>
                        <td><span class="badge text-bg-warning">70%</span></td>
                    </tr>
                    <tr class="align-middle">
                        <td>3.</td>
                        <td>Cron job running</td>
                        <td>
                            <div class="progress progress-xs progress-striped active">
                                <div class="progress-bar text-bg-primary" style="width: 30%"></div>
                            </div>
                        </td>
                        <td><span class="badge text-bg-primary">30%</span></td>
                    </tr>
                    <tr class="align-middle">
                        <td>4.</td>
                        <td>Fix and squish bugs</td>
                        <td>
                            <div class="progress progress-xs progress-striped active">
                                <div class="progress-bar text-bg-success" style="width: 90%"></div>
                            </div>
                        </td>
                        <td><span class="badge text-bg-success">90%</span></td>
                    </tr>--}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @include('modules.customer.customer-view')
    @include('modules.email.send-email')
</x-app-layout>
<style>

</style>
