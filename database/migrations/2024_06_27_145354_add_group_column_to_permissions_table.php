<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            if (!Schema::hasColumn('permissions', 'group')) {
                $table->string('group')->nullable()->after('name');
            }
        });

        $data = [
            'update-paid-debt-amount' => 'student-update-paid-debt-amount',
            'update-group-attach-settings' => 'group-update-group-attach-settings',
            'update-debt-amount' => 'student-update-debt-amount',
        ];

        foreach ($data as $key => $item) {
            \Spatie\Permission\Models\Permission::where('name', $key)->update(['name' => $item]);
        }

        $permissions = \Spatie\Permission\Models\Permission::all();
        foreach ($permissions as $permission) {
            $name = $permission->name;
            $group = explode('-', $name)[0];
            $permission->update(['group' => $group]);

            $name = $permission->name;
            $group = $permission->group;
            $count = \Spatie\Permission\Models\Permission::where('name', $name)->where('group', $group)->count();
            if ($count > 1) {
                $permission->delete();
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

            $data = [
                'student-update-paid-debt-amount' => 'update-paid-debt-amount',
                'student-update-group-attach-settings' => 'update-group-attach-settings',
                'student-update-debt-amount' => 'update-debt-amount',
            ];

            foreach ($data as $key => $item) {
                \Spatie\Permission\Models\Permission::where('name', $key)->update(['name' => $item]);
            }
        });
    }
};
