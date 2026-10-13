<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $fields = [
            [
                'group' => 'attendance',
                'name' => 'comment',
                'required' => false,
            ],
            [
                'group' => 'attendance',
                'name' => 'score',
                'required' => false,
            ],
        ];

        foreach ($fields as $field) {
            \App\Models\ValidationSettings::updateOrCreate($field);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
