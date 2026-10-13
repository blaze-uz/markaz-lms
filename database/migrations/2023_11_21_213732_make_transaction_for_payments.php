<?php

use App\Enums\TransactionTypeEnum;
use App\Models\Account;
use App\Models\Group;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedBigInteger('transaction_id')->nullable();
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('account_id');
            $table->unsignedBigInteger('from_account_id')->nullable();
            $table->unsignedBigInteger('to_account_id')->nullable();
        });

        DB::table('accounts')->truncate();
        DB::table('transactions')->truncate();
        DB::table('users')->update(['account_id' => null]);
        DB::table('groups')->update(['account_id' => null]);
        DB::table('students')->update(['account_id' => null]);

        Artisan::call('db:seed', [
            '--class' => 'AccountsTableSeeder',
            '--force' => true
        ]);

        $payments = \App\Models\Payment::with(['student' => function ($query) {
            $query->withTrashed();
        }])->get();
        foreach ($payments as $payment) {
            $transaction = \App\Models\Transaction::create([
                'to_account_id' => $payment->student->account->id,
                'amount' => $payment->amount,
                'type' => TransactionTypeEnum::INCOME->value,
                'description' => 'Payment for ' . $payment->student->name
            ]);

            $payment->transaction_id = $transaction->id;
            $payment->update();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('transaction_id');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('account_id')->nullable();
            $table->dropColumn('from_account_id');
            $table->dropColumn('to_account_id');
        });
    }
};
