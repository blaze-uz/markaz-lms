@php use Diglactic\Breadcrumbs\Breadcrumbs; @endphp
@extends('layouts.master')

@section("title")
    @lang("translation.groups.attendance")
@stop

@section("css")
    <style>
        .comment-input {
            width: auto !important;
        }

        .score-input {
            width: auto !important;
        }

        .homework-input {
            width: auto !important;
        }
    </style>
@endsection

@section('content')
    {{ Breadcrumbs::render() }}
    <div class="row">
        <div class="col-md-3 col-sm-12">
            <div class="card">
                <div class="card-body p-4">
                    <div>
                        <div class="d-flex justify-content-center mx-auto w-100"></div>
                        <div class="mt-4 text-center">
                            <h5 class="mb-1">{{ $item->name }}</h5>
                            <p class="text-muted">@lang("attr.create_date") {{ dateFormat($item->created_at) }}</p>
                        </div>
                        <div>
                            <table class="table mb-0 table-borderless">
                                <tbody>
                                <tr>
                                    <td colspan="2" class="text-center">
                                        @if($item->teacher)
                                            <a @can('user-show')
                                                   href="{{ route('admin.users.show', $item->teacher->id) }}"
                                               @elseif(auth()->user()->isTeacher() && auth()->user()->id == $item->teacher->id)
                                                   href="{{ route('admin.profile.settings') }}"
                                               @endcan
                                               class="text-reset" data-bs-toggle="tooltip"
                                               title="{{__('translation.groups.show_teacher')}}">
                                                @php
                                                    $avatar = $item->teacher->getFirstMediaUrl('avatar');
                                                @endphp
                                                <span class="me-1">
                                                    @if(!$avatar)
                                                        {!! Avatar::create($item->teacher->name)->setTheme('grayscale-light')->setDimension(35)->setFontSize(13)->toSvg() !!}
                                                    @else
                                                        <img class="rounded-circle header-profile-user"
                                                             src="@if($avatar) {{$avatar}} @endif"
                                                             alt="Header Avatar"
                                                             style="object-fit: cover; height: 35px; width: 35px">
                                                    @endif
                                                        </span>
                                                <abbr title="" class="cursor-pointer">
                                                    {{ $item->teacher->name }}
                                                </abbr>
                                            </a>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger">
                                                @lang("translation.users.teacher_not_found")!
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th><span class="fw-medium">@lang("translation.groups.course")</span></th>
                                    <td>{{ $item->course?->name }}</td>
                                </tr>
                                <tr>
                                    <th><span class="fw-medium">@lang("translation.groups.amount")</span></th>
                                    <td>{{ money($item->price) }}</td>
                                </tr>
                                @if($item->attendanceSettings)
                                    <tr>
                                        <th>
                                        <span class="fw-medium">
                                            @lang("translation.groups.attendance_notice")
                                            <span class="text-primary" data-bs-toggle="tooltip"
                                                  data-bs-placement="top"
                                                  title="@lang("translation.groups.automatically_send_attendance_notice_to_students")">
                                                <i class="mdi mdi-information-outline"></i>
                                            </span>
                                        </span>
                                        </th>
                                        <td>
                                            @if($item->attendanceSettings->auto_send == 'enable')
                                                <span class="badge bg-success-subtle text-success">
                                                @lang("translation.on")
                                            </span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger">
                                                @lang("translation.off")
                                            </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                                </tbody>
                            </table>
                            <div class="row mt-3">
                                <div class="col-6 mb-3">
                                    <a class="btn btn-warning w-100"
                                       href="{{ route('admin.groups.show', ['group' => $item->id]) }}">
                                        <span class="mdi mdi-arrow-left"></span>
                                        @lang("translation.btn.back")
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--end card-->
            @if(dateFormat($date) != dateFormat(today()))
                <div class="alert alert-warning">
                    <h4 class="alert-heading">
                        <i class="mdi mdi-information-outline"></i>
                        @lang("translation.groups.attendance_notice")
                    </h4>
                    <p>
                        @lang("translation.groups.attendance_notice_for_not_today")
                    </p>
                </div>
            @endif
        </div>
        <!--end col-->
        <div class="col-md-9 col-sm-12">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        <li>
                        @lang("translation.attendance_error")
                        </li>
                    </ul>
                </div>
            @endif
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-baseline">
                        <div class="card-title">
                            @lang("translation.groups.attendance_date"): {{ dateFormat($date) }}
                        </div>
                        @if($students->count() > 0)
                            <button type="submit"
                                    form="attendance-form"
                                    onclick="return confirm('@lang("translation.confirm")')"
                                    class="btn btn-primary">
                                <i class="mdi mdi-content-save"></i>
                                @lang("translation.btn.save")
                            </button>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <form action="{{route('admin.attendance.store', $item->id)}}" id="attendance-form"
                              method="POST">
                            @csrf
                            <input type="hidden" name="date" value="{{ $date }}">
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="theme" class="form-label">@lang("translation.groups.lesson_theme")</label>
                                    <input type="text" class="form-control" name="theme" id="theme" placeholder="@lang("translation.groups.lesson_theme")">
                                </div>
                            </div>
                            <table class="table">
                                <tr>
                                    <th>ID</th>
                                    <th>@lang("translation.students.full_name")</th>
                                    <th>@lang("translation.students.status")</th>
                                    <th>@lang("translation.students.comment")</th>
                                    <th>@lang("translation.students.score")</th>
                                    @if(isset($validationSettings['homework']) && $validationSettings['homework']->required)
                                        <th>@lang("translation.groups.homework")</th>
                                    @endif
                                </tr>
                                @foreach($students as $student)
                                    <tr>
                                        <td>{{ $student->id }}</td>
                                        <td>
                                            <a href="{{ route('admin.users.show', $student->id) }}">
                                                {{ $student->getFullName() }}
                                            </a>
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-start gap-2">
                                                <div class="mr-3">
                                                    <input type="radio" class="btn-check"
                                                           name="status[{{$student->id}}]"
                                                           id="{{$student->id}}-student" value="present"
                                                           checked>
                                                    <label class="btn btn-outline-primary text-nowrap"
                                                           for="{{$student->id}}-student">
                                                        <i class="mdi mdi-account-check"></i> {{__("attr.present")}}
                                                    </label>
                                                </div>
                                                <div>
                                                    <input type="radio" class="btn-check"
                                                           name="status[{{$student->id}}]"
                                                           id="{{$student->id}}-student2" value="absent">
                                                    <label class="btn btn-outline-primary text-nowrap"
                                                           for="{{$student->id}}-student2">
                                                        <i class="mdi mdi-account-cancel"></i> {{__("attr.absent")}}
                                                    </label>
                                                </div>
                                                @error('attendance-'.$student->id)
                                                <div class="text-danger">* {{$message}}</div>
                                                @enderror
                                            </div>
                                        </td>
                                        <td>
                                            <x-no-label-input class="comment-input" name="comment[{{$student->id}}]"
                                                              :placeholder="__('translation.students.comment')"/>
                                        </td>
                                        <td>
                                            <input type="number" class="form-control score-input"
                                                   name="score[{{$student->id}}]"
                                                   placeholder="@lang("translation.students.score")">
                                        </td>
                                        @if(isset($validationSettings['homework']) && $validationSettings['homework']->required)
                                        <td>
                                            <div class="form-check form-switch d-flex justify-content-center">
                                                <input class="form-check-input" type="checkbox" role="switch" 
                                                       name="homework[{{$student->id}}]" 
                                                       value="1" 
                                                       id="homework_{{$student->id}}">
                                            </div>
                                        </td>
                                        @endif
                                    </tr>
                                    @error('comment.'.$student->id)
                                    <tr>
                                        <td colspan="{{ (isset($validationSettings['homework']) && $validationSettings['homework']->required) ? '6' : '5' }}">
                                            <div class="text-danger">* {{$message}}</div>
                                        </td>
                                    </tr>
                                    @enderror
                                    @error('score.'.$student->id)
                                    <tr>
                                        <td colspan="{{ (isset($validationSettings['homework']) && $validationSettings['homework']->required) ? '6' : '5' }}">
                                            <div class="text-danger">* {{$message}}</div>
                                        </td>
                                    </tr>
                                    @enderror
                                    @if(isset($validationSettings['homework']) && $validationSettings['homework']->required)
                                        @error('homework.'.$student->id)
                                        <tr>
                                            <td colspan="6">
                                                <div class="text-danger">* {{$message}}</div>
                                            </td>
                                        </tr>
                                        @enderror
                                    @endif
                                @endforeach
                            </table>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
