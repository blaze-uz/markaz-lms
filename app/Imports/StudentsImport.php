<?php

namespace App\Imports;

use App\Enums\AccountEnum;
use App\Models\Account;
use App\Models\Student;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;

class StudentsImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        DB::transaction(function () use ($rows) {
            $this->validateRows($rows);
            $this->importStudents($rows);
        });
    }

    private function createAccount(string $fullName): int
    {
        return Account::create([
            'name' => $fullName,
            'account_code' => AccountEnum::STUDENT->value,
        ])->id;
    }

    private function importStudents(Collection $rows): void
    {
        $currentBranchId = auth()->user()->current_branch_id;

        foreach ($rows as $row) {
            $rowArray = $row->toArray();

            if ($this->shouldSkipRow($rowArray)) {
                continue;
            }

            $account = $this->createAccount($row[1] . ' ' . $row[2]);

            //formart birthday to Y-m-d
            $formatted_phone = preg_match('/\d{2}.\d{2}.\d{4}/', $row[6]) ? $row[6] : null;
            if ($formatted_phone) {
                $row[6] = date('Y-m-d', strtotime($formatted_phone));
            } else {
                $row[6] = null;
            }

            $student = Student::create([
                'name' => $row[1],
                'surname' => $row[2],
                'phone' => $this->cleanPhone($row[3]),
                'parent_phone' => $this->cleanPhone($row[4]),
                'gender' => strtolower($row[5]) === 'erkak' ? 'male' : 'female',
                'birthday' => $row[6],
                'comment' => $row[7] ?? '',
                'account_id' => $account,
            ]);

            $student->branches()->attach($currentBranchId);
        }
    }

    private function validateRows(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowArray = $row->toArray();

            if ($this->shouldSkipRow($rowArray)) {
                continue;
            }

            match (true) {
                empty($row[1]) => throw new \Exception(__('translation.import_validation.there_is_no_name_in_row', ['row' => $index + 1])),
                empty($row[2]) => throw new \Exception(__('translation.import_validation.there_is_no_surname_in_row', ['row' => $index + 1])),
                empty($row[3]) => throw new \Exception(__('translation.import_validation.there_is_no_phone_in_row', ['row' => $index + 1])),
                empty($row[4]) => throw new \Exception(__('translation.import_validation.there_is_no_parent_phone_in_row', ['row' => $index + 1])),
                empty($row[5]) => throw new \Exception(__('translation.import_validation.there_is_no_gender_in_row', ['row' => $index + 1])),
                empty($row[6]) => throw new \Exception(__('translation.import_validation.there_is_no_birthday_in_row', ['row' => $index + 1])),
                default => null
            };
        }
    }

    private function shouldSkipRow(array $row): bool
    {
        return empty(array_filter(array_slice($row, 1, 6))) || $row[0] === '#';
    }

    private function cleanPhone(mixed $phone): string
    {
        return substr(preg_replace('/\D/', '', $phone), -9);
    }
}
