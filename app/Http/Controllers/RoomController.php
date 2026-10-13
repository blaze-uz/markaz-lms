<?php

namespace App\Http\Controllers;

use App\Services\RoomService;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public RoomService $service;

    public function __construct()
    {
        $this->service = new RoomService();
    }

    public function index()
    {
        $items = $this->service->index();
        return view('admin.settings.rooms.index', compact('items'));
    }

    public function create()
    {
        return view('admin.settings.rooms.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'capacity' => 'sometimes',
            'status' => 'sometimes',
        ]);
        $this->service->store($data);
        session()->flash('success_alert', __('translation.alert_messages.room_added'));
        return redirect()->route('admin.rooms.index');
    }

    public function edit($id)
    {
        $item = $this->service->find($id);
        return view('admin.settings.rooms.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'required',
            'capacity' => 'sometimes',
            'status' => 'sometimes',
        ]);
        $this->service->update($data, $id);
        session()->flash('success_alert', __('translation.alert_messages.room_updated'));
        return redirect()->route('admin.rooms.index');
    }

    public function destroy($id)
    {
        $this->service->destroy($id);
        session()->flash('success_alert', __('translation.alert_messages.room_deleted'));
        return redirect()->route('admin.rooms.index');
    }
}
