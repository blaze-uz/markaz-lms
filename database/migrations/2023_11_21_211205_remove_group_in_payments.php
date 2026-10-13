<?php

use App\Models\Payment;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $payments = Payment::with(['student_group_payment' => function ($query) {
            $query->withTrashed();
        }])->get();

        foreach ($payments as $payment) {
            if ($payment->student_group_payment)
                $payment->update([
                    'group_id' => $payment->student_group_payment->group_id
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
