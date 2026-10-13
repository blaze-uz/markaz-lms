<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('student_calls', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('user_id'); // who made the call
            $table->enum('status', ['answered', 'not_answered', 'busy', 'no_answer'])->default('not_answered');
            $table->text('description')->nullable();
            $table->string('phone')->nullable(); // which phone number was called
            $table->integer('duration')->nullable(); // call duration in seconds
            $table->timestamp('called_at')->useCurrent();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->index(['student_id', 'called_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_calls');
    }
};
