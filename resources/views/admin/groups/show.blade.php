@extends('layouts.master')
@section('title')
    {{ $item->name }}
@stop
@section('css')
    <link href="{{ asset('assets/libs/choices.js/choices.js.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/libs/flatpickr/flatpickr.min.css') }}" rel="stylesheet" type="text/css">
@endsection
@section('content')
    {{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
    <div class="row">
        <!-- Group Info Card -->
        @include('admin.groups.show.info')

        <!-- Right info card -->
        @include('admin.groups.show.right-info')
    </div>

    @if ($item->state == 'active')
        <!-- Attendance Modal -->
        <div class="modal fade" id="attendanceModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="exampleModalLabel">@lang('translation.groups.attendance')</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('admin.attendance', ['group' => $item->id]) }}">
                        <div class="modal-body">
                            <label for="date">
                                @lang('translation.groups.attendance_date')
                            </label>
                            <input type="date" id="date" name="date" class="form-control"
                                value="{{ now() }}">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                @lang('translation.Close')
                            </button>
                            <button type="submit" class="btn btn-primary">
                                @lang('translation.groups.attendance')
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif


    <x-groups.more-info-modal :item="$item" />
    @if ($item->state == 'active' || $item->state == 'completed')
        <!-- Group attendance table -->
        {{-- <livewire:group.attendance-table :group="$item" lazy /> --}}

        <!-- Group attendance table -->
        {{-- <livewire:group.attendance-score-table :group="$item" lazy /> --}}
        <livewire:group.attendance-calendar :group="$item" />
    @endif
    {{--
<!-- Group calendar table --> --}}
    {{--
<livewire:group.group-calendar :group="$item" /> --}}
    {{-- @endif --}}
@stop
@section('script')
    <script src="{{ asset('assets/libs/choices.js/choices.js.min.js') }}"></script>
    <script>
        // Get today's date as a string in the format "YYYY-MM-DD"
        let today = new Date().toISOString().slice(0, 10);

        // Set the value of the date input field to today's date
        document.getElementById("date").value = today;
    </script>
@endsection
