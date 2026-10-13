<?php

use App\Enums\AccountEnum;
use App\Enums\TransactionTypeEnum;
use App\Models\Account;
use App\Models\Payment;
use App\Models\Transaction;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_discounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transaction_id');
            $table->unsignedBigInteger('payment_id');
            $table->unsignedBigInteger('amount');
            $table->timestamps();
        });

        $payments = Payment::with(['student' => fn($q) => $q->withTrashed()])->where('discount', '>', 0)->get();

        foreach ($payments as $payment) {
            $group = $payment->group;
            $teacherAccount = $group?->teacher->account;
            $mainAccount = Account::where('account_code', AccountEnum::MAIN)->first();

            $amount = $payment->discount;

            $teacherTransaction = $this->createTransaction($amount, $payment, $teacherAccount);
            $mainTransaction = $this->createTransaction($amount, $payment, $mainAccount);

            $payment->payment_discounts()->createMany([
                ['transaction_id' => $teacherTransaction->id, 'amount' => $amount],
                ['transaction_id' => $mainTransaction->id, 'amount' => $amount],
            ]);
        }

        // drop group_id from payments
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('discount');
            $table->dropColumn('discount_account_id');
            $table->dropColumn('group_id');
            $table->dropColumn('student_group_payment_id');
        });
    }

    public function createTransaction($amount, $payment, $account)
    {
        return Transaction::create([
            'description' => 'Chegirma',
            'amount' => $amount,
            'from_account_id' => $account?->id,
            'to_account_id' => $payment->student->account_id,
            'type' => TransactionTypeEnum::TRANSFER,
            'date' => now(),
            'created_by' => $payment->created_by,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_discounts');

        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedBigInteger('discount')->default(0);
            $table->unsignedBigInteger('discount_account_id')->nullable();
            $table->unsignedBigInteger('group_id')->nullable();
            $table->unsignedBigInteger('student_group_payment_id')->nullable();
        });
    }
};
