<?php

namespace App\Livewire\Exam;

use App\Models\Group;
use App\Models\StudentExam;
use App\Models\StudentExamDetail;
use Livewire\Component;

class ExamDetailTable extends Component
{
    public $exam;
    public $selected_student_exam;
    public $student_exam_details = [];

    public function mount($exam)
    {
        $this->exam = $exam;
    }

    public function showStudentExam($student_exam_id)
    {
        $this->reset('student_exam_details');
        $this->selected_student_exam = StudentExam::with('student')->findOrFail($student_exam_id);
        $this->student_exam_details = StudentExamDetail::with('examDetail')->where('student_exam_id', $student_exam_id)->get();
        $this->dispatch('showStudentExamModal');
    }

    public function render()
    {
        $student_exams = StudentExam::with('student')
            ->where('exam_id', $this->exam->id)
            ->paginate(10);

        $groups = Group::with('teacher')->active()->orderBy('name')->get(['id', 'name', 'teacher_id']);
        return view('livewire.exam.exam-detail-table', [
            'student_exams' => $student_exams,
            'groups' => $groups,
        ]);
    }
}
