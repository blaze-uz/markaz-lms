<?php

namespace App\Imports;

use App\Models\KanbanBoard;
use App\Models\KanbanTask;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;

class LeadsImport implements ToCollection
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection(Collection $rows)
    {
        DB::transaction(function () use ($rows) {
            $this->validateRows($rows);
            $this->importLeads($rows);
        });
    }

    private function validateRows(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $rowArray = $row->toArray();

            if ($this->shouldSkipRow($rowArray)) {
                continue;
            }

            match (true) {
                empty($row[1]) => throw new \Exception(__('translation.import_validation.there_is_no_name_in_row', ['row' => $index + 1])),
                empty($row[2]) => throw new \Exception(__('translation.import_validation.there_is_no_phone_in_row', ['row' => $index + 1])),
                empty($row[3]) => throw new \Exception(__('translation.import_validation.there_is_no_chosen_days_in_row', ['row' => $index + 1])),
                empty($row[4]) => throw new \Exception(__('translation.import_validation.there_is_no_chosen_time_in_row', ['row' => $index + 1])),
                default => null
            };
        }
    }

    private function shouldSkipRow($rowArray)
    {
        return empty(array_filter(array_slice($rowArray, 1, 4))) || $rowArray[0] === '#';
    }

    private function cleanPhone($phone)
    {
        return substr(preg_replace('/\D/', '', $phone), -9);
    }

    private function importLeads(Collection $rows)
    {
        $currentBranchId = auth()->user()->current_branch_id;

        foreach ($rows as $row) {
            $rowArray = $row->toArray();

            if ($this->shouldSkipRow($rowArray)) {
                continue;
            }

            $name = $row[1];
            $phoneNumber = $this->cleanPhone($row[2]);
            $formattedPhoneNumber = '+998(' . substr($phoneNumber, 0, 2) . ')' . substr($phoneNumber, 2, 3) . '-' . substr($phoneNumber, 5, 2) . '-' . substr($phoneNumber, 7, 2);
            $phone = $formattedPhoneNumber;
            $chosen_days = (strtolower($row[3]) == 'toq') ? 'odd' : 'even';
            $chosen_time = $row[4] ?? '';
            $comment = $row[5] ?? '';

            $collection_id = KanbanBoard::findOrFail($this->data['board'])->kanban_collection_id;
            $task = KanbanTask::create([
                'name' => $name,
                'phone' => $phone,
                'chosen_days' => $chosen_days,
                'chosen_time' => $chosen_time,
                'comment' => $comment,
                'kanban_board_id' => $this->data['board'],
                'kanban_collection_id' => $collection_id,
                'causer_id' => auth()->id(),
                'course_id' => $this->data['course'] ?? null,
            ]);

            $task->branches()->attach($currentBranchId);
        }
    }
}