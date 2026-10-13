<?php

namespace Tests\Feature\Livewire\Student;

use App\Livewire\Student\StudentReminders;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use ReflectionMethod;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class StudentRemindersTest extends TestCase
{
    use DatabaseTransactions;

    public function test_assignable_staff_includes_users_with_student_edit_permission(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $studentEdit = Permission::firstOrCreate(['name' => 'student-edit', 'guard_name' => 'web']);

        Role::firstOrCreate(['name' => 'Teacher', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Administratsiya', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Moliya Departamenti', 'guard_name' => 'web']);

        $teacher = User::factory()->create(['name' => 'Teacher User']);
        $admin = User::factory()->create(['name' => 'Admin User']);
        $administrator = User::factory()->create(['name' => 'Administrator User']);
        $finance = User::factory()->create(['name' => 'Finance User']);

        $teacher->assignRole('Teacher');
        $admin->assignRole('Admin');
        $administrator->assignRole('Administratsiya');
        $finance->assignRole('Moliya Departamenti');

        $teacher->givePermissionTo($studentEdit);
        $admin->givePermissionTo($studentEdit);
        $administrator->givePermissionTo($studentEdit);

        $method = new ReflectionMethod(StudentReminders::class, 'assignableStaff');
        $method->setAccessible(true);

        $staffNames = $method->invoke(new StudentReminders())
            ->pluck('name')
            ->all();

        $this->assertContains('Teacher User', $staffNames);
        $this->assertContains('Admin User', $staffNames);
        $this->assertContains('Administrator User', $staffNames);
        $this->assertNotContains('Finance User', $staffNames);
    }
}
