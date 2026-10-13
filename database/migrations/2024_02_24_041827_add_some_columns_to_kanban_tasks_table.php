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
        Schema::table('kanban_tasks', function (Blueprint $table) {
            $table->string('chosen_days')->nullable();
            $table->time('chosen_time')->nullable();
            $table->boolean('attend')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kanban_tasks', function (Blueprint $table) {
            $table->dropColumn('chosen_days');
            $table->dropColumn('chosen_time');
            $table->dropColumn('attend');
        });
    }
};
