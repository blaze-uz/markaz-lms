<?php

namespace App\Http\Controllers;

use App\Http\Requests\Group\GroupAttendanceSettingsRequest;
use App\Services\GroupAttendanceSettingsService;
use Illuminate\Support\Facades\DB;

class GroupAttendanceSettingsController extends Controller
{
    public $service;

    public function __construct()
    {
        $this->service = new GroupAttendanceSettingsService();
    }

    public function update(GroupAttendanceSettingsRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $this->service->update($id, $request->validated());
            DB::commit();
            session()->flash('success_alert', __('translation.alert_messages.updated'));
            return redirect()->route('admin.groups.show', $id);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Something went wrong');
        }
    }
}
