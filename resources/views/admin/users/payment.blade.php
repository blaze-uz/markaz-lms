@extends('layouts.master')
@section('title')
    @lang("translation.users.payment")
@stop
@section('content')
    {{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
    <form action="{{ route('admin.users.payment', $item->id) }}" method="POST">
        @csrf
        <div class="row">
            <div class="col-md-3"></div>
            <div class="col-md-6 col-sm-12">
                <div class="card">
                    @include("admin.users.partials.payment-form")
                    <div class="card-footer d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <span class="mdi mdi-cash-multiple"></span>
                            @lang("translation.users.pay")
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-md-3"></div>
        </div>
    </form>
@endsection
