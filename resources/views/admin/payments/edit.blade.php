@extends('layouts.master')
@section('title')
    {{ __('translation.payments.edit') }}
@endsection
@section('content')
    {{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
    <div class="row">
        <div class="col-md-2"></div>
        <div class="col-md-8">
            <form action="{{ route('admin.payments.update', $payment) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('translation.payments.edit') }}</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <th>ID</th>
                                <td>{{ $payment->id }}</td>
                            </tr>
                            <tr>
                                <th>{{__("translation.students.student")}}</th>
                                <td>
                                    @if($payment->student)
                                        <a href="{{ route('admin.students.show', $payment->student->id) }}">
                                            {{ $payment->student->getFullName() }}
                                        </a>
                                    @else
                                        {{ __('translation.groups.students_not_found') }}
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>{{__("translation.payments.created_by")}}</th>
                                <td>
                                    @if($payment->createdBy)
                                        <a href="{{ route('admin.users.show', $payment->createdBy->id) }}">
                                            {{ $payment->createdBy->name }}
                                        </a>
                                    @else
                                        Not found
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>{{ __("translation.students.payment_amount") }}</th>
                                <td>
                                    <span class="fw-semibold text-success">
                                        {{ money($payment->amount) }}
                                    </span>
                                </td>
                            </tr>
                        </table>
                        <hr>
                        <div class="d-flex justify-content-start gap-2 flex-wrap">
                            @foreach($paymentTypes as $key => $paymentType)
                                <div class="mr-3">
                                    <input type="radio" class="btn-check" name="payment_type_id"
                                           id="payment-type-{{$paymentType->id}}" value="{{$paymentType->id}}"
                                           @if($payment->payment_type_id === $paymentType->id) checked @endif>
                                    <label class="btn btn-outline-primary" for="payment-type-{{$paymentType->id}}">
                                        <i class="mdi mdi-cash-multiple"></i>
                                        {{$paymentType->name}}
                                    </label>
                                </div>
                            @endforeach
                            @error('payment_type_id')
                            <div class="text-danger">* {{$message}}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-end">
                    <button class="btn btn-primary" type="submit">{{__("attr.save")}}</button>
                </div>
            </form>
        </div>
        <div class="col-md-2"></div>
    </div>
@endsection