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
        Schema::create('branch_kanban_task', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('kanban_task_id');
            $table->timestamps();
        });

        $branch = \App\Models\Branch::first();
        $kanbanTasks = \App\Models\KanbanTask::all();

        foreach ($kanbanTasks as $kanbanTask) {
            $branch->kanbanTasks()->attach($kanbanTask->id);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branch_kanban_task');
    }
};
