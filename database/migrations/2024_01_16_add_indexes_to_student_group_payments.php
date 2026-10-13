<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('student_group_payments', function (Blueprint $table) {
            // Add indexes for frequently queried columns
            $table->index('left_amount');
            $table->index('created_at');
            $table->index(['group_id', 'student_id']);
            $table->index('range');
        });
    }

    public function down()
    {
        Schema::table('student_group_payments', function (Blueprint $table) {
            $table->dropIndex(['left_amount']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['group_id', 'student_id']);
            $table->dropIndex(['range']);
        });
    }
};
