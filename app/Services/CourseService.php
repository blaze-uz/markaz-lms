<?php

namespace App\Services;

use App\Models\Course;
use Exception;
use Illuminate\Support\Facades\DB;

class CourseService extends BaseService
{
    public function __construct(Course $course)
    {
        $this->model = $course;
    }

    public function create($dto)
    {
        DB::beginTransaction();

        try {
            $data = $dto->all();
            $branches = $data['branches'] ?? [];
            unset($data['branches']); // Remove branches from main data

            $entity = $this->model::create($data);

            // Handle branch assignments
            $this->syncBranches($entity, $branches);

            // Clear courses cache
            cache()->forget('courses');
        } catch (Exception $exception) {
            DB::rollBack();
            throw new Exception($exception->getMessage(), 400);
        }

        DB::commit();
        return $entity;
    }

    public function update($dto, $entity)
    {
        DB::beginTransaction();

        try {
            $data = $dto->all();
            $branches = $data['branches'] ?? [];
            unset($data['branches']); // Remove branches from main data

            $entity->update($data);

            // Handle branch assignments
            $this->syncBranches($entity, $branches);

            // Clear courses cache
            cache()->forget('courses');
        } catch (Exception $exception) {
            DB::rollBack();
            throw new Exception($exception->getMessage(), 400);
        }

        DB::commit();
        return $entity;
    }

    public function destroy($id): void
    {
        $course = $this->find($id);

        // Set the course_id of the associated kanban_tasks to NULL
        $course->kanbanTasks()->update(['course_id' => null]);

        // Detach from all branches
        $course->branches()->detach();

        $course->delete();

        // Clear courses cache
        cache()->forget('courses');
    }

    /**
     * Sync course branches based on provided branch IDs
     */
    private function syncBranches($entity, array $branchIds): void
    {
        // If no branches specified, assign to current user's branch
        if (empty($branchIds)) {
            $user = auth()->user();
            if ($user && $user->current_branch_id) {
                $branchIds = [$user->current_branch_id];
            }
        }

        // Sync branches
        $entity->branches()->sync($branchIds);
    }
}
