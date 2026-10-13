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
        Schema::create('lead_boards', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('lead_collection_id');
            $table->foreign('lead_collection_id')->references('id')->on('lead_collections')->onDelete('cascade');
            $table->string('url')->nullable();
            $table->unsignedBigInteger('group_id')->nullable();
            $table->foreign('group_id')->references('id')->on('groups')->onDelete('set null');
            $table->text('success_message')->nullable();
            $table->boolean('form_comment_state')->default(true);
            $table->boolean('form_course_state')->default(true);
            $table->unsignedBigInteger('kanban_board_id')->nullable();
            $table->foreign('kanban_board_id')->references('id')->on('kanban_boards')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_boards');
    }
};
