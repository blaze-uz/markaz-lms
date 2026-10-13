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
        Schema::create('branch_telegram_message', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('telegram_message_id');
            $table->timestamps();

            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->foreign('telegram_message_id')->references('id')->on('telegram_messages')->onDelete('cascade');
            $table->unique(['branch_id', 'telegram_message_id']);
        });

        // Assign all existing telegram messages to the first branch
        $branch = \App\Models\Branch::first();
        if ($branch) {
            $messages = \App\Models\TelegramMessage::all();
            foreach ($messages as $message) {
                $branch->telegramMessages()->attach($message->id);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branch_telegram_message');
    }
};
