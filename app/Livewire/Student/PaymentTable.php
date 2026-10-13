<?php

namespace App\Livewire\Student;

use App\Models\Payment;
use App\Models\Settings;
use App\Models\StudentGroupPayment;
use Livewire\Component;
use Livewire\WithPagination;

class PaymentTable extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $student;
    public $months;
    public $selectedMonth;
    public $payments;
    public $paymentModalItem;
    public $logo;
    public $settings;

    public function mount($student): void
    {
        $this->student = $student;
        $this->getData();
    }

    public function filter()
    {
        $this->getData();
    }

    public function openPayment($paymentId): void
    {
        $this->paymentModalItem = Payment::with(['student' => function ($query) {
            $query->withTrashed();
        }, 'creator', 'student_group_payment', 'payment_type'])->find($paymentId);
    }

    public function render()
    {
        return view('livewire.student.payment-table');
    }

    private function getData(): void
    {
        $this->months = StudentGroupPayment::where('student_id', $this->student->id)->latest()->limit(12)->get();
        $this->payments = Payment::with('student', 'creator')
            ->where('student_id', $this->student->id)
            ->latest()
            ->limit(10)
            ->get();
        if ($this->selectedMonth) {
            $this->payments = Payment::with('student', 'creator')
                ->where('student_id', $this->student->id)
                ->where('student_group_payment_id', $this->selectedMonth)
                ->latest()
                ->limit(10)
                ->get();
        }
        $logo = Settings::where('key', 'logo')->first();
        $this->logo = $logo->getFirstMediaUrl();
        $settings = Settings::all();
        foreach ($settings as $setting) {
            $this->settings[$setting->key] = $setting->value;
        }
    }
}
