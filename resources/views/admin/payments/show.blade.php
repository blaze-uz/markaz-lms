@extends('layouts.master')
@section('title')
    @lang('translation.payments.payment')
@endsection
@section('content')
    {{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th>ID</th>
                            <td>{{ $payment->id }}</td>
                        </tr>
                        <tr>
                            <th>@lang('translation.payments.amount')</th>
                            <td>{{ money($payment->amount) }}</td>
                        </tr>
                        <tr>
                            <th>@lang('translation.payments.date')</th>
                            <td>{{ dateFormat($payment->date) }}</td>
                        </tr>
                        <tr>
                            <th>@lang('translation.payments.payment_type')</th>
                            <td>{{ $payment->payment_type?->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>@lang('translation.students.causer')</th>
                            <td>
                                <a href="{{ route('admin.users.show', $payment->created_by) }}">
                                    {{ $payment->creator?->name ?? '-' }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th>@lang('attr.created_at')</th>
                            <td>{{ dateFormat($payment->created_at, 'd.m.Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection