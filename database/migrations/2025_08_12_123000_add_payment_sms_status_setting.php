<?php

use App\Models\Settings;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Settings::updateOrCreate(
            ['key' => 'payment_sms_status'],
            ['value' => '0'] // Default to off
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Settings::where('key', 'payment_sms_status')->delete();
    }
};
