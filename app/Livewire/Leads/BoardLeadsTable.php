<?php

namespace App\Livewire\Leads;

use App\Models\KanbanTask;
use App\Models\KanbanTaskCall;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

class BoardLeadsTable extends Component
{
    use WithPagination;

    public $paginationTheme = 'bootstrap';

    public $board;

    public $search;

    public $selectedChosenDays;

    public $lead;

    public $taskModalName;

    public $comments;

    public $calls;

    public $activities;

    public $model = KanbanTask::class;

    public $selectedLead;

    public $selectedLeads = [];

    public $perPage = 10;

    public function mount($board)
    {
        $this->board = $board;
    }

    public function clickTask($lead_id): void
    {
        $this->lead = $this->model::with(['teachers', 'calls', 'board'])->find($lead_id);
        $this->taskModalName = $this->lead->name;
        $this->getComments($lead_id);
        $this->getCalls($lead_id);
        $this->dispatch('task-clicked');
    }

    public function handleItem($lead_id)
    {
        if (in_array($lead_id, $this->selectedLeads)) {
            $index = array_search($lead_id, $this->selectedLeads);
            unset($this->selectedLeads[$index]);
        } else {
            $this->selectedLeads[] = $lead_id;
        }

        $this->dispatch('handleItem');
    }

    public function handleSelectAll()
    {
        $allLeadsIds = KanbanTask::where('kanban_board_id', $this->board->id)->pluck('id')->toArray();

        if (count($this->selectedLeads) === count($allLeadsIds)) {
            $this->selectedLeads = [];
        } else {
            $this->selectedLeads = $allLeadsIds;
        }
    }

    public function handleSendSms()
    {
        $ids = $this->selectedLeads;
        $type = 'leads';

        return redirect()->route('admin.sms.create', ['sendSmsIds' => $ids, 'sendSmsType' => $type]);
    }

    //    public function handleAttend($lead_id)
    //    {
    //        $lead = KanbanTask::find($lead_id);
    //        $lead->update([
    //            'attend' => !$lead->attend
    //        ]);
    //
    //        $this->dispatch('leadUpdated');
    //    }

    public function handleCall($lead_id)
    {
        $this->selectedLead = $this->model::with(['teachers', 'board'])->find($lead_id);
        $this->dispatch('handleCall');
    }

    public function removeTask($lead_id): void
    {
        abort_unless(auth()->user()?->can('lead-delete'), 403);

        $task = KanbanTask::find($lead_id);

        if ($task) {
            $task->delete();
        }

        $this->dispatch('task-removed');
    }

    public function render()
    {
        $items = KanbanTask::with('causer')
            ->where('kanban_board_id', $this->board->id)
            ->where(function ($query) {
                $query->where('name', 'ilike', '%'.$this->search.'%')
                    ->orWhere(DB::raw("replace(replace(replace(phone,'(',''),')',''),'-','')"), 'ilike', '%'.$this->search.'%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.leads.board-leads-table', [
            'items' => $items,
        ]);
    }

    private function getActivities($task_id): void
    {
        //group by date
        $this->activities = Activity::where('subject_id', $task_id)
            ->where('log_name', 'kanban_task')
            ->latest()
            ->limit(10)
            ->get();
    }

    private function getComments($task_id): void
    {
        $task = KanbanTask::with(['comments', 'board', 'causer'])->find($task_id);
        $this->comments = $task->comments()->with('user')->latest()->get();
    }

    private function getCalls($lead_id): void
    {
        $this->calls = KanbanTaskCall::with('causer')->where('kanban_task_id', $lead_id)->latest()->get();
    }
}
