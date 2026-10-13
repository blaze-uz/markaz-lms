@extends('layouts.master')
@section('title')
@lang("translation.leads.create_lead")
@endsection
@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection
@section("content")
{{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
<div class="row">
    <div class="col-md-3"></div>
    <div class="col-md-6">
        <form action="{{ route('admin.leads.store-lead') }}" method="POST">
            @csrf
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        {{ __('translation.leads.create_lead') }}
                    </div>
                </div>
                <div class="card-body">
                    <div class="form-group mb-3">
                        <label for="board-name">@lang("translation.leads.name") <span
                                class="text-danger">*</span></label>
                        <input id="board-name" type="text" name="name" autocomplete="off"
                            class="form-control @error('taskName') is-invalid @enderror"
                            placeholder="{{__('translation.leads.name')}}" wire:model="taskName">
                        @error('name')
                        <div class="invalid-feedback">
                            {{$message}}
                        </div>
                        @enderror
                    </div>
                    <div class="form-group mb-3">
                        <label for="taskPhone">@lang("translation.leads.phone") <span
                                class="text-danger">*</span></label>
                        <input type="tel" id="taskPhone" autocomplete="off" name="phone"
                            class="form-control phone @error('taskPhone') is-invalid @enderror"
                            placeholder="{{__('translation.leads.phone')}}">
                        @error('phone')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label for="taskPhone2">@lang("translation.leads.phone") 2</label>
                            <input type="tel" id="taskPhone2" autocomplete="off" name="phone2"
                                class="form-control phone @error('phone2') is-invalid @enderror"
                                placeholder="{{__('translation.leads.phone')}} 2">
                            @error('phone2')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label for="address">@lang("translation.leads.address")</label>
                            <input type="text" id="address" autocomplete="off" name="address"
                                class="form-control @error('address') is-invalid @enderror"
                                placeholder="{{__('translation.leads.address')}}">
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
                                        <input type="radio" class="btn-check" name="chosen_days" id="primary-outlined"
                                            value="odd">
                                        <label class="btn btn-outline-primary"
                                            for="primary-outlined">{{__("translation.leads.odd")}}</label>
                                    </div>
                                    <div>
                                        <input type="radio" class="btn-check" name="chosen_days" id="danger-outlined"
                                            value="even">
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
                                    <input type="time" class="form-control" id="chosen_time" name="chosen_time">
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
                                    <option value="{{ $teacher->id }}">
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
                                    <option value="" disabled @if(!$selectedBoard) selected @endif>
                                        @lang("translation.leads.select_board")
                                    </option>
                                    @foreach($boards as $board)
                                    <option value="{{ $board->id }}" @if($board->id == $selectedBoard) selected @endif>
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
                            <option value="{{ $course->id }}">
                                {{ $course->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <label for="taskComment">
                        @lang("translation.leads.comment")
                    </label>
                    <textarea class="form-control" name="comment" id="taskComment"></textarea>
                </div>
            </div>
            <div class="card reminder-form d-none">
                <div class="card-header">
                    @lang('translation.leads.add_reminder')
                </div>
                <div class="card-body">
                    <div class="form-group mb-3">
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="date">
                                        {{__("translation.leads.date")}}
                                    </label>
                                    <input type="date" name="reminder_date" id="date" class="form-control">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="time">
                                        {{__("translation.leads.time")}}
                                    </label>
                                    <input type="time" name="reminder_time" id="time" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="comment">
                            {{__("translation.leads.comment")}}
                        </label>
                        <textarea name="reminder_note" id="comment" rows="5" class="form-control"></textarea>
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-end gap-2 mb-3">
                <button type="button" class="btn btn-primary d-flex align-items-center gap-1" id="add-reminder-btn">
                    <i class="ri-notification-line"></i>
                    @lang("translation.leads.add_reminder")
                </button>
                <x-button id="detach-btn" class="btn-primary">
                    @lang("translation.btn.save")
                </x-button>
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

        let reminderForm = document.querySelector('.reminder-form');
        let addReminderBtn = document.querySelector('#add-reminder-btn');
        addReminderBtn.addEventListener('click', function() {
            reminderForm.classList.toggle('d-none');

            // change button text
            if (reminderForm.classList.contains('d-none')) {
                document.querySelector('#date').value = '';
                document.querySelector('#time').value = '';
                addReminderBtn.innerHTML = `<i class="ri-notification-line"></i> @lang("translation.leads.add_reminder")`;
            } else {
                document.querySelector('#date').value = "{{ \Carbon\Carbon::now()->format('Y-m-d') }}";
                document.querySelector('#time').value = "{{ \Carbon\Carbon::now()->format('H:i') }}";
                addReminderBtn.innerHTML = `<i class="ri-notification-off-line"></i> @lang("translation.leads.remove_reminder")`;
            }
        });
    })
</script>
@endsection