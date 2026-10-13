@extends('layouts.master')
@section('title')
    @lang('translation.students.students')
@endsection
@section('content')
    {{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
    {{-- @can("student-card-records")
        <livewire:student.student-index-cards/>
    @endcan --}}
    <livewire:student.students-table/>
@endsection
