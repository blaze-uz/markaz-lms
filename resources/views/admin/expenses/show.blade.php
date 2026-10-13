@extends('layouts.master')
@section('title')
{{ $item->item?->name ?? '-' }}
@endsection
@section('content')
{{ Diglactic\Breadcrumbs\Breadcrumbs::render() }}
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-baseline">
            <h4 class="card-title">
                {{ __('translation.expenses.expense') }}
            </h4>
            @can('edit-expenses')
            <a href="{{ route('admin.expenses.edit', $item?->id ?? 0) }}"
                class="btn bg-success-subtle text-success t-edit-btn">
                <i class="mdi mdi-pencil"></i>
            </a>
            @endcan
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <tr>
                    <th>@lang('translation.expenses.item_name')</th>
                    <td>{{$item->item?->name ?? '-'}}</td>
                </tr>
                <tr>
                    <th>@lang('translation.expenses.quantity')</th>
                    <td>{{$item->quantity ?? '-'}}</td>
                </tr>
                <tr>
                    <th>@lang('translation.expenses.price')</th>
                    <td class="text-danger">
                        <span class="fw-semibold">
                            {{money($item->price)}}
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>@lang('attr.payment_type')</th>
                    <td>
                        <span class="badge bg-success-subtle text-success">
                            {{$item->paymentType->name ?? '-'}}
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>@lang('translation.expenses.place_name')</th>
                    <td>{{$item->place?->name ?? '-'}}</td>
                </tr>
                <tr>
                    <th>@lang('translation.expenses.description')</th>
                    <td>{{$item->description ?? '-'}}</td>
                </tr>
                <tr>
                    <th>@lang('translation.expenses.expense_account')</th>
                    <td>{{$item->account?->name ?? '-'}}</td>
                </tr>
                <tr>
                    <th>@lang('translation.expenses.created_by')</th>
                    <td>{{$item->createdBy?->name ?? '-'}}</td>
                </tr>
                <tr>
                    <th>@lang('attr.created_at')</th>
                    <td>{{dateFormat($item->date)}}</td>
                </tr>
            </table>
        </div>
    </div>
</div>
@if($activities->count() > 0)
<div class="card">
    <div class="card-header">
        <h4 class="card-title">
            @lang('attr.activities_history')
        </h4>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <tr>
                    <th>@lang('translation.students.causer')</th>
                    <th>@lang('translation.old_values')</th>
                    <th>@lang('translation.new_values')</th>
                    <th>@lang('attr.created_at')</th>
                </tr>
                @foreach($activities as $activity)
                <tr>
                    <td>{{$activity->causer?->name ?? '-'}}</td>
                    <td>
                        @if(isset($activity['properties']['old']))
                        @foreach($activity['properties']['old'] as $key => $value)
                        <span>
                            {{ __('translation.expenses.'.$key) }}: {{ $value }}
                        </span>
                        <br>
                        @endforeach
                        @endif
                    </td>
                    <td>
                        @if(isset($activity['properties']['attributes']))
                        @foreach($activity['properties']['attributes'] as $key => $value)
                        <span>
                            {{ __('translation.expenses.'.$key) }}: {{ $value }}
                        </span>
                        <br>
                        @endforeach
                        @endif
                    </td>
                    <td>{{dateFormat($activity->created_at, 'd.m.Y H:i')}}</td>
                </tr>
                @endforeach
            </table>
            {{ $activities->links() }}
        </div>
    </div>
</div>
@endif
@endsection