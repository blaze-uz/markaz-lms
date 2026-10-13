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
        Schema::create('receipt_settings', function (Blueprint $table) {
            $table->id();
            $table->text('header_text')->nullable();
            $table->text('footer_text')->nullable();
            $table->string('logo_position')->default('bottom');
            $table->boolean('show_logo')->default(true);
            $table->boolean('show_header_text')->default(true);
            $table->boolean('show_payment_id')->default(true);
            $table->boolean('show_student_info')->default(true);
            $table->boolean('show_group_info')->default(true);
            $table->boolean('show_payment_type')->default(true);
            $table->boolean('show_cashier')->default(true);
            $table->boolean('show_amount')->default(true);
            $table->boolean('show_footer')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receipt_settings');
    }
};
