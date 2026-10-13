<?php

namespace App\DTOs\Branch;

use App\DTOs\BaseDTO;
use App\Http\Requests\BaseRequest;

class BranchDTO extends BaseDTO
{
    public function __construct(
        public ?string $name,
        public ?string $address,
        public ?string $phone,
        public ?string $status,
        public ?int    $account_id,
    )
    {
    }

    public static function fromRequest(BaseRequest $request): static
    {
        return new static(
            name: $request->input('name'),
            address: $request->input('address'),
            phone: $request->input('phone'),
            status: $request->input('status'),
            account_id: $request->input('account_id'),
        );
    }
}
