<div>
    @if ($students->count())
    <div class="card rounded-4">
        <div class="card-header d-flex justify-content-between align-items-center rounded-top-4">
            <span>
                @lang('translation.groups.scores')
            </span>
            <div
                class="d-flex flex-nowrap align-items-center gap-2 ms-2 flex-grow-1 justify-content-end"
                style="min-width: 0; overflow-x: auto;">
                <div class="d-flex flex-nowrap align-items-center gap-2 flex-shrink-0">
                    <label class="visually-hidden" for="attendance-score-date-from">
                        @lang('translation.from_date')
                    </label>
                    <input id="attendance-score-date-from" type="date" wire:model.live="dateFrom"
                        class="form-control form-control-sm" style="min-width: 8.75rem; max-width: 10.5rem;" />
                    <span class="text-muted fs-12 flex-shrink-0" aria-hidden="true">—</span>
                    <label class="visually-hidden" for="attendance-score-date-to">
                        @lang('translation.to_date')
                    </label>
                    <input id="attendance-score-date-to" type="date" wire:model.live="dateTo"
                        class="form-control form-control-sm" style="min-width: 8.75rem; max-width: 10.5rem;" />
                </div>
                @if($groups)
                    <div class="flex-grow-1 flex-shrink-1" style="min-width: 10rem; max-width: 22rem;">
                        <select name="attendance_score_group" wire:model.live="selectedGroup" class="form-control">
                            @foreach($groups as $group)
                                <option value="{{ $group->id }}">
                                    {{ $group->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                @if(!$students)
                    <x-no-result/>
                @else
                    <table class="table">
                        <tr>
                            <th>#</th>
                            <th>@lang("translation.students.full_name")</th>
                            @foreach ($uniqueDays as $day)
                                <th>{{ dateFormat($day, 'd-F') }}</th>
                            @endforeach
                            <th></th>
                        </tr>
                        @foreach ($students as $student)
                            <tr>
                                <td>{{ $loop->index +1 }}</td>
                                <td class="text-nowrap">
                                    <a href="{{ route('admin.students.show', $student->id) }}" class="text-reset py-3">
                                        @if($student->trashed())
                                            <del data-bs-toggle="tooltip"
                                                 title="{{__('translation.students.deleted')}}">
                                                {{ $student->getFullName() }}
                                            </del>
                                        @else
                                            {{ $student->getFullName() }}
                                        @endif
                                    </a>
                                </td>
                                @foreach ($uniqueDays as $day)
                                    <td>
                                        @php
                                            $attendance = $attendanceData->where('student_id', $student->id)
                                                ->where('date', $day)
                                                ->first();
                                        @endphp
                                        {{ $attendance?->score ?? '-' }}
                                    </td>
                                @endforeach
                                <td></td>
                            </tr>
                        @endforeach
                    </table>
                @endif
            </div>
        </div>
        <div class="card-preloader d-none" wire:loading.class.remove="d-none"
            wire:target="dateFrom,dateTo,selectedGroup">
            <div class="card-status">
                <div class="spinner-border text-success">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
