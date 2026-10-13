<?php

namespace App\Services;

use App\Models\Department;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class DepartmentService extends BaseService
{
    public function __construct(Department $department)
    {
        $this->model = $department;
    }

    public function query(): Builder
    {
        return parent::query()->with('users');
    }

    public function create($dto)
    {
        DB::beginTransaction();

        try {
            $data = $dto->all();
            $users = $data['users'] ?? [];
            unset($data['users']); // Remove users from main data

            $entity = $this->model::create($data);

            // Handle user assignments
            $this->syncUsers($entity, $users);
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
            $users = $data['users'] ?? [];
            unset($data['users']); // Remove users from main data

            $entity->update($data);

            // Handle user assignments
            $this->syncUsers($entity, $users);
        } catch (Exception $exception) {
            DB::rollBack();
            throw new Exception($exception->getMessage(), 400);
        }

        DB::commit();

        return $entity;
    }

    public function destroy($id): void
    {
        $department = $this->find($id);

        // Detach from all users
        $department->users()->detach();

        $department->delete();
    }

    /**
     * Sync department users based on provided user IDs
     */
    private function syncUsers($entity, array $userIds): void
    {
        // Sync users
        $entity->users()->sync($userIds);
    }
}
