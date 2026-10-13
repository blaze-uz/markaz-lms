<?php

namespace App\Livewire\Leads;

use App\Models\Group;
use App\Models\KanbanBoard as KanbanBoardModel;
use App\Models\KanbanCollection;
use App\Models\KanbanTask;
use App\Models\KanbanTaskCall;
use App\Models\LeadReminder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;

class KanbanBoard extends Component
{
    //boards
    public $boardName;

    public $collections;

    public $teachers;

    public $collectionId;

    //tasks
    public $taskName;

    public $taskPhone;

    public $taskComment;

    public $lead;

    public $students;

    public $comment;

    public $comments;

    public $chosenTeachers;

    public $chosenDays;

    public $chosenTime;

    public $taskModalName;

    public $activities;

    public $groups;

    public $groupId;

    public $groupSwitch = false;

    public $selectedTaskPhone;

    public $calls;

    public $reminders;

    public function mount(): void
    {
        $this->getData();
    }

    public function hydrate()
    {
        $this->getData();
    }

    public function clickTask($lead_id): void
    {
        $this->reset('selectedTaskPhone', 'comment', 'calls', 'reminders', 'comments', 'activities');
        $this->lead = KanbanTask::with('teachers')->find($lead_id);
        $this->taskModalName = $this->lead->name;
        $this->selectedTaskPhone = $this->lead->phone;
        $this->getCalls($lead_id);
        $this->getReminders($lead_id);
        $this->getComments($lead_id);
        $this->dispatch('task-clicked');
    }

    public function changeTaskName($lead_id): void
    {
        abort_unless(auth()->user()?->can('lead-update'), 403);

        $task = KanbanTask::find($lead_id);

        if ($task) {
            $task->update([
                'name' => $this->taskModalName,
            ]);
        }
    }

    public function removeTask($lead_id): void
    {
        abort_unless(auth()->user()?->can('lead-delete'), 403);

        $task = KanbanTask::find($lead_id);

        if ($task) {
            $task->delete();
        }

        $this->getData();
        $this->dispatch('task-removed');
    }

    public function openBoardModal($collectionId): void
    {
        $this->collectionId = $collectionId;
        $this->reset('groupSwitch', 'groupId');
    }

    public function storeBoard(): void
    {
        abort_unless(auth()->user()?->can('lead-store'), 403);

        $this->validate([
            'boardName' => ['required'],
            'groupId' => 'required_if:groupSwitch,1',
        ]);

        $board = KanbanBoardModel::create([
            'name' => $this->boardName,
            'kanban_collection_id' => $this->collectionId,
            'url' => uniqid(),
            'group_id' => $this->groupSwitch ? $this->groupId : null,
        ]);

        $board->branches()->attach(auth()->user()->current_branch_id);

        $this->getData();
        $this->reset('boardName', 'collectionId', 'groupSwitch', 'groupId');
        $this->dispatch('board-created');
    }

    public function openTaskModal($collectionId): void
    {
        $this->collectionId = $collectionId;
        $this->taskPhone = '';

        $this->dispatch('task-modal-opened');
    }

    public function storeTask(): void
    {
        abort_unless(auth()->user()?->can('lead-store'), 403);

        $this->validate([
            'taskName' => 'required',
            'taskPhone' => 'required',
        ]);

        DB::beginTransaction();
        try {
            $kanbanTask = KanbanTask::create([
                'name' => $this->taskName,
                'phone' => $this->taskPhone,
                'comment' => $this->taskComment,
                'kanban_collection_id' => $this->collectionId,
                'chosen_days' => $this->chosenDays,
                'chosen_time' => $this->chosenTime,
                'causer_id' => auth()->id(),
            ]);

            $kanbanTask->teachers()->attach($this->chosenTeachers);
            $kanbanTask->branches()->attach(auth()->user()->current_branch_id);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        $this->getData();
        $this->reset('taskName', 'taskComment', 'collectionId', 'chosenTeachers', 'chosenDays', 'chosenTime');
        $this->dispatch('task-created');
    }

    public function moveTask($taskId, $toCollectionId, $toBoardId): void
    {
        abort_unless(auth()->user()?->can('lead-update'), 403);

        $task = KanbanTask::find($taskId);
        $task->kanban_board_id = $toBoardId;
        $task->kanban_collection_id = $toCollectionId;
        $task->save();

        $this->getData();
        $this->dispatch('task-moved');
    }

    public function moveBoard($boardId, $toCollectionId): void
    {
        abort_unless(auth()->user()?->can('lead-update'), 403);

        $board = KanbanBoardModel::find($boardId);
        $board->kanban_collection_id = $toCollectionId;
        $board->save();

        $this->getData();
        $this->dispatch('board-moved');
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

    public function render()
    {
        return view('livewire.leads.kanban-board');
    }

    private function getData(): void
    {
        $this->collections = KanbanCollection::with(['boards.tasks' => function ($query) {
            $query->where('kanban_tasks.status', 'new')->latest();
        }, 'unsignedTasks' => function ($query) {
            $query->where('status', 'new')->latest();
        }])->get();

        $this->teachers = User::role('Teacher')
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get();

        $this->groups = Group::where('state', 'demo')
            ->select(['id', 'name'])
            ->get();
    }

    private function getComments($task_id): void
    {
        $task = KanbanTask::with('comments')->find($task_id);
        $this->comments = $task->comments()->with('user')->latest()->get();
    }

    private function getCalls($lead_id): void
    {
        $this->calls = KanbanTaskCall::where('kanban_task_id', $lead_id)->latest()->get();
    }

    private function getReminders($lead_id): void
    {
        $this->reminders = LeadReminder::where('lead_id', $lead_id)->orderByDesc('created_at')->get();
    }
}
