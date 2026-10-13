@extends('layouts.master')

@section('title')
    @lang('translation.groups.attendance')
@stop

@section('content')
    {{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}

    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body p-4">
                    <div>
                        <div class="d-flex justify-content-center mx-auto w-100"></div>
                        <div class="mt-4 text-center">
                            <h5 class="mb-1">{{ $item->name }}</h5>
                            <p class="text-muted">@lang('attr.create_date') {{ dateFormat($item->created_at) }}</p>
                        </div>
                        <div class="table-responsive">
                            <table class="table mb-0 table-borderless">
                                <tbody>
                                    <tr>
                                        <th><span class="fw-medium">@lang('translation.groups.teacher')</span></th>
                                        <td>
                                            <a href="{{ route('admin.users.show', $item->teacher->id ?? 0) }}"
                                                class="fw-bold">
                                                {{ $item->teacher?->name }}
                                            </a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><span class="fw-medium">@lang('translation.groups.course')</span></th>
                                        <td>{{ $item->course?->name }}</td>
                                    </tr>
                                    <tr>
                                        <th><span class="fw-medium">@lang('translation.groups.amount')</span></th>
                                        <td>{{ money($item->price) }}</td>
                                    </tr>
                                    <tr>
                                        <th colspan="2">
                                            <div class="row">
                                                <div class="col-6 mb-3">
                                                    <a class="btn btn-warning w-100"
                                                        href="{{ route('admin.groups.show', ['group' => $item->id]) }}">
                                                        <span class="mdi mdi-arrow-left"></span>
                                                        @lang('translation.btn.back')
                                                    </a>
                                                </div>
                                            </div>
                                        </th>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!--end card-->
        </div>
        <!--end col-->
        <div class="col-md-9">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-baseline">
                        <div class="card-title">
                            {{ dateFormat($date) }}
                            - @lang('translation.btn.edit_attendance')
                        </div>
                        <button type="submit" form="attendance-form" onclick="return confirm('@lang('translation.confirm')')"
                            class="btn btn-primary">
                            <i class="mdi mdi-content-save"></i>
                            @lang('translation.btn.save')
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <form action="{{ route('admin.attendance.update', $item->id) }}" id="attendance-form"
                            method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="date" value="{{ $date }}">
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="theme" class="form-label">@lang('translation.groups.lesson_theme')</label>
                                    <input type="text" class="form-control" name="theme" id="theme" value="{{ $attendances->first()->theme ?? '' }}" placeholder="@lang('translation.groups.lesson_theme')">
                                </div>
                            </div>
                            <table class="table">
                                <tr>
                                    <th>ID</th>
                                    <th>@lang('translation.students.full_name')</th>
                                    <th>@lang('translation.students.status')</th>
                                    <th>@lang('translation.students.comment')</th>
                                    <th>@lang('translation.students.score')</th>
                                    @if(isset($validationSettings['homework']) && $validationSettings['homework']->required)
                                        <th>@lang('translation.groups.homework')</th>
                                    @endif
                                </tr>
                                @foreach ($attendances as $attendance)
                                    <tr>
                                        <td>{{ $attendance->student?->id ?? '-' }}</td>
                                        <td>
                                            <a href="{{ route('admin.users.show', $attendance->student?->id ?? 0) }}">
                                                @if ($attendance->student)
                                                    {{ $attendance->student?->getFullName() }}
                                                @else
                                                    <span class="text-danger">
                                                        {{ __('translation.students.deleted') }} <i
                                                            class="ri-error-warning-line"></i>
                                                    </span>
                                                @endif
                                            </a>
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-start gap-2">
                                                <div class="mr-3">
                                                    <input type="radio" class="btn-check"
                                                        name="status[{{ $attendance->id }}]"
                                                        id="{{ $attendance->id }}-student" value="present"
                                                        @if ($attendance->status->value == 'present') checked @endif>
                                                    <label class="btn btn-outline-primary"
                                                        for="{{ $attendance->id }}-student">
                                                        <i class="mdi mdi-account-check"></i> {{ __('attr.present') }}
                                                    </label>
                                                </div>
                                                <div>
                                                    <input type="radio" class="btn-check"
                                                        name="status[{{ $attendance->id }}]"
                                                        id="{{ $attendance->id }}-student2" value="absent"
                                                        @if ($attendance->status->value == 'absent') checked @endif>
                                                    <label class="btn btn-outline-primary"
                                                        for="{{ $attendance->id }}-student2">
                                                        <i class="mdi mdi-account-cancel"></i> {{ __('attr.absent') }}
                                                    </label>
                                                </div>
                                                @error('attendance-' . $attendance->id)
                                                    <div class="text-danger">* {{ $message }}</div>
                                                @enderror
                                            </div>
                                        </td>
                                        <td>
                                            <x-no-label-input name="comment[{{ $attendance->id }}]"
                                                value="{{ $attendance->comment ?? '' }}" :placeholder="__('translation.students.comment')" />
                                        </td>
                                        <td>
                                            <input type="number" class="form-control score-input"
                                                name="score[{{ $attendance->id }}]" value="{{ $attendance->score ?? '' }}"
                                                placeholder='@lang('translation.students.score')'>
                                        </td>
                                        @if(isset($validationSettings['homework']) && $validationSettings['homework']->required)
                                        <td>
                                            <div class="form-check form-switch d-flex justify-content-center">
                                                <input class="form-check-input" type="checkbox" role="switch" 
                                                       name="homework[{{ $attendance->id }}]" 
                                                       value="1" 
                                                       id="homework_{{$attendance->id}}"
                                                       {{ ($attendance->homework ?? false) ? 'checked' : '' }}>
                                            </div>
                                        </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </table>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
