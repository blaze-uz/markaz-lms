<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Room;

class RoomService
{
    public function index()
    {
        return Room::orderBy('name')->paginate(20);
    }

    public function store(array $data): void
    {
        $room = Room::create($data);
        $room->branches()->attach(auth()->user()->current_branch_id);
    }

    public function find($id)
    {
        return Room::findOrFail($id);
    }

    public function update(array $data, $id): void
    {
        $room = $this->find($id);
        $room->update($data);
    }

    public function destroy($id): void
    {
        $room = $this->find($id);
        $room->delete();
    }

    public function getBranches(): \Illuminate\Database\Eloquent\Collection
    {
        return Branch::all();
    }
}
