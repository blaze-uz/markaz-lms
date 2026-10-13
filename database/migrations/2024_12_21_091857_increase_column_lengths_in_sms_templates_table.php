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
        Schema::table('sms_templates', function (Blueprint $table) {
            // Modify columns to use text type for longer content
            $table->text('name')->change();
            $table->text('content')->change();
            $table->text('template')->change();
            $table->text('sample')->change();
            $table->text('message')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sms_templates', function (Blueprint $table) {
            // Revert back to varchar(191)
            $table->string('name', 191)->change();
            $table->string('content', 191)->change();
            $table->string('template', 191)->change();
            $table->string('sample', 191)->change();
            $table->string('message', 191)->nullable()->change();
        });
    }
};
