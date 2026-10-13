<?php

namespace App\Livewire;

use App\Models\Payment;
use Illuminate\Support\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class TeacherStudentPaymentsTable extends Component
{
    use WithPagination;

    public $paginationTheme = 'bootstrap';
    public $teacher;
    public $date;
    public $search = '';

    public function mount($teacher)
    {
        $this->teacher = $teacher;
        $this->date = Carbon::now()->format('Y.m.d') . " - " . Carbon::now()->format('Y.m.d');
    }

    public function render()
    {
        [$startDate, $endDate] = $this->getDateRange();
        $groupIds = $this->teacher->groups()->pluck('id');
        $payments = Payment::search($this->search)->with(['student' => function ($query) {
            $query->withTrashed();
        }, 'creator', 'payment_type', 'student_group_payment.group'])
            ->whereIn('group_id', $groupIds)
            ->whereBetween('date', [$startDate, $endDate])
            ->paginate(20);
        return view('livewire.teacher-student-payments-table', [
            'payments' => $payments
        ]);
    }

    private function getDateRange()
    {
        $dates = explode(" - ", $this->date);
        $startDate = Carbon::parse(str_replace('.', '-', $dates[0]))->startOfDay();
        $endDate = Carbon::parse(str_replace('.', '-', $dates[1] ?? $dates[0]))->endOfDay();

        return [$startDate, $endDate];
    }
}
