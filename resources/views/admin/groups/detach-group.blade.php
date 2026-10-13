@extends('layouts.master')
@section('title')
{{ __('Detach Group') }}
@endsection
@section('content')
<div class="alert bg-danger-subtle text-danger">
    @lang("translation.groups.detach_has_debt_warning")
</div>
<div class="row">
    <div class="col-md-4 col-sm-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">@lang("translation.groups.group")</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <tr>
                            <th>ID</th>
                            <td>{{ $group->id }}</td>
                        </tr>
                        <tr>
                            <th>@lang("translation.groups.name")</th>
                            <td>{{ $group->name }}</td>
                        </tr>
                        <tr>
                            <th>@lang("translation.groups.teacher")</th>
                            <td>
                                <a href="{{ route('admin.users.show', $group->teacher_id) }}" class="fw-medium">
                                    {{ $group->teacher?->name ?? '-' }}
                                </a>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    @lang("translation.students.student")
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <tr>
                            <th>ID</th>
                            <td>{{ $student->id }}</td>
                        </tr>
                        <tr>
                            <th>@lang("translation.students.full_name")</th>
                            <td>
                                <a href="{{ route('admin.students.show', $student->id) }}" class="fw-medium">
                                    {{ $student->getFullName() }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th>@lang("translation.students.phone")</th>
                            <td>
                                <a href="tel:{{ $student->getFormattedPhone() }}" class="fw-medium">
                                    {{ $student->getFormattedPhone() }}
                                </a>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-8 col-sm-12">
        <form action="{{ route('admin.groups.detach-with-debt') }}" method="post">
            @csrf
            <input type="hidden" name="student_id" value="{{ $student->id }}">
            <input type="hidden" name="group_id" value="{{ $group->id }}">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">@lang("translation.students.debts")</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <tr>
                                <th>ID</th>
                                <th>@lang("translation.groups.debt_range")</th>
                                <th>@lang("attr.left_amount")</th>
                                <th>@lang("translation.groups.will_deleted")</th>
                            </tr>
                            @foreach($debts as $debt)
                            <tr>
                                <td>{{ $debt->id }}</td>
                                <td>{{ $debt->range }}</td>
                                <td>{{ money($debt->left_amount) }}</td>
                                <td>
                                    <div class="form-check form-switch mb-3 d-flex gap-1 align-items-center">
                                        <div>@lang("Yo'q")</div>
                                        <input type="checkbox" class="form-check-input" name="debts[]" checked value="{{ $debt->id }}">
                                        <div>@lang("Ha")</div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </table>
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-end">
                <x-button id="detach-btn" class="btn-danger">
                    @lang("translation.groups.detach")
                </x-button>
            </div>
        </form>
    </div>
</div>
@endsection