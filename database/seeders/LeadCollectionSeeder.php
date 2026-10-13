<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\KanbanCollection;
use App\Models\LeadCollection;
use Illuminate\Database\Seeder;

class LeadCollectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing collections
        $collections = KanbanCollection::with('branches')->get();

        // Get all branches to attach
        $branches = Branch::all();

        if ($branches->isEmpty()) {
            $this->command->error('No branches found. Please create at least one branch first.');
            return;
        }

        // If no collections exist, create default ones with emojis
        if ($collections->isEmpty()) {
            // Default collections with emojis
            $defaultCollections = [
                [
                    'name' => 'So\'rovlar',
                    'order' => 1,
                ],
                [
                    'name' => 'Jarayonda',
                    'order' => 2,
                ],
                [
                    'name' => 'Yakunlangan',
                    'order' => 3,
                ]
            ];

            // Create collections and attach to all branches
            foreach ($defaultCollections as $collection) {
                $leadCollection = LeadCollection::create([
                    'name' => $collection['name'],
                    'type' => 'default',
                    'order' => $collection['order'],
                ]);

                // Attach all branches using the pivot table
                $leadCollection->branches()->attach($branches->first()->id);
            }

            $this->command->info('Created default lead collections with emojis and attached to all branches!');
        } else {
            // Create collections from existing kanban collections
            foreach ($collections as $collection) {
                $leadCollection = LeadCollection::create([
                    'name' => $collection->name,
                    'type' => 'default',
                    'order' => $this->getOrder(),
                    'kanban_collection_id' => $collection->id,
                ]);

                if ($collection->branches && $collection->branches->count() > 0) {
                    $leadCollection->branches()->attach($collection->branches->pluck('id'));
                } elseif ($collection->branch) {
                    $leadCollection->branches()->attach($collection->branches()->first()->id);
                } else {
                    $leadCollection->branches()->attach($branches->first()->id);
                }
            }

            $this->command->info('Created lead collections from existing Kanban collections and attached to corresponding branches!');
        }
    }

    /**
     * Get the next available order number
     *
     * @return int
     */
    private function getOrder()
    {
        return LeadCollection::max('order') + 1;
    }
}
