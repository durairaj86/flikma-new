<div class="container px-4 py-3 align-items-center" id="modal-buttons" data-buttons="cancel,save"
     data-button-save="{{ __('Save Employee') }}">
    <!-- Meta Info -->
    <div class="row g-3 align-items-center bg-white border-bottom py-2 mb-3 small">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-4">
                <div class="module-info">
                    <span class="fw-semibold fs-5">{{ $user->name ?? __('New Employee') }}</span> <small
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
                            <i class="bi bi-person-lines-fill me-1"></i> {{ __('Basic Info') }}
                        </button>
                    </li>
                    <li class="nav-item me-2">
                        <button
                            class="nav-link px-3 py-2 d-flex align-items-center justify-content-between status-btn"
                            data-bs-toggle="tab" data-bs-target="#tab-address"
                            type="button">
                            <i class="bi bi-geo-alt-fill me-1"></i> {{ __('Address') }}
                        </button>
                    </li>
                    <li class="nav-item me-2">
                        <button
                            class="nav-link px-3 py-2 d-flex align-items-center justify-content-between status-btn"
                            data-bs-toggle="tab" data-bs-target="#tab-employee"
                            type="button">
                            <i class="bi bi-person-badge me-1"></i> {{ __('Employment') }}
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
                            <h5>{{ __('Basic Info') }}</h5>
                        </div>
                        <div class="row">
                            <div class="col-4 form-group">
                                <label class="form-label">{{ __('Full Name') }}</label>
                                <input type="text" name="name" class="form-control" required value="{{ $user->name }}">
                            </div>
                            <div class="col-4 form-group">
                                <label class="form-label">{{ __('Email') }}</label>
                                <input type="email" name="email" class="form-control" required
                                       value="{{ $user->email }}">
                            </div>
                            <div class="col-4 form-group">
                                <label class="form-label">{{ __('Status') }}</label>
                                <select name="status" class="tom-select" required>
                                    <option value="">{{ __('Select') }}</option>
                                    <option value="active" @selected($user->status == 'active' || !$user->exists)>{{ __('Active') }}</option>
                                    <option value="inactive" @selected($user->exists && $user->status != 'active')>{{ __('Inactive') }}</option>
                                </select>
                            </div>
                            <div class="col-4 form-group">
                                <label class="form-label">{{ __('Login Permission') }}</label>
                                <select name="login_permission" id="loginPermission" class="tom-select" required>
                                    <option value="">{{ __('Select') }}</option>
                                    <option value="yes" @selected($user->login_permission == 'yes')>{{ __('Yes') }}
                                    </option>
                                    <option value="no" @selected($user->login_permission == 'no')>{{ __('No') }}</option>
                                </select>
                                {{-- someone who can already sign in keeps their password unless this is clicked --}}
                                <div class="mt-1">
                                    <a href="#" id="changePasswordLink" class="small d-none">
                                        <i class="bi bi-key me-1"></i>{{ __('Change password') }}
                                    </a>
                                </div>
                            </div>
                            <div class="col-4 form-group passwordDiv">
                                <label class="form-label">{{ __('Password') }} <span class="text-danger" id="passwordRequiredMark">*</span></label>
                                <input type="password" name="password" id="password" class="form-control"
                                       autocomplete="new-password" minlength="8" maxlength="50" disabled>
                                {{-- the one-line help under the box comes from the Masters hints; these links sit under it --}}
                                <div class="d-flex justify-content-between small mt-1">
                                    <a href="#" id="togglePassword"><i class="bi bi-eye me-1"></i><span>{{ __('Show password') }}</span></a>
                                    <a href="#" id="cancelPasswordLink" class="d-none">{{ __('Cancel') }}</a>
                                </div>
                            </div>
                            <div class="col-4 form-group">
                                <label class="form-label">{{ __('Department') }}</label>
                                <select name="department" class="tom-select" required>
                                    @foreach($departments as $departmentId => $departmentName)
                                        <option
                                            value="{{ $departmentId }}" @selected($departmentId == $user->department_id)>{{ $departmentName }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-4 form-group">
                                <label class="form-label">{{ __('Role') }}</label>
                                <x-common.roles :value="$user->role"></x-common.roles>
                            </div>
                        </div>
                        <div class="model-form-sub-title">
                            <h5>{{ __('Contact') }}</h5>
                        </div>
                        <div class="row">
                            <div class="col-4 form-group">
                                <label class="form-label">{{ __('Phone') }}</label>
                                <input type="text" name="phone" class="form-control" value="{{ $user->phone }}" required>
                            </div>
                            <div class="col-4 form-group">
                                <label class="form-label">{{ __('Alternate Email') }}</label>
                                <input type="email" name="alt_email" class="form-control"
                                       value="{{ $user->alternate_email }}">
                            </div>
                            <div class="col-4 form-group">
                                <label class="form-label">{{ __('Remarks') }}</label>
                                <textarea name="remark" class="form-control" rows="2">{{ $user->remark }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab 2: Address -->
                <div class="tab-pane" id="tab-address">
                    <div class="model-form-tab-div">
                        <div class="model-form-sub-title">
                            <h5>{{ __('Address') }}</h5>
                        </div>
                        <div class="row">
                            <div class="col-4 form-group">
                                <label class="form-label">{{ __('Address Line 1') }}</label>
                                <input type="text" name="address1" class="form-control" value="{{ $user->address_1 }}">
                            </div>
                            <div class="col-4 form-group">
                                <label class="form-label">{{ __('Address Line 2') }}</label>
                                <input type="text" name="address2" class="form-control" value="{{ $user->address_2 }}">
                            </div>
                            <div class="col-4 form-group">
                                <label class="form-label">{{ __('City') }}</label>
                                <input type="text" name="city" class="form-control" value="{{ $user->city }}">
                            </div>
                            <div class="col-4 form-group">
                                <label class="form-label">{{ __('State') }}</label>
                                <input type="text" name="state" class="form-control" value="{{ $user->state }}">
                            </div>
                            <div class="col-4 form-group">
                                <label class="form-label">{{ __('Postal Code') }}</label>
                                <input type="text" name="postal_code" class="form-control"
                                       value="{{ $user->postal_code }}">
                            </div>
                            <div class="col-4 form-group">
                                <label class="form-label">{{ __('Country') }}</label>
                                <x-common.country :value="$user->country"></x-common.country>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab 3: Employment & payroll -->
                <div class="tab-pane" id="tab-employee">
                    <div class="model-form-tab-div">
                        <div class="model-form-sub-title">
                            <h5>{{ __('Employment') }}</h5>
                        </div>
                        <div class="row">
                            <div class="col-4 form-group">
                                <label class="form-label">{{ __('Employee Code') }}</label>
                                <input type="text" class="form-control" value="{{ $user->employee_code ?: __('Assigned when saved') }}" disabled>
                            </div>
                            <div class="col-4 form-group">
                                <label class="form-label">{{ __('Designation') }}</label>
                                <input type="text" name="designation" class="form-control" value="{{ $user->designation }}" maxlength="100">
                            </div>
                            <div class="col-4 form-group">
                                <label class="form-label">{{ __('Date of Joining') }}</label>
                                <input type="text" name="joining_date" class="form-control datepicker" value="{{ $user->joining_date?->format('d-m-Y') }}">
                            </div>
                            <div class="col-4 form-group">
                                <label class="form-label">{{ __('Date of Birth') }}</label>
                                <input type="text" name="dob" class="form-control datepicker" value="{{ $user->dob?->format('d-m-Y') }}">
                            </div>
                            <div class="col-4 form-group">
                                <label class="form-label">{{ __('Gender') }}</label>
                                <select name="gender" class="tom-select">
                                    <option value="">{{ __('Select') }}</option>
                                    <option value="0" @selected($user->gender !== null && (string) $user->gender === '0')>{{ __('Male') }}</option>
                                    <option value="1" @selected((string) $user->gender === '1')>{{ __('Female') }}</option>
                                    <option value="2" @selected((string) $user->gender === '2')>{{ __('Other') }}</option>
                                </select>
                            </div>
                            <div class="col-4 form-group">
                                <label class="form-label">{{ __('Machine ID (punch device)') }}</label>
                                <input type="text" name="device_user_id" class="form-control" value="{{ $user->device_user_id }}" maxlength="64">
                                <div class="form-text">{{ __('The ID this person uses on the fingerprint / face machine. Shift, bank, IBAN and GOSI are set under Payroll > Employee Profiles.') }}</div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>

<!-- Password: required when login is switched on for someone who could not sign in; "Change password" for someone who can -->
<script>
    $(document).ready(function () {
        // exists + could already sign in before this edit = the password stays unless "Change password" is used
        const keepsPassword = @json($user->exists && $user->login_permission === 'yes' && $user->login_enabled !== false);
        let changing = false;

        function syncPassword() {
            const loginOn = $('#loginPermission').val() === 'yes';
            const mustSet = loginOn && !keepsPassword;            // a new employee, or login was off: a password is needed
            const open = loginOn && (mustSet || changing);

            $('#changePasswordLink').toggleClass('d-none', !(loginOn && keepsPassword && !changing));
            $('.passwordDiv').toggle(open);
            $('#cancelPasswordLink').toggleClass('d-none', !(open && keepsPassword));
            $('#passwordRequiredMark').toggle(mustSet || changing);
            // a hidden box must not be sent: it would also let a browser's saved password slip in and replace the real one
            $('#password').prop('disabled', !open).prop('required', open);
            if (!open) {
                $('#password').val('').attr('type', 'password');
                $('#togglePassword i').attr('class', 'bi bi-eye me-1');
                $('#togglePassword span').text(@json(__('Show password')));
            }
        }

        $('#loginPermission').on('change', syncPassword);
        $('#changePasswordLink').on('click', function (e) {
            e.preventDefault();
            changing = true;
            syncPassword();
            $('#password').trigger('focus');
        });
        $('#cancelPasswordLink').on('click', function (e) {
            e.preventDefault();
            changing = false;
            syncPassword();
        });
        $('#togglePassword').on('click', function (e) {
            e.preventDefault();
            const input = $('#password');
            const show = input.attr('type') === 'password';
            input.attr('type', show ? 'text' : 'password');
            $(this).find('i').toggleClass('bi-eye', !show).toggleClass('bi-eye-slash', show);
            $(this).find('span').text(show ? @json(__('Hide password')) : @json(__('Show password')));
        });

        syncPassword();
    });
</script>

<style>
    .passwordDiv {
        display: none;
    }
</style>
