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
        //get refunds and save payment id that payment->id
        $payments = Payment::whereNotNull('refund_id')->get();
        foreach ($payments as $payment) {
            $payment->refund->update([
                'payment_id' => $payment->id,
            ]);
        }
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('refund_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedBigInteger('refund_id')->nullable();
        });
    }
};
