<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lms:install {email} {password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install Lms Application';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $user = User::create([
            'name' => 'Rahbar',
            'email' => $this->argument('email'),
            'password' => bcrypt($this->argument('password')),
        ]);

        $this->call('db:seed', [
            '--class' => 'SettingsSeeder',
            '--force' => true,
        ]);

        $this->call('db:seed', [
            '--class' => 'PaymentTypeSeeder',
            '--force' => true,
        ]);

        $this->call('db:seed', [
            '--class' => 'RolePermissionSeeder',
            '--force' => true,
        ]);

        $this->call('db:seed', [
            '--class' => 'AccountsTableSeeder',
            '--force' => true,
        ]);

        $this->call('db:seed', [
            '--class' => 'KanbanTableSeeder',
            '--force' => true,
        ]);

        $this->call('db:seed', [
            '--class' => 'KanbanBoardUrlSeeder',
            '--force' => true,
        ]);

        $this->call('db:seed', [
            '--class' => 'DefaultSmsTemplatesSeeder',
            '--force' => true,
        ]);

        $this->call('db:seed', [
            '--class' => 'SetMainBranchSeeder',
            '--force' => true,
        ]);

        $this->call('db:seed', [
            '--force' => true,
        ]);

        $this->call('storage:link', [
            '--force' => true,
        ]);

        $user->assignRole('Admin');

        $this->info('Application installed!');
    }
}
