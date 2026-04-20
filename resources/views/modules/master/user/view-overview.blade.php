<div class="user-overview card shadow-sm border-0">

    <!-- Header -->
    <div class="card-header bg-light border-bottom py-3 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <div class="avatar rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center me-3" style="width:50px;height:50px;">
                <i class="bi bi-person-fill fs-4"></i>
            </div>
            <div>
                <h5 class="fw-bold mb-0">{{ $user->name }}</h5>
                <small class="text-muted text-uppercase">{{ ucfirst(roleDisplay($user->role)) }}</small>
            </div>
        </div>
        <div>
            <span class="badge rounded-pill px-3 py-2
                @switch($user->status)
                    @case('1') bg-success-subtle text-success @break
                    @case('0') bg-secondary-subtle text-secondary @break
                @endswitch">
                @switch($user->status)
                    @case('1') Active @break
                    @case('0') Inactive @break
                @endswitch
            </span>
        </div>
    </div>

    <!-- Body -->
    <div class="card-body p-4">

        <!-- Info Sections -->
        <div class="row g-4 mb-4">

            <!-- Left Column -->
            <div class="col-md-6">
                <div class="border rounded-3 p-3 h-100">
                    <h6 class="fw-semibold border-bottom pb-2 mb-3 text-uppercase small text-muted">Basic Information</h6>
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted" style="width:40%"><i class="bi bi-person me-2 text-primary"></i> Name</td>
                            <td class="fw-medium">{{ $user->name }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted"><i class="bi bi-envelope me-2 text-primary"></i> Email</td>
                            <td class="fw-medium">{{ $user->email }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted"><i class="bi bi-telephone me-2 text-primary"></i> Phone</td>
                            <td class="fw-medium">{{ $user->phone ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted"><i class="bi bi-diagram-3 me-2 text-primary"></i> Department</td>
                            <td class="fw-medium">{{ $user->department?->name ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-md-6">
                <div class="border rounded-3 p-3 h-100">
                    <h6 class="fw-semibold border-bottom pb-2 mb-3 text-uppercase small text-muted">Access & Permissions</h6>
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted" style="width:45%"><i class="bi bi-key me-2 text-primary"></i> Login Permission</td>
                            <td>
                                <span class="{{ $user->login_permission == 'yes' ? 'text-success fw-semibold' : 'text-danger fw-semibold' }}">
                                    {{ ucfirst($user->login_permission ?? 'N/A') }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted"><i class="bi bi-toggle-on me-2 text-primary"></i> Status</td>
                            <td>{{ $user->status == 1 ? 'Active' : 'Inactive' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted"><i class="bi bi-calendar-check me-2 text-primary"></i> Joined On</td>
                            <td>{{ showDate($user->created_at) ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted"><i class="bi bi-clock-history me-2 text-primary"></i> Last Login</td>
                            <td>{{ $user->last_login ?? 'Never logged in' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Address Section -->
        <div class="border rounded-3 p-3 mb-4">
            <h6 class="fw-semibold border-bottom pb-2 mb-3 text-uppercase small text-muted">Address</h6>
            <table class="table table-sm table-borderless mb-0">
                <tr><td class="text-muted" style="width:35%"><i class="bi bi-geo-alt me-2 text-primary"></i> Address Line 1</td><td>{{ $user->address_1 ?? 'N/A' }}</td></tr>
                <tr><td class="text-muted"><i class="bi bi-geo me-2 text-primary"></i> Address Line 2</td><td>{{ $user->address_2 ?? 'N/A' }}</td></tr>
                <tr><td class="text-muted"><i class="bi bi-map me-2 text-primary"></i> City</td><td>{{ $user->city ?? 'N/A' }}</td></tr>
                <tr><td class="text-muted"><i class="bi bi-signpost me-2 text-primary"></i> State</td><td>{{ $user->state ?? 'N/A' }}</td></tr>
                <tr><td class="text-muted"><i class="bi bi-mailbox me-2 text-primary"></i> Postal Code</td><td>{{ $user->postal_code ?? 'N/A' }}</td></tr>
                <tr><td class="text-muted"><i class="bi bi-flag me-2 text-primary"></i> Country</td><td>{{ $user->country ?? 'N/A' }}</td></tr>
            </table>
        </div>

        <!-- Notes Section -->
        <div class="border rounded-3 p-3 bg-light">
            <h6 class="fw-semibold border-bottom pb-2 mb-3 text-uppercase small text-muted">Notes</h6>
            <p class="mb-0 small text-muted">{{ $user->remark ?? 'No additional information available.' }}</p>
        </div>
    </div>
</div>

<style>
    .user-overview .table td {
        vertical-align: middle;
        padding: 0.35rem 0.5rem;
    }
    .user-overview h6 {
        letter-spacing: 0.5px;
        font-weight: 700;
    }
    .user-overview .card-header {
        border-bottom: 1px solid rgba(0,0,0,0.05) !important;
    }
    .user-overview .card-body {
        font-size: 0.92rem;
        color: #333;
    }
</style>
