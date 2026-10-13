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
        Schema::table('refund_payments', function (Blueprint $table) {
            $table->unsignedBigInteger('student_group_payment_id')->nullable()->change();
            $table->unsignedBigInteger('payment_type_id')->nullable()->change();
            $table->unsignedBigInteger('created_by')->nullable()->change();

            //unlink reference
            $table->dropForeign(['student_group_payment_id']);
            $table->dropForeign(['payment_type_id']);
            $table->dropForeign(['created_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
