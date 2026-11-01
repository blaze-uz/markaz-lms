<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Group\GroupTimeForm;
use App\Livewire\Group\StudentsList;
use App\Livewire\Product\ProductsTable;
use App\Livewire\Student\StudentProductOrdersTable;
use App\Models\Branch;
use App\Models\Course;
use App\Models\Group;
use App\Models\GroupTime;
use App\Models\Product;
use App\Models\ProductOrder;
use App\Models\Room;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\Feature\Livewire\Fixtures\UpdateHydrationCanary;
use Tests\Feature\Livewire\Fixtures\UpdateHydrationProbe;
use Tests\TestCase;

/**
 * Livewire 3.6.4 (CVE-2025-54068) changed how `updates` are read: the signed
 * snapshot alone picks a synthesizer, metadata in the request is plain data.
 * These cases pin the update shapes the admin pages send, so a Livewire bump
 * cannot change them unnoticed. The app has no Livewire\Form class, so Form
 * objects and support Collections run on a probe component.
 */
class UpdateHydrationTest extends TestCase
{
    use DatabaseTransactions;

    private Branch $branch;

    private User $actor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create(['name' => 'Hydration branch']);
        $this->actor = User::factory()->create(['current_branch_id' => $this->branch->id]);
        $this->actor->branches()->attach($this->branch);
        $this->actingAs($this->actor);
    }

    public function test_nested_array_keys_build_an_order(): void
    {
        $this->actor->givePermissionTo(Permission::findOrCreate('product-order-store', 'web'));
        $student = $this->student();
        $product = $this->product(['price' => 15000, 'stock' => 10]);

        Livewire::test(StudentProductOrdersTable::class, ['student' => $student])
            ->set('orderItems.0.product_id', $product->id)
            ->set('orderItems.0.quantity', 3)
            ->call('addProductLine')
            ->set('orderItems.1.product_id', $product->id)
            ->call('removeProductLine', 1)
            ->assertCount('orderItems', 1)
            ->call('saveOrder')
            ->assertHasNoErrors();

        $this->assertEquals(45000, ProductOrder::query()->where('student_id', $student->id)->sole()->total_price);
        $this->assertSame(7, (int) $product->fresh()->stock);
    }

    public function test_checkbox_array_takes_whole_values_and_removed_keys(): void
    {
        $group = $this->group();
        $student = $this->student();
        $group->students()->attach($student, ['attached_at' => today()->startOfMonth(), 'amount' => 100000]);

        Livewire::test(StudentsList::class, ['group_id' => $group->id])
            ->set('selectedStudents', [(string) $student->id, '0'])
            ->assertSet('selectedStudents', [(string) $student->id, '0'])
            ->set('selectedStudents.1', '__rm__')
            ->assertSet('selectedStudents', [(string) $student->id]);
    }

    public function test_filter_array_narrows_the_list_by_key_and_as_a_whole(): void
    {
        $this->product(['name' => 'ShownNeedle', 'status' => 'active']);
        $this->product(['name' => 'HiddenNeedle', 'status' => 'inactive']);

        Livewire::test(ProductsTable::class)
            ->set('filters.status', 'inactive')
            ->assertSee('HiddenNeedle')->assertDontSee('ShownNeedle')
            ->set('filters', ['status' => 'active', 'price_min' => null, 'price_max' => null])
            ->assertSee('ShownNeedle')->assertDontSee('HiddenNeedle')
            ->call('resetFilters')
            ->assertSet('filters.status', '');
    }

    public function test_paginator_array_moves_through_the_custom_pagination_view(): void
    {
        $this->product(['name' => 'OldestNeedle']);
        foreach (range(1, 10) as $number) {
            $this->product(['name' => 'Newer product '.$number]);
        }

        Livewire::test(ProductsTable::class)
            ->assertDontSee('OldestNeedle')
            ->assertSeeHtml('class="pagination"')
            ->assertSeeHtml('wire:click="gotoPage(2,')
            ->call('gotoPage', 2)
            ->assertSet('paginators.page', 2)
            ->assertSee('OldestNeedle')
            ->call('previousPage')
            ->assertDontSee('OldestNeedle');
    }

    public function test_eloquent_collection_properties_survive_an_update(): void
    {
        $student = $this->student();
        $product = $this->product();

        $component = Livewire::test(StudentProductOrdersTable::class, ['student' => $student])
            ->set('notes', 'round trip');

        $this->assertInstanceOf(EloquentCollection::class, $component->get('availableProducts'));
        $this->assertTrue($component->get('availableProducts')->contains($product));
    }

    public function test_form_component_keeps_day_keys_apart(): void
    {
        $room = Room::create(['name' => 'Hydration room', 'status' => 'available']);
        $room->branches()->attach($this->branch);
        GroupTime::create([
            'group_id' => $this->group()->id,
            'name' => 'monday',
            'room_id' => $room->id,
            'start' => '09:00',
            'end' => '10:30',
        ]);

        Livewire::test(GroupTimeForm::class, ['group_id' => $this->group()->id])
            ->set('monday.start', '09:30')
            ->set('monday.end', '11:00')
            ->set('monday.room_id', $room->id)
            ->call('checkRoom', 'monday', $room->id)
            ->assertSet('monday.errors', [__('translation.groups.room_not_available')])
            ->assertSet('tuesday.room_id', null)
            ->call('clearSelectedRoom', 'monday')
            ->assertSet('monday.room_id', null);
    }

    public function test_support_collection_takes_key_and_whole_updates(): void
    {
        $component = Livewire::test(UpdateHydrationProbe::class)
            ->set('tags.0', 'edited')
            ->assertSet('tags.0', 'edited')
            ->set('tags', ['first', 'second']);

        $this->assertInstanceOf(Collection::class, $component->get('tags'));
        $this->assertSame(['first', 'second'], $component->get('tags')->all());
    }

    public function test_form_object_takes_scalar_and_array_updates(): void
    {
        Livewire::test(UpdateHydrationProbe::class)
            ->set('form.name', 'Ali')
            ->set('form.phones.0', '901234567')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('saved', ['name' => 'Ali', 'phones' => ['901234567']])
            ->set('form.phones', ['not a phone'])
            ->call('save')
            ->assertHasErrors('form.phones.0');
    }

    public function test_metadata_in_the_request_is_only_data(): void
    {
        UpdateHydrationCanary::$instances = 0;
        $tuple = [[], ['s' => 'clctn', 'class' => UpdateHydrationCanary::class]];

        Livewire::test(UpdateHydrationProbe::class)
            ->set('filters', ['status' => $tuple])
            ->assertSet('filters.status', $tuple);

        $this->assertSame(0, UpdateHydrationCanary::$instances);
    }

    /** @param  array<string, mixed>  $attributes */
    private function product(array $attributes = []): Product
    {
        return Product::create($attributes + ['name' => 'Notebook', 'price' => 10000, 'stock' => 100, 'min_stock' => 5, 'status' => 'active']);
    }

    private function student(): Student
    {
        $student = Student::create(['name' => 'Hydration', 'surname' => 'Student', 'phone' => '901234567']);
        $student->branches()->attach($this->branch);

        return $student;
    }

    private function group(): Group
    {
        $group = Group::create([
            'name' => 'Hydration group',
            'course_id' => Course::create(['name' => 'Hydration course'])->id,
            'teacher_id' => $this->actor->id,
            'price' => 100000,
            'start_date' => today(),
        ]);
        $group->branches()->attach($this->branch);

        return $group;
    }
}
