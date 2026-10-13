@extends('layouts.master')

@section('title')
    @lang('translation.products')
@endsection

@section('content')
{{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
<livewire:product.products-table/>
@endsection