<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('sms_templates', function (Blueprint $table) {
            $table->string('eskiz_status')->nullable();
            $table->text('message')->nullable();
            $table->text('template')->nullable();
            $table->text('sample')->nullable();
        });
    }

    public function down()
    {
        Schema::table('sms_templates', function (Blueprint $table) {
            $table->dropColumn([
                'eskiz_status',
                'message',
                'template',
                'sample'
            ]);
        });
    }
};
