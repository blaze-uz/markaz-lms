<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        \App\Models\ValidationSettings::updateOrCreate([
            'group' => 'attendance',
            'name' => 'homework',
        ], [
            'required' => false,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \App\Models\ValidationSettings::where('group', 'attendance')
            ->where('name', 'homework')
            ->delete();
    }
};
