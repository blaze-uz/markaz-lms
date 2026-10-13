<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Branch;
use App\Models\Expense;
use App\Models\Group;
use App\Models\KanbanBoard;
use App\Models\KanbanCollection;
use App\Models\KanbanTask;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\RefundPayment;
use App\Models\Room;
use App\Models\Sms;
use App\Models\Student;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DeleteBranchCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'branch:delete {--force : Force deletion without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete a branch and all its related data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $branchId = $this->ask('Enter branch ID to delete');

        if (!$branchId || !is_numeric($branchId)) {
            $this->error('Invalid branch ID provided.');
            return 1;
        }

        $branch = Branch::find($branchId);

        if (!$branch) {
            $this->error("Branch with ID {$branchId} not found.");
            return 1;
        }

        $this->info("Found branch: {$branch->name} (ID: {$branch->id})");

        // Show related data counts
        $this->showRelatedDataCounts($branch);

        // Confirmation
        if (!$this->option('force')) {
            if (!$this->confirm('Are you sure you want to delete this branch and ALL its related data? This action cannot be undone.')) {
                $this->info('Operation cancelled.');
                return 0;
            }

            if (!$this->confirm('This will permanently delete ALL data associated with this branch. Type "DELETE" to confirm')) {
                $this->info('Operation cancelled.');
                return 0;
            }

            $confirmation = $this->ask('Type "DELETE" to confirm');
            if ($confirmation !== 'DELETE') {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        return $this->deleteBranch($branch);
    }

    /**
     * Show counts of related data
     */
    private function showRelatedDataCounts(Branch $branch): void
    {
        $this->info('Related data analysis:');

        $counts = [
            'Students' => $branch->students()->count(),
            'Groups' => $branch->groups()->count(),
            'Rooms' => $branch->rooms()->count(),
            'Payments' => $branch->payments()->count(),
            'Refund Payments' => $branch->refundPayments()->count(),
            'Expenses' => $branch->expenses()->count(),
            'Kanban Collections' => $branch->kanbanCollections()->count(),
            'Kanban Boards' => $branch->kanbanBoards()->count(),
            'Kanban Tasks' => $branch->kanbanTasks()->count(),
            'Courses' => $branch->courses()->count(),
            'SMS Records' => $branch->sms()->count(),
            'Users' => $branch->users()->count(),
        ];

        $this->line('Data that will be:');
        $this->line('  🗑️  DELETED (exclusive to this branch):');

        foreach ($counts as $type => $count) {
            if ($count > 0) {
                $this->line("    - {$type}: {$count} (will check for multi-branch relationships)");
            }
        }

        $this->line('  🔗 DETACHED (shared with other branches):');
        $this->line('    - Any data that belongs to multiple branches will be detached from this branch only');

        $this->newLine();
    }

    /**
     * Delete the branch and all related data
     */
    private function deleteBranch(Branch $branch): int
    {
        $this->info('Starting branch deletion process...');

        DB::beginTransaction();

        try {
            $progressBar = $this->output->createProgressBar(12);
            $progressBar->start();

            // 1. Delete Kanban Tasks and related data
            $this->deleteKanbanTasks($branch);
            $progressBar->advance();

            // 2. Delete Leads and related data
            $this->deleteLeads($branch);
            $progressBar->advance();

            // 3. Delete Payments and related transactions
            $this->deletePayments($branch);
            $progressBar->advance();

            // 4. Delete Refund Payments
            $this->deleteRefundPayments($branch);
            $progressBar->advance();

            // 5. Delete Expenses
            $this->deleteExpenses($branch);
            $progressBar->advance();

            // 6. Delete Groups and related data
            $this->deleteGroups($branch);
            $progressBar->advance();

            // 7. Delete Students and related data
            $this->deleteStudents($branch);
            $progressBar->advance();

            // 8. Delete Rooms
            $this->deleteRooms($branch);
            $progressBar->advance();

            // 9. Delete Kanban Collections and Boards
            $this->deleteKanbanCollectionsAndBoards($branch);
            $progressBar->advance();

            // 10. Delete SMS records
            $this->deleteSmsRecords($branch);
            $progressBar->advance();

            // 11. Detach users from branch
            $this->detachUsers($branch);
            $progressBar->advance();

            // 12. Delete branch account and branch itself
            $this->deleteBranchAccountAndBranch($branch);
            $progressBar->advance();

            $progressBar->finish();
            $this->newLine();

            // Clear all caches
            $this->clearCaches();

            DB::commit();

            $this->info("✅ Branch '{$branch->name}' has been successfully deleted.");
            $this->info("📊 Summary:");
            $this->info("   - Data exclusive to this branch: DELETED");
            $this->info("   - Data shared with other branches: DETACHED (preserved)");
            return 0;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("❌ Error deleting branch: " . $e->getMessage());
            $this->error("All changes have been rolled back.");
            return 1;
        }
    }

    /**
     * Delete Kanban Tasks and related data
     */
    private function deleteKanbanTasks(Branch $branch): void
    {
        $tasks = $branch->kanbanTasks()->get();

        foreach ($tasks as $task) {
            // Check if task belongs to other branches
            $otherBranches = $task->branches()->where('branch_id', '!=', $branch->id)->count();

            if ($otherBranches > 0) {
                // Just detach from this branch, don't delete the task
                $task->branches()->detach($branch->id);
                continue;
            }

            // Delete related records only if task belongs exclusively to this branch
            $task->comments()->delete();
            $task->calls()->delete();
            $task->reminders()->delete();
            $task->teachers()->detach();

            // Delete the task itself
            $task->forceDelete();
        }
    }

    /**
     * Delete Leads and related data
     */
    private function deleteLeads(Branch $branch): void
    {
        $leads = Lead::whereHas('branches', function ($query) use ($branch) {
            $query->where('branch_id', $branch->id);
        })->get();

        foreach ($leads as $lead) {
            // Check if lead belongs to other branches
            $otherBranches = $lead->branches()->where('branch_id', '!=', $branch->id)->count();

            if ($otherBranches > 0) {
                // Just detach from this branch, don't delete the lead
                $lead->branches()->detach($branch->id);
                continue;
            }

            // Delete related records only if lead belongs exclusively to this branch
            $lead->calls()->delete();
            $lead->comments()->delete();

            // Delete the lead itself
            $lead->forceDelete();
        }
    }

    /**
     * Delete Payments and related transactions
     */
    private function deletePayments(Branch $branch): void
    {
        $payments = $branch->payments()->get();

        foreach ($payments as $payment) {
            // Check if payment belongs to other branches
            $otherBranches = $payment->branches()->where('branch_id', '!=', $branch->id)->count();

            if ($otherBranches > 0) {
                // Just detach from this branch, don't delete the payment
                $payment->branches()->detach($branch->id);
                continue;
            }

            // Delete related transaction only if payment belongs exclusively to this branch
            if ($payment->transaction) {
                $payment->transaction->delete();
            }

            // Delete payment discounts
            $payment->payment_discounts()->delete();

            // Delete the payment itself
            $payment->forceDelete();
        }
    }

    /**
     * Delete Refund Payments
     */
    private function deleteRefundPayments(Branch $branch): void
    {
        $refundPayments = $branch->refundPayments()->get();

        foreach ($refundPayments as $refundPayment) {
            // Check if refund payment belongs to other branches
            $otherBranches = $refundPayment->branches()->where('branch_id', '!=', $branch->id)->count();

            if ($otherBranches > 0) {
                // Just detach from this branch, don't delete the refund payment
                $refundPayment->branches()->detach($branch->id);
                continue;
            }

            // Delete the refund payment only if it belongs exclusively to this branch
            $refundPayment->forceDelete();
        }
    }

    /**
     * Delete Expenses
     */
    private function deleteExpenses(Branch $branch): void
    {
        $expenses = $branch->expenses()->get();

        foreach ($expenses as $expense) {
            // Check if expense belongs to other branches
            $otherBranches = $expense->branches()->where('branch_id', '!=', $branch->id)->count();

            if ($otherBranches > 0) {
                // Just detach from this branch, don't delete the expense
                $expense->branches()->detach($branch->id);
                continue;
            }

            // Delete the expense only if it belongs exclusively to this branch
            $expense->forceDelete();
        }
    }

    /**
     * Delete Groups and related data
     */
    private function deleteGroups(Branch $branch): void
    {
        $groups = $branch->groups()->get();

        foreach ($groups as $group) {
            // Check if group belongs to other branches
            $otherBranches = $group->branches()->where('branch_id', '!=', $branch->id)->count();

            if ($otherBranches > 0) {
                // Just detach from this branch, don't delete the group
                $group->branches()->detach($branch->id);
                continue;
            }

            // Delete related data only if group belongs exclusively to this branch
            $group->studentGroupPayments()->delete();
            $group->payments()->delete();
            $group->attendances()->delete();

            // Delete the group itself
            $group->forceDelete();
        }
    }

    /**
     * Delete Students and related data
     */
    private function deleteStudents(Branch $branch): void
    {
        $students = $branch->students()->get();

        foreach ($students as $student) {
            // Check if student belongs to other branches
            $otherBranches = $student->branches()->where('branch_id', '!=', $branch->id)->count();

            if ($otherBranches > 0) {
                // Just detach from this branch, don't delete the student
                $student->branches()->detach($branch->id);
                continue;
            }

            // Delete related records only if student belongs exclusively to this branch
            $student->payments()->delete();
            $student->attendances()->delete();
            $student->groupPayments()->delete();

            // Delete kanban tasks that belong to this student
            KanbanTask::where('student_id', $student->id)->delete();

            // Delete leads that belong to this student
            Lead::where('student_id', $student->id)->delete();

            // Delete student delete info
            $student->deleteInfo()->delete();

            // Delete the student itself
            $student->forceDelete();
        }
    }

    /**
     * Delete Rooms
     */
    private function deleteRooms(Branch $branch): void
    {
        $rooms = $branch->rooms()->get();

        foreach ($rooms as $room) {
            // Check if room belongs to other branches
            $otherBranches = $room->branches()->where('branch_id', '!=', $branch->id)->count();

            if ($otherBranches > 0) {
                // Just detach from this branch, don't delete the room
                $room->branches()->detach($branch->id);
                continue;
            }

            // Delete the room only if it belongs exclusively to this branch
            $room->forceDelete();
        }
    }

    /**
     * Delete Kanban Collections and Boards
     */
    private function deleteKanbanCollectionsAndBoards(Branch $branch): void
    {
        // Delete Kanban Collections
        $collections = $branch->kanbanCollections()->get();
        foreach ($collections as $collection) {
            // Check if collection belongs to other branches
            $otherBranches = $collection->branches()->where('branch_id', '!=', $branch->id)->count();

            if ($otherBranches > 0) {
                // Just detach from this branch, don't delete the collection
                $collection->branches()->detach($branch->id);
                continue;
            }

            // Delete the collection only if it belongs exclusively to this branch
            $collection->forceDelete();
        }

        // Delete Kanban Boards
        $boards = $branch->kanbanBoards()->get();
        foreach ($boards as $board) {
            // Check if board belongs to other branches
            $otherBranches = $board->branches()->where('branch_id', '!=', $branch->id)->count();

            if ($otherBranches > 0) {
                // Just detach from this branch, don't delete the board
                $board->branches()->detach($branch->id);
                continue;
            }

            // Delete the board only if it belongs exclusively to this branch
            $board->forceDelete();
        }
    }

    /**
     * Delete SMS records
     */
    private function deleteSmsRecords(Branch $branch): void
    {
        $smsRecords = $branch->sms()->get();

        foreach ($smsRecords as $sms) {
            // Check if SMS belongs to other branches
            $otherBranches = $sms->branches()->where('branch_id', '!=', $branch->id)->count();

            if ($otherBranches > 0) {
                // Just detach from this branch, don't delete the SMS
                $sms->branches()->detach($branch->id);
                continue;
            }

            // Delete the SMS only if it belongs exclusively to this branch
            $sms->forceDelete();
        }
    }

    /**
     * Detach users from branch
     */
    private function detachUsers(Branch $branch): void
    {
        $branch->users()->detach();

        // Update users who have this branch as current_branch_id
        User::where('current_branch_id', $branch->id)
            ->update(['current_branch_id' => null]);
    }

    /**
     * Delete branch account and branch itself
     */
    private function deleteBranchAccountAndBranch(Branch $branch): void
    {
        // Delete the associated account
        if ($branch->account) {
            $branch->account->delete();
        }

        // Finally delete the branch itself
        $branch->forceDelete();
    }

    /**
     * Clear all relevant caches
     */
    private function clearCaches(): void
    {
        Cache::forget('branches');
        Cache::forget('courses');
        Cache::forget('dashboard_statistics');
        Cache::flush();
    }
}
