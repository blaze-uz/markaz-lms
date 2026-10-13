<?php

namespace Database\Seeders;

use App\Enums\AccountEnum;
use App\Models\Account;
use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccountsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->createMainAccount();
        $this->createUsersAccount();
        $this->createGroupsAccount();
        $this->createStudentsAccount();
    }

    public function createUsersAccount()
    {
        $users = DB::table('users')->get();
        foreach ($users as $user) {
            if (property_exists($user, 'account') && $user->account) {
                DB::table('accounts')->where('id', $user->account->id)->update([
                    'name' => $user->name,
                    'account_code' => AccountEnum::STAFF->value,
                ]);
            } else {
                $account_id = DB::table('accounts')->insertGetId([
                    'name' => $user->name,
                    'account_code' => AccountEnum::STAFF->value,
                ]);

                DB::table('users')->where('id', $user->id)->update([
                    'account_id' => $account_id
                ]);
            }
        }
    }

    public function createGroupsAccount()
    {
        $groups = Group::withTrashed()->get();

        foreach ($groups as $group) {
            if ($group->account) {
                $group->account->update([
                    'name' => $group->name,
                    'account_code' => AccountEnum::GROUP->value,
                ]);
            } else {
                $account = $group->account()->create([
                    'name' => $group->name,
                    'account_code' => AccountEnum::GROUP->value,
                ]);
                $group->update([
                    'account_id' => $account->id
                ]);
            }
        }
    }

    public function createStudentsAccount()
    {
        $students = \App\Models\Student::withTrashed()->get();

        foreach ($students as $student) {
            if ($student->account) {
                $student->account->update([
                    'name' => $student->getFullName(),
                    'account_code' => AccountEnum::STUDENT->value,
                ]);
            } else {
                $account = Account::create([
                    'name' => $student->getFullName(),
                    'account_code' => AccountEnum::STUDENT->value,
                ]);
                $student->update([
                    'account_id' => $account->id
                ]);
            }
        }
    }

    public function createMainAccount()
    {
        $account = Account::where('account_code', AccountEnum::MAIN->value)->first();
        if (!$account) {
            Account::create([
                'name' => 'Asosiy hisob',
                'account_code' => AccountEnum::MAIN->value,
            ]);
        }
    }
}
