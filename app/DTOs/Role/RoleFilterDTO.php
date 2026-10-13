<?php

namespace App\DTOs\Role;

use App\DTOs\BaseDTO;
use Illuminate\Http\Request;

class RoleFilterDTO extends BaseDTO
{
    public function __construct(
        public ?string $search,
    )
    {
    }

    public static function fromRequest(Request $request): static
    {
        return new static(
            search: trim($request->input('search', ""))
        );
    }

    public static function fromArray(array $array): static
    {
        return new static(
            search: $array['search'] ?? "",
        );
    }

    public function getFilteredQuery($query): mixed
    {
        if ($this->search) {
            $query->where('name', 'ilike', "%$this->search%");
        }

        return $query->orderBy('name');
    }
}
