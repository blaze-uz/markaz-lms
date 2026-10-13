<?php

namespace App\Http\Controllers;

use App\Models\Test;
use App\Services\TestService;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public $service;

    public function __construct()
    {
        $this->service = new TestService();
    }

    public function create($quiz_id)
    {
        $item = new Test();
        return view('admin.tests.create', [
            'item' => $item,
            'quiz_id' => $quiz_id,
        ]);
    }

    public function store(Request $request, $quiz_id)
    {
        $data = $request->validate([
            'question' => 'required',
            'answers.*' => 'required',
            'is_correct' => 'required'
        ], [
            'question.required' => __('translation.quizzes.question_required'),
            'answers.*.required' => __('translation.quizzes.answers_required'),
            'is_correct.required' => __('translation.quizzes.is_correct_required')
        ]);

        $this->service->store($data, $quiz_id);

        session()->flash('success_alert', __('translation.alert_messages.created'));
        return redirect()->route('admin.quizzes.show', $quiz_id);
    }

    public function destroy($id)
    {
        $this->service->destroy($id);

        session()->flash('success_alert', __('translation.alert_messages.deleted'));
        return redirect()->back();
    }
}
