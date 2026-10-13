<?php

namespace Tests\Feature\Livewire\Report;

use App\Livewire\Report\Leads\OperatorResponsibilityTable;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class LeadOperatorResponsibilityTableTest extends TestCase
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

        Schema::create('leads', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('causer_id')->nullable();
            $table->unsignedBigInteger('student_id')->nullable();
            $table->string('status')->default('new');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('lead_calls', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('lead_id');
            $table->unsignedBigInteger('causer_id');
            $table->string('status');
            $table->unsignedInteger('duration')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function test_it_reports_operator_responsible_leads_calls_and_student_conversions(): void
    {
        $operatorId = DB::table('users')->insertGetId([
            'name' => 'Operator Ali',
            'email' => 'ali@example.com',
            'created_at' => '2026-05-01 09:00:00',
            'updated_at' => '2026-05-01 09:00:00',
        ]);

        $otherOperatorId = DB::table('users')->insertGetId([
            'name' => 'Operator Vali',
            'email' => 'vali@example.com',
            'created_at' => '2026-05-01 09:00:00',
            'updated_at' => '2026-05-01 09:00:00',
        ]);

        DB::table('leads')->insert([
            [
                'id' => 1,
                'name' => 'Lead One',
                'causer_id' => $operatorId,
                'status' => 'student',
                'student_id' => 10,
                'created_at' => '2026-05-02 09:00:00',
                'updated_at' => '2026-05-03 09:00:00',
                'deleted_at' => '2026-05-03 09:00:00',
            ],
            [
                'id' => 2,
                'name' => 'Lead Two',
                'causer_id' => $operatorId,
                'status' => 'new',
                'student_id' => null,
                'created_at' => '2026-05-04 09:00:00',
                'updated_at' => '2026-05-04 09:00:00',
                'deleted_at' => null,
            ],
            [
                'id' => 3,
                'name' => 'Lead Three',
                'causer_id' => $otherOperatorId,
                'status' => 'new',
                'student_id' => null,
                'created_at' => '2026-05-05 09:00:00',
                'updated_at' => '2026-05-05 09:00:00',
                'deleted_at' => null,
            ],
            [
                'id' => 4,
                'name' => 'Old Lead',
                'causer_id' => $operatorId,
                'status' => 'student',
                'student_id' => 11,
                'created_at' => '2026-04-30 09:00:00',
                'updated_at' => '2026-04-30 09:00:00',
                'deleted_at' => null,
            ],
        ]);

        DB::table('lead_calls')->insert([
            [
                'lead_id' => 1,
                'causer_id' => $operatorId,
                'status' => 'answered',
                'duration' => 120,
                'created_at' => '2026-05-02 10:00:00',
                'updated_at' => '2026-05-02 10:00:00',
            ],
            [
                'lead_id' => 1,
                'causer_id' => $operatorId,
                'status' => 'not_answered',
                'duration' => null,
                'created_at' => '2026-05-02 11:00:00',
                'updated_at' => '2026-05-02 11:00:00',
            ],
            [
                'lead_id' => 2,
                'causer_id' => $operatorId,
                'status' => 'answered',
                'duration' => 180,
                'created_at' => '2026-05-04 10:00:00',
                'updated_at' => '2026-05-04 10:00:00',
            ],
            [
                'lead_id' => 3,
                'causer_id' => $otherOperatorId,
                'status' => 'busy',
                'duration' => 60,
                'created_at' => '2026-05-05 10:00:00',
                'updated_at' => '2026-05-05 10:00:00',
            ],
        ]);

        Livewire::test(OperatorResponsibilityTable::class, [
            'startDate' => '2026-05-01',
            'endDate' => '2026-05-31',
        ])
            ->assertSee('Operator Ali')
            ->assertSee('2')
            ->assertSee('3')
            ->assertSee('1')
            ->assertSee('50%')
            ->assertSee('5m 00s')
            ->assertSee('Operator Vali')
            ->assertStatus(200);
    }
}
