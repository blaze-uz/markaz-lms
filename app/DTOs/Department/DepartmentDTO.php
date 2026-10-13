<?php

namespace App\DTOs\Department;

use App\DTOs\BaseDTO;
use App\Http\Requests\BaseRequest;

class DepartmentDTO extends BaseDTO
{
    public function __construct(
        public ?string $name,
        public ?string $description,
        public ?string $status,
        public ?array $users = null,
    ) {
    }

    public static function fromRequest(BaseRequest $request): static
    {
        return new static(
            name: $request->input('name'),
            description: $request->input('description'),
            status: $request->input('status'),
            users: $request->input('users', []),
        );
    }
}
