<?php

namespace App\Services;

use App\Enums\AccountEnum;
use App\Models\Account;
use App\Models\Branch;
use Exception;
use Illuminate\Support\Facades\DB;

class BranchService extends BaseService
{
    public function __construct(Branch $branch)
    {
        $this->model = $branch;
    }

    public function create($dto)
    {
        DB::beginTransaction();

        try {
            $data = $dto->all();
            $entity = $this->model::create($data);
            $user = auth()->user();

            $user->branches()->attach($entity->id);
            //remove branches cache
            cache()->forget('branches');
            $this->createAccount($entity);
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

            $entity->update($data);
            //remove branches cache
            cache()->forget('branches');
            //            $this->updateAccount($entity);
        } catch (Exception $exception) {
            DB::rollBack();
            throw new Exception($exception->getMessage(), 400);
        }

        DB::commit();

        return $entity;
    }

    private function createAccount($entity): void
    {
        $account = Account::create([
            'name' => $entity->name,
            'account_code' => AccountEnum::MAIN->value,
        ]);

        $entity->update([
            'account_id' => $account->id,
        ]);
    }

    private function updateAccount($entity): void
    {
        $entity->account->update([
            'name' => $entity->name,
        ]);
    }

    public function delete($ids): void
    {
        $branch = $this->model::query()
            ->whereIn('id', $ids)
            ->each(fn ($item) => $item->delete());

        $user = auth()->user();
        $user->branches()->detach($ids);

        //remove branches cache
        cache()->forget('branches');
    }

    public function changeCurrent($data): void
    {
        $user = auth()->user();
        $branch = $user->branches()->findOrFail($data['branch_id']);

        $user->update([
            'current_branch_id' => $branch->id,
        ]);
    }
}
