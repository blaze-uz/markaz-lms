<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between">
            <h4 class="card-title">{{ $selectedExam->name }}</h4>
        </div>
    </div>
    <div class="card-body">
        <table class="table">
            <tr>
                <th>@lang("translation.exams.name")</th>
                <td>
                    <a href="{{ route('admin.exams.show', $selectedExam->id) }}">
                        {{ $selectedExam->name }}
                    </a>
                </td>
            </tr>
            <tr>
                <th>@lang("translation.exams.tests_count")</th>
                <td>{{ $selectedExam->tests_count }}</td>
            </tr>
            <tr>
                <th>@lang("translation.exams.description")</th>
                <td>{{ $selectedExam->description }}</td>
            </tr>
            <tr>
                <th>@lang("translation.exams.creator")</th>
                <td>
                    <a href="{{ route('admin.users.show', $selectedExam->creator->id) }}">
                        {{ $selectedExam->creator?->name ?? '-' }}
                    </a>
                </td>
            </tr>
            <tr>
                <th>@lang("translation.exams.created_at")</th>
                <td>{{ $selectedExam->created_at }}</td>
            </tr>
        </table>
    </div>
    <div class="card-preloader d-none" wire:loading.class.remove="d-none">
        <div class="card-status">
            <div class="spinner-border text-success">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>
</div>