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
                'group' => 'group-attach',
                'name' => 'first_month_debt_amount',
                'required' => false,
            ],
            [
                'group' => 'group-attach',
                'name' => 'group_agreed_amount',
                'required' => false,
            ],
            [
                'group' => 'group-attach',
                'name' => 'attached_at',
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
