<?php

namespace App\Http\Controllers;

use App\Http\Requests\Group\AttachGroupRequest;
use App\Services\GroupAttachService;
use Illuminate\Http\Request;

class GroupAttachController extends Controller
{
    public GroupAttachService $service;

    public function __construct(GroupAttachService $service)
    {
        $this->service = $service;
    }


    public function attachStudentView(Request $request)
    {
        $selectedGroupId = $request->input('group_id');
        $selectedStudentId = $request->input('student_id');
        $selectedStudentsIds = $request->input('students_ids');
        return view('admin.groups.attach-group', compact(['selectedGroupId', 'selectedStudentId', 'selectedStudentsIds']));
    }

    public function attachStudent(AttachGroupRequest $request)
    {
        try {
            $this->service->attachGroup($request->validated());
        } catch (\Throwable $th) {
            session()->flash("error_alert", $th->getMessage());
            return redirect()->back();
        }
        session()->flash("success_alert", __("translation.alert_messages.attached"));
        return redirect()->route('admin.groups.show', $request->group_id);
    }

    public function detachStudent(Request $request)
    {
        $data = $request->validate([
            'group_id' => 'required|exists:groups,id',
            'student_id' => 'required|exists:students,id',
        ]);

        $debtExists = $this->service->checkDebtExists($data);
        if ($debtExists) {
            $group = $this->service->getGroup($data);
            $student = $this->service->getStudent($data);
            $debts = $this->service->getDebts($data);
            return view('admin.groups.detach-group', compact(['group', 'student', 'debts']));
        }
        return $this->detachGroupWithoutDebts($data);
    }

    public function detachGroupWithoutDebts($data)
    {
        $this->service->detachGroupWithoutDebts($data);
        session()->flash("success_alert", __("translation.alert_messages.detached"));
        return redirect()->back();
    }

    public function detachStudentWithDebt(Request $request)
    {
        $data = $request->validate([
            'group_id' => 'required|exists:groups,id',
            'student_id' => 'required|exists:students,id',
            'debts.*' => 'sometimes',
        ]);

        $this->service->detachGroupWithDebts($data);
        session()->flash("success_alert", __("translation.alert_messages.detached"));
        return redirect()->route('admin.students.show', $request->student_id);
    }
}
