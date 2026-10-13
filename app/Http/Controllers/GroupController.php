<?php

namespace App\Http\Controllers;

use App\DTOs\Group\GroupDTO;
use App\DTOs\Group\GroupFilterDTO;
use App\Http\Requests\Group\DebtRequest;
use App\Http\Requests\Group\GroupRequest;
use App\Http\Requests\Group\PaymentNoticeRequest;
use App\Http\Requests\Group\StartGroupRequest;
use App\Http\Requests\Group\TrialLessonNoticeRequest;
use App\Models\Course;
use App\Models\Group;
use App\Services\DebtManagementService;
use App\Services\GroupService;
use App\Traits\AfterCreateActions;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GroupController extends BaseController
{
    use AfterCreateActions;

    protected string $view_folder = 'admin.groups';

    protected string $index_route = 'admin.groups.index';

    protected string $create_route = 'admin.groups.create';

    protected string $edit_route = 'admin.groups.edit';

    protected $dto = GroupDTO::class;

    protected $filterDto = GroupFilterDTO::class;

    protected $requestClass = GroupRequest::class;

    protected $serviceClass = GroupService::class;

    public function index(Request $request): View
    {
        return view('admin.groups.index');
    }

    public function show($id)
    {
        $this->service->checkPermission($id);
        $item = $this->service->find($id, ['students', 'attendanceSettings', 'branches', 'account', 'students.groupPayments' => function ($query) use ($id) {
            $query->where('group_id', $id);
        }, 'groupTimes']);

        if (! $item->account) {
            $account = $this->service->createAccount($item->name);
            $item->account_id = $account->id;
            $item->save();
        }

        $debtStudentsCount = $this->service->getDebtStudentsCount($id);
        $todayAttendanceExists = $this->service->getTodayAttendance($id);

        return view('admin.groups.show', compact('item', 'todayAttendanceExists', 'debtStudentsCount'));
    }

    public function create()
    {
        $teachers = $this->service->getTeachers();
        $courses = Course::select('id', 'name', 'price')->get();
        $branches = $this->service->getBranches();

        return view($this->getViewPath('create'), [
            'teachers' => $teachers,
            'courses' => $courses,
            'branches' => $branches,
            'selectedTeacherId' => request()->get('teacher_id'),
        ]);
    }

    public function store(Request $request)
    {
        $requestClass = $this->getRequestClass();

        $request = new $requestClass($request->all());
        try {
            $entity = $this->service->create($this->dto::fromRequest($request));
        } catch (\Exception $exception) {
            session()->flash('error_alert', $exception->getMessage());

            return redirect()->back();
        }

        session()->flash('success_alert', __('translation.alert_messages.created'));

        return redirect()->route('admin.groups.show', $entity->id);
    }

    public function edit($id)
    {
        $item = $this->service->find($id, ['branches', 'teacher', 'course', 'groupTimes', 'attendanceSettings']);
        $teachers = $this->service->getTeachers();
        $courses = Course::all();
        $branches = $this->service->getBranches();
        $attendanceSettings = $this->service->getAttendanceSettings($id);
        $attendanceNoticeStatus = $this->service->getAttendanceNoticeStatus();

        return view($this->getViewPath('edit'), [
            'item' => $item,
            'teachers' => $teachers,
            'courses' => $courses,
            'branches' => $branches,
            'attendanceSettings' => $attendanceSettings,
            'attendanceNoticeStatus' => $attendanceNoticeStatus,
            'selectedTeacherId' => null,
        ]);
    }

    public function update(Request $request, $id)
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

        session()->flash('success_alert', __('translation.alert_messages.updated'));

        return redirect()->route('admin.groups.show', $id);
    }

    public function getCalendarData($id)
    {
        $data = $this->service->getCalendarEvents($id);

        return response()->json($data);
    }

    public function addDebt($id)
    {
        $item = $this->service->find($id, ['students']);

        return view('admin.groups.add-debt', compact('item'));
    }

    public function storeDebt(DebtRequest $request, $id)
    {
        $this->service->storeDebt($request->validated(), $id);
        session()->flash('success_alert', __('translation.alert_messages.debt_added'));

        return redirect()->route('admin.groups.show', $id);
    }

    public function startGroupView($id)
    {
        $item = $this->service->find($id, ['students']);

        return view('admin.groups.start-group', compact('item'));
    }

    public function startGroup(StartGroupRequest $request, $id)
    {
        $this->service->startGroup($request->validated(), $id);
        session()->flash('success_alert', __('translation.alert_messages.group_started'));

        return redirect()->route('admin.groups.show', $id);
    }

    public function debtSettingsView($id)
    {
        $item = $this->service->find($id);

        return view('admin.groups.debt-settings', compact('item'));
    }

    public function updateBillingFrequency(Request $request, $id)
    {
        $data = $request->validate([
            'billing_frequency' => 'required|in:recurring,one_time',
        ]);

        $item = $this->service->find($id);
        $this->service->updateBillingFrequency($item, $data['billing_frequency']);

        session()->flash('success_alert', __('translation.alert_messages.updated'));

        return redirect()->route('admin.groups.debt-settings', $id);
    }

    public function updateStartDate(Request $request, $id)
    {
        $item = $this->service->find($id);
        $debtService = new DebtManagementService();

        $result = $debtService->updateGroupStartDate($item, $request);

        if ($result['success']) {
            session()->flash('success_alert', __($result['message_key']));
        } else {
            session()->flash('error_alert', 'Error updating start date: '.$result['message']);
        }

        return redirect()->route('admin.groups.show', $id);
    }

    public function deleteDebt($studentGroupPaymentId)
    {
        $response = $this->service->deleteDebt($studentGroupPaymentId);
        if ($response['success']) {
            session()->flash('success_alert', $response['message']);
        } else {
            session()->flash('error_alert', $response['message']);
        }

        return redirect()->back();
    }

    public function paymentNoticeView($id)
    {
        $item = $this->service->find($id, ['students', 'course']);
        $unpaidStudents = $this->service->getUnpaidStudents($id);
        $studyCenterName = $this->service->getStudyCenterName();

        // Get SMS configuration status
        $smsConfig = $this->getSmsConfiguration();

        // Get debt SMS template setting (primary template for payment notices)
        $debtSmsTemplateSetting = \App\Models\Settings::where('key', 'debt_sms_template')->first();
        $debtSmsTemplate = null;
        $debtSmsTemplateWarning = false;

        if ($debtSmsTemplateSetting && $debtSmsTemplateSetting->value) {
            $debtSmsTemplate = \App\Models\SmsTemplate::find($debtSmsTemplateSetting->value);
        } else {
            $debtSmsTemplateWarning = true;
        }

        // Determine if SMS can be sent
        $canSendSms = $smsConfig['can_send_sms'];
        $smsWarnings = $smsConfig['warnings'];

        // Add template warning
        if ($debtSmsTemplateWarning) {
            $smsWarnings[] = __('translation.sms.debt_sms_template_not_set');
            $canSendSms = false;
        }

        $template = (object) [
            'content' => sprintf(
                "%s kursidan sizda qarzdorlik mavjud. Kechiktirmasdan to'lashingizni so'raymiz. Hurmat bilan \"%s\" o'quv markazi",
                $item->course?->name ?? 'Ingliz tili',
                settings('name')
            ),
        ];

        return view('admin.groups.payment_notice', [
            'unpaidStudents' => $unpaidStudents,
            'item' => $item,
            'studyCenterName' => $studyCenterName,
            'template' => $template,
            'debtSmsTemplate' => $debtSmsTemplate,
            'debtSmsTemplateWarning' => $debtSmsTemplateWarning,
            'smsConfig' => $smsConfig,
            'canSendSms' => $canSendSms,
            'smsWarnings' => $smsWarnings,
        ]);
    }

    public function paymentNotice(PaymentNoticeRequest $request, $id)
    {
        info('GroupController: Payment notice request received', [
            'group_id' => $id,
            'request_data' => $request->validated(),
            'user_id' => auth()->id(),
        ]);

        // Validate SMS configuration before sending
        $smsConfig = $this->getSmsConfiguration();

        info('GroupController: SMS configuration checked', [
            'can_send_sms' => $smsConfig['can_send_sms'],
            'warnings' => $smsConfig['warnings'],
        ]);

        if (! $smsConfig['can_send_sms']) {
            $errorMessages = implode(' ', $smsConfig['warnings']);
            info('GroupController: SMS configuration invalid', [
                'error_messages' => $errorMessages,
            ]);
            session()->flash('error_alert', $errorMessages);

            return redirect()->back();
        }

        // Check if debt SMS template is configured
        $debtSmsTemplate = \App\Models\Settings::where('key', 'debt_sms_template')->first();
        if (! $debtSmsTemplate || ! $debtSmsTemplate->value) {
            info('GroupController: Debt SMS template not configured');
            session()->flash('error_alert', __('translation.sms.debt_sms_template_not_set'));

            return redirect()->back();
        }

        info('GroupController: Debt SMS template found', [
            'template_id' => $debtSmsTemplate->value,
        ]);

        try {
            $this->service->paymentNotice($request->validated(), $id);
            info('GroupController: Payment notice service completed successfully');
            session()->flash('success_alert', __('translation.alert_messages.payment_notice_sent'));
        } catch (\Exception $e) {
            info('GroupController: Payment notice service failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            session()->flash('error_alert', $e->getMessage());

            return redirect()->back();
        }

        return redirect()->route('admin.groups.show', $id);
    }

    /**
     * Get SMS configuration status and warnings
     */
    private function getSmsConfiguration(): array
    {
        $warnings = [];
        $canSendSms = true;

        // Check if SMS is enabled
        $sendSmsEnabled = settings('send_sms') === 'enable';
        if (! $sendSmsEnabled) {
            $warnings[] = __('translation.sms.disabled_description');
            $canSendSms = false;
        }

        // Check if study center name is configured
        $studyCenterName = settings('name');
        if (empty($studyCenterName)) {
            $warnings[] = __('translation.sms.study_center_name_not_configured');
        }

        // Check if phone number is configured
        $phone = settings('phone');
        if (empty($phone)) {
            $warnings[] = __('translation.sms.phone_not_configured');
        }

        return [
            'can_send_sms' => $canSendSms,
            'warnings' => $warnings,
            'send_sms_enabled' => $sendSmsEnabled,
            'study_center_name' => $studyCenterName,
            'study_center_phone' => $phone,
        ];
    }

    public function editDebt($studentGroupPaymentId)
    {
        $this->service->checkPermission($this->service->findStudentGroupPaymentGroup($studentGroupPaymentId));
        $item = $this->service->findDebt($studentGroupPaymentId);
        $activities = $this->service->getEditDebtActivities($studentGroupPaymentId);

        return view('admin.groups.debt.edit', [
            'item' => $item,
            'activities' => $activities,
        ]);
    }

    public function updateDebt(Request $request, $studentPaymentId)
    {
        DB::beginTransaction();
        try {
            $item = $this->service->findDebt($studentPaymentId);
            $minAmount = $item->payments->sum('amount');
            // Remove commas from amount and cast to int if present in the request
            if ($request->has('amount')) {
                $request->merge([
                    'amount' => (int) str_replace(',', '', $request->input('amount')),
                ]);
            }

            $data = $request->validate([
                'amount' => [
                    'required',
                    'numeric',
                    'min:'.$minAmount,
                ],
            ], [
                'amount.min' => __('translation.groups.debt_cannot_be_less_than_paid_validation', ['amount' => money($minAmount)]),
            ]);

            $this->service->updateDebt($data, $studentPaymentId);
            DB::commit();
            session()->flash('success_alert', __('translation.alert_messages.debt_updated'));

            return redirect()->route('admin.groups.show', $this->service->findStudentGroupPaymentGroup($studentPaymentId));
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error_alert', $e->getMessage());

            return redirect()->back();
        }
    }

    public function markAsNoDebt($studentPaymentId)
    {
        DB::beginTransaction();
        try {
            $item = $this->service->findDebt($studentPaymentId);

            // Update the debt record to match payments
            $totalPaid = $item->payments->sum('amount');

            $item->update([
                'amount' => $totalPaid,
                'left_amount' => 0,
            ]);

            // Log the activity
            activity()
                ->performedOn($item)
                ->withProperties([
                    'old' => [
                        'amount' => $item->amount,
                        'left_amount' => 0,
                    ],
                    'attributes' => [
                        'amount' => $totalPaid,
                        'left_amount' => 0,
                    ],
                ])
                ->log('marked_as_no_debt');

            DB::commit();
            session()->flash('success_alert', __('translation.groups.debt_marked_as_no_debt'));

            return redirect()->route('admin.groups.show', $this->service->findStudentGroupPaymentGroup($studentPaymentId));
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;

            return redirect()->back();
        }
    }

    public function trialLessonNoticeView(Group $group)
    {
        $students = $group->students;
        $date = $group->start_date ?? now();
        $months = [
            'yanvar', 'fevral', 'mart', 'aprel', 'may', 'iyun',
            'iyul', 'avgust', 'sentabr', 'oktabr', 'noyabr', 'dekabr',
        ];
        $template = (object) [
            'content' => strtr(":day-:month kuni soat :time da :course_name dan sinov darsingiz bor. Darsga vaqtida kelishingizni so'raymiz. :study_center o'quv markazi", [
                ':day' => $date->format('d'),
                ':month' => $months[$date->month - 1],
                ':time' => $date->format('H:i'),
                ':course_name' => $group->course?->name ?: 'Ingliz tili',
                ':study_center' => settings('name') ?: 'Markaz LMS',
            ]),
        ];

        return view('admin.groups.trial_lesson_notice', [
            'item' => $group,
            'students' => $students,
            'template' => $template,
        ]);
    }

    public function trialLessonNotice(TrialLessonNoticeRequest $request, $id)
    {
        $this->service->trialLessonNotice($request->validated(), $id);
        session()->flash('success_alert', __('translation.alert_messages.trial_lesson_notice_sent'));

        return redirect()->route('admin.groups.show', $id);
    }

    public function complete($id)
    {
        $this->service->complete($id);
        session()->flash('success_alert', __('translation.alert_messages.group_completed'));

        return redirect()->route('admin.groups.show', $id);
    }

    public function sendSmsView($id)
    {
        $studentIds = $this->service->getStudentIds($id);

        return redirect()->route('admin.sms.create', [
            'sendSmsIds' => json_encode($studentIds),
        ]);
    }

    public function destroy($id)
    {
        $this->service->delete($id);
        session()->flash('success_alert', __('translation.alert_messages.deleted'));

        return redirect()->route('admin.groups.index');
    }

    public function deleteAgreement($id, Request $request)
    {
        $data = $request->validate([
            'student_id' => 'required',
        ]);
        $this->service->deleteAgreement($id, $data['student_id']);
        session()->flash('success_alert', __('translation.alert_messages.agreement_deleted'));

        return redirect()->route('admin.groups.show', $id);
    }

    public function agreementView($id, $student_id)
    {
        $group = $this->service->find($id);
        $student = $this->service->findStudent($student_id);

        return view('admin.groups.agreement', compact('student', 'group'));
    }

    public function agreement(Request $request, $id, $student_id)
    {
        $data = $request->validate([
            'amount' => 'required',
            'comment' => 'nullable',
        ]);
        $this->service->agreement($id, $student_id, $data['amount'], $data['comment']);
        session()->flash('success_alert', __('translation.alert_messages.agreement_added'));

        return redirect()->route('admin.groups.show', $id);
    }
}
