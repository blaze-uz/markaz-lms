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
        Schema::create('sms_batches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sms_group_id')->nullable()->index();
            $table->unsignedBigInteger('sms_template_id')->nullable()->index();
            $table->unsignedBigInteger('branch_id')->nullable()->index();
            $table->unsignedBigInteger('created_by_id')->nullable()->index();
            $table->string('source')->default('manual')->index();
            $table->string('send_sms_type')->default('student')->index();
            $table->string('recipient_scope')->default('students')->index();
            $table->string('status')->default('pending')->index();
            $table->string('template_name')->nullable();
            $table->text('template_content')->nullable();
            $table->unsignedInteger('template_character_count')->default(0);
            $table->unsignedInteger('selected_receivers_count')->default(0);
            $table->unsignedInteger('recipient_count')->default(0);
            $table->unsignedInteger('sms_parts_count')->default(0);
            $table->string('encoding')->nullable();
            $table->string('sms_type')->default('service');
            $table->unsignedInteger('min_parts_per_message')->default(0);
            $table->unsignedInteger('max_parts_per_message')->default(0);
            $table->decimal('avg_parts_per_message', 8, 1)->default(0);
            $table->timestamp('scheduled_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_batches');
    }
};
