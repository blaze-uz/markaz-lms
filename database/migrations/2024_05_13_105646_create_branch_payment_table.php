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
        Schema::create('branch_payment', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('payment_id');
            $table->timestamps();
        });

        //attach all payments to the first branch
        $branch = \App\Models\Branch::first();
        $payments = \App\Models\Payment::withTrashed()->get();
        foreach ($payments as $payment) {
            $branch->payments()->attach($payment->id);
        }

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('branch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branch_payment');

        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id');
        });
    }
};
