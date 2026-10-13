@extends('layouts.master')
@section('title')
    @lang('translation.students.import')
@stop
@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.css"
        integrity="sha512-H9jrZiiopUdsLpg94A333EfumgUBpO9MdbxStdeITo+KEIMaNfHNvwyjjDJb+ERPaRS6DpyRlKbvPUasNItRyw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        .download-template-card {
            display: block;
        }

        .download-template-card:hover {
            text-decoration: none;
        }

        .download-template-card>div {
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        }

        .download-template-card:hover>div {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
            border-color: var(--bs-success) !important;
        }

        .download-template-card:hover .download-arrow {
            transform: translateX(5px);
            color: var(--bs-success) !important;
        }

        .download-arrow {
            transition: transform 0.2s ease, color 0.2s ease;
        }
    </style>
@endsection
@section('content')
    {{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}

    <div class="card rounded-4 mb-4">
        <div class="card-header bg-primary-subtle rounded-top-4">
            <h5 class="mb-0 fw-semibold">
                <i class="ri-file-add-line me-2"></i>
                @lang('translation.students.import_instructions')
            </h5>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="d-flex align-items-center mb-3">
                        <i class="ri-image-line text-primary me-2 fs-5"></i>
                        <h6 class="mb-0 fw-semibold">@lang('translation.students.file_image')</h6>
                    </div>
                    <div class="border rounded-3 overflow-hidden bg-light">
                        <a href="{{ asset('assets/images/import-instruction.svg') }}" class="instruction-image d-block"
                            id="instruction-image">
                            <img src="{{ asset('assets/images/import-instruction.svg') }}" alt="Import Instruction"
                                class="w-100 h-auto" style="display: block;">
                        </a>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex align-items-center mb-3">
                        <i class="ri-file-download-line text-success me-2 fs-5"></i>
                        <h6 class="mb-0 fw-semibold">@lang('translation.students.file_for_download')</h6>
                    </div>
                    <a href="{{ asset('assets/files/students-template.xlsx') }}"
                        class="download-template-card text-decoration-none" download>
                        <div class="border border-2 rounded-4 bg-light-subtle d-flex align-items-center p-4 h-100"
                            style="min-height: 200px;">
                            <div class="flex-shrink-0 me-4">
                                <div class="bg-success-subtle rounded-3 p-3 d-inline-flex shadow-sm">
                                    <i class="ri-file-excel-2-line text-success" style="font-size: 3rem;"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-2 fw-semibold text-dark">@lang('translation.students.file_for_download')</h6>
                                <p class="mb-3 text-muted small">students-template.xlsx</p>
                                <span class="badge bg-success-subtle text-success rounded-pill px-3 py-2">
                                    <i class="ri-download-line me-1"></i>
                                    @lang('translation.download')
                                </span>
                            </div>
                            <div class="flex-shrink-0 ms-3">
                                <i class="ri-arrow-right-line text-muted fs-4 download-arrow"></i>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.students.import') }}" method="POST" enctype="multipart/form-data" class="mb-3">
        @csrf
        <div class="card rounded-4 mb-4">
            <div class="card-header bg-primary-subtle rounded-top-4">
                <h5 class="mb-0 fw-semibold">
                    <i class="ri-upload-cloud-2-line me-2"></i>
                    @lang('translation.students.import')
                </h5>
            </div>
            <div class="card-body">
                <x-input name="file" label="{{ __('translation.students.file') }}" type="file" attribute="required" />
            </div>
        </div>
        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('admin.students.index') }}" class="btn btn-light rounded-pill">
                <i class="ri-close-line me-1"></i>
                @lang('translation.btn.cancel')
            </a>
            <button type="submit" class="btn btn-primary rounded-pill">
                <i class="ri-save-line me-1"></i>
                @lang('translation.btn.save')
            </button>
        </div>
    </form>
@endsection
@section('script')
    <script src="{{ asset('assets/js/jquery-3.7.1.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js"
        integrity="sha512-uURl+ZXMBrF4AwGaWmEetzrd+J5/8NRkWAvJx5sbPSSuOb0bZLqf+tOzniObO00BjHa/dD7gub9oCGMLPQHtQA=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
        $(document).ready(function() {
            $(".instruction-image").fancybox({
                'transitionIn': 'elastic',
                'transitionOut': 'elastic',
                'speedIn': 600,
                'speedOut': 200,
                'overlayShow': false
            });
        });
    </script>
@endsection
