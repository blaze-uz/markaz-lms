@extends('layouts.master')
@section('title')
    @lang("translation.transfers.add")
@stop
@section('content')
    {{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
    <div class="row">
        <div class="col-md-3 col-sm-12 d-none d-md-block">
            <img src="{{ asset('images/transfer.webp') }}" alt="Credit Card" width="300">
        </div>
        <div class="col-md-9 col-sm-12">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form action="{{ route('admin.accounts.transfer-store') }}" method="POST">
                @csrf
                <div class="card">
                    <div class="card-header">
                        <h5>@lang("translation.transfers.add")</h5>
                    </div>
                    <div class="card-body">
                        <livewire:account.transfer-form/>
                    </div>
                </div>
                <div class="mb-5">
                    <div class="hstack gap-2 d-flex flex-wrap justify-content-end">
                        <x-button id="detach-btn" class="btn-success">
                            @lang("translation.btn.save")
                        </x-button>
                        <a href="{{ route("admin.accounts.index") }}" type="button"
                           class="btn btn-warning">@lang("translation.btn.back")</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection