<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\KanbanTask;

class DeleteLeadsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-leads-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $start = $this->ask('Enter start date');
        $end = $this->ask('Enter end date');

        $tasks = KanbanTask::whereBetween('created_at', [$start, $end])->get();
        
        if ($tasks->isEmpty()) {
            $this->info('No leads found between the specified dates.');
            return;
        }

        $progressBar = $this->output->createProgressBar($tasks->count());
        $progressBar->start();

        foreach ($tasks as $task) {
            // Delete related records
            $task->comments()->delete();
            $task->calls()->delete();
            $task->reminders()->delete();
            $task->teachers()->detach();
            
            // Finally delete the task itself
            $task->delete();

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();
        $this->info('Successfully deleted ' . $tasks->count() . ' leads and their related records.');
    }
}
