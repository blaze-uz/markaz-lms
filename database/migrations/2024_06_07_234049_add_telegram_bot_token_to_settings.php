<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        \App\Models\Settings::create([
            'key' => 'telegram_bot_token',
            'value' => '',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \App\Models\Settings::where('key', 'telegram_bot_token')->delete();
    }
};
