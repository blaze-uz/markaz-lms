<?php

namespace App\Livewire\Exam;

use Livewire\Component;

class ExamDetailCreate extends Component
{
    public $selectedExam;

    public function mount($exam): void
    {
        $this->selectedExam = $exam;
    }

    public function render()
    {
        return view('livewire.exam.exam-detail-create');
    }
}
