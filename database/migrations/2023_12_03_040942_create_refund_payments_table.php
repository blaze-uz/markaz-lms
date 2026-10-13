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
        Schema::create('refund_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_group_payment_id')->nullable()->constrained('student_group_payments');
            $table->foreignId('payment_type_id')->constrained('payment_types');
            $table->integer('amount');
            $table->foreignId('created_by')->constrained('users');
            $table->date('date')->nullable();
            $table->foreignId('branch_id')->constrained('branches');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refund_payments');
    }
};
