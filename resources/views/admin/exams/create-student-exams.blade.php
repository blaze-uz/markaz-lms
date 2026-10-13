@extends('layouts.master')

@section("title")
{{ $item->name }}
@stop

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@stop

@section('content')
{{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
<form action="{{ route('admin.exams.store-student-exams', ['exam' => $item->id]) }}" method="POST" id="form">
    @csrf
    <div class="row mb-3">
        <div class="col-lg-3">
            <livewire:exam.exam-detail-create :exam="$item" />
        </div>
        <div class="col-lg-9">
            @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between">
                        <h4 class="card-title">@lang("translation.exams.create_student_exam")</h4>
                        <button type="button" class="btn btn-success btn-sm t-create-btn" id="add-new-student">
                            <i class="mdi mdi-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="student-exam-rows">
                        @if(count($selected_students) > 0)
                        @foreach($selected_students as $selected_student)
                        <div class="row student-exam-row mb-3">
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label>
                                        @lang("translation.students.student")
                                    </label>
                                    <div class="form-control-static">
                                        <strong>{{ $selected_student->getFullName() }}</strong>
                                        <small class="text-muted d-block">{{ $selected_student->getFormattedPhone() }}</small>
                                        <input type="hidden" name="student_id[]" value="{{ $selected_student->id }}">
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-9">
                                <div class="d-flex gap-2 justify-content-between align-items-end">
                                    <div class="form-group w-100">
                                        <label>{{ __("translation.exams.score") }}</label>
                                        <input type="number" name="score[{{ $selected_student->id }}][]"
                                            placeholder="{{ __('translation.exams.correct_answers_count') }}"
                                            class="form-control" required>
                                    </div>
                                    <button type="button" class="btn btn-danger remove-student-row t-remove-btn">
                                        <i class="mdi mdi-minus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-end">
            <button type="submit" form="form" class="btn btn-success">@lang("translation.btn.save")</button>
        </div>
    </div>
</form>

<template id="student-exam-row-template">
    <div class="row student-exam-row mb-3">
        <div class="col-lg-3">
            <div class="form-group">
                <label for="student_id">
                    @lang("translation.students.student")
                </label>
                <select name="student_id[]" class="form-control student-select">
                    <option value="" selected disabled>@lang('translation.choose')</option>
                    @foreach($students as $student)
                    <option value="{{ $student->id }}">
                        {{ $student->getFullName() }} | {{ $student->getFormattedPhone() }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-lg-9">
            <div class="d-flex gap-2 justify-content-between align-items-end">
                <div class="form-group w-100">
                    <label>{{ __("translation.exams.score") }}</label>
                    <input type="number" name="score_placeholder"
                        placeholder="{{ __('translation.exams.correct_answers_count') }}" class="form-control" required
                        data-score-input>
                </div>
                <button type="button" class="btn btn-danger remove-student-row t-remove-btn">
                    <i class="mdi mdi-minus"></i>
                </button>
            </div>
        </div>
    </div>
</template>

@stop

@section('script')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://unpkg.com/@popperjs/core@2"></script>
<script src="https://unpkg.com/tippy.js@6"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const addNewStudentBtn = document.getElementById('add-new-student');
        const studentExamRowsContainer = document.getElementById('student-exam-rows');
        const studentExamRowTemplate = document.getElementById('student-exam-row-template').content;

        function initializePlugins(context) {
            $(context).find('.student-select').select2({
                placeholder: "@lang('translation.choose')",
                width: '100%'
            });

            // Update score input name when student is selected
            $(context).find('.student-select').on('change', function() {
                const studentId = $(this).val();
                const scoreInput = $(this).closest('.student-exam-row').find('[data-score-input]');
                if (studentId) {
                    // Format the name to match the expected format: score[student_id][]
                    scoreInput.attr('name', `score[${studentId}][]`);
                }
            });

            tippy(context.querySelectorAll('.t-remove-btn'), {
                content: "@lang('translation.remove')",
            });
        }

        addNewStudentBtn.addEventListener('click', function () {
            const newRow = studentExamRowTemplate.cloneNode(true);
            studentExamRowsContainer.appendChild(newRow);
            initializePlugins(studentExamRowsContainer.lastElementChild);
        });

        studentExamRowsContainer.addEventListener('click', function (event) {
            const target = event.target.closest('.remove-student-row');
            if (target) {
                target.closest('.student-exam-row').remove();
            }
        });

        // When form is submitted, ensure all score inputs have proper names
        document.getElementById('form').addEventListener('submit', function(event) {
            const scoreInputs = document.querySelectorAll('[data-score-input]');
            let hasError = false;

            scoreInputs.forEach(input => {
                const studentSelect = input.closest('.student-exam-row').querySelector('.student-select');
                if (studentSelect && !studentSelect.value) {
                    hasError = true;
                    alert("@lang('translation.students.please_select_student')");
                    studentSelect.focus();
                    event.preventDefault();
                    return false;
                }
            });

            if (!hasError) {
                return true;
            }
        });

        initializePlugins(document);
    });
</script>
@stop