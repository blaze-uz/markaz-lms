<?php

namespace Tests\Feature;

use App\Enums\AccountEnum;
use App\Models\Account;
use App\Models\Branch;
use App\Models\Course;
use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Paying a teacher out of their groups' balances, from the users show page.
 *
 * The split sends the study centre's share to the branch account (or the
 * code-1000 main account) and the rest to the teacher. Resolving that
 * destination used to read `$group->branch`, a relation Group does not have,
 * so it always fell through to the main account — and where none existed the
 * null destination raised a 500 before anything moved. A missing main account
 * is now opened rather than reported.
 */
class TeacherGroupPayoutTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_splits_each_group_balance_between_the_main_account_and_the_teacher(): void
    {
        [$admin, $teacher, $group] = $this->makePayoutFixture(teacherPercentage: 60, balance: 100000);
        $mainAccount = $this->makeAccount(AccountEnum::MAIN->value, 0);

        $response = $this->actingAs($admin)->post(route('admin.users.payment', $teacher->id), [
            'payment_type' => 'group',
            'amount' => '',
            'account_id' => '',
            'payment_month' => now()->format('Y-m'),
        ]);

        $response->assertRedirect(route('admin.users.show', ['user' => $teacher->id]));
        $response->assertSessionHas('success_alert');

        $this->assertSame(0, (int) $group->account->fresh()->balance);
        $this->assertSame(60000, (int) $teacher->account->fresh()->balance);
        $this->assertSame(40000, (int) $mainAccount->fresh()->balance);
    }

    public function test_it_sends_the_centre_share_to_the_branch_account_when_the_branch_has_one(): void
    {
        [$admin, $teacher, $group, $branch] = $this->makePayoutFixture(teacherPercentage: 60, balance: 100000);
        $mainAccount = $this->makeAccount(AccountEnum::MAIN->value, 0);
        $branchAccount = $this->makeAccount(AccountEnum::MAIN->value, 0);
        $branch->update(['account_id' => $branchAccount->id]);

        $this->actingAs($admin)->post(route('admin.users.payment', $teacher->id), [
            'payment_type' => 'group',
            'amount' => '',
            'account_id' => '',
            'payment_month' => now()->format('Y-m'),
        ]);

        $this->assertSame(40000, (int) $branchAccount->fresh()->balance);
        $this->assertSame(0, (int) $mainAccount->fresh()->balance);
        $this->assertSame(60000, (int) $teacher->account->fresh()->balance);
    }

    public function test_it_opens_a_main_account_when_the_installation_has_none(): void
    {
        [$admin, $teacher, $group, $branch] = $this->makePayoutFixture(teacherPercentage: 60, balance: 100000);
        Account::where('account_code', AccountEnum::MAIN->value)->delete();
        $branch->update(['account_id' => null]);

        $response = $this->actingAs($admin)->post(route('admin.users.payment', $teacher->id), [
            'payment_type' => 'group',
            'amount' => '',
            'account_id' => '',
            'payment_month' => now()->format('Y-m'),
        ]);

        $response->assertSessionHas('success_alert');

        $created = Account::where('account_code', AccountEnum::MAIN->value)->first();
        $this->assertNotNull($created);
        $this->assertSame(40000, (int) $created->balance);
        $this->assertSame($created->id, $branch->fresh()->account_id);

        $this->assertSame(0, (int) $group->account->fresh()->balance);
        $this->assertSame(60000, (int) $teacher->account->fresh()->balance);
    }

    public function test_it_refuses_the_payout_when_the_teacher_has_no_account(): void
    {
        [$admin, $teacher, $group] = $this->makePayoutFixture(teacherPercentage: 60, balance: 100000);
        $this->makeAccount(AccountEnum::MAIN->value, 0);
        $teacher->update(['account_id' => null]);

        $response = $this->actingAs($admin)->post(route('admin.users.payment', $teacher->id), [
            'payment_type' => 'group',
            'amount' => '',
            'account_id' => '',
            'payment_month' => now()->format('Y-m'),
        ]);

        $response->assertSessionHas('error_alert');
        $this->assertSame(100000, (int) $group->account->fresh()->balance);
    }

    public function test_it_uses_the_group_percentage_over_the_teacher_default(): void
    {
        [$admin, $teacher, $group] = $this->makePayoutFixture(teacherPercentage: 60, balance: 100000);
        $this->makeAccount(AccountEnum::MAIN->value, 0);
        $group->update(['teacher_percentage' => 90]);

        $this->actingAs($admin)->post(route('admin.users.payment', $teacher->id), [
            'payment_type' => 'group',
            'amount' => '',
            'account_id' => '',
            'payment_month' => now()->format('Y-m'),
        ]);

        $this->assertSame(90000, (int) $teacher->account->fresh()->balance);
    }

    private function makeAccount(int $code, int $balance): Account
    {
        return Account::create([
            'name' => 'test-'.$code.'-'.uniqid(),
            'account_code' => $code,
            'balance' => $balance,
            'status' => 'active',
        ]);
    }

    /**
     * @return array{0: User, 1: User, 2: Group, 3: \App\Models\Branch}
     */
    private function makePayoutFixture(int $teacherPercentage, int $balance): array
    {
        Account::where('account_code', AccountEnum::MAIN->value)->delete();
        $branch = Branch::create(['name' => 'Payout branch', 'status' => 'active']);
        $admin = User::factory()->create(['current_branch_id' => $branch->id]);
        $admin->branches()->attach($branch->id);
        $admin->givePermissionTo(Permission::findOrCreate('user-payment', 'web'));
        $admin->assignRole(Role::findOrCreate('Admin', 'web'));
        $course = Course::create(['name' => 'Payout course', 'price' => 100000]);

        $teacher = User::create([
            'name' => 'Payout teacher '.uniqid(),
            'email' => 'payout-'.uniqid().'@example.test',
            'password' => bcrypt('secret'),
            'percentage' => $teacherPercentage,
            'account_id' => $this->makeAccount(AccountEnum::STAFF->value, 0)->id,
        ]);
        $teacher->branches()->attach($branch->id);

        $group = Group::create([
            'name' => 'Payout group '.uniqid(),
            'course_id' => $course->id,
            'start_date' => now()->toDateString(),
            'teacher_id' => $teacher->id,
            'price' => 100000,
            'payment_type' => 'date_to_date',
            'state' => 'active',
            'account_id' => $this->makeAccount(AccountEnum::GROUP->value, $balance)->id,
        ]);
        $group->branches()->attach($branch->id);

        return [$admin, $teacher, $group->fresh(), $branch];
    }
}
