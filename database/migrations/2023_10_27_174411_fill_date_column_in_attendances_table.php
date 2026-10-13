<?php

use App\Models\Attendance;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // $attendances = Attendance::whereNull('date')->get();
        $attendances = DB::table('attendances')->whereNull('date')->get();
        foreach ($attendances as $attendance) {
            $attendance->date = $attendance->created_at;
            $attendance->save();
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
