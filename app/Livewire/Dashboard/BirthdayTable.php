<?php

namespace App\Livewire\Dashboard;

use App\Models\Settings;
use App\Models\SmsTemplate;
use App\Models\Student;
use Livewire\Component;

class BirthdayTable extends Component
{
    public $search = '';

    public function render()
    {
        $students = Student::whereNotNull('birthday')
            ->whereMonth('birthday', now()->month)
            ->whereDay('birthday', now()->day)
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('name', 'ilike', '%' . $this->search . '%')
                        ->orWhere('surname', 'ilike', '%' . $this->search . '%');
                });
            })
            ->select('id', 'name', 'surname', 'birthday', 'phone')
            ->paginate(20);

        $sms_templates = SmsTemplate::select('id', 'name', 'content')->orderBy('name')->get();
        $template_id = Settings::where('key', 'birthday_sms_template')->first()?->value ?? null;
        return view('livewire.dashboard.birthday-table', [
            'students' => $students,
            'sms_templates' => $sms_templates,
            'template_id' => $template_id,
        ]);
    }
}
