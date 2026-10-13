<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $review_time = rand(1, 3) . ':' . rand(0, 5) . '0';
        $daily_review_command_run_time = \App\Models\Settings::where('key', 'daily_review_command_run_time')->first();
        if ($daily_review_command_run_time) {
            $daily_review_command_run_time->update(['value' => $review_time]);
        } else {
            \App\Models\Settings::create(['key' => 'daily_review_command_run_time', 'value' => $review_time]);
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
