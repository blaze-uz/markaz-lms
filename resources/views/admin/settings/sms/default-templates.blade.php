@extends('layouts.master')

@section('title') @lang('translation.default_sms_templates') @endsection

@section('content')
{{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0 card-title">@lang('translation.default_sms_templates')</h4>
                    <a href="{{ route('admin.settings.sms') }}" class="btn btn-primary">
                        <i class="fas fa-arrow-left me-2"></i>
                        @lang('translation.back')
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if(count($templates))
                <div class="table-responsive">
                    <table class="table align-middle table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>@lang('translation.sms.template')</th>
                                <th>@lang('translation.sample')</th>
                                <th>@lang('translation.status')</th>
                                <th>@lang('translation.eskiz_status')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($templates as $template)
                            <tr>
                                <td>{{ $template['id'] }}</td>
                                <td>{{ $template['content'] }}</td>
                                <td>{{ $template['sample'] }}</td>
                                <td>{{ $template['status'] }}</td>
                                <td>{{ $template['eskiz_status'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center">
                    <p>@lang('translation.no_data_found')</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
