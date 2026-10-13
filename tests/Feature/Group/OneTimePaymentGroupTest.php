<?php

namespace Tests\Feature\Group;

use App\Jobs\CalculateDebtForStudent;
use App\Models\Group;
use Illuminate\Support\Carbon;
use Mockery;
use Tests\TestCase;

class OneTimePaymentGroupTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        Mockery::close();
        parent::tearDown();
    }

    public function test_calculate_debt_job_creates_one_time_debts_when_start_date_has_arrived(): void
    {
        $group = Mockery::mock(Group::class)->makePartial();
        $group->shouldReceive('getAttribute')->with('state')->andReturn('active');
        $group->shouldReceive('getAttribute')->with('payment_type')->andReturn('one_time');
        $group->shouldReceive('getAttribute')->with('start_date')
            ->andReturn(Carbon::now()->subDay());

        $job = Mockery::mock(CalculateDebtForStudent::class, [collect([$group])])->makePartial();
        $job->shouldReceive('calculateOneTimeDebt')->once()->with($group);
        $job->shouldNotReceive('calculateDebt');

        $job->handle();

        $this->addToAssertionCount(1);
    }

    public function test_calculate_debt_job_waits_for_future_one_time_start_date(): void
    {
        $group = Mockery::mock(Group::class)->makePartial();
        $group->shouldReceive('getAttribute')->with('state')->andReturn('active');
        $group->shouldReceive('getAttribute')->with('start_date')
            ->andReturn(Carbon::now()->addDay());

        $job = Mockery::mock(CalculateDebtForStudent::class, [collect([$group])])->makePartial();
        $job->shouldNotReceive('calculateOneTimeDebt');
        $job->shouldNotReceive('calculateDebt');

        $job->handle();

        $this->addToAssertionCount(1);
    }

    public function test_one_time_range_string_format(): void
    {
        Carbon::setTestNow('2026-03-27 12:00:00');

        $range = (new CalculateDebtForStudent())->getOneTimeDebtRange(42);

        $this->assertStringStartsWith('one_time/', $range);

        $parts = explode('/', $range);
        $this->assertCount(3, $parts);
        $this->assertEquals('one_time', $parts[0]);
        $this->assertEquals('42', $parts[1]);
        $this->assertEquals('2026-03-27', $parts[2]);
    }

    public function test_two_students_get_unique_one_time_ranges(): void
    {
        $student1Id = 10;
        $student2Id = 20;
        $date = now()->format('Y-m-d');

        $range1 = 'one_time/'.$student1Id.'/'.$date;
        $range2 = 'one_time/'.$student2Id.'/'.$date;

        $this->assertNotEquals($range1, $range2, 'Each student must get a unique one_time range.');
    }
}
