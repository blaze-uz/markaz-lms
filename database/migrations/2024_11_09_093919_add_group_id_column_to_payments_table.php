<?php

use App\Models\Payment;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedBigInteger('group_id')->nullable()->after('student_group_payment_id');
        });

        $payments = Payment::with('student_group_payment')->get();
        foreach ($payments as $payment) {
            if (!$payment->student_group_payment) {
                continue;
            }
            $payment->update([
                'group_id' => $payment->student_group_payment?->group_id,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('group_id');
        });
    }
};
