<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE payments ADD COLUMN date TIMESTAMP');
        $payments = \App\Models\Payment::all();
        foreach ($payments as $payment) {
            $payment->update([
                'date' => $payment->created_at,
            ]);
        }
        DB::statement('ALTER TABLE payments ALTER COLUMN date SET NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE payments DROP COLUMN date');
    }
};
