<?php

use App\Models\User;
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
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('account_id')->nullable();
        });

        // Add soft deletes column to accounts table first
        Schema::table('accounts', function (Blueprint $table) {
            $table->softDeletes();
        });

        $accounts = \App\Models\Account::all();
        foreach ($accounts as $account) {
            if ($account->user_id) {
                User::where('id', $account->user_id)->update([
                    'account_id' => $account->id
                ]);
            }
        }

        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('account_id');
        });

        // Remove soft deletes column
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
