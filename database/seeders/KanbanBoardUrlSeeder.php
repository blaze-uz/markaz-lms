<?php

namespace Database\Seeders;

use App\Models\KanbanBoard;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KanbanBoardUrlSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kanbanBoards = KanbanBoard::all();

        foreach ($kanbanBoards as $kanbanBoard) {
            $kanbanBoard->url = uniqid();
            $kanbanBoard->save();
        }
    }
}
