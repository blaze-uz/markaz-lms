<?php

namespace App\Livewire\Group;

use App\Models\Payment;
use App\Models\PaymentDiscount;
use App\Models\StudentGroupPayment;
use Livewire\Component;
use Livewire\WithPagination;

class DebtsTable extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $group;

    public $debt_range;

    public $payments;

    public $filter = 'unpaid';

    // properties
    public $selected_student_group_payment;

    public function mount($group): void
    {
        $this->group = $group;

        //get last debt range
        $this->debt_range = StudentGroupPayment::where('group_id', $this->group->id)->orderBy('id', 'desc')->first()?->range ?? null;
    }

    public function showPayments($studentGroupPaymentId): void
    {
        $this->payments = Payment::with(['payment_discounts', 'payment_type', 'student' => function ($query) {
            $query->withTrashed();
        }])->where('student_group_payment_id', $studentGroupPaymentId)->get();
    }

    public function showStudentGroupPayment($student_group_payment_id): void
    {
        $this->selected_student_group_payment = StudentGroupPayment::with(['student' => function ($query) {
            $query->withTrashed();
        }, 'payments.payment_discounts', 'group'])->find($student_group_payment_id);

        $this->dispatch('showStudentGroupPayment');
    }

    public function onDebtRangeChange($debt_range): void
    {
        $this->debt_range = $debt_range;
    }

    public function getPaymentStatistics()
    {
        $payments = Payment::whereHas('student_group_payment', function ($query) {
            $query->where('group_id', $this->group->id)
                ->where('range', $this->debt_range);
        })->orderBy('date')
            ->get();

        $paymentStatistics = [];

        // First collect payment type statistics
        foreach ($payments as $payment) {
            $paymentTypeName = $payment->payment_type?->name ?? 'Unknown';

            if (! isset($paymentStatistics[$paymentTypeName])) {
                $paymentStatistics[$paymentTypeName] = $payment->amount;
            } else {
                $paymentStatistics[$paymentTypeName] += $payment->amount;
            }
        }

        // Then add refunds sum at the end
        $refundSum = Payment::whereHas('student_group_payment', function ($query) {
            $query->where('group_id', $this->group->id)
                ->where('range', $this->debt_range);
        })
            ->whereHas('refunds')
            ->sum('amount');

        // Add refund sum as a separate key that will be shown last
        $paymentStatistics['__refund_sum'] = $refundSum;

        return $paymentStatistics;
    }

    public function render()
    {
        $currentBranchId = auth()->user()?->current_branch_id;

        $debt_ranges = StudentGroupPayment::query()
            ->where('group_id', $this->group->id)
            ->when($currentBranchId, function ($query, $branchId): void {
                $query->whereHas('group.branches', function ($branchQuery) use ($branchId): void {
                    $branchQuery->where('branch_id', $branchId);
                });
            })
            ->distinct('range')
            ->pluck('range');

        $studentGroupPayments = StudentGroupPayment::with(['student' => function ($query) {
            $query->withTrashed();
        }, 'payments.payment_discounts', 'group'])
            ->when($this->filter == 'unpaid', function ($query) {
                $query->where('left_amount', '>', 0);
            })
            ->when($this->filter == 'paid', function ($query) {
                $query->where('left_amount', '=', 0);
            })
            ->where('group_id', $this->group->id)
            ->where('range', $this->debt_range)
            ->orderBy('id', 'desc')
            ->paginate(15);

        // payments
        $payment_amount = Payment::whereHas('student_group_payment', function ($query) {
            $query->where('group_id', $this->group->id)
                ->where('range', $this->debt_range);
        })->whereDoesntHave('refunds')
            ->sum('amount');

        // discounts
        $discount_amount = PaymentDiscount::whereHas('payment.student_group_payment', function ($query) {
            $query->where('group_id', $this->group->id)
                ->where('range', $this->debt_range);
        })->sum('amount');

        // left amount
        $left_amount = StudentGroupPayment::whereRelation('group', 'id', $this->group->id)
            ->where('range', $this->debt_range)
            ->sum('left_amount');

        // Load payments with the necessary relationships in one query
        $groupPayments = Payment::with([
            'student' => function ($query) {
                $query->withTrashed();
            },
            'payment_type',
        ])->whereDoesntHave('refunds')
            ->whereHas('student_group_payment', function ($query) {
                $query->where('group_id', $this->group->id)
                    ->where('range', $this->debt_range);
            })->orderBy('date')
            ->get();

        $paymentStatistics = $this->getPaymentStatistics();

        return view('livewire.group.debts-table', compact([
            'debt_ranges',
            'studentGroupPayments',
            'paymentStatistics',
            'payment_amount',
            'discount_amount',
            'left_amount',
            'groupPayments',
        ]));
    }
}
