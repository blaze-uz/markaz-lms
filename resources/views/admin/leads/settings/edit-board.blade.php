@extends('layouts.master')
@section('title')
    @lang('translation.leads.edit_board')
@endsection
@section('content')
    {{ Diglactic\Breadcrumbs\Breadcrumbs::render() }}
    <div class="row">
        <div class="col-md-4 col-sm-none d-flex justify-content-center">
            <img src="{{ asset('assets/images/kanban/board.webp') }}" alt="Board image" width="350" height="400">
        </div>
        <div class="col-md-8 col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">@lang('translation.leads.edit_board')</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.leads.settings.update-board', $item->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        @include('admin.leads.settings.partials.board-form')
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary float-right">
                                @lang('translation.btn.save')
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
