<?php

namespace App\Services;

use App\Models\Course;
use App\Models\KanbanBoard;
use App\Models\KanbanCollection;
use App\Models\KanbanTask;

class LeadApiService
{
    /**
     * Get courses for the lead
     *
     * @return array
     */
    public function getBoards()
    {
        return KanbanBoard::orderBy('id')->get();
    }

    public function storeLead(array $data)
    {
        $kanbanCollection = KanbanCollection::first();

        if (!$kanbanCollection) {
            return [
                'success' => false,
                'message' => 'No kanban collection found',
            ];
        }

        $kanbanBoard = null;
        if (isset($data['board_id'])) {
            $kanbanBoard = KanbanBoard::findOrFail($data['board_id']);
        }

        $kanbanTask = KanbanTask::create([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'comment' => $data['comment'],
            'kanban_board_id' => $kanbanBoard?->id ?? null,
            'kanban_collection_id' => $kanbanBoard->kanban_collection_id ?? $kanbanCollection->id,
            'chosen_days' => $data['chosen_days'] ?? null,
            'chosen_time' => $data['chosen_time'] ?? null,
            'course_id' => $data['course_id'] ?? null,
            'teacher_ids' => $data['teachers'] ?? null,
        ]);

        $branch = $kanbanBoard->branches()->first();
        if (!$branch) {
            return [
                'success' => false,
                'message' => 'No branch found for the kanban board',
            ];
        }
        $kanbanTask->branches()->attach($branch->id);

        if (isset($data['teacher_ids'])) {
            foreach ($data['teacher_ids'] as $teacher) {
                if ($teacher != null)
                    $kanbanTask->teachers()->attach($teacher);
            }
        }

        return [
            'success' => true,
            'message' => 'Lead created successfully',
            'lead' => $kanbanTask,
        ];
    }
}
