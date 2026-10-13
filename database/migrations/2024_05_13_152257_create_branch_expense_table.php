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
        Schema::create('branch_expense', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('expense_id');
            $table->timestamps();
        });

        $branch = \App\Models\Branch::first();
        $expenses = \App\Models\Expense::all();

        foreach ($expenses as $expense) {
            $branch->expenses()->attach($expense->id);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branch_expense');
    }
};
