<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('students', 'created_by_id')) {
            Schema::table('students', function (Blueprint $table) {
                $table->unsignedBigInteger('created_by_id')->nullable();
            });

            $user = DB::table('users')->orderBy('id', 'asc')->first();

            if ($user) {
                DB::table('students')->update(['created_by_id' => $user->id]);
            }

            Schema::table('students', function (Blueprint $table) {
                $table->unsignedBigInteger('created_by_id')->nullable(false)->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('created_by_id');
        });
    }
};
