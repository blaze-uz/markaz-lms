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
        Schema::table('expenses', function (Blueprint $table) {
            $table->timestamp('date')->nullable();
        });

        $expenses = \App\Models\Expense::all();
        foreach ($expenses as $expense) {
            // Check if created_at is not null before updating date
            if ($expense->created_at) {
                $expense->update([
                    'date' => $expense->created_at,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn('date');
        });
    }
};
