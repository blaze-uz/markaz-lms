<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        \Illuminate\Support\Facades\Artisan::call('app:fix-debt-bugs-command');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
