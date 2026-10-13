<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleService extends BaseService
{
    public function __construct(Role $role)
    {
        $this->model = $role;
    }

    public function getPermissions(): \Illuminate\Database\Eloquent\Collection
    {
        return Permission::query()
            ->get()
            ->groupBy('group');
    }

    public function create($dto)
    {
        DB::beginTransaction();
        try {
            $role = $this->model::create([
                'name' => $dto->name,
                'guard_name' => 'web',
            ]);

            $role->syncPermissions($dto->permissions);

            DB::commit();
            return $role;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }

    public function update($dto, $entity)
    {
        DB::beginTransaction();
        try {
            $entity->update([
                'name' => $dto->name,
            ]);

            $entity->syncPermissions($dto->permissions);

            DB::commit();
            return $entity;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }

    public function delete($ids): void
    {
        $roles = $this->model::query()
            ->whereIn('id', $ids)
            ->get();

        if ($roles->contains('name', 'Admin') || $roles->contains('name', 'Teacher')) {
            throw new \Exception("You can't delete Admin or Teacher role.");
        }

        $this->model::query()
            ->whereIn('id', $ids)
            ->each(fn($item) => $item->delete());
    }
}
