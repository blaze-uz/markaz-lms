<?php

namespace Tests\Feature\Livewire\Report;

use App\Livewire\Report\HomeworkChart;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

class HomeworkChartTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function renders_successfully(): void
    {
        $branch = Branch::create(['name' => 'Report branch', 'status' => 'active']);
        $user = User::factory()->create(['current_branch_id' => $branch->id]);
        $user->branches()->attach($branch->id);

        Livewire::actingAs($user)->test(HomeworkChart::class)
            ->assertStatus(200);
    }
}
