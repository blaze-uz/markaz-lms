@extends("layouts.master")
@section("title")
    @lang("translation.calendar")
@endsection
@section("content")
    {{ Diglactic\Breadcrumbs\Breadcrumbs::render() }}
    {{-- <div class="mb-3 d-flex justify-content-between align-items-center">
        <h3>@lang("translation.calendar")</h3>
    </div> --}}
    <livewire:dashboard.dashboard-calendar />
@endsection
