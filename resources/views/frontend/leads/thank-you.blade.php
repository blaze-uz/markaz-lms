@extends('layouts.master-without-nav')
@section('title')
    @lang("translation.alert_messages.thank_you")
@endsection
@section('content')

    <!-- auth-page wrapper -->
    <div class="auth-page-wrapper auth-bg-cover py-5 d-flex justify-content-center align-items-center min-vh-100">
        <div class="bg-overlay"></div>
        <!-- auth-page content -->
        <div class="auth-page-content overflow-hidden pt-lg-5">
            <div class="container">
                <div class="row">
                    <div class="col-md-3 col-sm-12"></div>
                    <div class="col-md-6 col-sm-12">
                        <div class="card overflow-hidden">
                            <div class="justify-content-center g-0">
                                <div class="p-lg-5 p-4 text-center">
                                    <div class="avatar-lg mx-auto mt-2">
                                        <div class="avatar-title bg-light text-success display-3 rounded-circle">
                                            <i class="ri-checkbox-circle-fill"></i>
                                        </div>
                                    </div>
                                    <div class="mt-4 pt-2">
                                        <h4>@lang("translation.alert_messages.thank_you")</h4>
                                        <p class="text-muted mx-4">@lang("translation.alert_messages.thank_you_msg")</p>
                                    </div>
                                </div>
                            </div>
                            <!-- end row -->
                        </div>
                        <!-- end card -->
                    </div>
                    <div class="col-md-3 col-sm-12"></div>
                </div>
                <!-- end row -->
            </div>
            <!-- end container -->
        </div>
        <!-- end auth page content -->

        <!-- footer -->
        <footer class="footer">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="text-center">
                            <p class="mb-0">&copy;
                                <script>document.write(new Date().getFullYear())</script>
                                |
                                Markaz LMS
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
        <!-- end Footer -->
    </div>
    <!-- end auth-page-wrapper -->
@endsection
