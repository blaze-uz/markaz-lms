<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fetch all students with a non-null birthday
        $students = \App\Models\Student::whereNotNull('birthday')->get();

        foreach ($students as $student) {
            // Convert the birthday to a string before validation
            $birthday = (string) $student->birthday;

            // Validate the date format YYYY-MM-DD
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthday)) {
                // If the format is not valid, set the birthday to null
                $student->birthday = null;
                $student->save();
            }
        }

        // Ensure all non-compliant dates are null
        DB::table('students')
            ->whereNotNull('birthday')
            ->whereRaw("birthday !~ '^\d{4}-\d{2}-\d{2}$'")
            ->update(['birthday' => null]);

        // Alter the birthday column to date type with explicit casting
        Schema::table('students', function (Blueprint $table) {
            DB::statement('ALTER TABLE students ALTER COLUMN birthday TYPE DATE USING NULLIF(birthday, \'\')::date');
        });
    }


    public function down(): void
    {
        // Reverse the column type change if necessary
        Schema::table('students', function (Blueprint $table) {
            $table->string('birthday')->nullable()->change();
        });
    }
};
