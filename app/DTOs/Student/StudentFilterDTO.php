<?php

namespace App\DTOs\Student;

use App\DTOs\BaseDTO;
use Illuminate\Http\Request;

class StudentFilterDTO extends BaseDTO
{
    public function __construct(
        public ?string $search,
        public ?string $group_id,
        public ?string $phone,
        public ?string $status = 'active',
    )
    {
    }

    public static function fromRequest(Request $request): static
    {
        return new static(
            search: trim($request->input('search', "")),
            group_id: trim($request->input('group_id', "")),
            phone: trim($request->input('phone', "")),
            status: trim($request->input('status', "")),
        );
    }

    public static function fromArray(array $array): static
    {
        return new static(
            search: $array['search'] ?? "",
            group_id: $array['group_id'] ?? "",
            phone: $array['phone'] ?? "",
            status: $array['status'] ?? "",
        );
    }

    public function getFilteredQuery($query): mixed
    {
        if (auth()->user()->hasRole('Teacher')) {
            $groupIds = auth()->user()->groups()->pluck('id')->toArray();
            $query->where(function ($query) use ($groupIds) {
                $query->whereHas('groups', function ($teacherQuery) use ($groupIds) {
                    return $teacherQuery->whereIn('group_id', $groupIds);
                })->orWhere('created_by_id', auth()->id())
                    ->orWhere('created_at', '>=', now()->subDays(3));
            });
        }

        if ($this->status) {
            if ($this->status == 'active') {
                $query->whereNull('deleted_at');
            } elseif ($this->status == 'inactive') {
                $query->onlyTrashed();
            } elseif ($this->status == 'debt') {
                $query->whereRelation('groupPayments', 'left_amount', '!=', 0);
            }
        }

        if ($this->search) {
            if ($this->status == 'inactive')
                $query->withTrashed();
            $query->where(fn($q) => $q->whereRaw("CONCAT(name, ' ', surname) ilike ?", ["%$this->search%"])
                ->orWhereRaw("CONCAT(surname, ' ', name) ilike ?", ["%$this->search%"]));
        }

        if ($this->group_id) {
            if ($this->group_id == 'null')
                $query->whereDoesntHave('groups');
            else
                $query->whereRelation('groups', 'group_id', $this->group_id);
        }
        if ($this->phone) {
            $query->where('phone', 'ilike', "%$this->phone%");
        }
        return $query;
    }
}
