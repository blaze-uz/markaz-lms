<div class="modal fade" id="groupLessonTimesInfoModal" tabindex="-1"
     aria-labelledby="groupLessonTimesInfoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="groupLessonTimesInfoModalLabel">
                    @lang("translation.groups.group_times_info")
                </h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">
                    @lang("translation.groups.amount"):
                    <span class="fw-medium text-success">{{ money($selectedGroup?->price) }}</span>
                </p>
                <p class="text-muted">
                    @lang("translation.groups.lessons_count"):
                    <span class="fw-medium text-success">{{ $selectedGroupLessonsCount }}</span>
                </p>
                <p class="text-muted">
                    @lang("translation.groups.left_lessons_count"):
                    <span class="fw-medium text-success">{{ $selectedGroupLeftLessonsCount }}</span>
                </p>
                <p class="text-muted">
                    @lang("translation.groups.lesson days"):
                </p>
                <table class="table table-sm">
                    <tr>
                        <th>#</th>
                        <th>@lang("attr.day")</th>
                        <th>@lang("attr.price")</th>
                    </tr>
                    @forelse($selectedGroupLessonDays as $key => $amount)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ dateFormat($key) }}</td>
                            <td>{{ money($amount) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center">
                                <x-no-result/>
                            </td>
                        </tr>
                    @endforelse
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-primary-subtle text-primary" data-bs-dismiss="modal">
                    @lang("translation.Close")
                </button>
            </div>
        </div>
    </div>
</div>