<?php

namespace App\DTOs\Permission;

use App\DTOs\BaseDTO;
use App\Http\Requests\BaseRequest;

class PermissionDTO extends BaseDTO
{
    public function __construct(
        public ?string $name,
    )
    {
    }

    public static function fromRequest(BaseRequest $request): static
    {
        return new static(
            name: $request->input('name'),
        );
    }
}
