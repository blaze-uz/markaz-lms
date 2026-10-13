<?php

namespace App\DTOs\Group;

use App\DTOs\BaseDTO;
use Illuminate\Http\Request;

class GroupFilterDTO extends BaseDTO
{
    public function __construct(
        public ?string $search,
        public ?string $course_id = null,
        public ?string $teacher_id = null,
        public ?string $state = null,
    )
    {
    }

    public static function fromRequest(Request $request): static
    {
        return new static(
            search: trim($request->input('search', "")),
            course_id: $request->input('course_id'),
            teacher_id: $request->input('teacher_id'),
            state: $request->input('state'),
        );
    }

    public static function fromArray(array $array): static
    {
        return new static(
            search: $array['search'] ?? "",
            course_id: $array['course_id'] ?? null,
            teacher_id: $array['teacher_id'] ?? null,
            state: $array['state'] ?? null,
        );
    }

    public function getFilteredQuery($query): mixed
    {
        $query->with(['course', 'teacher']);
        if ($this->search) {
            if (auth()->user()->hasRole('Teacher')) {
                $query->where('name', 'ilike', "%$this->search%")
                    ->where('teacher_id', auth()->id());
            } else {
                $query->where('name', 'ilike', "%$this->search%")
                    ->orWhereHas('course', function ($query) {
                        $query->where('name', 'ilike', "%$this->search%");
                    })
                    ->orWhereHas('teacher', function ($query) {
                        $query->where('name', 'ilike', "%$this->search%");
                    });
            }
        }

        if ($this->course_id) {
            if (auth()->user()->hasRole('Teacher')) {
                $query->where('course_id', $this->course_id)
                    ->where('teacher_id', auth()->id());
            } else {
                $query->where('course_id', $this->course_id);
            }
        }

        if ($this->teacher_id) {
            if (auth()->user()->hasRole('Teacher')) {
                $query->where('teacher_id', auth()->id());
            } else {
                $query->where('teacher_id', $this->teacher_id);
            }
        }

        if (auth()->user()->hasRole('Teacher')) {
            $query->where('teacher_id', auth()->id());
        }

        if ($this->state) {
            $query->where('state', $this->state);
        } else {
            $query->notCompleted();
        }

        return $query;
    }
}
