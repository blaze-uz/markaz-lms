<?php

namespace App\Http\Controllers;

use App\Jobs\SendSms;
use App\Models\Lead;
use App\Models\Settings;
use App\Models\Sms;
use App\Models\SmsBatch;
use App\Models\SmsGroup;
use App\Models\SmsTemplate;
use App\Models\Student;
use App\Models\User;
use App\Rules\ScheduleSmsDateRule;
use App\Services\SmsBatchSnapshotService;
use App\Services\SmsBulkDispatchService;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class SmsController extends Controller
{
    public function __construct(
        protected SmsService $service,
        private SmsBatchSnapshotService $smsBatchSnapshotService,
    ) {
    }

    public function index()
    {
        return view('admin.sms.index');
    }

    public function batches()
    {
        return view('admin.sms.batches');
    }

    public function show($id)
    {
        $sms = Sms::with('smsable')
            ->findOrFail($id);

        return view('admin.sms.show', compact('sms'));
    }

    public function create(Request $request)
    {
        $receivers = $this->service->getReceivers($request);
        $send_sms_type = $request->sendSmsType ?? 'student';

        return view('admin.sms.create', [
            'receivers' => $receivers,
            'send_sms_type' => $send_sms_type,
        ]);
    }

    public function sendToPending(Request $request)
    {
        // Convert schedule_date to the required format if it exists
        if ($request->has('schedule_date')) {
            $request->merge([
                'schedule_date' => Carbon::parse($request->schedule_date)->format('Y-m-d H:i'),
            ]);
        }

        $request->validate([
            'ids' => 'required',
            'template_id' => 'required',
            'custom_template_content' => 'required_if:template_id,custom',
            'send_to_students' => 'sometimes|boolean',
            'send_to_parents' => 'sometimes|boolean',
            'schedule_sms_status' => 'sometimes',
            'schedule_date' => ['nullable', 'required_if:schedule_sms_status,on', 'date_format:Y-m-d H:i', new ScheduleSmsDateRule()],
        ], [
            'schedule_date.required_if' => __('translation.sms.schedule_date_required'),
            'template_id.required' => __('translation.sms.template_required'),
            'custom_template_content.required_if' => __('translation.sms.custom_template_required'),
        ]);

        $idsString = $request->input('ids')[0]; // Assuming you're using Laravel to handle the request
        $templateId = $request->input('template_id');
        $idsArray = json_decode($idsString);
        $sendSmsType = $request->input('sendSmsType');

        if ($sendSmsType == 'staff') {
            $receivers = User::whereIn('id', $idsArray)->get();
            $smsable_type = User::class;
        } elseif ($sendSmsType == 'leads') {
            $receivers = Lead::withTrashed()->whereIn('id', $idsArray)->get();
            $smsable_type = Lead::class;
        } else {
            $receivers = Student::whereIn('id', $idsArray)->get();
            $smsable_type = Student::class;
        }

        if ($templateId !== 'custom') {
            $template = SmsTemplate::find($templateId);
        } else {
            $template = new SmsTemplate([
                'content' => $request->get('custom_template_content'),
            ]);
        }

        $studyCenter = Settings::where('key', 'name')->first();
        $phone = Settings::where('key', 'phone')->first();
        if (! $template) {
            session()->flash('error_alert', __('translation.sms.no_template_selected'));

            return redirect()->back();
        }

        $sendToStudents = $request->input('send_to_students') == '1';
        $sendToParents = $request->input('send_to_parents') == '1';

        $status = $request->input('schedule_sms_status') == 'on' ? 'scheduled' : 'pending';
        $scheduledAt = $request->input('schedule_date') ?? null;
        $sms_group = SmsGroup::create();
        $smsBatchData = $this->smsBatchSnapshotService->build(
            receivers: $receivers,
            template: $template,
            sendSmsType: $sendSmsType,
            sendToStudents: $sendToStudents,
            sendToParents: $sendToParents,
            scheduledAt: $scheduledAt,
            smsGroupId: $sms_group->id,
            branchId: auth()->user()?->current_branch_id,
            createdById: auth()->id(),
            status: $status,
            source: 'manual_pending',
            studyCenterName: $studyCenter?->value ?? '',
            studyCenterPhone: $phone?->value ?? '',
        );
        $smsBatch = $smsBatchData['recipient_count'] > 0 ? SmsBatch::create($smsBatchData) : null;

        foreach ($receivers as $receiver) {
            if ($sendSmsType == 'staff') {
                $receiverName = $receiver->name;
            } elseif ($sendSmsType == 'leads') {
                $receiverName = $receiver->name;
            } else {
                $receiverName = $receiver->getFullName();
            }

            $message = str_replace(
                ['{STUDY_CENTER}', '{STUDENT}', '{TEACHER}', '{PHONE}'],
                [
                    $studyCenter->value,
                    $sendSmsType !== 'staff' ? $receiverName : '',
                    $sendSmsType === 'staff' ? $receiverName : '',
                    formattedPhone($phone->value),
                ],
                $template->content
            );

            // Send to student phone if checkbox is checked
            if ($sendToStudents && $receiver->phone) {
                $receiverPhone = unformattedPhone($receiver->phone);
                $sms = Sms::create([
                    'phone' => '998'.$receiverPhone,
                    'content' => $message,
                    'status' => $status,
                    'smsable_id' => $receiver->id,
                    'smsable_type' => $smsable_type,
                    'character_count' => strlen($message),
                    'scheduled_at' => $scheduledAt,
                    'sms_group_id' => $sms_group->id,
                    'sms_batch_id' => $smsBatch?->id,
                    'sms_template_id' => $template->id,
                    'channel' => config('sms.driver'),
                ]);
                $sms->branches()->attach(auth()->user()->current_branch_id);
            }

            // Send to parent phone if checkbox is checked
            if ($sendToParents && $receiver->parent_phone) {
                $parentPhone = unformattedPhone($receiver->parent_phone);
                $sms = Sms::create([
                    'phone' => '998'.$parentPhone,
                    'content' => $message,
                    'status' => $status,
                    'smsable_id' => $receiver->id,
                    'smsable_type' => $smsable_type,
                    'character_count' => strlen($message),
                    'scheduled_at' => $scheduledAt,
                    'sms_group_id' => $sms_group->id,
                    'sms_batch_id' => $smsBatch?->id,
                    'sms_template_id' => $template->id,
                    'channel' => config('sms.driver'),
                ]);
                $sms->branches()->attach(auth()->user()->current_branch_id);
            }
        }
        if ($receivers->count() > 0) {
            session()->flash('success_alert', __('translation.sms.sent_to_pending'));
        } else {
            session()->flash('error_alert', __('translation.sms.no_receivers'));
        }

        return redirect()->route('admin.sms.index');
    }

    public function sendSmart(Request $request)
    {
        $ids = $request->sendSmsIds;
        $sendSmsType = $request->sendSmsType;
        $sendToStudents = $request->send_to_students == '1';
        $sendToParents = $request->send_to_parents == '1';
        $scheduleDate = $request->schedule_date;
        $isScheduled = ! empty($scheduleDate);

        // Qabul qiluvchilarni olish
        if ($sendSmsType === 'staff') {
            $receivers = User::whereIn('id', $ids)->get();
            $smsable_type = User::class;
        } elseif ($sendSmsType === 'leads') {
            $receivers = Lead::withTrashed()->whereIn('id', $ids)->get();
            $smsable_type = Lead::class;
        } else {
            $receivers = Student::whereIn('id', $ids)->get();
            $smsable_type = Student::class;
        }

        // Agar schedule bo'lsa status = scheduled, bo'lmasa status = sending
        $status = $isScheduled ? 'scheduled' : 'sending';

        $sms_group = SmsGroup::create();
        $smsBatchData = $this->smsBatchSnapshotService->build(
            receivers: $receivers,
            template: $template,
            sendSmsType: $sendSmsType,
            sendToStudents: $sendToStudents,
            sendToParents: $sendToParents,
            scheduledAt: $scheduleDate,
            smsGroupId: $sms_group->id,
            branchId: auth()->user()?->current_branch_id,
            createdById: auth()->id(),
            status: $status,
            source: 'manual',
            studyCenterName: settings('name') ?? '',
            studyCenterPhone: settings('phone') ?? '',
        );
        $smsBatch = $smsBatchData['recipient_count'] > 0 ? SmsBatch::create($smsBatchData) : null;

        $sentCount = 0;
        $bulkSmsIds = [];

        foreach ($receivers as $receiver) {
            // Receiver ma'lumotlarini array formatida tayyorlash
            $receiverArray = [
                'id' => $receiver->id,
                'name' => $receiver->name ?? '',
                'surname' => $sendSmsType === 'student' ? ($receiver->surname ?? '') : '',
            ];

            // O'quvchiga yuborish
            if ($sendToStudents && $receiver->phone) {
                $message = $this->replaceTemplateKeys($template->content, $receiverArray, $sendSmsType === 'staff');
                $receiverPhone = unformattedPhone($receiver->phone);

                $sms = Sms::create([
                    'phone' => '998'.$receiverPhone,
                    'content' => $message,
                    'status' => $status,
                    'smsable_id' => $receiver->id,
                    'smsable_type' => $smsable_type,
                    'character_count' => mb_strlen($message),
                    'scheduled_at' => $scheduleDate,
                    'sms_group_id' => $sms_group->id,
                    'sms_batch_id' => $smsBatch?->id,
                    'sms_template_id' => $template->id,
                    'channel' => config('sms.driver'),
                ]);
                $sms->branches()->attach(auth()->user()->current_branch_id);

                // Agar schedule bo'lmasa, bulk job orqali yuborish
                if (! $isScheduled) {
                    $bulkSmsIds[] = $sms->id;
                }

                $sentCount++;
            }

            // Ota-onaga yuborish
            if ($sendToParents && $receiver->parent_phone) {
                $message = $this->replaceTemplateKeys($template->content, $receiverArray, $sendSmsType === 'staff');
                $parentPhone = unformattedPhone($receiver->parent_phone);

                $sms = Sms::create([
                    'phone' => '998'.$parentPhone,
                    'content' => $message,
                    'status' => $status,
                    'smsable_id' => $receiver->id,
                    'smsable_type' => $smsable_type,
                    'character_count' => mb_strlen($message),
                    'scheduled_at' => $scheduleDate,
                    'sms_group_id' => $sms_group->id,
                    'sms_batch_id' => $smsBatch?->id,
                    'sms_template_id' => $template->id,
                    'channel' => config('sms.driver'),
                ]);
                $sms->branches()->attach(auth()->user()->current_branch_id);

                // Agar schedule bo'lmasa, bulk job orqali yuborish
                if (! $isScheduled) {
                    $bulkSmsIds[] = $sms->id;
                }

                $sentCount++;
            }
        }

        if ($bulkSmsIds !== []) {
            SmsBulkDispatchService::dispatchFromSmsIds($bulkSmsIds);
        }

        if ($sentCount > 0) {
            if ($isScheduled) {
                session()->flash('success_alert', __('translation.sms.scheduled_successfully'));
            } else {
                session()->flash('success_alert', __('translation.sms.sent_to_pending'));
            }
        } else {
            session()->flash('error_alert', __('translation.sms.no_receivers'));
        }

        return redirect()->route('admin.sms.index');
    }

    public function send()
    {
        $sms = Sms::where('status', 'pending')->get();
        if (! $sms || $sms->count() == 0) {
            session()->flash('error_alert', __('translation.sms.no_pending'));

            return redirect()->route('admin.sms.index');
        }

        $smsIds = [];
        foreach ($sms as $item) {
            $smsIds[] = $item->id;
            $item->update(['status' => 'sending']);
        }
        SmsBulkDispatchService::dispatchFromSmsIds($smsIds);
        session()->flash('success_alert', __('translation.sms.successfully_sent'));

        return redirect()->route('admin.sms.index');
    }

    public function sendSingle($id)
    {
        $sms = Sms::where('id', $id)->first();
        if (! $sms) {
            session()->flash('error_alert', __('translation.sms.no_sms'));

            return redirect()->route('admin.sms.index');
        }

        $sms->update(['status' => 'sending']);
        SendSms::dispatch($sms->id, $sms->phone, $sms->content);
        session()->flash('success_alert', __('translation.sms.successfully_sent'));

        return redirect()->route('admin.sms.index');
    }

    public function sendScheduled()
    {
        $sms = Sms::where('status', 'scheduled')->get();
        if (! $sms || $sms->count() == 0) {
            session()->flash('error_alert', __('translation.sms.no_sms'));

            return redirect()->route('admin.sms.scheduled');
        }

        $smsIds = [];
        foreach ($sms as $item) {
            $smsIds[] = $item->id;
            $item->update(['status' => 'sending']);
        }
        SmsBulkDispatchService::dispatchFromSmsIds($smsIds);
        session()->flash('success_alert', __('translation.sms.successfully_sent'));

        return redirect()->route('admin.sms.index');
    }

    public function delete(Request $request)
    {
        $selectAll = $request->input('selectAll') === '1';
        $this->service->delete($request->ids, $selectAll, $request);
        session()->flash('success_alert', __('translation.alert_messages.canceled'));

        return redirect()->route('admin.sms.index');
    }

    public function returnToPending(Request $request)
    {
        $selectAll = $request->input('selectAll') === '1';
        $this->service->returnToPending($request->ids, $selectAll, $request);
        session()->flash('success_alert', __('translation.alert_messages.updated'));

        return redirect()->route('admin.sms.index');
    }

    /**
     * Replace template keys with actual values
     */
    private function replaceTemplateKeys(string $template, array $receiver, bool $isStaff = false): string
    {
        $studyCenter = Settings::where('key', 'name')->first();
        $phone = Settings::where('key', 'phone')->first();

        $receiverName = isset($receiver['name']) && isset($receiver['surname'])
            ? $receiver['name'].' '.$receiver['surname']
            : ($receiver['name'] ?? '');

        return str_replace(
            ['{STUDY_CENTER}', '{STUDENT}', '{TEACHER}', '{PHONE}'],
            [
                $studyCenter?->value ?? '',
                $receiverName,
                $isStaff ? $receiverName : '',
                $phone?->value ? formattedPhone($phone->value) : '',
            ],
            $template
        );
    }

    public function scheduled()
    {
        return view('admin.sms.scheduled');
    }
}
