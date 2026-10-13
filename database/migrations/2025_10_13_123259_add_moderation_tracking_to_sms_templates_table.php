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
        Schema::table('sms_templates', function (Blueprint $table) {
            $table->timestamp('sent_to_moderation_at')->nullable();
            $table->timestamp('moderation_response_at')->nullable();
            $table->text('moderation_response')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sms_templates', function (Blueprint $table) {
            $table->dropColumn(['sent_to_moderation_at', 'moderation_response_at', 'moderation_response']);
        });
    }
};
