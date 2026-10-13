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
        Schema::table('group_student', function (Blueprint $table) {
            // First remove any duplicate entries if they exist
            DB::statement('
                DELETE FROM group_student a
                USING group_student b
                WHERE a.id > b.id 
                AND a.student_id = b.student_id 
                AND a.group_id = b.group_id
            ');

            // Add unique constraint
            $table->unique(['student_id', 'group_id'], 'group_student_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('group_student', function (Blueprint $table) {
            $table->dropUnique('group_student_unique');
        });
    }
};
