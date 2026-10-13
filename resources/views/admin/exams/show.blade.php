@extends('layouts.master')

@section("title")
    {{ $item->name }}
@stop

@section('content')
    {{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between">
                        <h4 class="card-title">{{ $item->name }}</h4>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th>@lang("translation.exams.name")</th>
                            <td>{{ $item->name }}</td>
                        </tr>
                        <tr>
                            <th>@lang("translation.exams.tests_count")</th>
                            <td>{{ $item->tests_count }}</td>
                        </tr>
                        <tr>
                            <th>@lang("translation.exams.description")</th>
                            <td>{{ $item->description }}</td>
                        </tr>
                        <tr>
                            <th>@lang("translation.exams.creator")</th>
                            <td>
                                <a href="{{ route('admin.users.show', $item->creator->id) }}"
                                   class="text-reset">
                                    {{ $item->creator?->name ?? '-' }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th>@lang("translation.exams.created_at")</th>
                            <td>{{ $item->created_at }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <livewire:exam.exam-detail-table :exam="$item"/>
        </div>
    </div>
@stop