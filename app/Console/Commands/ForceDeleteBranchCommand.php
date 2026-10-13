<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Expense;
use App\Models\Group;
use App\Models\KanbanBoard;
use App\Models\KanbanCollection;
use App\Models\KanbanTask;
use App\Models\KanbanTaskCall;
use App\Models\KanbanTaskComment;
use App\Models\Lead;
use App\Models\LeadBoard;
use App\Models\LeadCall;
use App\Models\LeadCategory;
use App\Models\LeadCollection;
use App\Models\LeadComment;
use App\Models\LeadReminder;
use App\Models\Payment;
use App\Models\PaymentDiscount;
use App\Models\RefundPayment;
use App\Models\Room;
use App\Models\Sms;
use App\Models\Student;
use App\Models\StudentGroupPayment;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ForceDeleteBranchCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'branch:force-delete {branch_id : The ID of the branch to delete} {--force : Skip confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Force delete a branch and ALL its related data (ignores multi-branch relationships)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $branchId = $this->argument('branch_id');

        if (! is_numeric($branchId)) {
            $this->error('Invalid branch ID provided.');

            return 1;
        }

        $branch = Branch::withTrashed()->find($branchId);

        if (! $branch) {
            $this->error("Branch with ID {$branchId} not found.");

            return 1;
        }

        $this->info("Found branch: {$branch->name} (ID: {$branch->id})");

        $this->showRelatedDataCounts($branch);

        if (! $this->option('force')) {
            $this->warn('⚠️  WARNING: This will FORCE DELETE ALL data associated with this branch!');
            $this->warn('⚠️  This includes data that may be shared with other branches!');
            $this->newLine();

            if (! $this->confirm('Are you absolutely sure you want to proceed?', false)) {
                $this->info('Operation cancelled.');

                return 0;
            }

            $confirmation = $this->ask('Type "FORCE DELETE" to confirm');
            if ($confirmation !== 'FORCE DELETE') {
                $this->info('Operation cancelled.');

                return 0;
            }
        }

        return $this->forceDeleteBranch($branch);
    }

    /**
     * Show counts of related data
     */
    private function showRelatedDataCounts(Branch $branch): void
    {
        $this->info('Related data that will be FORCE DELETED (including soft deleted):');
        $this->newLine();

        $branchId = $branch->id;
        $counts = [
            'Students' => $this->branchRelationCount(Student::class, 'branches', $branchId, true),
            'Groups' => $this->branchRelationCount(Group::class, 'branches', $branchId, true),
            'Rooms' => $this->branchRelationCount(Room::class, 'branches', $branchId, false),
            'Payments' => $this->branchRelationCount(Payment::class, 'branches', $branchId, true),
            'Refund Payments' => $this->branchRelationCount(RefundPayment::class, 'branches', $branchId, false),
            'Expenses' => $this->branchRelationCount(Expense::class, 'branches', $branchId, true),
            'Kanban Collections' => $this->branchRelationCount(KanbanCollection::class, 'branches', $branchId, false),
            'Kanban Boards' => $this->branchRelationCount(KanbanBoard::class, 'branches', $branchId, false),
            'Kanban Tasks' => $this->branchRelationCount(KanbanTask::class, 'branches', $branchId, true),
            'Leads' => Lead::withTrashed()->whereHas('branches', fn ($q) => $q->where('branches.id', $branchId))->count(),
            'Lead Boards' => LeadBoard::whereHas('branches', fn ($q) => $q->where('branches.id', $branchId))->count(),
            'Lead Collections' => LeadCollection::withTrashed()->whereHas('branches', fn ($q) => $q->where('branches.id', $branchId))->count(),
            'Lead Categories' => LeadCategory::withTrashed()->whereHas('branches', fn ($q) => $q->where('branches.id', $branchId))->count(),
            'SMS Records' => $this->branchRelationCount(Sms::class, 'branches', $branchId, true),
            'Users' => $this->branchRelationCount(User::class, 'branches', $branchId, true),
            'Courses' => $branch->courses()->count(),
        ];

        foreach ($counts as $type => $count) {
            if ($count > 0) {
                $this->line("  ❌ {$type}: {$count}");
            }
        }

        $this->newLine();
    }

    /**
     * Count models linked to branch via pivot (including soft deleted when requested).
     */
    private function branchRelationCount(string $model, string $relation, int $branchId, bool $withTrashed): int
    {
        $query = $model::query()->whereHas($relation, fn ($q) => $q->where('branches.id', $branchId));

        return $withTrashed && in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive($model), true)
            ? $query->withTrashed()->count()
            : $query->count();
    }

    /**
     * Get models linked to branch via pivot (including soft deleted when requested).
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model>
     */
    private function branchRelationGet(string $model, string $relation, Branch $branch, bool $withTrashed): \Illuminate\Database\Eloquent\Collection
    {
        $query = $model::query()->whereHas($relation, fn ($q) => $q->where('branches.id', $branch->id));

        return $withTrashed && in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive($model), true)
            ? $query->withTrashed()->get()
            : $query->get();
    }

    /**
     * Force delete the branch and all related data
     */
    private function forceDeleteBranch(Branch $branch): int
    {
        $this->info('Starting force deletion process...');
        $this->newLine();

        DB::beginTransaction();

        try {
            $progressBar = $this->output->createProgressBar(15);
            $progressBar->start();

            // 1. Force delete Kanban Tasks
            $this->forceDeleteKanbanTasks($branch);
            $progressBar->advance();

            // 2. Force delete Leads
            $this->forceDeleteLeads($branch);
            $progressBar->advance();

            // 3. Force delete Lead Boards
            $this->forceDeleteLeadBoards($branch);
            $progressBar->advance();

            // 4. Force delete Lead Collections
            $this->forceDeleteLeadCollections($branch);
            $progressBar->advance();

            // 5. Force delete Lead Categories
            $this->forceDeleteLeadCategories($branch);
            $progressBar->advance();

            // 6. Force delete Payments
            $this->forceDeletePayments($branch);
            $progressBar->advance();

            // 7. Force delete Refund Payments
            $this->forceDeleteRefundPayments($branch);
            $progressBar->advance();

            // 8. Force delete Expenses
            $this->forceDeleteExpenses($branch);
            $progressBar->advance();

            // 9. Force delete Groups
            $this->forceDeleteGroups($branch);
            $progressBar->advance();

            // 10. Force delete Students
            $this->forceDeleteStudents($branch);
            $progressBar->advance();

            // 11. Force delete Rooms
            $this->forceDeleteRooms($branch);
            $progressBar->advance();

            // 12. Force delete Kanban Collections and Boards
            $this->forceDeleteKanbanCollectionsAndBoards($branch);
            $progressBar->advance();

            // 13. Force delete SMS records
            $this->forceDeleteSmsRecords($branch);
            $progressBar->advance();

            // 14. Detach users and courses
            $this->detachUsersAndCourses($branch);
            $progressBar->advance();

            // 15. Delete branch account and branch itself
            $this->deleteBranchAccountAndBranch($branch);
            $progressBar->advance();

            $progressBar->finish();
            $this->newLine();
            $this->newLine();

            $this->clearCaches();

            DB::commit();

            $this->info("✅ Branch '{$branch->name}' and ALL its data have been force deleted.");

            return 0;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Error force deleting branch: '.$e->getMessage());
            $this->error('Stack trace: '.$e->getTraceAsString());

            return 1;
        }
    }

    /**
     * Force delete Kanban Tasks
     */
    private function forceDeleteKanbanTasks(Branch $branch): void
    {
        $tasks = $this->branchRelationGet(KanbanTask::class, 'branches', $branch, true);

        foreach ($tasks as $task) {
            KanbanTaskComment::where('kanban_task_id', $task->id)->delete();
            KanbanTaskCall::where('kanban_task_id', $task->id)->delete();
            LeadReminder::withTrashed()->where('lead_id', $task->id)->forceDelete();
            $task->teachers()->detach();
            $task->branches()->detach();
            $task->forceDelete();
        }
    }

    /**
     * Force delete Leads
     */
    private function forceDeleteLeads(Branch $branch): void
    {
        $leads = Lead::withTrashed()->whereHas('branches', fn ($q) => $q->where('branches.id', $branch->id))->get();

        foreach ($leads as $lead) {
            LeadCall::withTrashed()->where('lead_id', $lead->id)->forceDelete();
            LeadComment::withTrashed()->where('lead_id', $lead->id)->forceDelete();
            LeadReminder::withTrashed()->where('lead_id', $lead->id)->forceDelete();
            $lead->branches()->detach();
            $lead->forceDelete();
        }
    }

    /**
     * Force delete Lead Boards
     */
    private function forceDeleteLeadBoards(Branch $branch): void
    {
        $boards = LeadBoard::whereHas('branches', fn ($q) => $q->where('branches.id', $branch->id))->get();

        foreach ($boards as $board) {
            Lead::withTrashed()->where('board_id', $board->id)->forceDelete();
            $board->branches()->detach();
            $board->delete();
        }
    }

    /**
     * Force delete Lead Collections
     */
    private function forceDeleteLeadCollections(Branch $branch): void
    {
        $collections = LeadCollection::withTrashed()->whereHas('branches', fn ($q) => $q->where('branches.id', $branch->id))->get();

        foreach ($collections as $collection) {
            $collection->branches()->detach();
            $collection->forceDelete();
        }
    }

    /**
     * Force delete Lead Categories
     */
    private function forceDeleteLeadCategories(Branch $branch): void
    {
        $categories = LeadCategory::withTrashed()->whereHas('branches', fn ($q) => $q->where('branches.id', $branch->id))->get();

        foreach ($categories as $category) {
            $category->branches()->detach();
            $category->forceDelete();
        }
    }

    /**
     * Force delete Payments
     */
    private function forceDeletePayments(Branch $branch): void
    {
        $payments = $this->branchRelationGet(Payment::class, 'branches', $branch, true);

        foreach ($payments as $payment) {
            if ($payment->transaction_id) {
                $transaction = Transaction::withTrashed()->find($payment->transaction_id);
                if ($transaction) {
                    $transaction->forceDelete();
                }
            }
            PaymentDiscount::where('payment_id', $payment->id)->delete();
            $payment->branches()->detach();
            $payment->forceDelete();
        }
    }

    /**
     * Force delete Refund Payments
     */
    private function forceDeleteRefundPayments(Branch $branch): void
    {
        $refundPayments = $this->branchRelationGet(RefundPayment::class, 'branches', $branch, false);

        foreach ($refundPayments as $refundPayment) {
            $refundPayment->branches()->detach();
            $refundPayment->delete();
        }
    }

    /**
     * Force delete Expenses
     */
    private function forceDeleteExpenses(Branch $branch): void
    {
        $expenses = $this->branchRelationGet(Expense::class, 'branches', $branch, true);

        foreach ($expenses as $expense) {
            $expense->branches()->detach();
            $expense->forceDelete();
        }
    }

    /**
     * Force delete Groups
     */
    private function forceDeleteGroups(Branch $branch): void
    {
        $groups = $this->branchRelationGet(Group::class, 'branches', $branch, true);

        foreach ($groups as $group) {
            StudentGroupPayment::withTrashed()->where('group_id', $group->id)->forceDelete();
            Payment::withTrashed()->where('group_id', $group->id)->forceDelete();
            Attendance::withTrashed()->where('group_id', $group->id)->forceDelete();
            $group->branches()->detach();
            $group->forceDelete();
        }
    }

    /**
     * Force delete Students
     */
    private function forceDeleteStudents(Branch $branch): void
    {
        $students = $this->branchRelationGet(Student::class, 'branches', $branch, true);

        foreach ($students as $student) {
            Payment::withTrashed()->where('student_id', $student->id)->forceDelete();
            Attendance::withTrashed()->where('student_id', $student->id)->forceDelete();
            StudentGroupPayment::withTrashed()->where('student_id', $student->id)->forceDelete();
            $student->deleteInfo()->delete();

            KanbanTask::withTrashed()->where('student_id', $student->id)->forceDelete();
            Lead::withTrashed()->where('student_id', $student->id)->forceDelete();

            $student->branches()->detach();
            $student->forceDelete();
        }
    }

    /**
     * Force delete Rooms
     */
    private function forceDeleteRooms(Branch $branch): void
    {
        $rooms = $this->branchRelationGet(Room::class, 'branches', $branch, false);

        foreach ($rooms as $room) {
            $room->branches()->detach();
            $room->delete();
        }
    }

    /**
     * Force delete Kanban Collections and Boards
     */
    private function forceDeleteKanbanCollectionsAndBoards(Branch $branch): void
    {
        $collections = $this->branchRelationGet(KanbanCollection::class, 'branches', $branch, false);
        foreach ($collections as $collection) {
            $collection->branches()->detach();
            $collection->delete();
        }

        $boards = $this->branchRelationGet(KanbanBoard::class, 'branches', $branch, false);
        foreach ($boards as $board) {
            $board->branches()->detach();
            $board->delete();
        }
    }

    /**
     * Force delete SMS records
     */
    private function forceDeleteSmsRecords(Branch $branch): void
    {
        $smsRecords = $this->branchRelationGet(Sms::class, 'branches', $branch, true);

        foreach ($smsRecords as $sms) {
            $sms->branches()->detach();
            $sms->forceDelete();
        }
    }

    /**
     * Detach users and courses from branch
     */
    private function detachUsersAndCourses(Branch $branch): void
    {
        $branch->users()->detach();
        $branch->courses()->detach();

        User::where('current_branch_id', $branch->id)
            ->update(['current_branch_id' => null]);
    }

    /**
     * Delete branch account and branch itself
     */
    private function deleteBranchAccountAndBranch(Branch $branch): void
    {
        if ($branch->account) {
            $account = Account::withTrashed()->find($branch->account->id);
            if ($account) {
                $account->forceDelete();
            }
        }

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
