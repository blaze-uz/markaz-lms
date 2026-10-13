<div>
    @push('css')
        <link href="{{ asset('assets/libs/choices.js/choices.js.min.css') }}" rel="stylesheet" type="text/css">
        <link href="{{ asset('assets/libs/flatpickr/flatpickr.min.css') }}" rel="stylesheet" type="text/css">
    @endpush
    <div class="gap-2 row">
        <div class="col-md-5 col-sm-12">
            <select name="group" id="group" class="form-control" wire:model="selectedGroupId">
                <option value="0">
                    @lang('translation.groups.select_group')
                </option>
                @foreach ($groups as $group)
                    <option value="{{ $group->id }}">
                        {{ $group->name }} ({{ $group->teacher?->name }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6 col-sm-12">
            <div class="row">
                <div class="col-7">
                    <div class="input-group">
                        <input id="range" name="from" type="text" wire:model="date" class="form-control"
                            data-provider="flatpickr" data-date-format="Y-m-d" value="{{ $date }}" auto>
                    </div>
                </div>
                <div class="col-5">
                    <button class="btn btn-primary w-100" wire:click="filter">
                        <i class="mdi mdi-calendar-blank"></i> @lang('translation.Filter')
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table">
            <tr>
                <th>ID</th>
                <th>@lang('attr.created_at')</th>
                <th>@lang('translation.groups.group')</th>
                <th>@lang('translation.students.comment')</th>
                <th>@lang('translation.groups.lesson_theme')</th>
                <th>@lang('translation.students.status')</th>
                <th></th>
            </tr>
            @forelse($attendances as $attendance)
                <tr>
                    <td>{{ $attendance->id }}</td>
                    <td class="text-nowrap">
                        {{ dateFormat($attendance->date) }}
                    </td>
                    <td class="text-nowrap">{{ $attendance->group?->name ?? '-' }}</td>
                    <td class="text-nowrap">{{ $attendance->comment ?? '-' }}</td>
                    <td class="text-nowrap">{{ $attendance->theme ?? '-' }}</td>
                    <td>
                        <span @class([
                            'badge',
                            $attendance->status->getBgColor(),
                            $attendance->status->getColor(),
                        ])>
                            {{ $attendance->status->getText() }}
                        </span>
                    </td>
                    <td></td>
                </tr>
            @empty
                <tr>
                    <td colspan="100" class="text-center">
                        <x-no-result />
                    </td>
                </tr>
            @endforelse
        </table>
        <div class="card-preloader d-none" wire:loading.class.remove="d-none">
            <div class="card-status">
                <div class="spinner-border text-success">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>
    </div>
    @push('custom-scripts')
        <script>
            flatpickr("#range", {
                mode: 'range',
            });
        </script>
    @endpush
</div>
