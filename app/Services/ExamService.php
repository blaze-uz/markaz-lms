<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\ExamDetail;
use App\Models\Student;
use App\Models\StudentExam;
use App\Models\StudentExamDetail;
use Illuminate\Support\Facades\DB;

class ExamService
{
    public function store(array $data)
    {
        DB::beginTransaction();
        try {
            // Create the exam
            $exam = Exam::create([
                'name' => $data['name'],
                'tests_count' => $data['tests_count'],
                'description' => $data['description'],
                'created_by' => auth()->id(),
            ]);

            // Create the exam details
            foreach ($data['exam_details']['subject'] as $index => $subject) {
                $score = $data['exam_details']['score'][$index];
                $exam->details()->create([
                    'subject' => $subject,
                    'score' => $score,
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        DB::commit();
    }

    public function show($id)
    {
        return Exam::with('details')->findOrFail($id);
    }

    public function getStudents()
    {
        return Student::select('id', 'name', 'surname', 'phone')->orderBy('name')->get();
    }

    public function getExamDetails($id)
    {
        return ExamDetail::where('exam_id', $id)->get(['id', 'subject', 'score']);
    }

    public function getGroupStudents($group_id)
    {
        return Student::whereHas('groups', function ($query) use ($group_id) {
            $query->where('group_id', $group_id);
        })->get(['id', 'name', 'surname', 'phone']);
    }

    public function storeStudentExams($id, array $data): void
    {
        DB::beginTransaction();
        try {
            foreach ($data['student_id'] as  $studentId) {
                // Create or find StudentExam record once per student
                $studentExam = StudentExam::firstOrCreate(
                    ['exam_id' => $id, 'student_id' => $studentId],
                    ['correct_answers' => 0, 'score' => 0]
                );

                // Reset totals if this is an existing record being updated
                if ($studentExam->wasRecentlyCreated === false) {
                    $studentExam->correct_answers = 0;
                    $studentExam->score = 0;
                }

                // If we have scores for this student
                if (isset($data['score'][$studentId])) {
                    $scores = $data['score'][$studentId];

                    // Each score represents a single exam detail
                    foreach ($scores as $examDetailIndex => $score) {
                        // Since we don't have exam detail IDs in the form, get them from the exam
                        $examDetails = ExamDetail::where('exam_id', $id)->get();

                        if (!isset($examDetails[$examDetailIndex])) {
                            throw new \Exception("Exam detail not found at index $examDetailIndex");
                        }

                        $examDetail = $examDetails[$examDetailIndex];

                        // Create StudentExamDetail record
                        StudentExamDetail::create([
                            'student_exam_id' => $studentExam->id,
                            'exam_detail_id' => $examDetail->id,
                            'score' => $score * $examDetail->score,
                            'correct_answers' => $score,
                        ]);

                        // Update the total score and correct answers of the student exam
                        $studentExam->correct_answers += $score;
                        $studentExam->score += $score * $examDetail->score;
                    }

                    $studentExam->save();
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @throws \Exception
     */
    public function deleteStudentExams($student_exam_id)
    {
        DB::beginTransaction();
        try {
            $studentExam = StudentExam::findOrFail($student_exam_id);
            $exam = Exam::findOrFail($studentExam->exam_id);
            $studentExam->details()->delete();
            $studentExam->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $exam;
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $exam = Exam::findOrFail($id);
            $exam_details = $exam->details;
            $student_exams = $exam->studentExams;
            foreach ($student_exams as $student_exam) {
                $student_exam_details = StudentExamDetail::where('student_exam_id', $student_exam->id)->get();
                foreach ($student_exam_details as $student_exam_detail) {
                    $student_exam_detail->delete();
                }
                $student_exam->delete();
            }
            foreach ($exam_details as $exam_detail) {
                $exam_detail->delete();
            }
            $exam->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
