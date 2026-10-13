<?php

namespace App\DTOs\User;

use App\DTOs\BaseDTO;
use Illuminate\Http\Request;

class UserFilterDTO extends BaseDTO
{
    public function __construct(
        public ?string $search,
        public ?string $role = null,
        public ?int $department_id = null,
    ) {
    }

    public static function fromRequest(Request $request): static
    {
        return new static(
            search: trim($request->input('search', '')),
            role: $request->input('role'),
            department_id: $request->input('department_id'),
        );
    }

    public static function fromArray(array $array): static
    {
        return new static(
            search: $array['search'] ?? '',
            role: $array['role'] ?? null,
            department_id: $array['department_id'] ?? null,
        );
    }

    public function getFilteredQuery($query): mixed
    {
        if ($this->search) {
            $query->where('name', 'ilike', "%$this->search%")
                ->whereNot('email', 'developer');
        }

        if ($this->role) {
            $query->role($this->role);
        }

        if ($this->department_id) {
            $query->whereHas('departments', function ($q) {
                $q->where('departments.id', $this->department_id);
            });
        }

        return $query->whereNot('email', 'developer');
    }
}
