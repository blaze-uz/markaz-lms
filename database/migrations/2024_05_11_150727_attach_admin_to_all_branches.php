<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->string('group')->nullable()->after('name');
        });

        //seed Role permission seeder
        Artisan::call('db:seed', [
            '--class' => 'RolePermissionSeeder',
            '--force' => true
        ]);

        // $admins = \App\Models\User::role('Admin')->get();
        $admins = DB::table('model_has_roles')->where('role_id', 1)->get();
        $branches = DB::table('branches')->get()->pluck('id')->toArray();

        foreach ($admins as $admin) {
            foreach ($branches as $branch) {
                DB::table('branch_user')->insert([
                    'branch_id' => $branch,
                    'user_id' => $admin->model_id
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropColumn('group');
        });
    }
};
