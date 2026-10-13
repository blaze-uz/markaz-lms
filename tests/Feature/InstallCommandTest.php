<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InstallCommandTest extends TestCase
{
    use DatabaseTransactions;

    public function test_the_installer_creates_an_admin_who_can_log_in_and_open_the_dashboard(): void
    {
        $this->artisan('lms:install', [
            'email' => 'install@example.test',
            'password' => 'install-test-password',
        ])->assertSuccessful();

        $admin = User::where('email', 'install@example.test')->firstOrFail();
        $this->assertTrue(Hash::check('install-test-password', $admin->password));
        $this->assertTrue($admin->hasRole('Admin'));
        $this->assertTrue($admin->branches()->exists());

        $this->post(route('login'), [
            'email' => $admin->email,
            'password' => 'install-test-password',
        ])->assertRedirect(route('index'));

        $this->get(route('admin.dashboard'))->assertOk();
    }
}
