<?php

namespace App\DTOs\Account;

use App\DTOs\BaseDTO;
use App\Http\Requests\BaseRequest;

class AccountDTO extends BaseDTO
{
    public function __construct(
        public ?string $name,
        public ?int  $account_code,
    ) { }

    public static function fromRequest(BaseRequest $request): static
    {
        return new static(
            name: $request->input('name'),
            account_code: $request->input('account_code'),
        );
    }
}
