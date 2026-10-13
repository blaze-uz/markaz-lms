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
        Schema::create('branch_student', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('student_id');
            $table->timestamps();
        });

        //assign all students to the first branch
        $branch = \App\Models\Branch::first();
        $students = \App\Models\Student::withTrashed()->get();
        foreach ($students as $student) {
            $branch->students()->attach($student->id);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branch_student');
    }
};
