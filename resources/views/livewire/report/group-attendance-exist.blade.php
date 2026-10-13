<div class="card card-height-100">
    @push('css')
    <link href="{{ asset('assets/libs/choices.js/choices.js.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/libs/flatpickr/flatpickr.min.css') }}" rel="stylesheet" type="text/css">
    @endpush
    <div class="card-header position-sticky top-0 bg-white" style="z-index: 1000;">
        <div class="d-flex justify-content-between align-items-center">
            <div class="card-title">
                @lang("translation.reports.today_group_attendance_not_exist") ({{ $groups->count() }})
            </div>
            <div class="d-flex gap-2">
                <x-search parent-class="" />
                <input type="date" id="range" wire:model.live="date" class="form-control" data-provider="flatpickr"
                    title="{{__('translation.reports.select_date')}}" data-date-format="Y-m-d" value="{{$date}}" auto
                    wire:ignore>
            </div>
        </div>
    </div>
    <div class="overflow-auto card-body" style="max-height: 400px">
        <div class="table-responsive">
            <table class="table">
                <tr>
                    <th>ID</th>
                    <th>@lang("translation.groups.name")</th>
                    <th>@lang("translation.groups.teacher")</th>
                    <th>@lang("translation.groups.students_count")</th>
                    <th></th>
                </tr>
                @forelse($groups as $group)
                <tr>
                    <td>{{ $group->id }}</td>
                    <td style="width: 200px !important;">
                        <a href="{{ route('admin.groups.show', $group->id) }}" class="fw-semibold">
                            {{ $group->name }}
                        </a>
                    </td>
                    <td class="text-nowrap">{{ $group->teacher?->name }}</td>
                    <td>
                        <span class="badge bg-primary-subtle text-primary">
                            {{ $group->students_count }}
                        </span>
                    </td>
                    <td class="text-nowrap">
                        <form action="{{ route('admin.attendance',['group' => $group->id]) }}">
                            <input type="hidden" id="date" name="date" class="form-control" value="{{$date}}">
                            <button type="submit" class="btn btn-primary btn-sm" data-bs-toggle="tooltip"
                                title="{{__('translation.groups.attendance')}}: {{$date}}">
                                <i class="mdi mdi-clipboard-plus"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="100">
                        <x-no-result />
                    </td>
                </tr>
                @endforelse
            </table>
        </div>
    </div>
    <div class="card-preloader d-none" wire:loading.class.remove="d-none">
        <div class="card-status">
            <div class="spinner-border text-success">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>
    @push('custom-scripts')
    <script src="{{ asset('assets/libs/flatpickr/flatpickr.min.js') }}"></script>
    <script>
        flatpickr("#range", {
                mode: "single",
            });
    </script>
    @endpush
</div>