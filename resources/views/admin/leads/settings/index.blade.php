@extends('layouts.master')
@section("title")
    @lang("translation.leads.settings")
@endsection
@section("content")
    {{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
    <div class="row">
        <div class="col-md-6 col-sm-12">
            <livewire:leads.collection-table/>
        </div>
        <div class="col-md-6 col-sm-12">
            <livewire:leads.board-table/>
        </div>
    </div>
@endsection
