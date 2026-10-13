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
        Schema::create('telegram_templates', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->boolean('status')->default(false);
            $table->time('time');
            $table->timestamps();
        });

        \App\Models\TelegramTemplate::create([
            'type' => 'daily_report',
            'status' => false,
            'time' => '19:00:00',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telegram_templates');
    }
};
