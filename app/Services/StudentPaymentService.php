<?php

namespace App\Services;

use App\Enums\AccountEnum;
use App\Enums\TransactionTypeEnum;
use App\Jobs\SendPaymentTelegramNotification;
use App\Jobs\Student\SendPaymentSmsNotification;
use App\Jobs\TransferGroupAmount;
use App\Models\Account;
use App\Models\Branch;
use App\Models\Payment;
use App\Models\Student;
use App\Models\StudentGroupPayment;
use App\Models\TextSuggestion;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StudentPaymentService
{
    public function getStudent($student_id)
    {
        return Student::with('account')->findOrFail($student_id);
    }

    public function getGroups()
    {
        return Student::getGroups();
    }

    public function getUnpaidMonths($id, $method = null): \Illuminate\Database\Eloquent\Collection|array
    {
        return StudentGroupPayment::with(['student', 'group' => function ($query) {
            $query->withTrashed()->with(['teacher']);
        }])
            ->notPaid()
            ->where('student_id', $id)
            ->get();
    }

    /**
     * @throws \Exception
     */
    public function payment(array $data, $student_id)
    {
        DB::beginTransaction();
        try {
            $totalAmount = removeComma($data['amount']);
            if ($data['discount_amount']) {
                $totalAmount += removeComma($data['discount_amount']);
            }

            $studentUnPaidMonth = null;

            //update student group payment
            if (isset($data['month']) && $data['month']) {
                $studentUnPaidMonth = StudentGroupPayment::findOrFail($data['month']);
                $debtAmount = removeComma($data['debt_amount']);
                $paidAmount = $studentUnPaidMonth->amount - $studentUnPaidMonth->left_amount;

                if ($debtAmount < $paidAmount + $totalAmount) {
                    session()->flash("error_alert", "Amount is less than possible editable amount");
                    //return validation error and stop the process
                    return [
                        'status' => 'error',
                        'message' => "Qarzdorlikni o'zgartirish to'g'ri amalga oshirilmadi!",
                    ];
                } else {
                    $studentUnPaidMonth->amount = $debtAmount;
                    $studentUnPaidMonth->left_amount = $debtAmount - $paidAmount;
                    $studentUnPaidMonth->save();
                }

                if ($studentUnPaidMonth->left_amount < $totalAmount) {
                    session()->flash("error_alert", "Amount is greater than left amount");
                    //return validation error and stop the process
                    return [
                        'status' => 'error',
                        'message' => "To'lov summa qarz summasidan ko'p kiritilgan!",
                    ];
                }
            }

            //update student balance
            $student = Student::findOrFail($student_id);

            // create payment and transaction
            $payment = $this->createPaymentWithTransaction($data, $student);

            if ($studentUnPaidMonth) {
                // create discounts
                $discount_amount = removeComma($data['discount_amount']);
                if ($discount_amount) {
                    $this->createDiscounts($payment, $discount_amount, $data['discount_account_id'], $studentUnPaidMonth);
                }

                // create transaction for student_group_payment
                $this->createStudentGroupPaymentTransaction($student, $data, $studentUnPaidMonth);

                //send telegram notification to teacher
                $telegram_data = [
                    'student_name' => $student->getFullName(),
                    'payment_type' => $payment->payment_type?->name ?? '-',
                    'amount' => money($payment->amount),
                    'teacher_id' => $studentUnPaidMonth->group?->teacher_id ?? 0,
                    'date' => $data['date'] ?? now(),
                ];

                $this->sendPaymentNotificationTelegram($telegram_data);
            }

            DB::commit();

            return [
                'status' => 'success',
                'payment_id' => $payment->id,
            ];
        } catch (\Exception $e) {
            logger()->error('Failed to create payment with transaction: '.$e->getMessage());
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }

    public function createPaymentWithTransaction($data, $student)
    {
        $amount = removeComma($data['amount']);

        DB::beginTransaction();

        try {
            // Create payment record
            $payment = Payment::create([
                'student_id' => $student->id,
                'payment_type_id' => $data['payment_type_id'],
                'amount' => $amount,
                'created_by' => auth()->id(),
                'date' => $data['date'] ?? now(),
                'student_group_payment_id' => $data['month'] ?? null,
                'comment' => $data['comment'] ?? null,
            ]);

            // Get the branch ID
            $branchId = auth()->check() ? auth()->user()->current_branch_id : Branch::first()->id;
            $payment->branches()->attach($branchId);

            // Create transaction record
            $payment->transaction()->create([
                'to_account_id' => $student->account->id,
                'amount' => $amount,
                'type' => TransactionTypeEnum::INCOME->value,
                'date' => $data['date'] ?? now(),
                'created_by' => auth()->id(),
            ]);

            DB::commit();

            // Load relationships for SMS notification
            $payment->load(['group.teacher', 'studentGroupPayment.group.teacher']);

            // Send payment notification
            $this->sendPaymentNotification($student, $payment, $data['date']);

            return $payment;
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error('Failed to create payment with transaction: ' . $e->getMessage());
            // Handle the exception, possibly logging it or rethrowing
            throw new \Exception('Failed to create payment with transaction: ' . $e->getMessage());
        }
    }

    public function createStudentGroupPaymentTransaction($student, $data, $student_group_payment)
    {
        $student_balance = $student->account->getItemBalance();
        $got_amount = min($student_balance, $student_group_payment->left_amount);

        $student_group_payment->transactions()->create([
            'from_account_id' => $student->account->id,
            'to_account_id' => $student_group_payment->group->account->id,
            'amount' => $got_amount,
            'type' => TransactionTypeEnum::TRANSFER->value,
            'date' => $data['date'] ?? now(),
            'created_by' => auth()->user()->id,
        ]);

        $student_group_payment->left_amount = $student_group_payment->left_amount - $got_amount;
        $student_group_payment->save();

        TransferGroupAmount::dispatch($student_group_payment->group->id, auth()->user()->id);
    }

    public function createDiscounts($payment, $discount_amount, $discount_account_id, $student_group_payment)
    {
        $teacher = $student_group_payment->group->teacher;
        $teacher_account = $teacher?->account;

        if ($discount_account_id == 'both') {
            if (! $teacher || ! $teacher->percentage) {
                throw new \Exception("Teacher or teacher's percentage not found");
            }

            if (! $teacher_account) {
                throw new \Exception("Teacher's account not found");
            }

            // Calculate amounts based on agreement percentage
            $teacher_percentage = $teacher->percentage;
            $main_percentage = 100 - $teacher_percentage;

            $teacher_amount = intval(($discount_amount * $teacher_percentage) / 100);
            // Get student's branch account instead of global main account
            $studentBranch = $payment->student->branches()->first();
            $main_account = $studentBranch ? $studentBranch->account : Account::where('account_code', AccountEnum::MAIN->value)->first();

            if (! $main_account) {
                throw new \Exception('Main account not found');
            }

            $main_amount = $discount_amount - $teacher_amount;

            // Create transactions for both accounts
            if ($main_amount > 0) {
                $this->createDiscount($payment, $main_amount, $main_account, "Chegirma (Asosiy hisob - {$main_percentage}%)");
            }
            if ($teacher_amount > 0) {
                $this->createDiscount($payment, $teacher_amount, $teacher_account, "Chegirma (O'qituvchi - {$teacher_percentage}%)");
            }
        } else {
            $account = Account::findOrFail($discount_account_id);
            if ($discount_amount > 0) {
                $description = match (true) {
                    $account->account_code == AccountEnum::MAIN->value => 'Chegirma (Asosiy hisob)',
                    $account->account_code == AccountEnum::STAFF->value && $teacher_account && $account->id === $teacher_account->id => "Chegirma (O'qituvchi)",
                    default => 'Chegirma (Boshqa hisob)'
                };
                $this->createDiscount($payment, $discount_amount, $account, $description);
            }
        }
    }

    public function createDiscount($payment, $discount_amount, $discount_account, $description = 'Chegirma')
    {
        // Create transaction for the discount
        $transaction = Transaction::create([
            'description' => $description,
            'amount' => $discount_amount,
            'from_account_id' => $discount_account->id,
            'to_account_id' => $payment->student->account_id,
            'type' => TransactionTypeEnum::TRANSFER->value,
            'date' => $payment->date ?? now(),
            'created_by' => $payment->created_by,
        ]);

        // Link the discount to the payment
        $payment->payment_discounts()->create([
            'transaction_id' => $transaction->id,
            'payment_id' => $payment->id,
            'amount' => $discount_amount,
        ]);

        // Log the discount creation
        Log::info('Discount created', [
            'payment_id' => $payment->id,
            'amount' => $discount_amount,
            'account' => $discount_account->account_code,
            'description' => $description,
        ]);
    }

    public function getPayment($payment_id)
    {
        return Payment::with('studentGroupPayment', 'group.teacher')->findOrFail($payment_id);
    }

    public function paymentUpdate(array $data, $payment_id)
    {
        $payment = Payment::findOrFail($payment_id);

        $totalAmount = removeComma($data['amount']);

        if ($data['discount_amount']) {
            $totalAmount += removeComma($data['discount_amount']);
        }
        DB::beginTransaction();
        try {
            match ($data['method_type']) {
                'updateSavedPayment' => $this->updateSavedPayment($payment, $data),
                'updateDraftPayment' => $this->updateDraftPayment($payment, $data, $totalAmount),
            };

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error_alert', $e->getMessage());
            throw new \Exception($e->getMessage());
        }

        return [
            'status' => 'success',
            'student_id' => $payment->student_id,
        ];
    }

    private function updateSavedPayment($payment, array $data): void
    {
        $payment->update([
            'payment_type_id' => $data['payment_type_id'],
        ]);
    }

    private function updateStudentGroupPayment($payment, $totalAmount, $data)
    {
        return [
            'status' => 'error',
            'message' => "To'lovni yangilash mumkin emas!",
        ];
        $studentUnPaidMonth = StudentGroupPayment::findOrFail($data['month']);

        if ($studentUnPaidMonth->left_amount < $totalAmount) {
            session()->flash('error_alert', 'Amount is greater than left amount');

            //return validation error and stop the process
            return [
                'status' => 'error',
                'message' => "To'lov summasi qarz summasidan ko'p kiritilgan!",
            ];
        }

        $studentUnPaidMonth->left_amount = $studentUnPaidMonth->left_amount - $totalAmount;
        $studentUnPaidMonth->save();
    }

    private function updateUpdatedStudentBalance($payment, ?string $totalAmount): void
    {
        $student = $payment->student;
        $student->balance -= ($payment->amount + $payment->discount);
        $student->balance += $totalAmount;
        $student->save();
    }

    private function updatePaymentRecord($payment, array $data): void
    {
        $studentGroupPayment = null;
        if (isset($data['month']) && $data['month']) {
            $studentGroupPayment = StudentGroupPayment::findOrFail($data['month']);
        }
        $payment->update([
            'group_id' => $studentGroupPayment?->group_id,
            'payment_type_id' => $data['payment_type_id'],
            'amount' => removeComma($data['amount']),
            'discount' => removeComma($data['discount_amount']),
            'discount_account_id' => $data['discount_account_id'] ?? null,
            'student_group_payment_id' => $data['month'] ?? null,
            'date' => $data['date'] ?? now(),
        ]);
    }

    private function updateDraftPayment($payment, array $data, $totalAmount): void
    {
        if (isset($data['month']) && $data['month']) {
            $this->updateStudentGroupPayment($payment, $totalAmount, $data);
        }

        //update student balance
        $this->updateUpdatedStudentBalance($payment, $totalAmount);

        //update payment record
        $this->updatePaymentRecord($payment, $data);
    }

    public function paymentDelete($payment_id)
    {
        return [
            'status' => 'error',
            'message' => "To'lovni yangilash mumkin emas!",
        ];
        $payment = Payment::findOrFail($payment_id);
        DB::beginTransaction();
        try {

            //update student balance
            $this->updateStudentBalance($payment);

            //delete payment record
            $payment->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error_alert', $e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    private function updateStudentBalance($payment): void
    {
        $student = $payment->student;
        $student->balance -= ($payment->amount + $payment->discount);
        $student->save();
    }

    private function sendPaymentNotification($student, $payment, $date = null): void
    {
        $studentData = [
            'id' => $student->id,
            'name' => $student->getFullName(),
            'phone' => $student->phone,
            'branch_id' => $student->branches()->first()->id,
        ];

        SendPaymentSmsNotification::dispatch($studentData, $payment, $date);
    }

    public function getBranches()
    {
        return Branch::select('id', 'name')->get();
    }

    public function getPaymentTextSuggestions()
    {
        return TextSuggestion::where('type', 'payment')
            ->where('user_id', auth()->id())
            ->get();
    }

    public function getCommentTextSuggestions()
    {
        return TextSuggestion::where('type', 'payment_comment')
            ->where('user_id', auth()->id())
            ->get();
    }

    public function sendPaymentNotificationTelegram($data)
    {
        try {
            $telegram_bot_token = settings('telegram_bot_token');

            if ($telegram_bot_token) {
                SendPaymentTelegramNotification::dispatch($data); // Small delay to ensure transaction is committed
            }
        } catch (\Exception $e) {
            // Log the error but don't stop the payment process
            Log::error('Failed to send telegram notification: ' . $e->getMessage(), [
                'teacher_id' => $data['teacher_id'],
                'student_name' => $data['student_name']
            ]);
        }
    }

    public function getDiscountAccounts()
    {
        return Account::active()->where('account_code', AccountEnum::STAFF->value)
            ->get();
    }

    public function getMainAccount()
    {
        // Get current user's branch account instead of global main account
        if (auth()->check()) {
            $currentBranch = auth()->user()->branches()->where('branch_id', auth()->user()->current_branch_id)->first();
            if ($currentBranch && $currentBranch->account) {
                return $currentBranch->account;
            }
        }

        // Fallback to global main account if branch account not found
        return Account::where('account_code', AccountEnum::MAIN->value)->first();
    }
}
