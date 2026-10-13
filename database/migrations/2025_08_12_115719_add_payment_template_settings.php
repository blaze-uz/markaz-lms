<?php

use App\Models\Settings;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Remove old payment notice template setting if exists
        Settings::where('key', 'payment_notice_template')->delete();

        // Add payment SMS template setting to settings table
        $paymentSmsTemplate = [
            'key' => 'payment_sms_template',
            'value' => null,
        ];

        Settings::create($paymentSmsTemplate);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Settings::where('key', 'payment_notice_template')->delete();
    }
};
