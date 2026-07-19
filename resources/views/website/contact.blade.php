<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Request a Flikma demo or get in touch with our team — logistics ERP for freight forwarders and 3PLs in Saudi Arabia, Bahrain and Dubai.">
    <meta name="keywords" content="contact logistics software Saudi Arabia, request demo freight forwarding ERP, logistics software Bahrain contact, logistics ERP Dubai demo">

    <title>Contact Us - Flikma Logistics ERP for Saudi Arabia, Bahrain & Dubai</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200;300;400;500;600;700;800&display=swap" rel="stylesheet">

    <link href="{{ asset('css/website/style.css') }}" rel="stylesheet">

    <link href="{{ asset('css/website/responsive.css') }}" rel="stylesheet">

    <link href="{{ asset('css/website/contact.css') }}" rel="stylesheet">

</head>

<body>


<!-- ==========================
Navbar
=========================== -->

@include('website.partials.nav')


<!-- =====================
Page Header
====================== -->

<section class="page-header">

    <div class="container text-center">

        <span class="section-tag">Contact Us</span>

        <h1 class="mt-3">

            Let's Talk Logistics

        </h1>

        <p class="page-header-desc mx-auto">

            Book a personalized demo or send us a message — our team typically
            replies within one business day.

        </p>

    </div>

</section>


<!-- =====================
Contact Section
====================== -->

<section class="contact-section">

    <div class="container">

        <div class="row g-5">

            <div class="col-lg-4">

                <div class="contact-info-card mb-4">

                    <div class="contact-info-icon">
                        <i class="bi bi-telephone-fill"></i>
                    </div>

                    <h5>Call Us</h5>

                    <p>+966 595555343</p>
                    <p class="text-muted-sm">Sun&ndash;Thu, 9:00 AM &ndash; 6:00 PM (KSA)</p>

                </div>

                <div class="contact-info-card mb-4">

                    <div class="contact-info-icon">
                        <i class="bi bi-envelope-fill"></i>
                    </div>

                    <h5>Email Us</h5>

                    <p>support@flikma.com</p>
                    <p class="text-muted-sm">We reply within one business day</p>

                </div>

                <div class="contact-info-card">

                    <div class="contact-info-icon">
                        <i class="bi bi-geo-alt-fill"></i>
                    </div>

                    <h5>Visit Us</h5>

                    <p>Riyadh, Saudi Arabia</p>
                    <p class="text-muted-sm">By appointment only</p>

                </div>

            </div>

            <div class="col-lg-8">

                <div class="contact-form-card">

                    @if(session('contact_success'))
                        <div class="alert alert-success d-flex align-items-center gap-2" role="alert">
                            <i class="bi bi-check-circle-fill"></i>
                            <div>{{ session('contact_success') }}</div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('website.contact.submit') }}">
                        @csrf

                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" value="{{ old('name') }}"
                                       class="form-control @error('name') is-invalid @enderror" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Work Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" value="{{ old('email') }}"
                                       class="form-control @error('email') is-invalid @enderror" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Company Name</label>
                                <input type="text" name="company" value="{{ old('company') }}"
                                       class="form-control @error('company') is-invalid @enderror">
                                @error('company')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Phone Number</label>
                                <input type="text" name="phone" value="{{ old('phone') }}"
                                       class="form-control @error('phone') is-invalid @enderror">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">What are you interested in?</label>
                                <select name="interest" class="form-select @error('interest') is-invalid @enderror">
                                    <option value="">Select an option</option>
                                    <option value="Request a Demo" {{ old('interest') == 'Request a Demo' ? 'selected' : '' }}>Request a Demo</option>
                                    <option value="Pricing" {{ old('interest') == 'Pricing' ? 'selected' : '' }}>Pricing</option>
                                    <option value="Implementation & Onboarding" {{ old('interest') == 'Implementation & Onboarding' ? 'selected' : '' }}>Implementation &amp; Onboarding</option>
                                    <option value="Support" {{ old('interest') == 'Support' ? 'selected' : '' }}>Existing Customer Support</option>
                                    <option value="Other" {{ old('interest') == 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('interest')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Message</label>
                                <textarea name="message" rows="4"
                                          class="form-control @error('message') is-invalid @enderror"
                                          placeholder="Tell us a bit about your operation...">{{ old('message') }}</textarea>
                                @error('message')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn-primary rounded-pill px-5 py-3 fw-bold">
                                    Request Demo
                                </button>
                            </div>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</section>


<!-- ==========================================
Footer
========================================== -->

@include('website.partials.footer')



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>

<script src="{{ asset('js/app.js') }}"></script>

</body>

</html>
