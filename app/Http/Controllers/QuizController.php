<?php

namespace App\Http\Controllers;

use App\Services\QuizService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class QuizController extends Controller
{
    public $service;

    public function __construct()
    {
        $this->service = new QuizService();
    }

    public function index()
    {
        return view('admin.quizzes.index');
    }

    public function create()
    {
        return view('admin.quizzes.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|min:3|max:255',
            'tests_count' => 'required|integer|min:1|max:100',
            'passing_percentage' => 'required|integer|min:1|max:100',
            'description' => 'nullable|string|min:3|max:255',
        ], [
            'name.required' => __('translation.required'),
            'tests_count.required' => __('translation.required'),
            'passing_percentage.required' => __('translation.required'),
        ]);

        $item = $this->service->store($data);

        session()->flash('success_alert', __('translation.alert_messages.created'));
        return redirect()->route('admin.quizzes.show', $item->id);
    }

    public function show($id)
    {
        $item = $this->service->show($id);
        return view('admin.quizzes.show', [
            'item' => $item,
        ]);
    }

    public function edit($id)
    {
        $item = $this->service->show($id);
        return view('admin.quizzes.edit', [
            'item' => $item,
        ]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'required|string|min:3|max:255',
            'tests_count' => 'required|integer|min:1|max:100',
            'passing_percentage' => 'required|integer|min:1|max:100',
            'description' => 'nullable|string|min:3|max:255',
        ], [
            'name.required' => __('translation.required'),
            'tests_count.required' => __('translation.required'),
            'passing_percentage.required' => __('translation.required'),
            'description.required' => __('translation.required'),
        ]);

        $item = $this->service->update($data, $id);

        session()->flash('success_alert', __('translation.alert_messages.updated'));
        return redirect()->route('admin.quizzes.show', $item->id);
    }

    public function setToken(Request $request)
    {
        $data = $request->validate([
            'bot_token' => 'required|string|min:3|max:255',
        ]);
        $this->service->setToken($data);

        session()->flash('success_alert', __('translation.alert_messages.token_set'));
        return redirect()->route('admin.quizzes.index');
    }

    public function test()
    {
        $quizzes = $this->service->getQuizzes();
        $selectedQuiz = $this->service->getQuiz(request()->quiz_id);
        return Inertia::render('Test', [
            'quizzes' => $quizzes,
            'selectedQuiz' => $selectedQuiz,
        ]);
    }

    public function start($quiz_id)
    {
        $quiz = $this->service->generateQuiz($quiz_id);
        return Inertia::render('TestShow', [
            'quiz' => $quiz
        ]);
    }

    public function save(Request $request)
    {
        $data = $request->validate([
            'quizId' => 'required',
            'uniqueId' => 'required',
            'answers' => 'required|array',
        ]);

        $this->service->save($data);

        return redirect()->route('test.result', $data['uniqueId']);
    }

    public function result($unique_id)
    {
        $result = $this->service->getResult($unique_id);
        return Inertia::render('TestResult', [
            'result' => $result
        ]);
    }
}
