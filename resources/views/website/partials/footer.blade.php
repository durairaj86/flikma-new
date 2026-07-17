<!-- ==========================================
Footer
========================================== -->

<footer class="footer">

    <div class="container">

        <div class="row">

            <div class="col-lg-4">

                <img src="{{ asset('img/logo-white.svg') }}"
                     height="42">

                <p class="mt-4">

                    Flikma is an integrated cloud ERP for Freight,
                    Transportation,
                    Billing and ZATCA e-Invoicing.

                </p>

            </div>

            <div class="col-lg-2">

                <h5>

                    Company

                </h5>

                <ul>

                    <li><a href="#">About</a></li>

                    <li><a href="#">Careers</a></li>

                    <li><a href="{{ route('website.contact') }}">Contact</a></li>

                    <li><a href="#">Blog</a></li>

                </ul>

            </div>

            <div class="col-lg-2">

                <h5>

                    Products

                </h5>

                <ul>

                    <li><a href="#">Freight ERP</a></li>

                    <li><a href="#">Transport</a></li>

                    <li><a href="#">Billing</a></li>

                </ul>

            </div>

            <div class="col-lg-2">

                <h5>

                    Support

                </h5>

                <ul>

                    <li><a href="{{ route('website.documentation') }}">Documentation</a></li>

                    <li><a href="#">Help Center</a></li>

                    <li><a href="#">Privacy</a></li>

                    <li><a href="#">Terms</a></li>

                </ul>

            </div>

            <div class="col-lg-2">

                <h5>

                    Follow

                </h5>

                <div class="social-icons">

                    <a href="#"><i class="bi bi-facebook"></i></a>

                    <a href="#"><i class="bi bi-instagram"></i></a>

                    <a href="#"><i class="bi bi-linkedin"></i></a>

                    <a href="#"><i class="bi bi-twitter-x"></i></a>

                </div>

            </div>

        </div>

        <hr class="my-5">

        <div class="text-center">

            &copy; {{ date('Y') }} Flikma. All Rights Reserved.

        </div>

    </div>

</footer>
