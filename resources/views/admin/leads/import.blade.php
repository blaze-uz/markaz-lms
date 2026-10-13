@extends('layouts.master')
@section('title')
    @lang("translation.leads.create_lead")
@endsection
@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.css"/>
@endsection
@section("content")
    {{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
    <form action="{{ route('admin.leads.import-store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row mt-5">
            <div class="col-md-1 col-sm-12"></div>
            <div class="col-md-10 col-sm-12">
                <div class="row">
                    <div class="col-md-6 col-sm-12">
                        <div class="mb-3">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">
                                        <h6>{{__("translation.leads.template_for_uploading_leads_from_excel_to_the_system")}}</h6>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <a href="{{ asset('assets/images/leads-import-instruction.svg') }}"
                                       id="instruction-image">
                                        <img src="{{ asset('assets/images/leads-import-instruction.svg') }}"
                                             alt="Import Instruction"
                                             style="width: 100%" class="img-fluid img-thumbnail">
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header">
                                <div class="card-title">
                                    <h6>{{__("translation.leads.click_to_download_the_template_file")}}</h6>
                                </div>
                            </div>
                            <div class="card-body">
                                <a href="{{ asset('assets/files/leads-template.xlsx') }}" id="instruction-image"
                                   download>
                                    <div class="border-2 border d-flex justify-content-center align-items-center p-3">
                                        <img src="{{ asset('assets/images/file-image.png') }}"
                                             alt="Import Instruction"
                                             height="93">
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <div class="card card-height-100">
                            <div class="card-header">
                                <div class="card-title">
                                    <h6>
                                        {{__("translation.leads.upload_leads_from_excel_to_the_system")}}
                                    </h6>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="form-group mb-3">
                                    <label for="board">
                                        @lang("translation.leads.select_board") <span class="text-danger">*</span>
                                    </label>
                                    <select name="board" id="board" class="form-control form-select">
                                        <option value="" disabled selected></option>
                                        @foreach($boards as $board)
                                            <option value="{{$board->id}}">{{$board->name}}</option>
                                        @endforeach
                                    </select>
                                    @error('board')
                                    <span class="text-danger">{{$message}}</span>
                                    @enderror
                                </div>
                                <div class="form-group mb-3">
                                    <label for="course">
                                        @lang("translation.leads.select_course")
                                    </label>
                                    <select name="course" id="course" class="form-control form-select">
                                        <option value="" disabled selected></option>
                                        @foreach($courses as $course)
                                            <option value="{{$course->id}}">{{$course->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="file">
                                        @lang("translation.leads.upload_file") <span class="text-danger">*</span>
                                    </label>
                                    <input type="file" name="file" id="file" required
                                           class="form-control @error('file') is-invalid @enderror">
                                    @error('file')
                                    <span class="text-danger">{{$message}}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-end mb-3">
                    <button class="btn btn-primary" type="submit">
                        @lang("translation.btn.save")
                    </button>
                </div>
            </div>
            <div class="col-md-1 col-sm-12"></div>
        </div>
    </form>
@endsection
@section('script-bottom')
    <script src="{{ asset('assets/js/jquery-3.7.1.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="{{asset('assets/js/inputmask/inputmask.js')}}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js"></script>
    <script>
        $(document).ready(function () {
            $('.form-select').select2();
            $("#instruction-image").fancybox({
                'transitionIn': 'elastic',
                'transitionOut': 'elastic',
                'speedIn': 600,
                'speedOut': 200,
                'overlayShow': false
            });
        });
        document.addEventListener('DOMContentLoaded', function () {
            let selector = document.querySelectorAll(".phone");
            let im = new Inputmask("+\\9\\98(99)999-99-99", {'clearIncomplete': true, 'removeMaskOnSubmit': false});
            im.mask(selector);
        });
    </script>
@endsection