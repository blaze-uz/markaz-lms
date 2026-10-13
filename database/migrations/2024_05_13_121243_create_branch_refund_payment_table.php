<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('branch_refund_payment', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('refund_payment_id');
            $table->timestamps();
        });

        $branch = \App\Models\Branch::first();
        $refundPayments = \App\Models\RefundPayment::all();
        foreach ($refundPayments as $refundPayment) {
            $branch->refundPayments()->attach($refundPayment->id);
        }

        Schema::table('refund_payments', function (Blueprint $table) {
            $table->dropColumn('branch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branch_refund_payment');

        Schema::table('refund_payments', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id');
        });
    }
};
