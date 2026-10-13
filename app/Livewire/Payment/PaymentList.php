<?php

namespace App\Livewire\Payment;

use App\Exports\PaymentsExport;
use App\Models\Group;
use App\Models\Payment;
use App\Models\PaymentType;
use App\Models\StudentGroupPayment;
use App\Models\User;
use Illuminate\Support\Carbon;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class PaymentList extends Component
{
    use WithPagination;

    public $paginationTheme = 'bootstrap';
    public $perPage = 20;
    public $search = '';
    public string $date;
    public $paymentModalItem;
    public $paymentType = 'all';
    public $creator = 'all';
    public $teacher = 'all';
    public $group = 'all';
    public $showCards = true;
    public bool $showRefunds = false;
    public $showFilters = true;
    public $studentGroupPaymentRange = 'all';

    protected $queryString = [
        'search' => ['except' => ''],
        'paymentType' => ['except' => 'all'],
        'creator' => ['except' => 'all'],
        'teacher' => ['except' => 'all'],
        'group' => ['except' => 'all'],
        'date' => ['except' => ''],
        'studentGroupPaymentRange' => ['except' => 'all']
    ];

    public function mount(): void
    {
        $this->date = request()->query('date') ?? Carbon::now()->format('Y.m.d') . " - " . Carbon::now()->format('Y.m.d');
        $this->search = request()->query('search', '');
        $this->paymentType = request()->query('paymentType', 'all');
        $this->creator = request()->query('creator', 'all');
        $this->teacher = request()->query('teacher', 'all');
        $this->group = request()->query('group', 'all');
        $this->studentGroupPaymentRange = request()->query('studentGroupPaymentRange', session('studentGroupPaymentRange', 'all'));
        $this->showCards = session('showCards', true);
        $this->showFilters = session('showFilters', true);
    }

    public function openPayment($id): void
    {
        // Clear the current item first to avoid conflicts
        $this->paymentModalItem = null;

        // Then load the new one after a brief delay
        $this->paymentModalItem = Payment::with(['student', 'student_group_payment.group.course', 'student_group_payment.group.teacher', 'payment_type', 'creator'])
            ->findOrFail($id);
    }

    public function typeFilter($value): void
    {
        $paymentType = PaymentType::where('name', $value)->first();

        $this->paymentType = $paymentType ? ($this->paymentType === $paymentType->id ? 'all' : $paymentType->id) : 'all';
        $this->resetPage();
    }

    public function refundsFilter(): void
    {
        $this->showRefunds = !$this->showRefunds;
        $this->resetPage();
    }

    public function filterByCreator($creatorId): void
    {
        $this->resetPage();
        if ($creatorId === '' || $creatorId === null || $creatorId === 'all') {
            $this->creator = 'all';
        } else if ($this->creator === $creatorId) {
            $this->creator = 'all';
        } else {
            $this->creator = $creatorId;
        }
    }

    public function filterByTeacher($teacherId): void
    {
        $this->resetPage();
        if ($teacherId === '' || $teacherId === null) {
            $this->teacher = 'all';
        } else if ($this->teacher === $teacherId) {
            $this->teacher = 'all';
        } else {
            $this->teacher = $teacherId;
        }
    }

    public function filterByGroup($groupId): void
    {
        $this->resetPage();
        if ($groupId === '' || $groupId === null || $groupId === 'all') {
            $this->group = 'all';
        } else if ($this->group === $groupId) {
            $this->group = 'all';
        } else {
            $this->group = $groupId;
        }
    }

    public function filterByStudentGroupPaymentRange($range): void
    {
        $this->resetPage();
        if ($range === '' || $range === null || $range === 'all') {
            $this->studentGroupPaymentRange = 'all';
        } else {
            $this->studentGroupPaymentRange = $range;
        }

        // Store in session to maintain state after page refresh
        session(['studentGroupPaymentRange' => $this->studentGroupPaymentRange]);

        // Notify frontend to reset select2 components if needed
        $this->dispatch('filterUpdated');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function export()
    {
        [$startDate, $endDate] = $this->getDateRange();

        $filters = [
            'search' => $this->search,
            'paymentType' => $this->paymentType,
            'creator' => $this->creator,
            'teacher' => $this->teacher,
            'group' => $this->group,
            'showRefunds' => $this->showRefunds,
            'studentGroupPaymentRange' => $this->studentGroupPaymentRange,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];

        // Export file name is date range + payments
        return Excel::download(new PaymentsExport($filters), dateFormat($startDate) . ' - ' . dateFormat($endDate) . '-payments.xlsx');
    }

    public function toggleCards()
    {
        $this->showCards = !$this->showCards;
        session(['showCards' => $this->showCards]);
    }

    public function toggleFilterVisibility()
    {
        $this->showFilters = !$this->showFilters;
        session(['showFilters' => $this->showFilters]);
    }

    public function printPayment()
    {
        try {
            if (!$this->paymentModalItem) {
                return;
            }

            $url = route('admin.payments.print', $this->paymentModalItem->id);

            // Dispatch event with a proper object structure
            $this->dispatch('openPrintWindow', $url);
        } catch (\Exception $e) {
            // Log error
            \Log::error('Error printing payment: ' . $e->getMessage());
        }
    }

    public function render()
    {
        [$startDate, $endDate] = $this->getDateRange();

        $payments = Payment::search($this->search)
            ->with(['student' => function ($query) {
                $query->withTrashed();
            }, 'creator', 'payment_type'])
            ->whereBetween('date', [$startDate, $endDate])
            ->when($this->paymentType !== 'all', fn($query) => $query->where('payment_type_id', $this->paymentType))
            ->when($this->creator !== 'all', fn($query) => $query->where('created_by', $this->creator))
            ->when($this->teacher !== 'all', fn($query) => $query->whereHas('student_group_payment.group', fn($q) => $q->where('teacher_id', $this->teacher)))
            ->when($this->group !== 'all', function ($query) {
                $query->where(function ($q) {
                    $q->where('group_id', $this->group)
                        ->orWhereHas('student_group_payment', function ($q) {
                            $q->where('group_id', $this->group);
                        });
                });
            })
            ->when($this->showRefunds, fn($query) => $query->has('refunds'))
            ->when($this->studentGroupPaymentRange !== 'all', function ($query) {
                $query->whereHas('student_group_payment', function ($q) {
                    $q->where('range', $this->studentGroupPaymentRange);
                });
            })
            ->orderByDesc('date')
            ->paginate($this->perPage);

        $paymentTypeCards = $this->getCardsData($startDate, $endDate);
        $refunds = $this->getRefunds($startDate, $endDate);
        $creators = $this->getPaymentCreators();
        $teachers = $this->getTeachers();
        $groups = $this->getGroups();
        $studentGroupPaymentRanges = $this->getStudentGroupPaymentRanges();

        return view('livewire.payment.payment-list', compact(
            'payments',
            'paymentTypeCards',
            'refunds',
            'creators',
            'teachers',
            'groups',
            'studentGroupPaymentRanges'
        ));
    }

    private function getDateRange()
    {
        $dates = explode(" - ", $this->date);
        $startDate = Carbon::parse(str_replace('.', '-', $dates[0]))->startOfDay();
        $endDate = Carbon::parse(str_replace('.', '-', $dates[1] ?? $dates[0]))->endOfDay();

        return [$startDate, $endDate];
    }

    private function getCardsData($startDate, $endDate)
    {
        $query = Payment::selectRaw('payment_type_id, sum(amount) as total_amount')
            ->with('payment_type')
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->when($this->creator !== 'all', function ($query) {
                $query->where('created_by', $this->creator);
            })
            ->when($this->teacher !== 'all', function ($query) {
                $query->whereHas('student_group_payment.group', function ($q) {
                    $q->where('teacher_id', $this->teacher);
                });
            })
            ->when($this->group !== 'all', function ($query) {
                $query->where(function ($q) {
                    $q->where('group_id', $this->group)
                        ->orWhereHas('student_group_payment', function ($q) {
                            $q->where('group_id', $this->group);
                        });
                });
            })
            ->when($this->studentGroupPaymentRange !== 'all', function ($query) {
                $query->whereHas('student_group_payment', function ($q) {
                    $q->where('range', $this->studentGroupPaymentRange);
                });
            });

        $totals = (clone $query)
            ->groupBy('payment_type_id')
            ->get()
            ->keyBy(fn($payment) => $payment->payment_type?->name ?? __('translation.unknown_payment_type'))
            ->map(fn($payment) => $payment->total_amount);

        $refunds = (clone $query)
            ->has('refunds')
            ->groupBy('payment_type_id')
            ->get()
            ->keyBy(fn($payment) => $payment->payment_type?->name ?? __('translation.unknown_payment_type'))
            ->map(fn($payment) => $payment->total_amount);

        $mergedData = [
            __('translation.payments.total') => [
                'payments' => $totals->sum() - $refunds->sum(),
                'refunds' => $refunds->sum()
            ]
        ];

        foreach ($totals as $paymentType => $amount) {
            $mergedData[$paymentType] = [
                'payments' => $amount - ($refunds[$paymentType] ?? 0),
                'refunds' => $refunds[$paymentType] ?? 0
            ];
        }

        return $mergedData;
    }

    private function getPaymentCreators()
    {
        //get payments unique creators
        return User::whereIn('id', Payment::distinct()->pluck('created_by'))->select(['id', 'name'])->get();
    }

    private function getRefunds($startDate, $endDate)
    {
        return Payment::with('refunds')
            ->whereBetween('date', [$startDate, $endDate])
            ->when($this->creator !== 'all', function ($query) {
                $query->where('created_by', $this->creator);
            })
            ->when($this->teacher !== 'all', function ($query) {
                $query->whereHas('student_group_payment.group', function ($q) {
                    $q->where('teacher_id', $this->teacher);
                });
            })
            ->when($this->group !== 'all', function ($query) {
                $query->where(function ($q) {
                    $q->where('group_id', $this->group)
                        ->orWhereHas('student_group_payment', function ($q) {
                            $q->where('group_id', $this->group);
                        });
                });
            })
            ->has('refunds')
            ->sum('amount');
    }

    private function getTeachers()
    {
        return User::role('Teacher')->select(['id', 'name'])->get();
    }

    private function getGroups()
    {
        return Group::active()->select(['id', 'name'])->get();
    }

    private function getStudentGroupPaymentRanges()
    {
        return StudentGroupPayment::whereNotNull('range')
            ->distinct()
            ->orderByRaw('range DESC')
            ->pluck('range');
    }
}
