<?php

namespace App\Services;

use App\Enums\AccountEnum;
use App\Imports\LeadsImport;
use App\Models\Account;
use App\Models\Course;
use App\Models\Group;
use App\Models\KanbanBoard;
use App\Models\KanbanCollection;
use App\Models\KanbanTask;
use App\Models\KanbanTaskCall;
use App\Models\Lead;
use App\Models\LeadBoard;
use App\Models\LeadCollection;
use App\Models\LeadReminder;
use App\Models\Settings;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class LeadService
{
    public function storeCollection(array $data): void
    {
        $collection = KanbanCollection::create([
            'name' => $data['name'],
        ]);

        $collection->branches()->attach(auth()->user()->current_branch_id);
    }

    public function getCollection($collection_id)
    {
        return KanbanCollection::findOrFail($collection_id);
    }

    public function updateCollection(array $data, $collection_id): void
    {
        $item = $this->getCollection($collection_id);
        $item->update([
            'name' => $data['name'],
        ]);
    }

    public function deleteCollection($id)
    {
        $item = $this->getCollection($id);
        $item->delete();
    }

    public function getCollections(): \Illuminate\Database\Eloquent\Collection
    {
        return KanbanCollection::all();
    }

    public function storeBoard(array $data): void
    {
        $board = KanbanBoard::create([
            'name' => $data['name'],
            'kanban_collection_id' => $data['collection_id'],
            'group_id' => $data['group_id'] ?? null,
        ]);

        $board->branches()->attach(auth()->user()->current_branch_id);
    }

    public function getBoard($board_id)
    {
        return KanbanBoard::findOrFail($board_id);
    }

    public function updateBoard(array $data, $board_id): void
    {
        $item = $this->getBoard($board_id);
        $form_comment_state = isset($data['form_comment_state']) && $data['form_comment_state'] == 'on';
        $item->update([
            'name' => $data['name'],
            'kanban_collection_id' => $data['collection_id'],
            'group_id' => $data['group_id'] ?? null,
            'success_message' => $data['success_message'] ?? '',
            'form_comment_state' => $form_comment_state,
        ]);
    }

    public function deleteBoard($board_id): void
    {
        $item = $this->getBoard($board_id);
        $item->delete();
    }

    /**
     * @throws \Exception
     */
    public function storeStudent(array $data): void
    {
        DB::beginTransaction();
        try {
            //create student
            $student = $this->createStudent($data);
            $this->createAccount($student);

            //update lead item
            // $this->updateKanbanTask($data['lead_id'], $student);
            $this->updateLeadItem($data['lead_id'], $student);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error_alert', $e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    private function createStudent(array $data)
    {
        //remove +998 and () - from phone
        $phone = substr(preg_replace('/\D/', '', $data['phone']), 3);
        if (isset($data['phone2'])) {
            $phone2 = substr(preg_replace('/\D/', '', $data['phone2']), 3);
        } else {
            $phone2 = null;
        }

        $student = Student::create([
            'name' => $data['name'],
            'surname' => $data['surname'],
            'phone' => $phone,
            'phone2' => $phone2,
            'address' => $data['address'] ?? null,
            'lead_id' => $data['lead_id'],
            'comment' => $data['comment'] ?? null,
        ]);

        $student->branches()->attach(auth()->user()->current_branch_id);

        return $student;
    }

    private function updateKanbanTask(mixed $lead_id, Student $student): void
    {
        $task = $this->getKanbanTask($lead_id);

        if (! $task) {
            return;
        }

        $task->update([
            'student_id' => $student->id,
            'status' => 'student',
        ]);
        $task->delete();
    }

    private function updateLeadItem(mixed $lead_id, Student $student): void
    {
        $lead = Lead::findOrFail($lead_id);

        $lead->update([
            'student_id' => $student->id,
            'status' => 'student',
        ]);

        $lead->delete();
    }

    private function getKanbanTask(mixed $task_id)
    {
        return KanbanTask::findOrFail($task_id);
    }

    public function getBoardWithUrl($board_url)
    {
        // First try to find LeadCollection with board_url
        $leadCollection = LeadCollection::where('board_url', $board_url)->first();
        if ($leadCollection) {
            // Return a mock board object with collection data
            return (object) [
                'id' => $leadCollection->board_url, // Use board_url as ID for the route
                'lead_collection_id' => $leadCollection->id,
                'branch_ids' => $leadCollection->branches->pluck('id')->toArray(),
                'name' => $leadCollection->name,
                'url' => $leadCollection->board_url,
                'form_comment_state' => true,
                'form_course_state' => true,
                'success_message' => 'Thank you for your submission!',
                'group_id' => null,
                'type' => 'lead_collection',
            ];
        }

        // Fallback to KanbanBoard for backward compatibility
        return KanbanBoard::where('url', $board_url)->firstOrFail();
    }

    public function getLogo()
    {
        $logo = Settings::where('key', 'logo')->firstOrFail();

        return $logo->getFirstMediaUrl();
    }

    /**
     * @throws \Exception
     */
    public function leadStore(array $data, $board_id)
    {
        DB::beginTransaction();
        try {
            //create kanban task
            $success_message = $this->createKanbanTask($data, $board_id);
            DB::commit();

            return $success_message;
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error_alert', $e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    private function createKanbanTask(array $data, $board_id)
    {
        $board = $this->getBoardWithUrl($board_id);

        // Check if this is a LeadCollection or KanbanBoard
        if (isset($board->type) && $board->type === 'lead_collection') {
            // Create Lead directly for LeadCollection
            $lead = Lead::create([
                'name' => $data['name'],
                'phone' => $data['phone'],
                'comment' => $data['comment'] ?? null,
                'course_id' => $data['course_id'] ?? null,
                'collection_id' => $board->lead_collection_id,
                'session_id' => request()->session()->getId(),
                'causer_id' => auth()->id(),
                'custom_attributes' => $data['custom_attributes'] ?? null,
            ]);

            // Attach branches if available
            $leadCollection = LeadCollection::find($board->lead_collection_id);
            if ($leadCollection && $leadCollection->branches()->exists()) {
                $lead->branches()->attach($leadCollection->branches->pluck('id')->toArray());
            }

            return $board->success_message;
        } else {
            // Original KanbanBoard logic - $board is already a KanbanBoard model
            $task = KanbanTask::create([
                'name' => $data['name'],
                'phone' => $data['phone'],
                'comment' => $data['comment'] ?? null,
                'course_id' => $data['course_id'] ?? null,
                'kanban_board_id' => $board->id,
                'kanban_collection_id' => $board->kanban_collection_id,
                'session_id' => request()->session()->getId(),
            ]);

            $task->branches()->attach($board->branches->pluck('id')->toArray());

            return $board->success_message;
        }
    }

    private function createAccount($student): void
    {
        $account = Account::create([
            'name' => $student->getFullName(),
            'account_code' => AccountEnum::STUDENT->value,
        ]);

        $student->update([
            'account_id' => $account->id,
        ]);
    }

    public function addComment(array $data): void
    {
        $task = $this->getKanbanTask($data['task_id']);
        $task->comments()->create([
            'comment' => $data['comment'],
            'user_id' => auth()->id(),
        ]);
    }

    public function storeLead(array $data)
    {
        $kanbanBoard = null;
        $kanbanCollection = KanbanCollection::first();
        if (! $kanbanCollection) {
            $kanbanCollection = KanbanCollection::create([
                'name' => "Boshlang'ich ⏰",
            ]);

            $kanbanCollection->branches()->attach(auth()->user()->current_branch_id);
        }
        $success_message = null;
        if (isset($data['board'])) {
            $kanbanBoard = KanbanBoard::findOrFail($data['board']);
            $success_message = $kanbanBoard->success_message;
        }

        $kanbanTask = KanbanTask::create([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'comment' => $data['comment'],
            'kanban_board_id' => $kanbanBoard?->id ?? null,
            'kanban_collection_id' => $kanbanBoard->kanban_collection_id ?? $kanbanCollection->id,
            'chosen_days' => $data['chosen_days'] ?? null,
            'chosen_time' => $data['chosen_time'] ?? null,
            'course_id' => $data['course'] ?? null,
            'causer_id' => auth()->id(),
        ]);

        if ($data['reminder_date'] && $data['reminder_time']) {
            $this->addReminder($kanbanTask->id, $data);
        }

        $kanbanTask->branches()->attach(auth()->user()->current_branch_id);

        if (isset($data['teachers'])) {
            foreach ($data['teachers'] as $teacher) {
                if ($teacher != null) {
                    $kanbanTask->teachers()->attach($teacher);
                }
            }
        }

        return [
            'success_message' => $success_message,
        ];
    }

    /**
     * @param  int|null  $branchId  When set, only teachers linked to this branch are returned.
     */
    public function getTeachers(?int $branchId = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = User::role('Teacher')
            ->select(['id', 'name'])
            ->orderBy('name');

        if ($branchId !== null) {
            $query->whereHas('branches', fn ($q) => $q->where('branches.id', $branchId));
        }

        return $query->get();
    }

    public function getBoards()
    {
        return LeadBoard::orderBy('name')->get();
    }

    public function storeCall($id, array $data): void
    {
        KanbanTaskCall::create([
            'kanban_task_id' => $id,
            'status' => $data['status'],
            'comment' => $data['comment'] ?? null,
            'causer_id' => auth()->id(),
        ]);
    }

    public function getCourses(array $branchIds = [])
    {
        return Course::select(['id', 'name'])
            ->when(! empty($branchIds), function ($query) use ($branchIds) {
                $query->whereHas('branches', function ($branchQuery) use ($branchIds) {
                    $branchQuery->whereIn('branches.id', $branchIds);
                });
            })
            ->orderBy('name')
            ->get();
    }

    /**
     * @return array<int, int>
     */
    public function getBoardBranchIds($board): array
    {
        if (isset($board->type) && $board->type === 'lead_collection') {
            return $board->branch_ids ?? [];
        }

        return $board->branches->pluck('id')->toArray();
    }

    public function getLead($id)
    {
        return KanbanTask::findOrFail($id);
    }

    public function updateLead(array $data, $id)
    {
        $kanbanTask = $this->getLead($id);
        $kanbanTask->update([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'comment' => $data['comment'],
            'chosen_days' => $data['chosen_days'] ?? null,
            'chosen_time' => $data['chosen_time'] ?? null,
            'kanban_board_id' => $data['board'] ?? null,
            'course_id' => $data['course'] ?? null,
        ]);

        $kanbanTask->teachers()->detach();
        if (isset($data['teachers'])) {
            foreach ($data['teachers'] as $teacher) {
                if ($teacher != null) {
                    $kanbanTask->teachers()->attach($teacher);
                }
            }
        }
    }

    public function importStore($data): void
    {
        Excel::import(new LeadsImport($data), $data['file']);
    }

    public function getLeadsPage()
    {
        return Cache::remember('leads_page', now()->addHour(), function () {
            return Settings::where('key', 'leads_page')->first()->value ?? 'table';
        });
    }

    public function getGroups()
    {
        return Group::where('state', 'demo')->select(['id', 'name'])->get();
    }

    public function getLeads($board_id)
    {
        return KanbanTask::with('teachers', 'board')
            ->where('kanban_board_id', $board_id)
            ->orderBy('name')
            ->get()
            ->map(function ($lead) {
                $name = explode(' ', $lead->name);
                $lead->name = $name[0] ?? null;
                $lead->surname = $name[1] ?? null;

                return $lead;
            });
    }

    public function transferToGroup(array $data, $board_id): void
    {
        DB::beginTransaction();
        try {
            $board = $this->getBoard($board_id);
            foreach ($data as $item) {
                foreach ($item as $key => $lead) {
                    $student = Student::create([
                        'name' => $lead['name'],
                        'surname' => $lead['surname'],
                        'phone' => $lead['phone'],
                        'gender' => $lead['gender'],
                        'lead_id' => $key,
                    ]);

                    $student->branches()->attach(auth()->user()->current_branch_id);

                    //create account
                    $this->createAccount($student);

                    //attach group
                    $student->groups()->attach($board->group_id);
                    $leadObj = $this->getLead($key);
                    $leadObj->update([
                        'attend' => true,
                    ]);
                    $leadObj->delete();
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error_alert', $e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function setUrl($board_id): void
    {
        $board = $this->getBoard($board_id);
        $board->update([
            'url' => uniqid(),
        ]);
    }

    public function addReminder($lead_id, $data): void
    {
        $reminder = LeadReminder::where('lead_id', $lead_id)->first();
        if ($reminder) {
            $reminder->update([
                'reminder_date' => $data['reminder_date'],
                'reminder_time' => $data['reminder_time'],
                'reminder_status' => 'pending',
                'reminder_note' => $data['reminder_note'],
            ]);
        } else {
            LeadReminder::create([
                'lead_id' => $lead_id,
                'reminder_date' => $data['reminder_date'],
                'reminder_time' => $data['reminder_time'],
                'reminder_status' => 'pending',
                'reminder_note' => $data['reminder_note'],
                'created_by' => auth()->id(),
            ]);
        }
    }

    public function updateReminder($data, $reminderId)
    {
        $reminder = LeadReminder::find($reminderId);
        if ($reminder && $data['status'] !== 'pending') {
            $reminder->reminder_status = $data['status'];
            $reminder->save();
        }
    }

    public function deleteReminder($reminderId): void
    {
        $reminder = LeadReminder::find($reminderId);
        if ($reminder) {
            $reminder->delete();
        }
    }

    public function checkLeadExists($board_url)
    {
        // First try to find LeadCollection with board_url
        $leadCollection = LeadCollection::where('board_url', $board_url)->first();
        if ($leadCollection) {
            // Check if lead exists in the collection
            return Lead::where('collection_id', $leadCollection->id)
                ->where('session_id', request()->session()->getId())
                ->where('created_at', '>', now()->subMinutes(5))
                ->exists();
        }

        // Fallback to KanbanBoard for backward compatibility
        $board = KanbanBoard::where('url', $board_url)->first();
        if (! $board) {
            return false;
        }

        return KanbanTask::where('kanban_board_id', $board->id)
            ->where('session_id', request()->session()->getId())
            ->where('created_at', '>', now()->subMinutes(5))
            ->exists();
    }

    /**
     * Get all lead collections ordered by order
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getLeadCollections()
    {
        return LeadCollection::orderBy('order')->get();
    }

    /**
     * Get leads grouped by collection with pagination
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $collections
     * @param  int  $limit  Number of leads per collection
     * @param  int  $offset  Starting offset for pagination
     * @param  string|null  $search  Search query
     * @param  array|null  $filters  Additional filters
     * @return array
     */
    public function getLeadsGroupedByCollection($collections = null, $limit = 50, $offset = 0, $search = null, $filters = null)
    {
        if (! $collections) {
            $collections = $this->getLeadCollections();
        }

        $leads = [];
        foreach ($collections as $collection) {
            $query = Lead::where('collection_id', $collection->id)
                ->with([
                    'reminders',
                    'comments',
                    'causer' => function ($query) {
                        $query->select('id', 'name')->with('media');
                    },
                    'teacher:id,name',
                    'board:id,name',
                    'course:id,name',
                ]);

            // Add search functionality
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'ILIKE', '%'.$search.'%')
                        ->orWhere('phone', 'ILIKE', '%'.$search.'%')
                        ->orWhere('comment', 'ILIKE', '%'.$search.'%');
                });
            }

            // Add additional filters
            if ($filters) {
                if (! empty($filters['selectedChosenDays'])) {
                    $query->where('chosen_days', $filters['selectedChosenDays']);
                }

                if (! empty($filters['selectedTeacher'])) {
                    $query->where('teacher_id', $filters['selectedTeacher']);
                }

                if (! empty($filters['selectedBoard'])) {
                    $query->where('board_id', $filters['selectedBoard']);
                }

                if (! empty($filters['selectedCourse'])) {
                    $query->where('course_id', $filters['selectedCourse']);
                }

                if (isset($filters['selectedAttend']) && $filters['selectedAttend'] !== null && $filters['selectedAttend'] !== '') {
                    $query->where('attend', (bool) $filters['selectedAttend']);
                }

                // Handle date filtering
                if (! empty($filters['dateRange'])) {
                    $this->applyDateFilter($query, $filters['dateRange']);
                } elseif (! empty($filters['startDate']) && ! empty($filters['endDate'])) {
                    $query->whereDate('created_at', '>=', $filters['startDate'])
                        ->whereDate('created_at', '<=', $filters['endDate']);
                }
            }

            $leads[$collection->id] = $query
                ->orderByRaw('COALESCE("order", 999999) ASC')
                ->orderBy('created_at', 'desc')
                ->offset($offset)
                ->limit($limit)
                ->get();
        }

        return $leads;
    }

    /**
     * Get lead counts for each collection
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $collections
     * @param  string|null  $search  Search query
     * @param  array|null  $filters  Additional filters
     * @return array
     */
    public function getLeadCounts($collections = null, $search = null, $filters = null)
    {
        if (! $collections) {
            $collections = $this->getLeadCollections();
        }

        $counts = [];
        foreach ($collections as $collection) {
            $query = Lead::where('collection_id', $collection->id);

            // Add search functionality
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'ILIKE', '%'.$search.'%')
                        ->orWhere('phone', 'ILIKE', '%'.$search.'%')
                        ->orWhere('comment', 'ILIKE', '%'.$search.'%');
                });
            }

            // Add additional filters
            if ($filters) {
                if (! empty($filters['selectedChosenDays'])) {
                    $query->where('chosen_days', $filters['selectedChosenDays']);
                }

                if (! empty($filters['selectedTeacher'])) {
                    $query->where('teacher_id', $filters['selectedTeacher']);
                }

                if (! empty($filters['selectedBoard'])) {
                    $query->where('board_id', $filters['selectedBoard']);
                }

                if (! empty($filters['selectedCourse'])) {
                    $query->where('course_id', $filters['selectedCourse']);
                }

                if (isset($filters['selectedAttend']) && $filters['selectedAttend'] !== null && $filters['selectedAttend'] !== '') {
                    $query->where('attend', (bool) $filters['selectedAttend']);
                }

                // Handle date filtering
                if (! empty($filters['dateRange'])) {
                    $this->applyDateFilter($query, $filters['dateRange']);
                } elseif (! empty($filters['startDate']) && ! empty($filters['endDate'])) {
                    $query->whereDate('created_at', '>=', $filters['startDate'])
                        ->whereDate('created_at', '<=', $filters['endDate']);
                }
            }

            $counts[$collection->id] = $query->count();
        }

        return $counts;
    }

    /**
     * Load more leads for infinite scroll
     *
     * @param  int  $collectionId
     * @param  int  $offset
     * @param  int  $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function loadMoreLeads($collectionId, $offset = 0, $limit = 50)
    {
        return Lead::where('collection_id', $collectionId)
            ->with([
                'reminders',
                'comments',
                'causer' => function ($query) {
                    $query->select('id', 'name')->with('media');
                },
                'teacher:id,name',
                'board:id,name',
                'course:id,name',
            ])
            ->orderByRaw('COALESCE("order", 999999) ASC')
            ->orderBy('created_at', 'desc')
            ->offset($offset)
            ->limit($limit)
            ->get();
    }

    /**
     * Get total count of leads for a specific collection
     *
     * @param  int  $collectionId
     * @return int
     */
    public function getCollectionLeadCount($collectionId)
    {
        return Lead::where('collection_id', $collectionId)->count();
    }

    /**
     * Store a new lead collection
     */
    public function storeLeadCollection(array $data): LeadCollection
    {
        $collection = LeadCollection::create([
            'name' => $data['name'],
            'type' => 'default',
            'order' => LeadCollection::max('order') + 1,
        ]);

        // Attach to current user's branch
        if (auth()->user()->current_branch_id) {
            $collection->branches()->attach(auth()->user()->current_branch_id);
        }

        return $collection;
    }

    /**
     * Update a lead collection
     */
    public function updateLeadCollection(array $data, $collection_id): LeadCollection
    {
        $collection = LeadCollection::findOrFail($collection_id);
        $collection->update([
            'name' => $data['name'],
        ]);

        return $collection;
    }

    /**
     * Delete a lead collection
     */
    public function deleteLeadCollection($collection_id): void
    {
        $collection = LeadCollection::findOrFail($collection_id);
        $collection->delete();
    }

    /**
     * Update lead collection order
     */
    public function updateLeadCollectionOrder(array $orderData): void
    {
        DB::beginTransaction();
        try {
            foreach ($orderData as $item) {
                LeadCollection::where('id', $item['id'])
                    ->update(['order' => $item['order']]);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Apply date filter to query based on date range
     */
    private function applyDateFilter($query, $dateRange)
    {
        $now = now();

        switch ($dateRange) {
            case 'today':
                $query->whereDate('created_at', $now->toDateString());
                break;
            case 'yesterday':
                $query->whereDate('created_at', $now->subDay()->toDateString());
                break;
            case 'this_week':
                $query->whereBetween('created_at', [
                    $now->startOfWeek()->toDateString(),
                    $now->endOfWeek()->toDateString(),
                ]);
                break;
            case 'last_week':
                $lastWeekStart = $now->subWeek()->startOfWeek();
                $lastWeekEnd = $now->endOfWeek();
                $query->whereBetween('created_at', [
                    $lastWeekStart->toDateString(),
                    $lastWeekEnd->toDateString(),
                ]);
                break;
            case 'this_month':
                $query->whereBetween('created_at', [
                    $now->startOfMonth()->toDateString(),
                    $now->endOfMonth()->toDateString(),
                ]);
                break;
            case 'last_month':
                $lastMonthStart = $now->subMonth()->startOfMonth();
                $lastMonthEnd = $now->endOfMonth();
                $query->whereBetween('created_at', [
                    $lastMonthStart->toDateString(),
                    $lastMonthEnd->toDateString(),
                ]);
                break;
            case 'this_year':
                $query->whereBetween('created_at', [
                    $now->startOfYear()->toDateString(),
                    $now->endOfYear()->toDateString(),
                ]);
                break;
        }
    }

    /**
     * Get all lead boards
     */
    public function getLeadBoards()
    {
        return \App\Models\LeadBoard::with(['leadCollection:id,name', 'group:id,name'])
            ->orderBy('name')
            ->get();
    }

    /**
     * Store a new lead board
     */
    public function storeLeadBoard(array $data): \App\Models\LeadBoard
    {
        $board = \App\Models\LeadBoard::create([
            'name' => $data['name'],
            'lead_collection_id' => $data['lead_collection_id'],
            'group_id' => $data['group_id'] ?? null,
        ]);

        // Attach to current user's branch
        if (auth()->user()->current_branch_id) {
            $board->branches()->attach(auth()->user()->current_branch_id);
        }

        return $board->load(['leadCollection:id,name', 'group:id,name']);
    }

    /**
     * Update a lead board
     */
    public function updateLeadBoard(array $data, $board_id): \App\Models\LeadBoard
    {
        $board = \App\Models\LeadBoard::findOrFail($board_id);

        $board->update([
            'name' => $data['name'],
            'lead_collection_id' => $data['lead_collection_id'],
            'group_id' => $data['group_id'] ?? null,
        ]);

        return $board->load(['leadCollection:id,name', 'group:id,name']);
    }

    /**
     * Delete a lead board
     */
    public function deleteLeadBoard($board_id): void
    {
        $board = \App\Models\LeadBoard::findOrFail($board_id);

        // Set board_id to null for all leads using this board
        \App\Models\Lead::where('board_id', $board_id)->update(['board_id' => null]);

        // Delete the board
        $board->delete();
    }

    /**
     * Notify teacher via Telegram when a lead is attached to them
     */
    public function notifyTeacherAboutLead(Lead $lead): void
    {
        if (! $lead->teacher_id) {
            return;
        }

        // Load course relationship if not already loaded
        if (! $lead->relationLoaded('course')) {
            $lead->load('course:id,name');
        }

        $telegramService = new UserTelegramService();
        $message = $this->formatLeadNotificationMessage($lead);
        $telegramService->sendTelegramMessage($message, $lead->teacher_id);
    }

    /**
     * Format the notification message for teacher about attached lead
     */
    private function formatLeadNotificationMessage(Lead $lead): string
    {
        $message = "👤 <b>Yangi lid sizga biriktirildi</b>\n\n";
        $message .= "📝 Ism: <b>{$lead->name}</b>\n";
        $message .= "📞 Telefon: <b>{$lead->phone}</b>\n";

        if ($lead->comment) {
            $message .= "\n💬 Izoh:\n{$lead->comment}\n";
        }

        if ($lead->course) {
            $message .= "\n📚 Kurs: <b>{$lead->course->name}</b>\n";
        }

        if ($lead->chosen_days || $lead->chosen_time) {
            $message .= "\n⏰ Qulay vaqt: ";
            if ($lead->chosen_days) {
                $translatedDays = $lead->chosen_days === 'odd'
                    ? __('translation.leads.odd')
                    : ($lead->chosen_days === 'even'
                        ? __('translation.leads.even')
                        : $lead->chosen_days);
                $message .= "<b>{$translatedDays}</b>";
            }
            if ($lead->chosen_time) {
                $message .= " <b>{$lead->chosen_time}</b>";
            }
            $message .= "\n";
        }

        return $message;
    }
}
