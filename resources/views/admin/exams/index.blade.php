@extends('layouts.master')
@section('title')
    @lang('translation.exams.exams')
@endsection
@section('content')
    {{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
    <livewire:exam.exam-table/>
@endsection