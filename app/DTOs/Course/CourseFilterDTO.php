<?php

namespace App\DTOs\Course;

use App\DTOs\BaseDTO;
use Illuminate\Http\Request;

class CourseFilterDTO extends BaseDTO
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
            $query->with('groups')
                ->where('name', 'ilike', "%$this->search%")
                ->orWhere('price', 'ilike', "%$this->search%")
                ->orWhere('duration', 'ilike', "%$this->search%")
                ->orWhere('description', 'ilike', "%$this->search%");
        }

        return $query->with('groups')->orderBy('id', 'desc');
    }
}
