<?php

namespace App\Livewire\Leads;

use App\Exports\LeadsExport;
use App\Models\KanbanBoard;
use App\Models\KanbanTask;
use App\Models\KanbanTaskCall;
use App\Models\Lead;
use App\Models\LeadReminder;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Activitylog\Models\Activity;

class LeadsTable extends Component
{
    use WithPagination;

    public $paginationTheme = 'bootstrap';

    public $date;

    public $lead;

    public $taskModalName;

    public $activities;

    public $comments;

    public $search = '';

    public $selectedChosenDays = '';

    public $selectedTeacher;

    public $selectedCauser;

    public $selectedBoard;

    public $model = KanbanTask::class;

    public $selectedLead;

    public $calls;

    public $reminders;

    public $selectedTaskPhone;

    public function mount()
    {
        $this->search = request()->query('search', $this->search);
        if (request()->has('date')) {
            $this->date = request()->input('date');
        } else {
            $this->date = Carbon::now()->format('Y.m.d').' - '.Carbon::now()->format('Y.m.d');
        }
    }

    public function updated()
    {
        request()->query('search', $this->search);
    }

    public function clickTask($lead_id): void
    {
        $this->reset('selectedTaskPhone', 'calls', 'reminders', 'comments', 'activities');
        $this->lead = $this->model::with(['teachers', 'calls', 'board'])->find($lead_id);
        $this->taskModalName = $this->lead->name;
        $this->selectedTaskPhone = $this->lead->phone;
        $this->getComments($lead_id);
        $this->getCalls($lead_id);
        $this->getReminders($lead_id);
        $this->dispatch('task-clicked');
    }

    public function handleCall($lead_id)
    {
        $this->selectedLead = $this->model::find($lead_id);
        $this->dispatch('handleCall');
    }

    public function handleReminder($lead_id): void
    {
        $this->selectedLead = $this->model::find($lead_id);
        $this->dispatch('handleReminder');
    }

    public function handleSms($lead_id)
    {
        $ids = [$lead_id];
        $type = 'leads';

        return redirect()->route('admin.sms.create', ['sendSmsIds' => $ids, 'sendSmsType' => $type]);
    }

    public function handleAttend($lead_id)
    {
        abort_unless(auth()->user()?->can('lead-update'), 403);

        $lead = Lead::find($lead_id);
        $lead->update([
            'attend' => ! $lead->attend,
        ]);

        $this->dispatch('leadUpdated');
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

    public function export()
    {
        $search = $this->search ?? '';
        $selectedChosenDays = $this->selectedChosenDays ?? '';
        [$startDate, $endDate] = $this->getDateRange();

        $query = KanbanTask::with([
            'teachers:id,name',
            'board:id,name',
            'course:id,name',
            'causer:id,name',
        ])
            ->when($search, function ($query, $search) {
                $query->where('name', 'ilike', '%'.$search.'%')
                    ->orWhere(DB::raw("replace(replace(replace(phone,'(',''),')',''),'-','')"), 'ilike', '%'.$search.'%');
            })
            ->when($selectedChosenDays !== '', function ($query) use ($selectedChosenDays) {
                $query->where('chosen_days', $selectedChosenDays);
            })
            ->when($this->selectedTeacher, function ($query) {
                $query->whereHas('teachers', function ($query) {
                    $query->where('teacher_id', $this->selectedTeacher);
                });
            })
            ->when($this->selectedCauser, function ($query) {
                $query->where('causer_id', $this->selectedCauser);
            })
            ->when($this->selectedBoard, function ($query) {
                $query->where('kanban_board_id', $this->selectedBoard);
            })
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->orderBy('created_at', 'desc');

        return Excel::download(new LeadsExport($query), 'leads.xlsx');
    }

    public function render()
    {
        // Validate that search and selectedChosenDays are set to avoid unnecessary queries
        $search = $this->search ?? '';
        $selectedChosenDays = $this->selectedChosenDays ?? '';
        [$startDate, $endDate] = $this->getDateRange();

        $leads = KanbanTask::with([
            'teachers:id,name',
            'board:id,name',
            'course:id,name',
            'causer:id,name',
            'reminders:id,lead_id,reminder_date,reminder_status',
        ])
            ->leftJoin('lead_reminders', 'kanban_tasks.id', '=', 'lead_reminders.lead_id')
            ->select('kanban_tasks.*') // Avoids column name ambiguity
            ->when($search, function ($query, $search) {
                $query->where('name', 'ilike', '%'.$search.'%')
                    ->orWhere(DB::raw("replace(replace(replace(phone,'(',''),')',''),'-','')"), 'ilike', '%'.$search.'%');
            })
            ->when($selectedChosenDays !== '', function ($query) use ($selectedChosenDays) {
                $query->where('chosen_days', $selectedChosenDays);
            })
            ->when($this->selectedTeacher, function ($query) {
                $query->whereHas('teachers', function ($query) {
                    $query->where('teacher_id', $this->selectedTeacher);
                });
            })
            ->when($this->selectedCauser, function ($query) {
                $query->where('causer_id', $this->selectedCauser);
            })
            ->when($this->selectedBoard, function ($query) {
                $query->where('kanban_board_id', $this->selectedBoard);
            })
            ->whereDate('kanban_tasks.created_at', '>=', $startDate)
            ->whereDate('kanban_tasks.created_at', '<=', $endDate)
            ->orderByRaw('CASE WHEN lead_reminders.reminder_date IS NULL THEN 1 ELSE 0 END, CASE WHEN lead_reminders.reminder_status = \'pending\' THEN 0 ELSE 1 END, lead_reminders.reminder_date ASC')
            ->orderBy('kanban_tasks.created_at', 'desc')
            ->paginate(20);

        $teachers = $this->getTeachers();
        $causers = $this->getCausersProperty();

        return view('livewire.leads.leads-table', [
            'leads' => $leads,
            'teachers' => $teachers,
            'causers' => $causers,
            'boards' => $this->getBoardsProperty(),
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
        $task = KanbanTask::with(['comments', 'board'])->find($task_id);
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

    private function getTeachers()
    {
        return User::whereHas('roles', function ($query) {
            $query->where('name', 'Teacher');
        })->select('id', 'name')
            ->get();
    }

    private function getCausersProperty()
    {
        $dates = explode(' - ', $this->date);
        if (count($dates) !== 2) {
            return collect();
        }

        try {
            $startDate = Carbon::createFromFormat('Y.m.d', trim($dates[0]))->startOfDay();
            $endDate = Carbon::createFromFormat('Y.m.d', trim($dates[1]))->endOfDay();

            return User::whereIn('id', function ($query) use ($startDate, $endDate) {
                $query->select('causer_id')
                    ->from('kanban_tasks')
                    ->whereNotNull('causer_id')
                    ->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate)
                    ->distinct();
            })
                ->orderBy('name')
                ->get();
        } catch (\Exception $e) {
            return collect();
        }
    }

    private function getBoardsProperty()
    {
        return KanbanBoard::orderBy('name')->get();
    }

    private function getDateRange()
    {
        $dates = explode(' - ', $this->date);
        $startDate = Carbon::parse(str_replace('.', '-', $dates[0]))->startOfDay();
        $endDate = Carbon::parse(str_replace('.', '-', $dates[1] ?? $dates[0]))->endOfDay();

        return [$startDate, $endDate];
    }
}
