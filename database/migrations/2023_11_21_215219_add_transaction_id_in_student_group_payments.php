<?php

use App\Models\StudentGroupPayment;
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
        Schema::create('student_group_payment_transaction', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_group_payment_id');
            $table->unsignedBigInteger('transaction_id');
            $table->timestamps();
        });

        $studentGroupPayments = \App\Models\StudentGroupPayment::all();

        foreach($studentGroupPayments as $studentGroupPayment) {
            $studentGroupPayment->update([
                'left_amount' => $studentGroupPayment->amount
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_group_payment_transaction');
    }
};
