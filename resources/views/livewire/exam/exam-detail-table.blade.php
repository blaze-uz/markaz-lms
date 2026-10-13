<div>
    @push('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    @endpush
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between">
                <h4 class="card-title">@lang("translation.exams.details")</h4>
                <button class="btn btn-success btn-sm t-create-btn" data-bs-toggle="modal"
                    data-bs-target="#createStudentExam">
                    <i class="mdi mdi-plus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            @if($student_exams->count() > 0)
            <div class="table-responsive">
                <table class="table">
                    <tr>
                        <th>ID</th>
                        <th>@lang("translation.students.student")</th>
                        <th>@lang("translation.exams.correct_answers_count")</th>
                        <th>@lang("translation.exams.score")</th>
                        <th></th>
                    </tr>
                    @foreach($student_exams as $student_exam)
                    <tr>
                        <td>{{ $student_exam->id }}</td>
                        <td>
                            <a href="{{ route('admin.students.show', $student_exam->student_id) }}" class="text-reset">
                                {{ $student_exam->student?->getFullName() ?? '-' }}
                            </a>
                        </td>
                        <td>{{ $student_exam->correct_answers }}</td>
                        <td>{{ $student_exam->score }}</td>
                        <td>
                            <a class="btn btn-primary btn-sm t-show-btn"
                                wire:click="showStudentExam({{$student_exam->id}})">
                                <i class="mdi mdi-eye"></i>
                            </a>
                            <form action="{{ route('admin.exams.delete-student-exams', $student_exam->id) }}"
                                method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm t-delete-btn"
                                    onclick="return confirm('Are you sure?')">
                                    <i class="mdi mdi-delete"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </table>

                <div class="d-flex justify-content-end">
                    {{ $student_exams->links() }}
                </div>
            </div>
            @else
            <x-no-result />
            @endif
        </div>

        <div class="card-preloader d-none" wire:loading.class.remove="d-none">
            <div class="card-status">
                <div class="spinner-border text-success">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>

    </div>

    <!-- Create Modal -->
    <div class="modal fade" id="createStudentExam" tabindex="-1" aria-labelledby="createStudentExamLabel"
        data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="createStudentExamLabel">
                        @lang("translation.exams.create_student_exam")
                    </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.exams.create-student-exams', ['exam' => $exam->id]) }}">
                    <div class="modal-body">
                        <select name="group_id" id="group_id" class="form-control">
                            <option value=""></option>
                            @foreach($groups as $group)
                            <option value="{{ $group->id }}">
                                {{ $group->name }} | {{ $group->teacher?->name ?? '-' }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            @lang("translation.Close")
                        </button>
                        <button type="submit" class="btn btn-primary">
                            @lang("translation.btn.create")
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Show Modal -->
    <div class="modal fade" id="showStudentExam" tabindex="-1" aria-labelledby="showStudentExamLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="showStudentExamLabel">
                        @lang("translation.exams.info")
                    </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if($selected_student_exam)
                    <table class="table">
                        <tr>
                            <th>@lang("translation.students.student")</th>
                            <td>{{ $selected_student_exam->student?->getFullName() ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>@lang("translation.exams.correct_answers_count")</th>
                            <td>{{ $selected_student_exam->correct_answers }}</td>
                        </tr>
                        <tr>
                            <th>@lang("translation.exams.score")</th>
                            <td>{{ $selected_student_exam->score }}</td>
                        </tr>
                    </table>
                    <br>
                    <h1 class="modal-title fs-5 mb-2">
                        @lang("translation.exams.more")
                    </h1>
                    <table class="table">
                        <thead class="bg-primary-subtle">
                            <tr>
                                <th>@lang("translation.exams.subject")</th>
                                <th>@lang("translation.exams.correct_answers_count")</th>
                                <th>@lang("translation.exams.score")</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($student_exam_details as $student_exam_detail)
                            <tr>
                                <th>{{ $student_exam_detail->examDetail?->subject ?? '-' }}</th>
                                <td>{{ $student_exam_detail->correct_answers }}</td>
                                <td>{{ $student_exam_detail->score }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        @lang("translation.Close")
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('custom-scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            $('#group_id').select2({
                dropdownParent: $('#createStudentExam'),
                placeholder: "@lang('translation.groups.choose_group')",
            });
        });
    </script>
    @script
    <script>
        $wire.on('showStudentExamModal', () => {
            $('#showStudentExam').modal('show');
        });
    </script>
    @endscript
    @endpush
</div>
