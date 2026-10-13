<?php

namespace Tests\Feature;

use App\Livewire\Product\ProductsTable;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SecurityPermissionsTest extends TestCase
{
    use DatabaseTransactions;

    public function test_staff_payments_require_permission(): void
    {
        $user = $this->staff();
        $transactions = Transaction::withTrashed()->count();

        $this->actingAs($user)
            ->post(route('admin.users.payment', $user), [
                'payment_type' => 'salary',
                'amount' => 100000,
                'payment_month' => now()->format('Y-m'),
            ])
            ->assertForbidden();

        $this->assertSame($transactions, Transaction::withTrashed()->count());
    }

    public function test_group_debt_changes_require_permission(): void
    {
        $this->actingAs($this->staff());

        $this->put(route('admin.groups.update-debt', 1), ['amount' => 100000])->assertForbidden();
        $this->patch(route('admin.groups.mark-no-debt', 1))->assertForbidden();
        $this->delete(route('admin.groups.delete-debt', 1))->assertForbidden();
        $this->post(route('admin.groups.store-debt', 1), ['amount' => 100000])->assertForbidden();
    }

    public function test_livewire_product_changes_require_permission(): void
    {
        $products = Product::count();

        foreach (['saveProduct', 'addStock', 'updateProduct'] as $method) {
            Livewire::actingAs($this->staff())
                ->test(ProductsTable::class)
                ->call($method)
                ->assertForbidden();
        }

        $this->assertSame($products, Product::count());
    }

    public function test_web_branch_switch_requires_membership(): void
    {
        $user = $this->staff();
        $otherBranch = Branch::create(['name' => 'Other branch']);

        $this->actingAs($user)
            ->post(route('admin.branches.change-current'), ['branch_id' => $otherBranch->id])
            ->assertNotFound();

        $this->assertSame($user->current_branch_id, $user->fresh()->current_branch_id);
    }

    public function test_api_branch_switch_requires_membership(): void
    {
        $user = $this->staff();
        $otherBranch = Branch::create(['name' => 'Other branch']);
        Sanctum::actingAs($user);

        $this->postJson('/api/branches/change-current', ['branch_id' => $otherBranch->id])->assertNotFound();

        $this->assertSame($user->current_branch_id, $user->fresh()->current_branch_id);
    }

    public function test_a_missing_current_branch_uses_the_users_membership(): void
    {
        Branch::create(['name' => 'Unassigned branch']);
        $user = $this->staff();
        $branchId = $user->current_branch_id;
        $user->update(['current_branch_id' => null]);

        $this->actingAs($user)->get(route('admin.lang', 'uz'))->assertRedirect();

        $this->assertSame($branchId, $user->fresh()->current_branch_id);
    }

    public function test_public_registration_is_disabled(): void
    {
        $this->get('/register')->assertNotFound();
        $this->post('/register')->assertNotFound();
    }

    public function test_public_teacher_directory_hides_private_fields_and_other_staff(): void
    {
        $teacher = $this->staff();
        $teacher->assignRole(Role::findOrCreate('Teacher', 'web'));
        $otherStaff = $this->staff();

        $this->getJson('/api/teachers/'.$teacher->id)
            ->assertOk()
            ->assertJsonPath('user.name', $teacher->name)
            ->assertJsonMissingPath('user.phone')
            ->assertJsonMissingPath('user.email')
            ->assertJsonMissingPath('user.password')
            ->assertJsonMissingPath('user.remember_token');

        $this->getJson('/api/teachers')
            ->assertOk()
            ->assertJsonMissing(['name' => $otherStaff->name])
            ->assertJsonMissingPath('teachers.0.phone')
            ->assertJsonMissingPath('teachers.0.email');

        $this->getJson('/api/teachers/'.$otherStaff->id)->assertNotFound();
    }

    private function staff(): User
    {
        $branch = Branch::create(['name' => 'Assigned branch']);
        $user = User::factory()->create(['current_branch_id' => $branch->id]);
        $user->branches()->attach($branch);

        return $user;
    }
}
