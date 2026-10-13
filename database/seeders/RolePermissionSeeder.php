<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Branch;
use App\Models\Course;
use App\Models\Department;
use App\Models\Expense;
use App\Models\Group;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductOrder;
use App\Models\Sms;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Artisan::call('db:seed --force --class=MenuTableSeeder');
        Artisan::call('optimize:clear');

        // Define classes for admin permissions
        $classesForAdmin = [
            User::class,
            Course::class,
            Group::class,
            Student::class,
            Payment::class,
            Expense::class,
            Role::class,
            Permission::class,
            Account::class,
            Branch::class,
            Sms::class,
            Product::class,
            ProductOrder::class,
            Lead::class,
            Department::class,
        ];

        // Define classes for teacher permissions
        $classesForTeacher = [
            Course::class,
            Group::class,
            Student::class,
        ];

        // Get permissions for admin and teacher
        $adminPermissions = $this->getPermissions($classesForAdmin);
        $teacherPermissions = $this->getPermissions($classesForTeacher);

        // Merge additional permissions
        $additionalPermissions = $this->getAdditionalPermissions();

        // Extra permissions (assigned to all roles)
        $extraPermissions = $this->getExtraPermissions();

        // New permissions (not assigned to any role)
        $newPermissions = $this->getNewPermissions();

        // Create or find roles
        $admin = Role::findOrCreate('Admin', 'web');
        $teacher = Role::findOrCreate('Teacher', 'web');

        // Assign extra permissions to all roles
        $roles = Role::all();
        foreach ($extraPermissions as $permission) {
            // Give permission to all Roles
            foreach ($roles as $role) {
                if (! $role->hasPermissionTo($permission)) {
                    $role->givePermissionTo($permission);
                }
            }
        }

        // Add new permissions to the admin role
        foreach ($adminPermissions as $permission) {
            // If the permission is not assigned to the role, assign it
            if (! $admin->hasPermissionTo($permission)) {
                $admin->givePermissionTo($permission);
            }
        }

        // Add new permissions to the teacher role
        foreach ($teacherPermissions as $permission) {
            // If the permission is not assigned to the role, assign it
            if (! $teacher->hasPermissionTo($permission)) {
                $teacher->givePermissionTo($permission);
            }
        }

        // Add additional permissions to the admin role
        foreach ($additionalPermissions as $permission) {
            // If the permission is not assigned to the role, assign it
            if (! $admin->hasPermissionTo($permission)) {
                $admin->givePermissionTo($permission);
            }
        }

        // Ensure all permissions have groups assigned
        $allPermissions = Permission::all();
        foreach ($allPermissions as $permission) {
            if (empty($permission->group)) {
                $group = explode('-', $permission->name)[0];
                $permission->update(['group' => $group]);
            }
        }

        // Remove all unused permissions
        $allPermissions = Permission::all();
        foreach ($allPermissions as $permission) {
            if (
                ! $admin->hasPermissionTo($permission) && ! $teacher->hasPermissionTo($permission) &&
                ! in_array($permission->name, $this->getNewPermissionsNames())
            ) {
                $permission->delete();
            }
        }

        // Assign admin role to first user
        $count_admin_users = DB::table('model_has_roles')->where('role_id', 1)->count();
        if ($count_admin_users == 0) {
            $user = DB::table('users')->orderBy('id')->first();
            if ($user) {
                DB::table('model_has_roles')->insert([
                    'role_id' => 1,
                    'model_type' => 'App\Models\User',
                    'model_id' => $user->id,
                ]);
            }
        }
    }

    /**
     * Get permissions for the given classes.
     */
    protected function getPermissions(array $classes): array
    {
        $permissions = [];

        foreach ($classes as $class) {
            $name = Str::kebab(class_basename($class));

            $actions = ['create', 'store', 'update', 'delete', 'edit', 'show', 'index'];

            foreach ($actions as $action) {
                $permission = Permission::where('name', "{$name}-{$action}")->first();
                if (! $permission) {
                    $permissions[] = Permission::create([
                        'guard_name' => 'web',
                        'name' => "{$name}-{$action}",
                        'group' => $name,
                    ]);
                } else {
                    // Update group if it's missing or empty
                    if (empty($permission->group)) {
                        $permission->update(['group' => $name]);
                    }
                }
            }
        }

        // Add admin-specific permissions
        $adminSpecificPermissions = [
            'report-index',
            'report-educational',
            'report-leads',
            'report-debts',
            'report-sms',
            'student-card-records',
            'settings-index',
            'student-update-paid-debt-amount',
            'group-update-group-attach-settings',
            'settings-index',
            'student-transfer-group',
            'user-delete-penalty',
            'salary-index',
            // Add more admin permissions as needed
        ];

        foreach ($adminSpecificPermissions as $permissionName) {
            $permission = Permission::where('name', $permissionName)->first();
            $group = explode('-', $permissionName)[0];
            if (! $permission) {
                $permissions[] = Permission::create([
                    'guard_name' => 'web',
                    'name' => $permissionName,
                    'group' => $group,
                ]);
            } else {
                // Update group if it's missing or empty
                if (empty($permission->group)) {
                    $permission->update(['group' => $group]);
                }
            }
        }

        return $permissions;
    }

    /**
     * Get additional permissions.
     */
    private function getAdditionalPermissions(): array
    {
        $permission = [
            [
                'guard_name' => 'web',
                'name' => 'group-attendance',
            ],
            [
                'guard_name' => 'web',
                'name' => 'group-attach-student',
            ],
            [
                'guard_name' => 'web',
                'name' => 'group-detach-student',
            ],
            [
                'guard_name' => 'web',
                'name' => 'group-payment-notice',
            ],
            [
                'guard_name' => 'web',
                'name' => 'student-attach-group',
            ],
            [
                'guard_name' => 'web',
                'name' => 'student-detach-group',
            ],
            [
                'guard_name' => 'web',
                'name' => 'student-payment',
            ],
            [
                'guard_name' => 'web',
                'name' => 'student-send-sms',
            ],
            [
                'guard_name' => 'web',
                'name' => 'group-attach-change-debt',
            ],
            [
                'guard_name' => 'web',
                'name' => 'group-update-debt',
            ],
            [
                'guard_name' => 'web',
                'name' => 'payment-refund',
            ],
            [
                'guard_name' => 'web',
                'name' => 'group-trial-lesson-notice',
            ],
            [
                'guard_name' => 'web',
                'name' => 'lead-index',
            ],
            [
                'guard_name' => 'web',
                'name' => 'group-complete',
            ],
            [
                'guard_name' => 'web',
                'name' => 'group-attendance-edit',
            ],
            [
                'guard_name' => 'web',
                'name' => 'group-attendance-custom-date',
            ],
            [
                'guard_name' => 'web',
                'name' => 'user-payment-list',
            ],
            [
                'guard_name' => 'web',
                'name' => 'student-update-debt-amount',
            ],
            [
                'guard_name' => 'web',
                'name' => 'group-update-teacher-percentage',
            ],
            [
                'guard_name' => 'web',
                'name' => 'payment-card-data',
            ],
            [
                'guard_name' => 'web',
                'name' => 'payment-update-data',
            ],
        ];

        $additionalPermissions = [];
        foreach ($permission as $perm) {
            // add to the permissions array which is created now
            $existingPermission = Permission::where('name', $perm['name'])->first();
            $perm['group'] = explode('-', $perm['name'])[0];
            if (! $existingPermission) {
                $additionalPermissions[] = Permission::create($perm);
            } else {
                // Update group if it's missing or empty
                if (empty($existingPermission->group)) {
                    $existingPermission->update(['group' => $perm['group']]);
                }
            }
        }

        return $additionalPermissions;
    }

    /**
     * Get extra permissions assigned to all roles.
     *
     * @return array
     */
    public function getExtraPermissions()
    {
        $permissions = [
            'student-balance',
            'group-add-student-agreement',
        ];

        $additionalPermissions = [];
        foreach ($permissions as $perm) {
            // add to the permissions array which is created now
            $permission = Permission::where('name', $perm)->first();
            $group = explode('-', $perm)[0];
            if (! $permission) {
                $additionalPermissions[] = Permission::create([
                    'guard_name' => 'web',
                    'name' => $perm,
                    'group' => $group,
                ]);
            } else {
                // Update group if it's missing or empty
                if (empty($permission->group)) {
                    $permission->update(['group' => $group]);
                }
            }
        }

        return $additionalPermissions;
    }

    /**
     * Get new permissions that won't be assigned to any role.
     *
     * @return array
     */
    public function getNewPermissions()
    {
        $permissions = [
            'group-update-name-only', // New permission for teachers to only update group name
        ];

        $newPermissions = [];
        foreach ($permissions as $perm) {
            $permission = Permission::where('name', $perm)->first();
            $group = explode('-', $perm)[0];
            if (! $permission) {
                $newPermissions[] = Permission::create([
                    'guard_name' => 'web',
                    'name' => $perm,
                    'group' => $group,
                ]);
            } else {
                // Update group if it's missing or empty
                if (empty($permission->group)) {
                    $permission->update(['group' => $group]);
                }
            }
        }

        return $newPermissions;
    }

    /**
     * Get the names of new permissions that won't be assigned to any role.
     *
     * @return array
     */
    private function getNewPermissionsNames()
    {
        return [
            'group-update-name-only', // New permission for teachers to only update group name
        ];
    }
}
