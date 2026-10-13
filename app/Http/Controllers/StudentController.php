<?php

namespace App\Http\Controllers;

use App\DTOs\Student\StudentDTO;
use App\DTOs\Student\StudentFilterDTO;
use App\Exports\StudentsExport;
use App\Http\Requests\Student\StudentRequest;
use App\Imports\StudentsImport;
use App\Models\StudentGroupPayment;
use App\Services\StudentService;
use App\Traits\AfterCreateActions;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class StudentController extends BaseController
{
    use AfterCreateActions;

    protected string $view_folder = 'admin.students';

    protected string $index_route = 'admin.students.index';

    protected string $create_route = 'admin.students.create';

    protected string $edit_route = 'admin.students.edit';

    protected $dto = StudentDTO::class;

    protected $filterDto = StudentFilterDTO::class;

    protected $requestClass = StudentRequest::class;

    public $serviceClass = StudentService::class;

    public function index(Request $request): View
    {
        return view('admin.students.index');
    }

    public function restore($id)
    {
        // Check if the user has permission to access this student
        $this->service->checkPermission($id);

        $this->service->restore($id);
        session()->flash('success_alert', __('translation.alert_messages.restored'));

        return redirect()->route($this->index_route);
    }

    public function show($id)
    {
        // Check if the user has permission to access this student
        $this->service->checkPermission($id);

        $item = $this->service->findWithTrashed($id, ['account', 'groupPayments']);
        $lead_data = $this->service->getLeadData($id);
        $groups = $this->service->getStudentGroups($id);
        $studentGroupPayments = $this->service->getStudentGroupPayments($id);
        $attendance = $this->service->getThisMonthAttendance($id);
        $groupLogs = $this->service->getGroupLogs($id);
        $sms = $this->service->getSms($id);
        $smsCount = $this->service->getSmsCount($id);
        $familiarsCount = $this->service->getFamiliarsCount($item->phone);
        $deleteInfo = $this->service->getDeleteInfo($id);
        $is_today_birthday = $this->service->isTodayBirthday($item->birthday);
        $debt_amount = $this->service->getDebtAmount($id);

        return view('admin.students.show', [
            'item' => $item,
            'studentGroupPayments' => $studentGroupPayments,
            'attendance' => $attendance,
            'groupLogs' => $groupLogs,
            'sms' => $sms,
            'smsCount' => $smsCount,
            'familiarsCount' => $familiarsCount,
            'deleteInfo' => $deleteInfo,
            'is_today_birthday' => $is_today_birthday,
            'groups' => $groups,
            'debt_amount' => $debt_amount,
            'lead_data' => $lead_data,
        ]);
    }

    public function create()
    {
        $groups = $this->service->getGroups();

        return view('admin.students.create', [
            'groups' => $groups,
        ]);
    }

    public function edit($id)
    {
        // Check if the user has permission to access this student
        $this->service->checkPermission($id);

        $item = $this->service->find($id);
        $groups = $this->service->getStudentGroupAttachedTimes($id);

        return view('admin.students.edit', compact('item', 'groups'));
    }

    public function store(Request $request)
    {
        $requestClass = $this->getRequestClass();

        $request = new $requestClass($request->all());
        $data = $this->dto::fromRequest($request);
        try {
            $entity = $this->service->create($data);
        } catch (\Exception $exception) {
            session()->flash('error_alert', $exception->getMessage());

            return redirect()->back();
        }
        if ($data->group_id) {
            session()->forget('_old_input');
            session()->flash('success_alert', __('translation.alert_messages.created_and_ready_to_attach'));

            return redirect()->route('admin.groups.attach-student.view', [
                'group_id' => $data->group_id,
                'student_id' => $entity->id,
            ]);
        } else {
            session()->forget('_old_input');
            session()->flash('success_alert', __('translation.alert_messages.created'));

            return redirect()->route($this->index_route);
        }
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $requestClass = $this->getRequestClass();
        $request = new $requestClass($request->all());
        $item = $this->service->find($id);

        try {
            $this->service->update($this->dto::fromRequest($request), $item);
        } catch (\Exception $exception) {
            session()->flash('error_alert', $exception->getMessage());

            return redirect()->back();
        }

        session()->forget('_old_input');
        session()->flash('success_alert', __('translation.alert_messages.updated'));

        return redirect()->route($this->index_route);
    }

    public function importView()
    {
        return view($this->getViewPath('import'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        try {
            Excel::import(new StudentsImport, $request->file('file'));
        } catch (\Exception $e) {
            session()->flash('error_alert', $e->getMessage());

            return redirect()->back();
        }

        session()->flash('success_alert', __('translation.alert_messages.imported'));

        return redirect()->route($this->index_route);
    }

    public function export(Request $request)
    {
        return Excel::download(new StudentsExport($request->all()), 'students.xlsx');
    }

    public function getStudentGroupPayment(StudentGroupPayment $studentGroupPayment)
    {
        return response()->json($studentGroupPayment);
    }

    public function transferGroupView($id)
    {
        // Check if the user has permission to access this student
        $this->service->checkPermission($id);

        $item = $this->service->find($id);

        return view('admin.students.transfer-group', ['item' => $item]);
    }

    public function destroy($id, Request $request)
    {
        // Check if the user has permission to access this student
        $this->service->checkPermission($id);

        $data = $request->validate([
            'comment' => 'required',
        ]);
        $this->service->destroy($id, $data['comment']);
        session()->flash('success_alert', __('translation.alert_messages.deleted'));

        return redirect()->route($this->index_route);
    }

    public function forceDelete($id)
    {
        // Check if the user has permission to access this student
        $this->service->checkPermission($id);

        $this->service->forceDelete($id);
        session()->flash('success_alert', __('translation.alert_messages.deleted'));

        return redirect()->route($this->index_route, ['status' => 'inactive']);
    }

    public function refreshBalance($id)
    {
        // Check if the user has permission to access this student
        $this->service->checkPermission($id);

        $this->service->refreshBalance($id);
        session()->flash('success_alert', __('translation.alert_messages.refreshed'));

        return redirect()->route('admin.students.show', $id);
    }

    public function addDebtView($id)
    {
        // Check if the user has permission to access this student
        $this->service->checkPermission($id);

        $item = $this->service->find($id);
        $groups = $this->service->getStudentGroups($id);

        return view('admin.students.add-debt', [
            'item' => $item,
            'groups' => $groups,
        ]);
    }

    public function storeDebt(Request $request, $id)
    {
        // Check if the user has permission to access this student
        $this->service->checkPermission($id);

        $data = $request->validate([
            'group_id' => 'required|exists:groups,id',
            'range' => 'required|string',
            'amount' => 'required|min:0',
        ]);

        try {
            $this->service->createDebt($id, $data);
            session()->flash('success_alert', __('translation.alert_messages.debt_added'));

            return redirect()->route('admin.students.show', $id);
        } catch (\Exception $exception) {
            session()->flash('error_alert', $exception->getMessage());

            return redirect()->back();
        }
    }

    public function getGroupRanges($groupId)
    {
        $ranges = $this->service->getGroupPaymentRanges($groupId);

        return response()->json($ranges);
    }

    public function getGroupInfo($groupId)
    {
        $group = \App\Models\Group::with(['teacher', 'course', 'account'])->findOrFail($groupId);

        return response()->json([
            'id' => $group->id,
            'name' => $group->name,
            'teacher' => $group->teacher?->name ?? '-',
            'course' => $group->course?->name ?? '-',
            'price' => $group->price,
            'payment_type' => $group->payment_type,
            'state' => $group->state,
            'lesson_count' => $group->lesson_count,
            'start_date' => $group->start_date ? dateFormat($group->start_date) : '-',
            'students_count' => $group->students()->count(),
            'teacher_percentage' => $group->teacher_percentage,
        ]);
    }
}
