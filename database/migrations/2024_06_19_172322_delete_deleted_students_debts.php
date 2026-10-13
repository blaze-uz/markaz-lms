<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $students = \App\Models\Student::withTrashed()->pluck('id')->toArray();
        //check student group payments and delete if student is force deleted
        \App\Models\StudentGroupPayment::where('left_amount', '!=', '0')->whereNotIn('student_id', $students)->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
