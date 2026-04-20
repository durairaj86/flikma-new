@section('page-title', 'My Account Settings')
@section('js', 'user')

<x-app-layout>
    <main class="gmail-content bg-light d-flex">
        {{-- Navigation Menu (Sidebar) --}}
        @include('includes.settings-navigation')

        <section class="flex-grow-1 px-4 d-flex flex-column">
            @include('includes.master-header')

            <div class="content-wrapper flex-grow-1">
                <section class="content py-5">
                    <div class="container-fluid">

                        <div class="row g-4">

                            {{-- Profile Settings Column (Full Width) --}}
                            <div class="col-md-12">
                                <div class="rounded-3">

                                    {{-- FORM START: Ensure enctype="multipart/form-data" for file uploads --}}
                                    {{-- You need a valid action URL and method for Laravel here --}}
                                    <form id="profile-form" method="POST" action="{{ route('user.profile.update') }}"
                                          enctype="multipart/form-data">
                                        @csrf
                                        <div class="mb-5 pb-3 border-bottom">

                                            <h5 class="fw-bold mb-4 text-secondary">Basic Profile Information</h5>

                                            <div class="d-flex align-items-center mb-4">
                                                <div class="profile-pic me-4 position-relative">
                                                    {{-- Profile Image with ID for JS Preview --}}
                                                    @if($user->profile_photo_path)
                                                        <img src="{{ asset($user->profile_photo_path) }}"
                                                             class="rounded-circle shadow-lg"
                                                             width="80" height="80" alt="Profile Picture"
                                                             id="profile_photo_preview">
                                                    @else
                                                        <div id="profile_avatar"
                                                             class="rounded-circle shadow-lg text-center"
                                                             style="background-color: #0054d2; color: #fff; font-size: 32px; font-weight: 600; line-height: 80px; width: 80px; height: 80px; object-fit: cover;">
                                                            {{ getInitials($user->name) }}
                                                        </div>
                                                    @endif
                                                    {{-- Hidden File Input: This is the actual element that handles the file --}}
                                                    <input type="file" id="profile_photo_upload" name="profile_photo"
                                                           style="display: none;">

                                                    {{-- Camera Icon Button (Label linked to the hidden input) --}}
                                                    {{--<label for="profile_photo_upload"
                                                           class="btn btn-sm btn-light p-1 rounded-circle position-absolute translate-middle"
                                                           style="bottom: 0px; right: -5px; box-shadow: 0 0 8px rgba(0,0,0,.2); cursor: pointer;"
                                                           title="Upload Photo">
                                                        <i class="bi bi-camera fs-6 text-muted"></i>
                                                    </label>--}}
                                                </div>
                                                <div>
                                                    <h4 class="mb-1 fw-bolder">{{ $user->name }}</h4>
                                                    <p class="text-muted mb-0 small">{{ roleDisplay($user->role) }}</p>

                                                    {{-- Change Photo Button (Label linked to the hidden input) --}}
                                                    <label for="profile_photo_upload"
                                                           class="btn btn-outline-primary btn-sm mt-2"
                                                           style="cursor: pointer;">
                                                        Change Photo
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="row g-4 mt-4">
                                                <div class="col-md-6">
                                                    <label class="form-label fw-medium">Full Name</label>
                                                    <input type="text" class="form-control" name="full_name"
                                                           value="{{ $user->name }}">
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label fw-medium">Role / Designation</label>
                                                    <input type="text" class="form-control" disabled
                                                           value="{{ roleDisplay($user->role) }}">
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label fw-medium">Email Address
                                                        (Read-Only)</label>
                                                    <input type="email" class="form-control bg-light" disabled
                                                           value="{{ $user->email }}" readonly>
                                                    <small class="text-muted">Contact support to change your primary
                                                        email.</small>
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label fw-medium">Phone Number</label>
                                                    <input type="tel" class="form-control" name="phone"
                                                           value="{{ $user->phone }}">
                                                </div>
                                            </div>
                                        </div>

                                        {{-- ------------------------------------------------ --}}
                                        {{-- 2. SECURITY & AUTHENTICATION SECTION --}}
                                        {{-- ------------------------------------------------ --}}
                                        <div class="mt-4">
                                            <h5 class="fw-bold mb-4 text-secondary">Security & Authentication</h5>

                                            <div class="row g-4">
                                                <div class="col-md-6">
                                                    <label class="form-label fw-medium">Current Password</label>
                                                    <input type="password" class="form-control" name="current_password"
                                                           placeholder="Enter current password">
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label fw-medium">New Password</label>
                                                    <input type="password" class="form-control" name="new_password"
                                                           placeholder="Enter new password">
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label fw-medium">Confirm New Password</label>
                                                    <input type="password" class="form-control"
                                                           name="confirm_new_password"
                                                           placeholder="Confirm new password">
                                                </div>
                                            </div>
                                        </div>

                                        {{-- ------------------------------------------------ --}}
                                        {{-- 3. SAVE BUTTON --}}
                                        {{-- ------------------------------------------------ --}}
                                        <div class="text-end mt-5 pt-3 border-top">
                                            <button type="submit" class="btn btn-primary btn-lg px-5 fw-bold shadow-sm"
                                                    id="submit">
                                                <i class="bi bi-cloud-arrow-up me-2"></i> Save All Changes
                                            </button>
                                        </div>

                                    </form>
                                </div>
                            </div>
                        </div>

                    </div>
                </section>
            </div>

            {{-- ------------------------------------------------ --}}
            {{-- CSS STYLES --}}
            {{-- ------------------------------------------------ --}}
            <style>
                .account-nav .list-group-item {
                    border: 0;
                    padding: 15px 20px;
                    cursor: pointer;
                    font-size: 15px;
                    transition: background 0.2s, color 0.2s;
                }

                .account-nav .list-group-item:hover {
                    background: #f8f9fa;
                }

                .account-nav .list-group-item.active {
                    background: #eef5ff; /* Light blue background */
                    color: #0054d2; /* Primary blue text */
                    font-weight: 600;
                    border-left: 4px solid #0054d2;
                    border-radius: 0;
                }

                .profile-pic img {
                    object-fit: cover;
                    box-shadow: 0 4px 12px rgba(0, 0, 0, .1);
                }
            </style>


        </section>
    </main>

    {{-- ------------------------------------------------ --}}
    {{-- JAVASCRIPT FOR IMAGE PREVIEW --}}
    {{-- ------------------------------------------------ --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const fileInput = document.getElementById('profile_photo_upload');
            const preview = document.getElementById('profile_photo_preview');

            fileInput.addEventListener('change', function (e) {
                if (e.target.files && e.target.files[0]) {
                    const reader = new FileReader();

                    reader.onload = function (event) {
                        // Update the 'src' attribute of the image tag with the new preview
                        preview.src = event.target.result;
                    }

                    reader.readAsDataURL(e.target.files[0]);
                }
            });
        });
    </script>
</x-app-layout>
