<div>
    <h5 class="mb-3">
        {{ __("translation.groups.today_lesson_times") }}
    </h5>
    <div class="d-flex flex-column gap-1">
        @forelse($todayLessons as $lesson)
            <a href="{{ route('admin.attendance', $lesson->group_id) }}">
                <div class="card rounded-4 mb-3">
                    <div class="row align-items-center p-3">
                        <div class="times d-flex gap-1 fs-5 text-muted col-4">
                            <div class="start">
                                {{ $lesson->start }}
                            </div>
                            <div class="separator">
                                -
                            </div>
                            <div class="end">
                                {{ $lesson->end }}
                            </div>
                        </div>
                        <div class="groups d-flex flex-column gap-1 col-8">
                        <span class="fw-semibold fs-5 text-primary py-2">
                            {{ $lesson->group->name }}
                        </span>
                            <div class="d-flex gap-2">
                            <span class="text-muted">
                                <i class="fas fa-users"></i> {{ $lesson->group->students->count() }}
                            </span>
                                <span class="text-muted">
                                <i class="fas fa-door-open"></i>
                                {{ $lesson->room->name ?? '-' }}
                            </span>

                            </div>
                        </div>
                    </div>
                </div>
            </a>
        @empty
            <x-no-result/>
        @endforelse
    </div>
</div>