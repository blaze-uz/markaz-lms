<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class AddAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lms:add-admin {login} {password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $login = $this->argument('login');
        $password = $this->argument('password');

        $user = User::create([
            'name' => 'Developer',
            'email' => $login,
            'password' => bcrypt($password),
        ]);

        $user->assignRole('Admin');

        $this->info('Admin created');
    }
}
