<?php

namespace App\DTOs\Role;

use App\DTOs\BaseDTO;
use App\Http\Requests\BaseRequest;

class RoleDTO extends BaseDTO
{
    public function __construct(
        public ?string $name,
        public ?array  $permissions,
    )
    {
    }

    public static function fromRequest(BaseRequest $request): static
    {
        return new static(
            name: $request->input('name'),
            permissions: $request->input('permissions'),
        );
    }
}
