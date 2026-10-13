<div class="card card-height-100">
    @push('css')
        <link href="{{ asset('assets/libs/choices.js/choices.js.min.css') }}" rel="stylesheet" type="text/css">
        <link href="{{ asset('assets/libs/flatpickr/flatpickr.min.css') }}" rel="stylesheet" type="text/css">
    @endpush
    <div class="card-header position-sticky top-0 bg-white" style="z-index: 1000;">
        <div class="d-flex justify-content-between align-items-center">
            <div class="card-title">
                @lang('translation.group_attendances') ({{ $groups->count() }})
            </div>
            <div class="d-flex gap-2">
                <x-search parent-class="" />
                <input type="date" id="range-group" wire:model.live="date" class="form-control"
                    data-provider="flatpickr" title="{{ __('translation.reports.select_date') }}"
                    data-date-format="Y-m-d" value="{{ $date }}" auto wire:ignore>
            </div>
        </div>
    </div>
    <div class="overflow-auto card-body" style="max-height: 400px">
        <div class="table-responsive">
            <table class="table">
                <tr>
                    <th>ID</th>
                    <th>@lang('translation.groups.name')</th>
                    <th>@lang('translation.total_students_count')</th>
                    <th>@lang('translation.comers')</th>
                    <th>@lang('translation.not_comers')</th>
                    <th>@lang('translation.percentage')</th>
                    <th>@lang('translation.groups.lesson_theme')</th>
                    <th></th>
                </tr>
                @forelse($groups as $group)
                    @php
                        $percentage = 0;
                        $total_attendance = $group->comers + $group->absent_count + $group->not_comers;
                        if ($group->students_count > 0 && $total_attendance > 0) {
                            $percentage = round(($group->comers / $total_attendance) * 100, 2);
                        }
                    @endphp
                    <tr>
                        <td>{{ $group->id }}</td>
                        <td style="width: 200px !important;">
                            <a href="{{ route('admin.groups.show', $group->id) }}" class="fw-semibold">
                                {{ $group->name }}
                            </a>
                            <div>
                                <i class="ri-user-line text-primary"></i>
                                {{ $group->teacher?->name }}
                            </div>
                        </td>
                        <td>
                            <span class="fw-semibold badge bg-primary-subtle text-primary">
                                {{ $group->students_count }}
                            </span>
                        </td>
                        <td>
                            <span @class([
                                'fw-semibold',
                                'text-success' => $percentage >= 80,
                                'text-warning' => $percentage >= 50 && $percentage < 80,
                                'text-danger' => $percentage < 50,
                            ])>
                                {{ $group->comers }}
                            </span>
                        </td>
                        <td>
                            <span @class([
                                'fw-semibold',
                                'text-success' => $percentage >= 80,
                                'text-warning' => $percentage >= 50 && $percentage < 80,
                                'text-danger' => $percentage < 50,
                            ])>
                                {{ $group->absent_count + $group->not_comers }}
                            </span>
                        </td>
                        <td>
                            <span @class([
                                'fw-semibold',
                                'text-success' => $percentage >= 80,
                                'text-warning' => $percentage >= 50 && $percentage < 80,
                                'text-danger' => $percentage < 50,
                            ])>
                                {{ $percentage }}%
                            </span>
                        </td>
                        <td>
                            <span class="text-muted">
                                {{ $group->attendances->first()?->theme ?? '-' }}
                            </span>
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
            flatpickr("#range-group", {
                mode: "single",
            });
        </script>
    @endpush
</div>
