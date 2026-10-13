@props(['item'])

@can('group-trial-lesson-notice')
    <a class="dropdown-item" href="{{ route('admin.groups.trial-lesson-notice-view', $item->id) }}">
        <span class="mdi mdi-email text-primary"></span>
        @lang("translation.students.trial_lesson_notice")
    </a>
@endcan
