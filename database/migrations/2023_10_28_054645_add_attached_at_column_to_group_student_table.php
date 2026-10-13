<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('group_student', function (Blueprint $table) {
            $table->timestamp('attached_at')->nullable();
        });
        // Update attached_at column with created_at value
        DB::statement('UPDATE group_student SET attached_at = created_at');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('group_student', function (Blueprint $table) {
            $table->dropColumn('attached_at');
        });
    }
};
