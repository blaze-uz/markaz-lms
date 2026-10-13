<?php

use App\Models\Branch;
use App\Models\Payment;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $payments = Payment::whereNull('branch_id')->get();
        $branch = Branch::first();
        if ($payments->count() > 0) {
            $payments->each(function ($payment) use ($branch) {
                $payment->update([
                    'branch_id' => $branch->id ?? 1,
                ]);
            });
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
