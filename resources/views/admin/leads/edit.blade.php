@extends('layouts.master')
@section('title')
@lang("translation.leads.edit_lead")
@endsection
@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection
@section("content")
{{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
<div class="row">
    <div class="col-md-3"></div>
    <div class="col-md-6">
        <form action="{{ route("admin.leads.update-lead", $item->id) }}" method="POST">
            @csrf
            @method("PUT")
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        {{ __('translation.leads.edit_lead') }}
                    </div>
                </div>
                <div class="card-body">
                    <div class="form-group mb-3">
                        <label for="board-name">@lang("translation.leads.name") <span
                                class="text-danger">*</span></label>
                        <input id="board-name" type="text"
                            name="name"
                            autocomplete="off"
                            class="form-control @error('taskName') is-invalid @enderror"
                            placeholder="{{__('translation.leads.name')}}"
                            value="{{ $item->name }}">
                        @error('name')
                        <div class="invalid-feedback">
                            {{$message}}
                        </div>
                        @enderror
                    </div>
                    <div class="form-group mb-3">
                        <label for="taskPhone">@lang("translation.leads.phone") <span
                                class="text-danger">*</span></label>
                        <input type="tel" id="taskPhone" autocomplete="off"
                            name="phone"
                            class="form-control phone @error('taskPhone') is-invalid @enderror"
                            placeholder="{{__('translation.leads.phone')}}"
                            value="{{ $item->phone }}">
                        @error('phone')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label for="taskPhone2">@lang("translation.leads.phone") 2</label>
                            <input type="tel" id="taskPhone2" autocomplete="off"
                                name="phone2"
                                class="form-control phone @error('phone2') is-invalid @enderror"
                                placeholder="{{__('translation.leads.phone')}} 2"
                                value="{{ $item->phone2 }}">
                            @error('phone2')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label for="address">@lang("translation.leads.address")</label>
                            <input type="text" id="address" autocomplete="off"
                                name="address"
                                class="form-control @error('address') is-invalid @enderror"
                                placeholder="{{__('translation.leads.address')}}"
                                value="{{ $item->address }}">
                            @error('address')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <div class="row">
                            <div class="col-md-6 col-sm-12">
                                <label for="chosen_days">
                                    @lang("translation.leads.chosen_days")
                                </label>
                                <div class="d-flex justify-content-start gap-2">
                                    <div class="mr-3">
                                        <input type="radio" class="btn-check" name="chosen_days"
                                            id="primary-outlined" value="odd"
                                            @if($item->chosen_days == "odd") checked @endif>
                                        <label class="btn btn-outline-primary"
                                            for="primary-outlined">{{__("translation.leads.odd")}}</label>
                                    </div>
                                    <div>
                                        <input type="radio" class="btn-check"
                                            name="chosen_days"
                                            id="danger-outlined" value="even"
                                            @if($item->chosen_days == "even") checked @endif>
                                        <label class="btn btn-outline-primary"
                                            for="danger-outlined">{{__("translation.leads.even")}}
                                        </label>
                                    </div>
                                    @error('chosen_days')
                                    <div class="text-danger">* {{$message}}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12">
                                <label for="chosen_time">
                                    @lang("translation.leads.chosen_time")
                                </label>
                                <div class="input-group">
                                    <input type="time" class="form-control" id="chosen_time"
                                        name="chosen_time" value="{{ $item->chosen_time }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 col-sm-12">
                            <div class="form-group mb-3">
                                <label for="teachers">
                                    @lang("translation.users.select_teacher")
                                </label>
                                <select name="teachers[]" id="teachers" class="form-control form-select">
                                    <option value="">@lang("translation.leads.select_teacher")</option>
                                    @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->id }}"
                                        @if(in_array($teacher->id, $item->teachers->pluck('id')->toArray())) selected @endif>
                                        {{ $teacher->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <div class="form-group mb-3">
                                <label for="board">
                                    @lang("translation.leads.select_board")
                                </label>
                                <select name="board" id="board" class="form-control form-select">
                                    <option value="" disabled
                                        selected>@lang("translation.leads.select_board")</option>
                                    @foreach($boards as $board)
                                    <option value="{{ $board->id }}"
                                        @if($item->kanban_board_id == $board->id) selected @endif>
                                        {{ $board->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label for="course">
                            @lang("translation.leads.select_course")
                        </label>
                        <select name="course" id="course" class="form-control form-select">
                            <option value="" disabled selected>
                                @lang("translation.leads.select_course")
                            </option>
                            @foreach($courses as $course)
                            <option value="{{ $course->id }}"
                                @if($item->course_id == $course->id) selected @endif>
                                {{ $course->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <label for="taskComment">
                        @lang("translation.leads.comment")
                    </label>
                    <textarea class="form-control" name="comment" id="taskComment">{{ $item->comment }}</textarea>
                </div>
            </div>
            <div class="d-flex justify-content-end mb-3">
                <button class="btn btn-primary" type="submit">
                    {{__("attr.edit")}}
                </button>
            </div>
        </form>
    </div>
    <div class="col-md-3"></div>
</div>
@endsection
@section('script-bottom')
<script src="{{ asset('assets/js/jquery-3.7.1.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="{{asset('assets/js/inputmask/inputmask.js')}}"></script>
<script>
    $(document).ready(function() {
        $('.form-select').select2();
    });
    document.addEventListener('DOMContentLoaded', function() {
        let selector = document.querySelectorAll(".phone");
        let im = new Inputmask("+\\9\\98(99)999-99-99", {
            'clearIncomplete': true,
            'removeMaskOnSubmit': false
        });
        im.mask(selector);
    })
</script>
@endsection