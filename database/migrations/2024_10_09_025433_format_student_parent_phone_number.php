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
        $students = \App\Models\Student::whereNotNull('parent_phone')->withTrashed()->get();
        foreach ($students as $student) {
            $formattedPhone = preg_replace('/[^0-9]/', '', $student->parent_phone);
            if (strlen($formattedPhone) > 9 && substr($formattedPhone, 0, 3) == '998') {
                $formattedPhone = substr($formattedPhone, 3);
            }
            $student->parent_phone = $formattedPhone;
            $student->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
