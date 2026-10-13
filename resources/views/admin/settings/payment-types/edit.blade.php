@extends('layouts.master')
@section('title')
    @lang('translation.settings')
@endsection
@section('content')
    {{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
    <div class="row">
        <div class="col-md-3 col-sm-12"></div>
        <div class="col-md-6 col-sm-12">
            <form action="{{ route('admin.settings.payment-types.update', $paymentType->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card">
                    <div class="card-body">
                        <x-input label="{{__('translation.payments.type-name')}}" name="name" :value="$paymentType->name"/>
                    </div>
                </div>
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        @lang("translation.btn.save")
                    </button>
                </div>
            </form>
        </div>
        <div class="col-md-3 col-sm-12"></div>
    </div>
@endsection
