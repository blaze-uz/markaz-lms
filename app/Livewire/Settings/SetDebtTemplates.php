<?php

namespace App\Livewire\Settings;

use App\Models\Settings;
use App\Models\SmsTemplate;
use Livewire\Component;
use Carbon\Carbon;

class SetDebtTemplates extends Component
{
    public $selectedTemplateId;
    public $selectedTemplateContent = '';
    public $lastSentAt = '';
    public $autoSendEnabled = false;
    public $sendDay = '';
    public $sendTime = '';
    public $sendType = 'all';
    public $selectedGroups = [];

    public function mount()
    {
        $debtSmsTemplate = Settings::where('key', 'debt_sms_template')->first();
        if ($debtSmsTemplate && $debtSmsTemplate->value) {
            $this->selectedTemplateId = $debtSmsTemplate->value;
            $this->loadTemplateContent();
        }

        // Load last sent time
        $this->loadLastSentTime();

        // Load auto send status
        $this->loadAutoSendStatus();

        // Load scheduled day and time
        $this->loadScheduledDayTime();

        // Load send type and selected groups
        $this->loadSendTypeAndGroups();
    }

    public function updatedSelectedTemplateId()
    {
        $this->loadTemplateContent();
    }

    private function loadTemplateContent()
    {
        if ($this->selectedTemplateId) {
            $template = SmsTemplate::find($this->selectedTemplateId);
            $this->selectedTemplateContent = $template ? trim($template->content) : '';
        } else {
            $this->selectedTemplateContent = '';
        }
    }

    private function loadLastSentTime()
    {
        $lastSentSetting = Settings::where('key', 'debt_sms_last_sent')->first();
        if ($lastSentSetting && $lastSentSetting->value) {
            $this->lastSentAt = Carbon::parse($lastSentSetting->value)->format('Y-m-d H:i:s');
        }
    }

    private function loadAutoSendStatus()
    {
        $autoSendSetting = Settings::where('key', 'debt_auto_send_enabled')->first();
        $this->autoSendEnabled = $autoSendSetting && $autoSendSetting->value == '1';
    }

    private function loadScheduledDayTime()
    {
        $scheduledDay = Settings::where('key', 'debt_sms_scheduled_day')->first();
        $scheduledTime = Settings::where('key', 'debt_sms_scheduled_time')->first();

        $this->sendDay = $scheduledDay ? $scheduledDay->value : date('j');
        $this->sendTime = $scheduledTime ? $scheduledTime->value : date('H:i');
    }

    public function updatedSendDay()
    {
        Settings::updateOrCreate(
            ['key' => 'debt_sms_scheduled_day'],
            ['value' => $this->sendDay]
        );
    }

    public function updatedSendTime()
    {
        Settings::updateOrCreate(
            ['key' => 'debt_sms_scheduled_time'],
            ['value' => $this->sendTime]
        );
    }

    private function loadSendTypeAndGroups()
    {
        $sendTypeSetting = Settings::where('key', 'debt_sms_send_type')->first();
        $this->sendType = $sendTypeSetting ? $sendTypeSetting->value : 'all';

        $selectedGroupsSetting = Settings::where('key', 'debt_sms_selected_groups')->first();
        if ($selectedGroupsSetting && $selectedGroupsSetting->value) {
            $decoded = json_decode($selectedGroupsSetting->value, true);
            $this->selectedGroups = is_array($decoded) ? $decoded : [];
        } else {
            $this->selectedGroups = [];
        }

        // If we have selected groups, ensure send type is 'custom'
        if (!empty($this->selectedGroups) && $this->sendType === 'all') {
            $this->sendType = 'custom';
            Settings::updateOrCreate(
                ['key' => 'debt_sms_send_type'],
                ['value' => 'custom']
            );
        }
    }

    public function updatedSendType()
    {
        Settings::updateOrCreate(
            ['key' => 'debt_sms_send_type'],
            ['value' => $this->sendType]
        );

        // Clear selected groups if switching to 'all'
        if ($this->sendType === 'all') {
            $this->selectedGroups = [];
            Settings::updateOrCreate(
                ['key' => 'debt_sms_selected_groups'],
                ['value' => json_encode([])]
            );
        }
    }

    public function updatedSelectedGroups()
    {
        Settings::updateOrCreate(
            ['key' => 'debt_sms_selected_groups'],
            ['value' => json_encode($this->selectedGroups)]
        );
    }

    public function toggleGroup($groupId)
    {
        if (in_array($groupId, $this->selectedGroups)) {
            // Remove group from selection
            $this->selectedGroups = array_values(array_filter($this->selectedGroups, function ($id) use ($groupId) {
                return $id != $groupId;
            }));
        } else {
            // Add group to selection
            $this->selectedGroups[] = $groupId;
        }

        // Save to database
        Settings::updateOrCreate(
            ['key' => 'debt_sms_selected_groups'],
            ['value' => json_encode($this->selectedGroups)]
        );

        // Clear groups cache to ensure fresh data
        cache()->forget('groups_with_student_count');
    }

    public function updatedAutoSendEnabled()
    {
        if ($this->autoSendEnabled) {
            // Enable auto send and record the time
            $now = Carbon::now();
            Settings::updateOrCreate(
                ['key' => 'debt_auto_send_enabled'],
                ['value' => '1']
            );
            Settings::updateOrCreate(
                ['key' => 'debt_sms_last_sent'],
                ['value' => $now->toDateTimeString()]
            );

            // Save scheduled day and time
            Settings::updateOrCreate(
                ['key' => 'debt_sms_scheduled_day'],
                ['value' => $this->sendDay ?: date('j')]
            );
            Settings::updateOrCreate(
                ['key' => 'debt_sms_scheduled_time'],
                ['value' => $this->sendTime ?: date('H:i')]
            );

            $this->lastSentAt = $now->format('Y-m-d H:i:s');
            session()->flash('success_alert', __('translation.sms.auto_send_enabled'));
        } else {
            // Disable auto send
            Settings::updateOrCreate(
                ['key' => 'debt_auto_send_enabled'],
                ['value' => '0']
            );
            session()->flash('success_alert', __('translation.sms.auto_send_disabled'));
        }
    }

    public function toggleAutoSend()
    {
        // This method is kept for backward compatibility but not used
        $this->updatedAutoSendEnabled();
    }

    public function render()
    {
        // Cache templates for 5 minutes
        $templates = cache()->remember('sms_templates_list', 300, function () {
            return SmsTemplate::orderBy('name')->select('id', 'name', 'content')->get();
        });

        $debtSmsTemplate = Settings::where('key', 'debt_sms_template')->first();

        // Only load groups if custom send type is selected
        $groups = collect();
        if ($this->sendType === 'custom') {
            // Cache groups for 2 minutes
            $groups = cache()->remember('groups_with_student_count', 120, function () {
                return \App\Models\Group::select('id', 'name')
                    ->withCount('students')
                    ->orderBy('name')
                    ->get()
                    ->map(function ($group) {
                        $group->students_count = (int) $group->students_count;
                        return $group;
                    });
            });
        }

        return view('livewire.settings.set-debt-templates', [
            'templates' => $templates,
            'debtSmsTemplate' => $debtSmsTemplate,
            'groups' => $groups
        ]);
    }
}
