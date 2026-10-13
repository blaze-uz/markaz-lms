<?php

namespace App\Livewire\Quiz;

use App\Models\QuizResult;
use App\Models\Test;
use Livewire\Component;
use Livewire\WithPagination;

class QuizShow extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $selectedTab = 'user_exam_results';
    public $item;
    public $selected_test;
    public $search = '';
    public $quiz_result_search = '';

    public function mount($item)
    {
        $this->item = $item;
        $this->selectedTab = cache()->get('selected_tab', $this->selectedTab);
        cache()->put('selected_tab', $this->selectedTab);
    }

    public function changeTab($tab)
    {
        $this->selectedTab = $tab;
        cache()->put('selected_tab', $this->selectedTab);
    }

    public function openTestModal($test_id)
    {
        $this->selected_test = Test::with('answers')->findOrfail($test_id);
        $this->dispatch('openTestModal');
    }

    public function render()
    {
        $tests = Test::select('id', 'question')
            ->where('quiz_id', $this->item->id)
            ->when($this->search !== '', function ($query) {
                return $query->where('question', 'ilike', '%' . $this->search . '%');
            })
            ->paginate(10);

        $quiz_results = QuizResult::where('quiz_id', $this->item->id)->paginate(10);

        return view('livewire.quiz.quiz-show', [
            'tests' => $tests,
            'quiz_results' => $quiz_results,
        ]);
    }
}
