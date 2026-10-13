<?php

namespace App\DTOs\Group;

use App\DTOs\BaseDTO;
use App\Http\Requests\BaseRequest;

class GroupDTO extends BaseDTO
{
    public function __construct(
        public ?string $name,
        public ?int $course_id,
        public ?int $teacher_id,
        public ?int $teacher_percentage,
        public ?string $teacher_percentage_switch,
        public ?string $price,
        public ?string $branch_id,
        public ?string $payment_type,
        public ?string $start_date,
        public ?string $lesson_count,
        public ?string $create_lead_board_switch,
        public ?array $monday,
        public ?array $tuesday,
        public ?array $wednesday,
        public ?array $thursday,
        public ?array $friday,
        public ?array $saturday,
        public ?array $sunday,
    ) {
    }

    public static function fromRequest(BaseRequest $request): static
    {
        return new static(
            name: $request->input('name'),
            course_id: $request->input('course_id'),
            teacher_id: $request->input('teacher_id'),
            teacher_percentage: $request->input('teacher_percentage'),
            teacher_percentage_switch: $request->input('teacher_percentage_switch'),
            price: removeComma($request->input('price')),
            branch_id: $request->input('branch_id'),
            payment_type: $request->input('payment_type'),
            start_date: $request->input('start_date'),
            lesson_count: $request->input('lesson_count'),
            create_lead_board_switch: $request->input('create_lead_board_switch'),
            monday: [
                'start' => $request->input('monday.start'),
                'end' => $request->input('monday.end'),
                'room_id' => $request->input('monday.room_id'),
            ],
            tuesday: [
                'start' => $request->input('tuesday.start'),
                'end' => $request->input('tuesday.end'),
                'room_id' => $request->input('tuesday.room_id'),
            ],
            wednesday: [
                'start' => $request->input('wednesday.start'),
                'end' => $request->input('wednesday.end'),
                'room_id' => $request->input('wednesday.room_id'),
            ],
            thursday: [
                'start' => $request->input('thursday.start'),
                'end' => $request->input('thursday.end'),
                'room_id' => $request->input('thursday.room_id'),
            ],
            friday: [
                'start' => $request->input('friday.start'),
                'end' => $request->input('friday.end'),
                'room_id' => $request->input('friday.room_id'),
            ],
            saturday: [
                'start' => $request->input('saturday.start'),
                'end' => $request->input('saturday.end'),
                'room_id' => $request->input('saturday.room_id'),
            ],
            sunday: [
                'start' => $request->input('sunday.start'),
                'end' => $request->input('sunday.end'),
                'room_id' => $request->input('sunday.room_id'),
            ],
        );
    }
}
