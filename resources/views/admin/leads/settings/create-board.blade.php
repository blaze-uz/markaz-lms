@extends('layouts.master')
@section('title')
@lang('translation.leads.add_board')
@endsection
@section('content')
{{ Diglactic\Breadcrumbs\Breadcrumbs::render() }}
<div class="row">
    <div class="col-md-4 col-sm-none d-flex justify-content-center">
        <img src="{{ asset('assets/images/kanban/board.webp') }}" alt="Board image" width="350">
    </div>
    <div class="col-md-8 col-sm-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">@lang('translation.leads.add_board')</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.leads.settings.store-board') }}" method="POST">
                    @csrf
                    @include('admin.leads.settings.partials.board-form')
                    <div>
                        <x-button id="detach-btn" class="btn-primary float-end">
                            @lang("translation.btn.save")
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection