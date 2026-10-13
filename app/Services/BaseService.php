<?php

namespace App\Services;

use App\DTOs\BaseDTO;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BaseService
{
    const DATE_FORMAT = 'y-m-d';

    //model
    public function __construct(protected $model = null)
    {
    }

    protected function addModeQuery($query, $filter)
    {
        return $query->when($filter->mode != null, function ($q) use ($filter) {
            return $q->where('mode', $filter->mode);
        });
    }

    public function find($id, $with = [])
    {
        if (!is_numeric($id)) {
            abort(404, 'Invalid ID');
        }
        return $this->model::with($with)->findOrFail($id);
    }

    public function findWithTrashed($id, $with = [])
    {
        if (!is_numeric($id)) {
            abort(404, 'Invalid ID');
        }
        return $this->model::with($with)->withTrashed()->findOrFail($id);
    }

    /**
     * @return Builder
     */
    public function baseQuery(): Builder
    {
        return $this->model::query();
    }

    /**
     * @return Builder
     */
    public function query(): Builder
    {
        return $this->baseQuery();
    }

    public function filter(Builder $query, BaseDTO $filterDto): Builder
    {
        if ($query->hasNamedScope('filterByAuthUser')) {
            $query->filterByAuthUser();
        }

        return $filterDto->getFilteredQuery($query);
    }

    /**
     * @throws Exception
     */
    public function create($dto)
    {
        DB::beginTransaction();

        try {
            $data = $dto->all();
            $entity = $this->model::create($data);
        } catch (Exception $exception) {
            DB::rollBack();
            throw new Exception($exception->getMessage(), 400);
        }

        DB::commit();
        return $entity;
    }

    /**
     * @throws Exception
     */
    public function update($dto, $entity)
    {
        DB::beginTransaction();

        try {
            $data = $dto->all();

            $entity->update($data);
        } catch (Exception $exception) {
            DB::rollBack();
            throw new Exception($exception->getMessage(), 400);
        }

        DB::commit();

        return $entity;
    }

    public function delete($ids): void
    {
        $this->model::query()
            ->whereIn('id', $ids)
            ->each(fn($item) => $item->delete());
    }

    public function getModelName()
    {
        return Str::kebab(class_basename($this->model?->getModel() ?? Model::class));
    }
}
