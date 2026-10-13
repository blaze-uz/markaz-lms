<?php

namespace App\Services;

use App\Enums\AccountEnum;
use App\Enums\TransactionTypeEnum;
use App\Jobs\Student\SendPaymentSmsNotification;
use App\Models\Account;
use App\Models\Group;
use App\Models\Payment;
use App\Models\RefundPayment;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public $model = Payment::class;

    public function find($id, $with = [])
    {
        return $this->model::with($with)->findOrFail($id);
    }

    public function getRefundItem($id)
    {
        return $this->model::with([
            'student',
            'student_group_payment' => function ($query) {
                $query->withTrashed();
            },
            'payment_type',
            'transaction',
            'branch',
            'payment_discounts.transaction',
        ])->findOrFail($id);
    }

    public function refund($id)
    {
        DB::beginTransaction();
        try {
            $payment = $this->find($id, [
                'student',
                'student_group_payment' => function ($query) {
                    $query->withTrashed();
                },
                'payment_type',
                'transaction',
                'payment_discounts.transaction',
                'refunds',
            ]);

            if ($payment->refunds()->count()) {
                throw new \Exception(__('translation.payments.already_refunded'));
            }

            $studentAccount = $payment->student?->account ?? null;

            // If student or student account doesn't exist, just mark as refunded without creating transactions
            if (! $studentAccount) {
                $this->markAsRefundedWithoutTransactions($payment);
            } else {
                //create refund transaction
                $transaction = $this->createRefundTransaction($payment);

                if ($payment->student_group_payment_id) {
                    $this->refundWithStudentGroupPayment($payment);
                } else {
                    //create refund document
                    $createdBy = auth()->id() ?? User::first()->id;
                    $refund = RefundPayment::create([
                        'amount' => $payment->amount,
                        'student_group_payment_id' => $payment->student_group_payment_id,
                        'payment_type_id' => $payment->payment_type_id,
                        'created_by' => $createdBy,
                        'date' => Carbon::parse($payment->date),
                        'payment_id' => $payment->id,
                        'transaction_id' => $transaction->id,
                    ]);

                    $refund->branches()->attach(auth()->user()->current_branch_id);
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
        DB::commit();

        return $payment->student_id;
    }

    private function markAsRefundedWithoutTransactions($payment): void
    {
        // Just create a refund record without transaction
        $createdBy = auth()->id() ?? User::first()->id;
        $refund = RefundPayment::create([
            'amount' => $payment->amount,
            'student_group_payment_id' => $payment->student_group_payment_id,
            'payment_type_id' => $payment->payment_type_id,
            'created_by' => $createdBy,
            'date' => Carbon::parse($payment->date),
            'payment_id' => $payment->id,
            'transaction_id' => null,
        ]);

        $refund->branches()->attach(auth()->user()->current_branch_id);

        // If it's a group payment, update the left_amount
        if ($payment->student_group_payment_id && $payment->student_group_payment) {
            $payment->student_group_payment->update([
                'left_amount' => $payment->student_group_payment->left_amount + $payment->amount,
            ]);
        }
    }

    private function createRefundTransaction($payment)
    {
        $studentAccount = $payment->student->account ?? null;

        if (! $studentAccount) {
            throw new \Exception(__('translation.payments.student_account_not_found'));
        }

        return Transaction::create([
            'type' => TransactionTypeEnum::EXPENSE->value,
            'from_account_id' => $studentAccount->id,
            'to_account_id' => null,
            'amount' => $payment->amount,
            'created_by' => auth()->id() ?? 1,
            'description' => __('translation.payments.refund_payment_description', [
                'payment_type' => $payment->payment_type->name,
                'amount' => money($payment->amount),
                'date' => Carbon::parse($payment->date)->format('d.m.Y'),
            ]),
        ]);
    }

    private function refundWithStudentGroupPayment($payment): void
    {
        $group = $payment->student_group_payment->group()->withTrashed()->first();
        $groupAccount = $group?->account ?? null;
        $studentAccount = $payment->student->account ?? null;
        $allAmount = $payment->amount + $payment->payment_discounts->sum('amount');

        if (! $studentAccount) {
            throw new \Exception(__('translation.payments.student_account_not_found'));
        }

        $mainAccount = $this->getRefundMainAccount($payment, $group);

        foreach ($this->getGroupRefundSources($payment, $group, $groupAccount, $mainAccount, $allAmount) as $source) {
            $refund = RefundPayment::create([
                'amount' => $source['amount'],
                'student_group_payment_id' => $payment->student_group_payment_id,
                'payment_type_id' => $payment->payment_type_id,
                'created_by' => auth()->id() ?? 1,
                'date' => Carbon::parse($payment->date),
                'payment_id' => $payment->id,
            ]);

            $this->attachRefundBranch($refund);

            $transaction = Transaction::create([
                'type' => TransactionTypeEnum::EXPENSE->value,
                'from_account_id' => $source['account']->id,
                'to_account_id' => $studentAccount->id,
                'amount' => $source['amount'],
                'created_by' => auth()->id() ?? 1,
            ]);

            $refund->update([
                'transaction_id' => $transaction->id,
            ]);
        }

        //if payment has discount, create refund transaction for discount
        if ($payment->payment_discounts->count()) {
            foreach ($payment->payment_discounts as $discount) {
                //create refund document
                $discountRefund = RefundPayment::create([
                    'amount' => $discount->amount,
                    'student_group_payment_id' => $payment->student_group_payment_id,
                    'payment_type_id' => $payment->payment_type_id,
                    'created_by' => auth()->id() ?? 1,
                    'date' => now(),
                    'payment_id' => $payment->id,
                ]);

                $this->attachRefundBranch($discountRefund);

                $discountAccount = $discount->transaction?->from_account ?? null;

                if ($discountAccount) {
                    $transaction = Transaction::create([
                        'type' => TransactionTypeEnum::EXPENSE->value,
                        'from_account_id' => $studentAccount->id,
                        'to_account_id' => $discountAccount->id,
                        'amount' => $discount->amount,
                        'created_by' => auth()->id() ?? 1,
                    ]);
                    $discount->transaction_id = $transaction->id;
                    $discount->save();

                    //update refund document
                    $discountRefund->update([
                        'transaction_id' => $transaction->id,
                    ]);
                }
            }
        }

        //update student group payment left amount
        $payment->student_group_payment->update([
            'left_amount' => $payment->student_group_payment->left_amount + $allAmount,
        ]);
    }

    /**
     * @return array<int, array{account: Account, amount: int|float}>
     */
    private function getGroupRefundSources(
        Payment $payment,
        ?Group $group,
        ?Account $groupAccount,
        ?Account $mainAccount,
        int|float $amount
    ): array {
        $teacher = $group?->teacher()->withTrashed()->first();
        $teacherAccount = $teacher?->account ?? null;
        $teacherPercentage = $group?->teacher_percentage ?? $teacher?->percentage ?? 0;

        return $this->buildGroupRefundSources(
            amount: $amount,
            groupAccount: $groupAccount,
            canUseGroupAccount: (bool) ($groupAccount && $group && ! $group->deleted_at),
            teacherAccount: $teacherAccount,
            mainAccount: $mainAccount,
            teacherPercentage: $teacherPercentage,
        );
    }

    /**
     * @return array<int, array{account: Account, amount: int|float}>
     */
    private function buildGroupRefundSources(
        int|float $amount,
        ?Account $groupAccount,
        bool $canUseGroupAccount,
        ?Account $teacherAccount,
        ?Account $mainAccount,
        int|float $teacherPercentage,
    ): array {
        $sources = [];
        $remainingAmount = $amount;

        if ($canUseGroupAccount && $groupAccount) {
            $groupAmount = min($remainingAmount, max(0, $groupAccount->getBalance()));

            if ($groupAmount > 0) {
                $sources[] = [
                    'account' => $groupAccount,
                    'amount' => $groupAmount,
                ];

                $remainingAmount -= $groupAmount;
            }
        }

        if ($remainingAmount <= 0) {
            return $sources;
        }

        if (! $mainAccount) {
            throw new \Exception(__('translation.payments.main_account_not_found'));
        }

        if (! $teacherAccount) {
            $sources[] = [
                'account' => $mainAccount,
                'amount' => $remainingAmount,
            ];

            return $sources;
        }

        $mainAmount = intval($remainingAmount * (100 - $teacherPercentage) / 100);
        $teacherAmount = $remainingAmount - $mainAmount;

        if ($teacherAmount > 0) {
            $sources[] = [
                'account' => $teacherAccount,
                'amount' => $teacherAmount,
            ];
        }

        if ($mainAmount > 0) {
            $sources[] = [
                'account' => $mainAccount,
                'amount' => $mainAmount,
            ];
        }

        return $sources;
    }

    private function getRefundMainAccount(Payment $payment, ?Group $group): ?Account
    {
        return $payment->branches()->with('account')->first()?->account
            ?? $group?->branches()->with('account')->first()?->account
            ?? Account::where('account_code', AccountEnum::MAIN->value)->first();
    }

    private function attachRefundBranch(RefundPayment $refund): void
    {
        $branchId = auth()->user()?->current_branch_id;

        if ($branchId) {
            $refund->branches()->attach($branchId);
        }
    }

    public function update(array $data, $id): void
    {
        $payment = $this->find($id);
        $payment->update($data);
    }

    public function sendSms($id)
    {
        $payment = $this->find($id);
        $student = $payment->student;
        SendPaymentSmsNotification::dispatch($student, $payment, $payment?->date ?? now());
    }
}
