<?php

namespace Tests\Feature\Livewire\Report;

use App\Livewire\Report\Leads\UserCallsTable;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class LeadUserCallsTableTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('database.default', 'sqlite');
        Config::set('database.connections.sqlite.database', ':memory:');

        DB::purge('sqlite');
        DB::connection('sqlite')->getPdo();

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->timestamps();
        });

        Schema::create('lead_calls', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('lead_id');
            $table->unsignedBigInteger('causer_id');
            $table->string('status');
            $table->text('comment')->nullable();
            $table->unsignedInteger('duration')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function test_it_shows_total_and_average_lead_call_duration_by_operator(): void
    {
        $operatorId = DB::table('users')->insertGetId([
            'name' => 'Operator Ali',
            'email' => 'operator@example.com',
            'created_at' => '2026-07-01 09:00:00',
            'updated_at' => '2026-07-01 09:00:00',
        ]);
        $otherOperatorId = DB::table('users')->insertGetId([
            'name' => 'Operator Vali',
            'email' => 'operator-vali@example.com',
            'created_at' => '2026-07-01 09:00:00',
            'updated_at' => '2026-07-01 09:00:00',
        ]);

        DB::table('lead_calls')->insert([
            [
                'lead_id' => 1,
                'causer_id' => $operatorId,
                'status' => 'answered',
                'duration' => 120,
                'created_at' => '2026-07-01 09:00:00',
                'updated_at' => '2026-07-01 09:00:00',
            ],
            [
                'lead_id' => 1,
                'causer_id' => $operatorId,
                'status' => 'declined',
                'duration' => 240,
                'created_at' => '2026-07-02 10:00:00',
                'updated_at' => '2026-07-02 10:00:00',
            ],
            [
                'lead_id' => 1,
                'causer_id' => $otherOperatorId,
                'status' => 'answered',
                'duration' => 600,
                'created_at' => '2026-06-30 10:00:00',
                'updated_at' => '2026-06-30 10:00:00',
            ],
        ]);

        Livewire::test(UserCallsTable::class, [
            'startDate' => '2026-07-01',
            'endDate' => '2026-07-02',
        ])
            ->assertSee('Operator Ali')
            ->assertSee('6m 00s')
            ->assertSee('3m 00s')
            ->assertDontSee('Operator Vali')
            ->assertStatus(200);
    }
}
