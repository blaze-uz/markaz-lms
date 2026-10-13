<?php

namespace App\Jobs;

use App\Models\Group;
use App\Services\DebtManagementService;
use Illuminate\Support\Facades\Log;

class CreateGroupDebtsJob extends BaseJob
{
    protected Group $group;

    protected string $newStartDate;

    protected array $options;

    /**
     * Create a new job instance.
     */
    public function __construct(Group $group, string $newStartDate, array $options = [])
    {
        $this->group = $group;
        $this->newStartDate = $newStartDate;
        $this->options = $options;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $debtService = new DebtManagementService();

            Log::info("Starting debt creation for group {$this->group->id} with start date {$this->newStartDate}");

            $result = $debtService->createDebtsForNewStartDate($this->group, $this->newStartDate);

            Log::info("Created {$result} debts for group {$this->group->id}");
        } catch (\Exception $e) {
            Log::error("Failed to create debts for group {$this->group->id}: ".$e->getMessage());
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("CreateGroupDebtsJob failed for group {$this->group->id}: ".$exception->getMessage());
    }
}
