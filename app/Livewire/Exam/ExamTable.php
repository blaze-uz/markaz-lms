<?php

namespace App\Livewire\Exam;

use App\Models\Exam;
use Livewire\Component;

class ExamTable extends Component
{
    public $search = '';

    public function render()
    {
        $items = Exam::search($this->search)->latest('id')->paginate(10);
        return view('livewire.exam.exam-table', [
            'items' => $items
        ]);
    }
}
