@extends('layouts.master')
@section('title')
    @lang("translation.telegram.add")
@stop
@section('content')
    {{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
    <div class="row">
        <div class="col-md-4"></div>
        <div class="col-md-4">
            <form action="{{ route('admin.telegrams.store') }}" method="POST">
                @csrf
                <div class="card">
                    @include("admin.telegrams.partials.form")
                </div>
                <div class="mb-5">
                    <div class="d-flex flex-wrap gap-2 justify-content-end">
                        <x-button id="detach-btn" class="btn-success">
                            @lang("translation.btn.save")
                        </x-button>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-md-4"></div>
    </div>
@endsection