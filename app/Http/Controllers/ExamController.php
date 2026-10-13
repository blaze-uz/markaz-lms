<?php

namespace App\Http\Controllers;

use App\Services\ExamService;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    public ExamService $service;

    public function __construct()
    {
        $this->service = new ExamService();
    }

    public function index()
    {
        return view('admin.exams.index');
    }

    public function show($id)
    {
        $item = $this->service->show($id);
        return view('admin.exams.show', [
            'item' => $item,
        ]);
    }

    public function create()
    {
        return view('admin.exams.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'tests_count' => 'required|integer',
            'description' => 'nullable|string',
            'exam_details.subject' => 'required|array',
            'exam_details.subject.*' => 'required|string|max:255',
            'exam_details.score' => 'required|array',
            'exam_details.score.*' => 'required|numeric|max:99',
        ], [
            'name.required' => __('translation.exams.name_required'),
            'tests_count.required' => __('translation.exams.tests_count_required'),
            'exam_details.subject.*.required' => __('translation.exams.subject_required'),
            'exam_details.score.*.required' => __('translation.exams.score_required'),
            'exam_details.score.*.max' => __('translation.exams.score_max_99'),
        ]);

        $this->service->store($data);

        session()->flash('success_alert', __('translation.alert_messages.exam_created'));
        return redirect()->route('admin.exams.index');
    }

    public function createStudentExams($id)
    {
        $group_id = request()->get('group_id');
        $selected_students = [];
        if ($group_id) {
            $selected_students = $this->service->getGroupStudents($group_id);
        }
        $item = $this->service->show($id);
        $students = $this->service->getStudents();
        $exam_details = $this->service->getExamDetails($id);
        return view('admin.exams.create-student-exams', [
            'students' => $students,
            'item' => $item,
            'exam_details' => $exam_details,
            'selected_students' => $selected_students,
        ]);
    }

    /**
     * @throws \Exception
     */
    public function storeStudentExams($id, Request $request)
    {
        $data = $request->validate([
            'student_id' => 'required|array',
            'student_id.*' => 'required',
            'score' => 'required|array',
            'score.*' => 'required|max:99',
        ], [
            'student_id.required' => __('translation.exams.student_required'),
            'score.required' => __('translation.exams.score_required'),
            'score.*.required' => __('translation.exams.score_required'),
            'score.*.max' => __('translation.exams.score_max_99'),
        ]);

        $this->service->storeStudentExams($id, $data);
        session()->flash('success_alert', __('translation.alert_messages.student_exams_created'));
        return redirect()->route('admin.exams.show', $id);
    }

    /**
     * @throws \Exception
     */
    public function deleteStudentExams($student_exam_id)
    {
        $exam = $this->service->deleteStudentExams($student_exam_id);
        session()->flash('success_alert', __('translation.alert_messages.student_exams_deleted'));
        return redirect()->route('admin.exams.show', $exam->id);
    }

    public function destroy($id)
    {
        $this->service->destroy($id);
        session()->flash('success_alert', __('translation.alert_messages.exam_deleted'));
        return redirect()->route('admin.exams.index');
    }
}
