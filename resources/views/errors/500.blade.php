@extends('layouts.master-without-nav')

@section('title')
@lang('translation.internal_server_error')
@endsection

@section('body')

<body>
    @endsection
    @section('content')

    <!-- auth-page wrapper -->
    <div class="py-5 auth-page-wrapper d-flex justify-content-center align-items-center min-vh-100">

        <!-- auth-page content -->
        <div class="p-0 overflow-hidden auth-page-content">
            <div class="container-fluid">
                <div class="row justify-content-center">
                    <div class="text-center col-xl-4">
                        <div class="error-500 position-relative">

                            <h1 class="title text-muted">500</h1>
                        </div>
                        <div>
                            <h4>{{__('translation.internal_server_error')}}</h4>
                            <p class="mx-auto text-muted w-75">{{__('translation.internal_server_error_description')}}
                            </p>
                            <a href="{{route('admin.dashboard')}}" class="btn btn-success"><i
                                    class="mdi mdi-home me-1"></i>{{__('translation.back_to_dashboard')}}</a>
                        </div>
                    </div><!-- end col-->
                </div>
                <!-- end row -->
            </div>
            <!-- end container -->
        </div>
        <!-- end auth-page content -->
    </div>
    <!-- end auth-page-wrapper -->
    @endsection