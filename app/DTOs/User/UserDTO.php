<?php

namespace App\DTOs\User;

use App\DTOs\BaseDTO;
use App\Http\Requests\BaseRequest;

class UserDTO extends BaseDTO
{
    public function __construct(
        public ?string $name,
        public ?string $email,
        public ?string $password,
        public ?string $phone,
        public ?string $birthday,
        public ?string $role,
        public ?string $avatar,
        public ?string $percentage,
        public ?int $stars,
        public ?array $branches = []
    ) {
    }

    public static function fromRequest(BaseRequest $request): static
    {
        return new static(
            name: $request->input('name'),
            email: $request->input('email'),
            password: $request->input('password'),
            phone: $request->input('phone'),
            birthday: $request->input('birthday'),
            role: $request->input('role'),
            avatar: $request->input('avatar'),
            percentage: $request->input('percentage'),
            stars: $request->input('stars'),
            branches: $request->input('branches'),
        );
    }
}
