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
        <form action="{{ route('admin.accounts.transaction', $item) }}" method="POST">
            @csrf
            <div class="card">
                <div class="card-header">
                    <h5>@lang("translation.transfers.add")</h5>
                </div>
                <div class="card-body">
                    <div class="form-group mb-3">
                        <label for="account">
                            {{__("translation.accounts.account")}}
                        </label>
                        <input type="text" id="account" class="form-control" disabled value="{{$item->name}}">
                    </div>
                    <div class="form-group mb-3">
                        <label for="">{{__("translation.accounts.transaction_type")}}</label>
                        <div class="d-flex justify-content-start gap-2">
                            <div class="mr-3">
                                <input type="radio" class="btn-check" name="type" id="danger-outlined" value="expense">
                                <label class="btn btn-outline-primary" for="danger-outlined">
                                    <i class="mdi mdi-arrow-up-bold"></i> {{__("translation.accounts.expense")}}
                                </label>
                            </div>
                            <div>
                                <input type="radio" class="btn-check" name="type" id="primary-outlined" value="income">
                                <label class="btn btn-outline-primary" for="primary-outlined">
                                    <i class="mdi mdi-arrow-down-bold"></i> {{__("translation.accounts.income")}}
                                </label>
                            </div>
                            @error('type')
                            <div class="text-danger">* {{$message}}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="form-group">
                        <x-money-input :label="__('translation.students.payment_amount')" name="amount" :placeholder="__('translation.students.payment_amount')" value="{{ old('amount') }}" />
                    </div>
                    <div class="form-group">
                        <label for="description">
                            {{__("translation.accounts.description")}}
                        </label>
                        <textarea name="description" id="description" cols="30" rows="5" class="form-control">{{old('description')}}</textarea>
                    </div>
                </div>
            </div>
            <div class="mb-5">
                <div class="hstack gap-2 d-flex flex-wrap justify-content-end">
                    <x-button id="detach-btn" class="btn-success">
                        @lang("translation.btn.save")
                    </x-button>
                    <a href="{{ route("admin.accounts.index") }}" type="button" class="btn btn-warning">@lang("translation.btn.back")</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection