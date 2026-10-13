<div>
    @push('css')
    @endpush
    <div class="mb-3 row">
        <div class="col-md-5 col-sm-12"></div>
        <div class="col-md-7 col-sm-12">
            <div class="gap-2 d-flex justify-content-end">
                @can('group-attendance-edit')
                @if($attendances->count() > 0 && $attendances->first()->group->state === 'active')
                <form action="{{ route('admin.attendance.edit', ['group' => $groupId]) }}">
                    <input type="hidden" name="date" value="{{ $date }}">
                    <button class="btn btn-success">
                        <i class="ri-calendar-edit-line me-1"></i> @lang("translation.btn.edit_attendance")
                    </button>
                </form>
                @endif
                @endif
                <div style="width: 150px">
                    <input type="date" wire:model="date" wire:change="$refresh" class="form-control" value="{{$date}}">
                </div>
            </div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table mb-0 align-middle table-nowrap">
            <thead>
                <tr>
                    <th class="py-2">ID</th>
                    <th class="py-2">@lang("translation.students.full_name")</th>
                    <th class="py-2">@lang("translation.students.comment")</th>
                    <th class="py-2">@lang("translation.students.score")</th>
                    <th class="py-2">@lang("translation.students.status")</th>
                    <th class="py-2"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($attendances as $attendance)
                <tr>
                    <td class="py-2">{{$attendance->id}}</td>
                    <td class="py-2 text-nowrap text-truncate" style="max-width: 270px">
                        <a href="{{ route('admin.students.show', $attendance->student_id) }}"
                            class="text-reset text-decoration-none">
                            <span class="me-2">
                                {!!
                                Avatar::create($attendance->student?->getFullName())->setTheme('grayscale-dark')->setDimension(32)->setFontSize(12)->toSvg()
                                !!}
                            </span>
                            @if($attendance->student->deleted_at)
                            <del data-bs-toggle="tooltip" title="{{__('translation.students.deleted')}}">
                                {{$attendance->student?->getFullName()}}
                            </del>
                            @else
                            {{$attendance->student?->getFullName()}}
                            @endif
                        </a>
                    </td>
                    <td class="py-2">{{$attendance->comment ?? '-'}}</td>
                    <td class="py-2">{{$attendance->score ?? '-'}}</td>
                    <td class="py-2">
                        <span @class(['badge', $attendance->status->getBgColor(), $attendance->status->getColor()])>
                            {{$attendance->status->getText()}}
                        </span>
                    </td>
                    <td class="py-2"></td>
                </tr>
                @empty
                <tr>
                    <td colspan="100" class="text-center">
                        <x-no-result />
                    </td>
                </tr>
                @endforelse
            </tbody>
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
    @endpush
</div>
