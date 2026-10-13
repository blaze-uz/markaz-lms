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
        Schema::table('sms', function (Blueprint $table) {
            $table->string('eskiz_status')->nullable()->after('status');
            $table->timestamp('result_date')->nullable()->after('sent_at');
            $table->integer('total_price')->nullable()->after('result_date');
            $table->integer('price')->nullable()->after('total_price');
            $table->integer('parts_count')->nullable()->after('price');
            $table->boolean('is_ad')->default(false)->after('parts_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sms', function (Blueprint $table) {
            $table->dropColumn(['eskiz_status', 'result_date', 'total_price', 'price', 'parts_count', 'is_ad']);
        });
    }
};
