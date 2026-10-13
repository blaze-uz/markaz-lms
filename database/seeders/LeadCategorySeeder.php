<?php

namespace Database\Seeders;

use App\Models\KanbanBoard;
use App\Models\LeadCategory;
use Illuminate\Database\Seeder;

class LeadCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing boards, fix typo in 'branches'
        $boards = KanbanBoard::with('branches')->get();

        if ($boards->isEmpty()) {
            $this->command->info('No kanban boards found. No lead categories created.');
            return;
        }

        $createdCount = 0;

        foreach ($boards as $board) {
            // Check if a category with this board ID already exists
            $existingCategory = LeadCategory::where('kanban_board_id', $board->id)->first();

            if (!$existingCategory) {
                // Create only if it doesn't exist
                $lead_category = LeadCategory::create([
                    'name' => $board->name,
                    'lead_collection_id' => $board->kanban_collection_id,
                    'url' => $board->url,
                    'group_id' => $board->group_id,
                    'form_comment_state' => $board->form_comment_state,
                    'form_course_state' => $board->form_course_state,
                    'success_message' => $board->success_message,
                    'kanban_board_id' => $board->id,
                ]);

                // Attach branches to the lead category
                if ($board->branches) {
                    foreach ($board->branches as $branch) {
                        $lead_category->branches()->attach($branch->id);
                    }
                }

                $createdCount++;
            }
        }

        if ($createdCount > 0) {
            $this->command->info("Created $createdCount new lead categories from existing kanban boards.");
        } else {
            $this->command->info('All kanban boards already have lead categories. No new categories created.');
        }
    }
}
