<?php

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
        $deleted_students = DB::table('students')->whereNotNull('deleted_at')->get();
        foreach ($deleted_students as $student) {
            DB::table('accounts')->where('id', $student->account_id)->update(['status' => 'inactive']);
        }

        $deleted_users = DB::table('users')->whereNotNull('deleted_at')->get();
        foreach ($deleted_users as $user) {
            DB::table('accounts')->where('id', $user->account_id)->update(['status' => 'inactive']);
        }

        $deleted_groups = DB::table('groups')->whereNotNull('deleted_at')->get();
        foreach ($deleted_groups as $group) {
            DB::table('accounts')->where('id', $group->account_id)->update(['status' => 'inactive']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {}
};
