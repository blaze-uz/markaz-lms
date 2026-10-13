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
        Schema::create('kanban_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone');
            $table->string('comment')->nullable();
            $table->unsignedBigInteger('kanban_board_id')->nullable();
            $table->unsignedBigInteger('kanban_collection_id');
            $table->foreign('kanban_board_id')->references('id')->on('kanban_boards')->onDelete('cascade');
            $table->foreign('kanban_collection_id')->references('id')->on('kanban_collections')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kanban_tasks');
    }
};
