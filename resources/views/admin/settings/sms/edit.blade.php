@extends('layouts.master')
@section('title')
    @lang('translation.sms.edit')
@endsection
@section('content')
    {{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
    <div class="row">
        <div class="col-md-8 col-sm-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        @lang('translation.sms.edit')
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.sms.update', $template) }}" method="POST">
                        @csrf
                        @method('PUT')
                        @include('admin.settings.sms.partials.form')
                        <div class="d-flex justify-content-end">
                            <button class="btn btn-primary">
                                @lang('attr.update')
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-12">
            @include('admin.settings.sms.partials.hint')
        </div>
    </div>
@endsection
