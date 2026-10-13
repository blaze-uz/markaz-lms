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
        Schema::create('staff_salary_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('staff_salary_id');
            $table->unsignedBigInteger('transaction_id');
            $table->decimal('amount', 10);
            $table->string('type');
            $table->unsignedBigInteger('causer_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_salary_transactions');
    }
};
