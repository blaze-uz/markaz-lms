<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AfterDeployCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lms:after-deploy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run after deploy commands. It will run necessary commands after deployment.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        //queue restart
        $this->call('queue:restart');

        //seed role and permissions seeder with force
        $this->call('db:seed', [
            '--class' => 'RolePermissionSeeder',
            '--force' => true,
        ]);
    }
}
