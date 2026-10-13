@extends('layouts.master')
@section('title')
    @lang('translation.expenses.expenses')
@endsection
@section('content')
    {{ Diglactic\Breadcrumbs\Breadcrumbs::render() }}
    <div class="row">
        <div class="col-md-6 col-sm-12">
            <livewire:expense.expense-item-list/>
        </div>
        <div class="col-md-6 col-sm-12">
            <livewire:expense.expense-place-list/>
        </div>
    </div>
@endsection
