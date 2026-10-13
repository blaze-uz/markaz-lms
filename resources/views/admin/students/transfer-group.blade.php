@extends("layouts.master")
@section("title")
    @lang("translation.students.transfer_group")
@endsection
@section("content")
    {{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
    <livewire:student.transfer-group :student="$item"/>
@endsection