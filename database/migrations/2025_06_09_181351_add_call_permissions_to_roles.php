<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create call permissions
        $permissions = [
            'student-call-add',
            'student-call-view',
            'student-call-edit',
            'student-call-delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign permissions to roles
        $adminRole = Role::where('name', 'Admin')->first();
        $teacherRole = Role::where('name', 'Teacher')->first();

        if ($adminRole) {
            $adminRole->givePermissionTo($permissions);
        }

        if ($teacherRole) {
            $teacherRole->givePermissionTo(['student-call-add', 'student-call-view']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove permissions from roles
        $permissions = [
            'student-call-add',
            'student-call-view',
            'student-call-edit',
            'student-call-delete',
        ];

        $roles = Role::whereIn('name', ['Admin', 'Teacher'])->get();

        foreach ($roles as $role) {
            $role->revokePermissionTo($permissions);
        }

        // Delete the permissions
        Permission::whereIn('name', $permissions)->delete();
    }
};
