@extends('layouts.master')
@section('title')
    @lang('translation.sms.create_template')
@endsection
@section('content')
    {{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
    <div class="row">
        <div class="col-md-8 col-sm-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        @lang('translation.sms.create_template')
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{route('admin.settings.sms.store')}}" method="POST">
                        @csrf
                        @include('admin.settings.sms.partials.form')
                        <div class="d-flex justify-content-end">
                            <div class="btn-group">
                                <button type="submit" name="action" value="create" class="btn btn-primary">
                                    @lang('translation.sms.create_action')
                                </button>
                                <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <span class="visually-hidden">Toggle Dropdown</span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <button type="submit" name="action" value="create_and_moderate"
                                            class="dropdown-item">
                                            @lang('translation.sms.create_and_send_to_moderation')
                                        </button>
                                    </li>
                                </ul>
                            </div>
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
