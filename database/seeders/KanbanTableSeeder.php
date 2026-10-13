<?php

namespace Database\Seeders;

use App\Models\KanbanBoard;
use App\Models\KanbanCollection;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KanbanTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $collections = [
            [
                "name" => "So'rovlar ☎️",
                "boards" => [
                    "Instagram 📸",
                    "Telegram 📱",
                    "Yo'l yo'lakay 🚶",
                    "Do'stlar 👨‍👩‍👧‍👦",
                    "Boshqa 🤷‍♂️",
                ]
            ],
            [
                "name" => "Kutish ro'yxati ⏳",
                "boards" => []
            ],
            [
                "name" => "To'plamlar 📦",
                "boards" => []
            ],
        ];

        foreach ($collections as $collection) {
            $kanbanCollection = KanbanCollection::create([
                "name" => $collection["name"]
            ]);

            foreach ($collection["boards"] as $board) {
                KanbanBoard::create([
                    "name" => $board,
                    "kanban_collection_id" => $kanbanCollection->id
                ]);
            }
        }
    }
}
