<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class FixStudentPhoneNumber extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //get students where phone number length is greater than 9
        $students = \App\Models\Student::whereRaw('LENGTH(phone) > 9')->get();
        if ($students->count() > 0) {
            $students->each(function ($student) {
                $phone = $student->phone;
                $phone = preg_replace('/[^0-9]/', '', $phone);
                $phone = substr($phone, -9);
                $student->phone = $phone;
                $student->save();
            });
        }

        info('Phone numbers are fixed');
    }
}
