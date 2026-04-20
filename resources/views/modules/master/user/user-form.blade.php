<div class="container px-4 py-3 align-items-center" id="modal-buttons" data-buttons="cancel,save"
     data-button-save="Save Customer">
    <!-- Meta Info -->
    <div class="row g-3 align-items-center bg-white border-bottom py-2 mb-3 small">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-4">
                <div class="module-info">
                    <span class="fw-semibold fs-5">{{ $user->name ?? 'New User' }}</span> <small
                        class="text-secondary">{{ $user->row_no ? ' - ' . $user->row_no : '' }}</small>
                </div>

            </div>

            <!-- Save & Next Button -->
            <div id="show-buttons"></div>
        </div>
    </div>
    <div class="row">
        <div class="d-flex justify-content-center">
            <div class="d-inline-block p-1">
                <ul class="nav status-tabs align-items-center border-bottom mb-0 mt-0 justify-content-center"
                    id="modalTabs" role="tablist">
                    <li class="nav-item me-2">
                        <button
                            class="nav-link px-3 py-2 d-flex align-items-center justify-content-between status-btn active"
                            data-bs-toggle="tab" data-bs-target="#tab-basic"
                            type="button">
                            <i class="bi bi-person-lines-fill me-1"></i> Basic Info
                        </button>
                    </li>
                    <li class="nav-item me-2">
                        <button
                            class="nav-link px-3 py-2 d-flex align-items-center justify-content-between status-btn"
                            data-bs-toggle="tab" data-bs-target="#tab-address"
                            type="button">
                            <i class="bi bi-geo-alt-fill me-1"></i> Address
                        </button>
                    </li>
                </ul>
            </div>
        </div>
        <form id="moduleForm" novalidate action="{{ request()->url() }}">
            @csrf
            <input type="hidden" name="data-id" value="{{ $user->id }}">

            <!-- Tab Content -->
            <div class="tab-content">

                <!-- Tab 1: Basic Info -->
                <div class="tab-pane show active" id="tab-basic">
                    <div class="model-form-tab-div">
                        <div class="model-form-sub-title">
                            <h5>Basic Info</h5>
                        </div>
                        <div class="row">
                            <div class="col-4 form-group">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="name" class="form-control" required value="{{ $user->name }}">
                            </div>
                            <div class="col-4 form-group">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" required
                                       value="{{ $user->email }}">
                            </div>
                            <div class="col-4 form-group">
                                <label class="form-label">Status</label>
                                <select name="status" class="tom-select" required>
                                    <option value="">Select</option>
                                    <option value="1" @selected($user->status)>Active</option>
                                    <option value="0" @selected(!$user->status)>Inactive</option>
                                </select>
                            </div>
                            <div class="col-4 form-group">
                                <label class="form-label">Login Permission</label>
                                <select name="login_permission" id="loginPermission" class="tom-select" required>
                                    <option value="">Select</option>
                                    <option value="yes" @selected($user->login_permission == 'yes')>Yes
                                    </option>
                                    <option value="no" @selected($user->login_permission == 'no')>No</option>
                                </select>
                            </div>
                            <div class="col-4 form-group passwordDiv">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" id="password" class="form-control">
                            </div>
                            <div class="col-4 form-group confirmPasswordDiv">
                                <label class="form-label">Confirm Password</label>
                                <input type="password" name="confirm_password" id="confirmPassword"
                                       class="form-control">
                            </div>
                            <div class="col-4 form-group">
                                <label class="form-label">Department</label>
                                <select name="department" class="tom-select" required>
                                    @foreach($departments as $departmentId => $departmentName)
                                        <option
                                            value="{{ $departmentId }}" @selected($departmentId == $user->department_id)>{{ $departmentName }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-4 form-group">
                                <label class="form-label">Role</label>
                                <x-common.roles :value="$user->role"></x-common.roles>
                            </div>
                        </div>
                        <div class="model-form-sub-title">
                            <h5>Contact</h5>
                        </div>
                        <div class="row">
                            <div class="col-4 form-group">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control" value="{{ $user->phone }}" required>
                            </div>
                            <div class="col-4 form-group">
                                <label class="form-label">Alternate Email</label>
                                <input type="email" name="alt_email" class="form-control"
                                       value="{{ $user->alternate_email }}">
                            </div>
                            <div class="col-4 form-group">
                                <label class="form-label">Remarks</label>
                                <textarea name="remark" class="form-control" rows="2">{{ $user->remark }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab 2: Address -->
                <div class="tab-pane" id="tab-address">
                    <div class="model-form-tab-div">
                        <div class="model-form-sub-title">
                            <h5>Address</h5>
                        </div>
                        <div class="row">
                            <div class="col-4 form-group">
                                <label class="form-label">Address Line 1</label>
                                <input type="text" name="address1" class="form-control" value="{{ $user->address_1 }}">
                            </div>
                            <div class="col-4 form-group">
                                <label class="form-label">Address Line 2</label>
                                <input type="text" name="address2" class="form-control" value="{{ $user->address_2 }}">
                            </div>
                            <div class="col-4 form-group">
                                <label class="form-label">City</label>
                                <input type="text" name="city" class="form-control" value="{{ $user->city }}">
                            </div>
                            <div class="col-4 form-group">
                                <label class="form-label">State</label>
                                <input type="text" name="state" class="form-control" value="{{ $user->state }}">
                            </div>
                            <div class="col-4 form-group">
                                <label class="form-label">Postal Code</label>
                                <input type="text" name="postal_code" class="form-control"
                                       value="{{ $user->postal_code }}">
                            </div>
                            <div class="col-4 form-group">
                                <label class="form-label">Country</label>
                                <x-common.country :value="$user->country"></x-common.country>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>

<!-- JS for password field toggling -->
<script>
    $(document).ready(function () {
        function togglePasswordFields() {
            if ($('#loginPermission').val() === 'yes') {
                $('.passwordDiv, .confirmPasswordDiv').show();
                //$('#password, #confirmPassword').attr('required', true);
            } else {
                $('.passwordDiv, .confirmPasswordDiv').hide();
                //$('#password, #confirmPassword').removeAttr('required').val('');
            }
        }

        togglePasswordFields();
        $('#loginPermission').change(togglePasswordFields);
    });
</script>

<!-- Optional: style password fields initially -->
<style>
    .passwordDiv,
    .confirmPasswordDiv {
        display: none;
    }
</style>
