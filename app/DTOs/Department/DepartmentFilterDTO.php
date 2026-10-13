<?php

namespace App\DTOs\Department;

use App\DTOs\BaseDTO;

class DepartmentFilterDTO extends BaseDTO
{
    public function __construct(
        public ?string $search = null,
        public ?string $status = null,
    ) {
    }

    public static function fromRequest($request): static
    {
        return new static(
            search: $request->input('search'),
            status: $request->input('status'),
        );
    }

    public function getFilteredQuery($query): mixed
    {
        return $query
            ->when($this->search, function ($q) {
                return $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%');
            })
            ->when($this->status, function ($q) {
                return $q->where('status', $this->status);
            });
    }
}
