<?php

namespace App\Livewire\Leads;

use Livewire\Component;

class KanbanBoardFormCourseSwitcher extends Component
{
    public $item;

    public function mount($item)
    {
        $this->item = $item;
    }

    public function updateFormCourseState()
    {
        abort_unless(auth()->user()?->can('settings-index'), 403);

        $this->item->form_course_state = ! $this->item->form_course_state;
        $this->item->save();
    }

    public function render()
    {
        return view('livewire.leads.kanban-board-form-course-switcher');
    }
}
