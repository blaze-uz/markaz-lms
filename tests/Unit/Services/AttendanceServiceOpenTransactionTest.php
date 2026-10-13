<?php

namespace Tests\Unit\Services;

use App\Services\AttendanceService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Saving a register that already exists for that group and day returned
 * from inside AttendanceService's transaction without closing it. Nothing was
 * written — but the transaction stayed open until the request ended, so whatever
 * the rest of that request wrote went down with it, and (since sync jobs run after commit) a job
 * dispatched after it would wait for a commit that never comes.
 */
class AttendanceServiceOpenTransactionTest extends TestCase
{
    private string $database;

    protected function setUp(): void
    {
        parent::setUp();

        $this->database = tempnam(sys_get_temp_dir(), 'lms-attendance-');

        config([
            'database.default' => 'probe',
            'database.connections.probe' => [
                'driver' => 'sqlite',
                'database' => $this->database,
                'prefix' => '',
                'foreign_key_constraints' => false,
            ],
        ]);
        DB::purge('probe');

        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('group_attendance_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('group_id');
            $table->timestamps();
        });
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('group_id');
            $table->string('status');
            $table->date('date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('groups')->insert(['id' => 1, 'name' => 'Matematika']);
        DB::table('students')->insert(['id' => 5]);
        DB::table('attendances')->insert(['student_id' => 5, 'group_id' => 1, 'status' => 'present', 'date' => '2026-09-22']);
    }

    protected function tearDown(): void
    {
        DB::purge('probe');
        @unlink($this->database);

        parent::tearDown();
    }

    public function test_saving_a_register_that_already_exists_closes_its_transaction(): void
    {
        (new AttendanceService)->store(['date' => '22.09.2026', 'status' => [5 => 'absent']], 1);

        $this->assertSame(0, DB::transactionLevel(), 'store() returned with its transaction still open.');
        $this->assertSame('present', DB::table('attendances')->value('status'));
    }

    public function test_the_telegram_register_closes_its_transaction_too(): void
    {
        (new AttendanceService)->storeTelegram(['date' => '22.09.2026', 'status' => [5 => 'absent']], 1);

        $this->assertSame(0, DB::transactionLevel(), 'storeTelegram() returned with its transaction still open.');
    }
}
