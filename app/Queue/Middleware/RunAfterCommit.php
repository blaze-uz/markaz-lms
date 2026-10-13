<?php

namespace App\Queue\Middleware;

use App\Jobs\BaseJob;
use Illuminate\Queue\Jobs\SyncJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class RunAfterCommit
{
    /**
     * Process the queued job.
     */
    public function handle(mixed $job, callable $next): void
    {
        if ($job instanceof BaseJob && $job->job instanceof SyncJob) {
            $this->runAfterCommit($job, $next);

            return;
        }

        $next($job);
    }

    private function runAfterCommit(BaseJob $job, callable $next): void
    {
        DB::afterCommit(function () use ($job, $next): void {
            try {
                $next($job);
            } catch (Throwable $e) {
                report($e);

                Log::error('Sync job failed after its request committed; the request was not interrupted', [
                    'job' => get_class($job),
                    'error' => $e->getMessage(),
                ]);

                if (method_exists($job, 'failed')) {
                    try {
                        $job->failed($e);
                    } catch (Throwable $hookError) {
                        report($hookError);
                    }
                }

                return;
            }

            if ($job->job instanceof SyncJob && $job->job->isReleased()) {
                Log::warning('Sync job asked to be retried later; the sync queue cannot retry it', [
                    'job' => get_class($job),
                ]);
            }
        });
    }
}
