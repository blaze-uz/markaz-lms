<?php

namespace App\DTOs\Course;

use App\DTOs\BaseDTO;
use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;

class CourseDto extends BaseDTO
{
    public function __construct(
        public ?string $name,
        public ?string $price,
        public ?int    $duration,
        public ?string $description,
        public ?array  $branches = null,
    ) {}

    public static function fromRequest(BaseRequest $request): static
    {
        /** @var \Illuminate\Foundation\Http\FormRequest $request */
        return new static(
            name: $request->input('name'),
            price: removeComma($request->input('price')),
            duration: $request->input('duration'),
            description: $request->input('description'),
            branches: $request->input('branches', []),
        );
    }
}
