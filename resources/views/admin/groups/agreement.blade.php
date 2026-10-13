@extends('layouts.master')
@section('title')
@lang('translation.groups.agreement')
@endsection
@section('content')
{{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
<div class="row">
    <div class="col-lg-3 col-sm-12"></div>
    <div class="col-lg-6 col-sm-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">@lang('translation.groups.agreement')</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-6">
                        <h5 class="card-title">@lang('translation.students.student')</h5>
                        <a href="{{route('admin.students.show', $student->id)}}">{{$student->getFullName()}}</a>
                    </div>
                    <div class="col-lg-6">
                        <h5 class="card-title">@lang('translation.groups.group')</h5>
                        <a href="{{route('admin.groups.show', $group->id)}}">{{$group->name}}</a>
                        <p>{{money($group->price)}}</p>
                    </div>
                </div>
                <hr>
                <form action="{{route('admin.groups.agreement', [$group->id, $student->id])}}" method="POST">
                    @csrf
                    <div class="row mb-1">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <x-money-input :label="__('translation.students.edit_group_agreed_amount')" name="amount" :placeholder="__('translation.payments.amount')" value="{{ old('amount') }}" />
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="form-group">
                                <x-text-area :label="__('translation.groups.comment')" name="comment" :placeholder="__('translation.groups.comment')" value="{{ old('comment') }}" />
                            </div>
                        </div>
                        <div class="col-lg-12 mb-3">
                            <div class="form-group w-100">
                                <button type="submit" class="btn btn-primary mt-4 w-100">@lang('attr.save')</button>
                            </div>
                        </div>
                    </div>
                </form>
                <span class="text-muted">
                    * @lang('translation.groups.agreement_description')
                </span>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-sm-12"></div>
</div>
@endsection
