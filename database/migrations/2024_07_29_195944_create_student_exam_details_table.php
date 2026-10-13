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
        Schema::create('student_exam_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_exam_id');
            $table->unsignedBigInteger('exam_detail_id');
            $table->integer('score');
            $table->integer('correct_answers');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_exam_details');
    }
};
