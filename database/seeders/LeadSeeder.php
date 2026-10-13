<?php

namespace Database\Seeders;

use App\Models\KanbanTask;
use App\Models\KanbanTaskCall;
use App\Models\KanbanTaskComment;
use App\Models\KanbanBoard;
use App\Models\KanbanCollection;
use App\Models\Lead;
use App\Models\LeadCall;
use App\Models\LeadComment;
use App\Models\LeadCategory;
use App\Models\LeadCollection;
use App\Models\LeadBoard;
use App\Models\LeadReminder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LeadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting lead data transfer from kanban tables...');

        DB::beginTransaction();

        try {
            // Step 1: Ensure lead collections are created/updated from kanban collections
            $this->transferLeadCollections();

            // Step 2: Ensure lead categories are created/updated from kanban boards
            $this->transferLeadCategories();

            // Step 2.5: Transfer kanban boards to lead boards
            $this->transferLeadBoards();

            // Step 3: Transfer kanban tasks to leads
            $this->transferKanbanTasksToLeads();

            // Step 4: Transfer kanban calls to lead calls
            $this->transferKanbanCallsToLeadCalls();

            // Step 5: Transfer kanban comments to lead comments
            $this->transferKanbanCommentsToLeadComments();

            // Step 6: Transfer kanban reminders to lead reminders
            $this->transferKanbanRemindersToLeadReminders();

            // Step 7: Update collection_id in leads based on kanban_collection_id
            $this->updateLeadCollectionReferences();

            DB::commit();
            $this->command->info('Lead data transfer completed successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Lead data transfer failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Transfer kanban collections to lead collections
     */
    private function transferLeadCollections(): void
    {
        $this->command->info('Transferring kanban collections to lead collections...');

        $kanbanCollections = KanbanCollection::all();
        $transferredCount = 0;

        $progressBar = $this->command->getOutput()->createProgressBar($kanbanCollections->count());
        $progressBar->start();

        foreach ($kanbanCollections as $kanbanCollection) {
            // Check if lead collection already exists
            $existingLeadCollection = LeadCollection::where('kanban_collection_id', $kanbanCollection->id)->first();

            if (!$existingLeadCollection) {
                $leadCollection = LeadCollection::create([
                    'name' => $kanbanCollection->name,
                    'type' => 'default',
                    'order' => LeadCollection::max('order') + 1,
                    'kanban_collection_id' => $kanbanCollection->id,
                ]);

                // Attach the same branches as the kanban collection
                if ($kanbanCollection->branches()->exists()) {
                    $leadCollection->branches()->attach($kanbanCollection->branches->pluck('id'));
                }

                $transferredCount++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->command->newLine();
        $this->command->info("Transferred {$transferredCount} kanban collections to lead collections.");
    }

    /**
     * Transfer kanban boards to lead categories
     */
    private function transferLeadCategories(): void
    {
        $this->command->info('Transferring kanban boards to lead categories...');

        $kanbanBoards = KanbanBoard::all();
        $transferredCount = 0;

        $progressBar = $this->command->getOutput()->createProgressBar($kanbanBoards->count());
        $progressBar->start();

        foreach ($kanbanBoards as $kanbanBoard) {
            // Check if lead category already exists
            $existingLeadCategory = LeadCategory::where('kanban_board_id', $kanbanBoard->id)->first();

            if (!$existingLeadCategory) {
                // Find the corresponding lead collection
                $leadCollection = LeadCollection::where('kanban_collection_id', $kanbanBoard->kanban_collection_id)->first();

                $leadCategory = LeadCategory::create([
                    'name' => $kanbanBoard->name,
                    'lead_collection_id' => $leadCollection ? $leadCollection->id : null,
                    'url' => $kanbanBoard->url,
                    'group_id' => $kanbanBoard->group_id,
                    'form_comment_state' => $kanbanBoard->form_comment_state,
                    'form_course_state' => $kanbanBoard->form_course_state,
                    'success_message' => $kanbanBoard->success_message,
                    'kanban_board_id' => $kanbanBoard->id,
                ]);

                // Attach the same branches as the kanban board
                if ($kanbanBoard->branches()->exists()) {
                    $leadCategory->branches()->attach($kanbanBoard->branches->pluck('id'));
                }

                $transferredCount++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->command->newLine();
        $this->command->info("Transferred {$transferredCount} kanban boards to lead categories.");
    }

    /**
     * Transfer kanban boards to lead boards
     */
    private function transferLeadBoards(): void
    {
        $this->command->info('Transferring kanban boards to lead boards...');

        $kanbanBoards = KanbanBoard::all();
        $transferredCount = 0;

        $progressBar = $this->command->getOutput()->createProgressBar($kanbanBoards->count());
        $progressBar->start();

        foreach ($kanbanBoards as $kanbanBoard) {
            // Check if lead board already exists
            $existingLeadBoard = LeadBoard::where('kanban_board_id', $kanbanBoard->id)->first();

            if (!$existingLeadBoard) {
                // Find the corresponding lead collection
                $leadCollection = LeadCollection::where('kanban_collection_id', $kanbanBoard->kanban_collection_id)->first();

                $leadBoard = LeadBoard::create([
                    'name' => $kanbanBoard->name,
                    'lead_collection_id' => $leadCollection ? $leadCollection->id : null,
                    'url' => $kanbanBoard->url,
                    'group_id' => $kanbanBoard->group_id,
                    'success_message' => $kanbanBoard->success_message,
                    'form_comment_state' => $kanbanBoard->form_comment_state,
                    'form_course_state' => $kanbanBoard->form_course_state,
                    'kanban_board_id' => $kanbanBoard->id,
                ]);

                // Attach the same branches as the kanban board
                if ($kanbanBoard->branches()->exists()) {
                    $leadBoard->branches()->attach($kanbanBoard->branches->pluck('id'));
                }

                $transferredCount++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->command->newLine();
        $this->command->info("Transferred {$transferredCount} kanban boards to lead boards.");
    }

    /**
     * Transfer kanban tasks to leads
     */
    private function transferKanbanTasksToLeads(): void
    {
        $this->command->info('Transferring kanban tasks to leads...');

        $kanbanTasks = KanbanTask::withTrashed()->with('board', 'collection', 'teachers')->get();
        $transferredCount = 0;

        $progressBar = $this->command->getOutput()->createProgressBar($kanbanTasks->count());
        $progressBar->start();

        foreach ($kanbanTasks as $kanbanTask) {
            // Check if lead already exists for this kanban task
            $existingLead = Lead::where('session_id', $kanbanTask->session_id)
                ->where('phone', $kanbanTask->phone)
                ->where('name', $kanbanTask->name)
                ->first();

            if (!$existingLead) {
                // Find the corresponding lead collection
                $leadCollection = LeadCollection::where('kanban_collection_id', $kanbanTask->kanban_collection_id)->first();

                // Find the corresponding lead board
                $leadBoard = null;
                if ($kanbanTask->kanban_board_id) {
                    $leadBoard = LeadBoard::where('kanban_board_id', $kanbanTask->kanban_board_id)->first();
                }

                // Get the first teacher from the kanban task (if multiple teachers exist, take the first one)
                $teacherId = $kanbanTask->teachers->first() ? $kanbanTask->teachers->first()->id : null;

                $lead = Lead::create([
                    'name' => $kanbanTask->name,
                    'phone' => $kanbanTask->phone,
                    'comment' => $kanbanTask->comment,
                    'collection_id' => $leadCollection ? $leadCollection->id : null,
                    'board_id' => $leadBoard ? $leadBoard->id : null,
                    'student_id' => $kanbanTask->student_id,
                    'status' => $kanbanTask->status,
                    'chosen_days' => $kanbanTask->chosen_days,
                    'chosen_time' => $kanbanTask->chosen_time,
                    'causer_id' => $kanbanTask->causer_id,
                    'course_id' => $kanbanTask->course_id,
                    'teacher_id' => $teacherId,
                    'session_id' => $kanbanTask->session_id,
                    'attend' => $kanbanTask->attend,
                    'created_at' => $kanbanTask->created_at,
                    'updated_at' => $kanbanTask->updated_at,
                ]);

                // If the kanban task was soft deleted, also soft delete the lead
                if ($kanbanTask->trashed()) {
                    $lead->delete();
                    // Update the deleted_at timestamp to match the original
                    $lead->update(['deleted_at' => $kanbanTask->deleted_at]);
                }

                // Attach the same branches as the kanban task
                if ($kanbanTask->branches()->exists()) {
                    $lead->branches()->attach($kanbanTask->branches->pluck('id'));
                }

                // Update any students that reference this kanban task ID to reference the new lead ID
                \App\Models\Student::where('lead_id', $kanbanTask->id)->update(['lead_id' => $lead->id]);

                $transferredCount++;
            } else {
                // Update existing lead with attend value and board_id from kanban task
                $leadBoard = null;
                if ($kanbanTask->kanban_board_id) {
                    $leadBoard = LeadBoard::where('kanban_board_id', $kanbanTask->kanban_board_id)->first();
                }

                $existingLead->update([
                    'attend' => $kanbanTask->attend,
                    'board_id' => $leadBoard ? $leadBoard->id : null,
                    'session_id' => $kanbanTask->session_id
                ]);
                $transferredCount++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->command->newLine();
        $this->command->info("Transferred {$transferredCount} kanban tasks to leads.");

        // Update any remaining students with kanban task IDs to use lead IDs
        $this->updateStudentLeadReferences();
    }

    private function updateStudentLeadReferences(): void
    {
        $this->command->info('Updating student lead references...');

        // Find all students that have lead_id but the lead_id doesn't exist in the leads table
        $studentsToUpdate = \App\Models\Student::whereNotNull('lead_id')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('leads')
                    ->whereColumn('leads.id', 'students.lead_id');
            })
            ->get();

        $updatedCount = 0;

        if ($studentsToUpdate->count() > 0) {
            $progressBar = $this->command->getOutput()->createProgressBar($studentsToUpdate->count());
            $progressBar->start();
        }

        foreach ($studentsToUpdate as $student) {
            // Try to find a matching lead by looking for kanban tasks that were converted
            $kanbanTask = \App\Models\KanbanTask::withTrashed()->find($student->lead_id);

            if ($kanbanTask) {
                // Find the corresponding lead
                $lead = \App\Models\Lead::where('session_id', $kanbanTask->session_id)
                    ->where('phone', $kanbanTask->phone)
                    ->where('name', $kanbanTask->name)
                    ->first();

                if ($lead) {
                    $student->update(['lead_id' => $lead->id]);
                    $updatedCount++;
                }
            }

            if ($studentsToUpdate->count() > 0) {
                $progressBar->advance();
            }
        }

        if ($studentsToUpdate->count() > 0) {
            $progressBar->finish();
            $this->command->newLine();
        }

        $this->command->info("Updated {$updatedCount} student lead references.");
    }

    /**
     * Transfer kanban calls to lead calls
     */
    private function transferKanbanCallsToLeadCalls(): void
    {
        $this->command->info('Transferring kanban calls to lead calls...');

        $kanbanCalls = KanbanTaskCall::with('lead')->get();
        $transferredCount = 0;

        $progressBar = $this->command->getOutput()->createProgressBar($kanbanCalls->count());
        $progressBar->start();

        foreach ($kanbanCalls as $kanbanCall) {
            // Find the corresponding lead by kanban task
            $kanbanTask = KanbanTask::find($kanbanCall->kanban_task_id);
            if (!$kanbanTask) continue;

            // Find lead by matching session_id, phone, and name
            $lead = Lead::where('session_id', $kanbanTask->session_id)
                ->where('phone', $kanbanTask->phone)
                ->where('name', $kanbanTask->name)
                ->first();

            if ($lead) {
                // Check if this call already exists
                $existingLeadCall = LeadCall::where('lead_id', $lead->id)
                    ->where('causer_id', $kanbanCall->causer_id)
                    ->where('status', $kanbanCall->status)
                    ->where('created_at', $kanbanCall->created_at)
                    ->first();

                if (!$existingLeadCall) {
                    LeadCall::create([
                        'lead_id' => $lead->id,
                        'causer_id' => $kanbanCall->causer_id,
                        'status' => $kanbanCall->status,
                        'comment' => $kanbanCall->comment,
                        'created_at' => $kanbanCall->created_at,
                        'updated_at' => $kanbanCall->updated_at,
                    ]);

                    $transferredCount++;
                }
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->command->newLine();
        $this->command->info("Transferred {$transferredCount} kanban calls to lead calls.");
    }

    /**
     * Transfer kanban comments to lead comments
     */
    private function transferKanbanCommentsToLeadComments(): void
    {
        $this->command->info('Transferring kanban comments to lead comments...');

        $kanbanComments = KanbanTaskComment::with('task')->get();
        $transferredCount = 0;

        $progressBar = $this->command->getOutput()->createProgressBar($kanbanComments->count());
        $progressBar->start();

        foreach ($kanbanComments as $kanbanComment) {
            // Find the corresponding lead by kanban task
            $kanbanTask = $kanbanComment->task;
            if (!$kanbanTask) continue;

            // Find lead by matching session_id, phone, and name
            $lead = Lead::where('session_id', $kanbanTask->session_id)
                ->where('phone', $kanbanTask->phone)
                ->where('name', $kanbanTask->name)
                ->first();

            if ($lead) {
                // Check if this comment already exists
                $existingLeadComment = LeadComment::where('lead_id', $lead->id)
                    ->where('user_id', $kanbanComment->user_id)
                    ->where('comment', $kanbanComment->comment)
                    ->where('created_at', $kanbanComment->created_at)
                    ->first();

                if (!$existingLeadComment) {
                    LeadComment::create([
                        'lead_id' => $lead->id,
                        'user_id' => $kanbanComment->user_id,
                        'comment' => $kanbanComment->comment,
                        'created_at' => $kanbanComment->created_at,
                        'updated_at' => $kanbanComment->updated_at,
                    ]);

                    $transferredCount++;
                }
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->command->newLine();
        $this->command->info("Transferred {$transferredCount} kanban comments to lead comments.");
    }

    /**
     * Transfer kanban reminders to lead reminders
     */
    private function transferKanbanRemindersToLeadReminders(): void
    {
        $this->command->info('Transferring kanban reminders to lead reminders...');

        // Get all existing reminders that are currently referencing kanban task IDs
        $kanbanReminders = LeadReminder::all();
        $transferredCount = 0;

        $progressBar = $this->command->getOutput()->createProgressBar($kanbanReminders->count());
        $progressBar->start();

        foreach ($kanbanReminders as $reminder) {
            // The current reminder->lead_id is actually pointing to a kanban task ID
            $kanbanTaskId = $reminder->lead_id;

            // Find the kanban task
            $kanbanTask = KanbanTask::find($kanbanTaskId);
            if (!$kanbanTask) continue;

            // Find the corresponding lead by matching session_id, phone, and name
            $lead = Lead::where('session_id', $kanbanTask->session_id)
                ->where('phone', $kanbanTask->phone)
                ->where('name', $kanbanTask->name)
                ->first();

            if ($lead) {
                // Update the reminder to point to the actual lead ID instead of kanban task ID
                $reminder->update(['lead_id' => $lead->id]);
                $transferredCount++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->command->newLine();
        $this->command->info("Updated {$transferredCount} kanban reminders to reference lead IDs.");
    }

    /**
     * Update collection_id references in leads table
     */
    private function updateLeadCollectionReferences(): void
    {
        $this->command->info('Updating lead collection references...');

        $leads = Lead::whereNull('collection_id')->get();
        $updatedCount = 0;

        $progressBar = $this->command->getOutput()->createProgressBar($leads->count());
        $progressBar->start();

        foreach ($leads as $lead) {
            // Find kanban task by matching data
            $kanbanTask = KanbanTask::where('session_id', $lead->session_id)
                ->where('phone', $lead->phone)
                ->where('name', $lead->name)
                ->first();

            if ($kanbanTask && $kanbanTask->kanban_collection_id) {
                $leadCollection = LeadCollection::where('kanban_collection_id', $kanbanTask->kanban_collection_id)->first();

                if ($leadCollection) {
                    $lead->update(['collection_id' => $leadCollection->id]);
                    $updatedCount++;
                }
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->command->newLine();
        $this->command->info("Updated {$updatedCount} lead collection references.");
    }
}
