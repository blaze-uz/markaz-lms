<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Group;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SetMainBranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * @throws \Exception
     */
    public function run(): void
    {
        DB::beginTransaction();
        try {
            $this->createMainBranch();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function attachUsersToBranch($branch): void
    {
        $users = User::all();

        foreach ($users as $user) {
            $user->branches()->attach($branch->id);
        }
    }

    private function setGroupsBranchId($branch): void
    {
        $groups = Group::all();

        foreach ($groups as $group) {
            $group->branch_id = $branch->id;
            $group->save();
        }
    }

    private function setPaymentsBranchId($branch): void
    {
        $payments = Payment::all();

        foreach ($payments as $payment) {
            $payment->branch_id = $branch->id;
            $payment->save();
        }
    }

    private function createMainBranch(): void
    {
        // Create main branch
        $branch = Branch::create([
            'name' => 'Asosiy filial',
            'status' => 'active',
            'account_id' => 1,
        ]);

        // Attach all users to main branch
        $this->attachUsersToBranch($branch);

        // Set groups branch_id to main branch
        $this->setGroupsBranchId($branch);

        // Set payment branch_id to main branch
        $this->setPaymentsBranchId($branch);
    }
}
