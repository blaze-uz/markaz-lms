@extends('layouts.master-without-nav')
@section('title')
@lang("translation.leads.form")
@endsection
@section('content')

<!-- auth-page wrapper -->
<div class="py-5 auth-page-wrapper auth-bg-cover d-flex justify-content-center align-items-center min-vh-100">
    <div class="bg-overlay"></div>
    <!-- auth-page content -->
    <div class="overflow-hidden auth-page-content pt-lg-5">
        <div class="container">
            <div class="row">
                <div class="col-md-3"></div>

                <div class="col-md-6">
                    <div class="overflow-hidden card">
                        <div class="p-4 p-lg-5">
                            <div class="d-flex justify-content-center align-items-center">
                                <img src="{{ (!empty($logo)) ? asset($logo) : asset('images/markaz-lms.svg')  }}"
                                    alt="logo" width="170">
                            </div>
                            <div class="mt-4">
                                <form action="{{ route('lead.store', $board->id) }}" method="POST">
                                    @csrf
                                    <x-input name="name" label="{{__('translation.students.full_name')}}"
                                        placeholder="{!!__('translation.students.full_name')!!}" attribute="required" />

                                    <div class="mb-3 form-group">
                                        <label for="phone">
                                            @lang("translation.leads.phone")
                                        </label>
                                        <input type="tel" id="phone" name="phone" autocomplete="off"
                                            class="form-control phone @error('phone') is-invalid @enderror"
                                            placeholder="{{__('translation.leads.phone')}}" required>
                                        @error('phone')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                        @enderror
                                    </div>

                                    @if(count($courses))
                                    <div class="mb-3 form-group">
                                        <label for="course_id">
                                            @lang("translation.courses.courses")
                                        </label>
                                        <select name="course_id" id="course_id" class="form-control">
                                            <option value="">
                                                @lang("translation.courses.course")
                                            </option>
                                            @foreach($courses as $course)
                                            <option value="{{ $course->id }}">
                                                {{ $course->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('course_id')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                        @enderror
                                    </div>
                                    @endif

                                    @if($board->form_comment_state)
                                    <div class="mb-3 form-group">
                                        <label for="comment">
                                            @lang("translation.leads.comment")
                                        </label>
                                        <textarea name="comment" id="comment" class="form-control"></textarea>
                                        @error('comment')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                        @enderror
                                    </div>
                                    @endif

                                    @foreach($leadDynamicFields ?? [] as $field)
                                    <div class="mb-3 form-group">
                                        <label for="custom_attr_{{ $field['key'] }}">{{ $field['label'] }}@if(!empty($field['required'])) <span class="text-danger">*</span>@endif</label>
                                        @if($field['type'] === 'textarea')
                                        <textarea name="custom_attributes[{{ $field['key'] }}]" id="custom_attr_{{ $field['key'] }}" class="form-control @error('custom_attributes.'.$field['key']) is-invalid @enderror"@if(!empty($field['required'])) required @endif></textarea>
                                        @elseif($field['type'] === 'number')
                                        <input type="number" step="any" name="custom_attributes[{{ $field['key'] }}]" id="custom_attr_{{ $field['key'] }}" class="form-control @error('custom_attributes.'.$field['key']) is-invalid @enderror"@if(!empty($field['required'])) required @endif>
                                        @elseif($field['type'] === 'select')
                                        <select name="custom_attributes[{{ $field['key'] }}]" id="custom_attr_{{ $field['key'] }}" class="form-control @error('custom_attributes.'.$field['key']) is-invalid @enderror"@if(!empty($field['required'])) required @endif>
                                            <option value="">@lang('translation.leads.select_dynamic_value')</option>
                                            @foreach($field['options'] ?? [] as $opt)
                                            <option value="{{ $opt }}">{{ $opt }}</option>
                                            @endforeach
                                        </select>
                                        @else
                                        <input type="text" name="custom_attributes[{{ $field['key'] }}]" id="custom_attr_{{ $field['key'] }}" class="form-control @error('custom_attributes.'.$field['key']) is-invalid @enderror"@if(!empty($field['required'])) required @endif>
                                        @endif
                                        @error('custom_attributes.'.$field['key'])
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    @endforeach

                                    <div class="mt-4">
                                        <button class="btn btn-success w-100" type="submit">
                                            @lang('translation.btn.register')
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <!-- end card -->
                </div>

                <div class="col-md-3"></div>
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
                        <script>
                            document.write(new Date().getFullYear())
                        </script>
                        | {{ $board->name }} -
                        Markaz LMS
                    </div>
                </div>
            </div>
        </div>
    </footer>
    <!-- end Footer -->
</div>
<!-- end auth-page-wrapper -->

@endsection
@section('script')
<script src="{{asset('assets/js/inputmask/inputmask.js')}}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
            let selector = document.querySelectorAll(".phone");
            let im = new Inputmask("+\\9\\98(99)999-99-99", {'clearIncomplete': true});
            im.mask(selector);
        })
</script>
@endsection