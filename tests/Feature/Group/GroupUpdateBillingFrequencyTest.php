<?php

namespace Tests\Feature\Group;

use App\Jobs\CalculateDebtForStudent;
use App\Models\Group;
use App\Services\GroupService;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Bus;
use Mockery;
use Tests\TestCase;

class GroupUpdateBillingFrequencyTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function mockGroupWithNoBillingHistory(string $paymentType): Group
    {
        $group = Mockery::mock(Group::class)->makePartial();
        $group->shouldReceive('getAttribute')->with('payment_type')->andReturn($paymentType);
        $group->shouldReceive('getAttribute')->with('start_date')->andReturn(null);
        $group->shouldReceive('getAttribute')->with('lesson_count')->andReturn(null);
        $group->shouldReceive('getAttribute')->with('duration_months')->andReturn(null);
        $group->shouldReceive('getAttribute')->with('price')->andReturn(100000);

        $emptyRelation = Mockery::mock(HasMany::class);
        $emptyRelation->shouldReceive('count')->andReturn(0);
        $emptyRelation->shouldReceive('where')->andReturnSelf();
        $emptyRelation->shouldReceive('delete')->andReturn(0);

        $group->shouldReceive('attendances')->andReturn($emptyRelation);
        $group->shouldReceive('studentGroupPayments')->andReturn($emptyRelation);

        return $group;
    }

    public function test_switching_to_one_time_updates_payment_type_and_recalculates_debt(): void
    {
        Bus::fake();

        $group = $this->mockGroupWithNoBillingHistory('date_to_date');
        $group->shouldReceive('update')->once()->with(Mockery::on(function ($data) {
            return $data['payment_type'] === 'one_time';
        }))->andReturnTrue();

        (new GroupService(new Group()))->updateBillingFrequency($group, 'one_time');

        Bus::assertDispatched(CalculateDebtForStudent::class);
    }

    public function test_switching_to_recurring_from_one_time_defaults_to_date_to_date(): void
    {
        Bus::fake();

        $group = $this->mockGroupWithNoBillingHistory('one_time');
        $group->shouldReceive('update')->once()->with(Mockery::on(function ($data) {
            return $data['payment_type'] === 'date_to_date';
        }))->andReturnTrue();

        (new GroupService(new Group()))->updateBillingFrequency($group, 'recurring');

        Bus::assertDispatched(CalculateDebtForStudent::class);
    }

    public function test_switching_to_recurring_preserves_existing_lesson_count_billing(): void
    {
        Bus::fake();

        $group = $this->mockGroupWithNoBillingHistory('by_count_lessons');
        $group->shouldNotReceive('update');

        (new GroupService(new Group()))->updateBillingFrequency($group, 'recurring');

        Bus::assertNotDispatched(CalculateDebtForStudent::class);
    }

    public function test_requesting_the_same_frequency_is_a_no_op(): void
    {
        Bus::fake();

        $group = $this->mockGroupWithNoBillingHistory('one_time');
        $group->shouldNotReceive('update');

        (new GroupService(new Group()))->updateBillingFrequency($group, 'one_time');

        Bus::assertNotDispatched(CalculateDebtForStudent::class);
    }
}
