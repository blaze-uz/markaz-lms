<?php

namespace App\DTOs\Student;

use App\DTOs\BaseDTO;
use App\Http\Requests\BaseRequest;

class StudentDTO extends BaseDTO
{
    public function __construct(
        public ?string $name,
        public ?string $surname,
        public ?string $phone,
        public ?string $phone2,
        public ?string $address,
        public ?string $parent_phone,
        public ?string $gender,
        public ?string $birthday,
        public ?string $comment,
        public ?array $attached_at,
        public ?string $group_id,
    ) {
    }

    public static function fromRequest(BaseRequest $request): static
    {
        return new static(
            name: $request->input('name'),
            surname: $request->input('surname'),
            phone: $request->input('phone'),
            phone2: $request->input('phone2'),
            address: $request->input('address'),
            parent_phone: $request->input('parent_phone'),
            gender: $request->input('gender'),
            birthday: $request->input('birthday'),
            comment: $request->input('comment'),
            attached_at: $request->input('attached_at'),
            group_id: $request->input('group_id'),
        );
    }
}
