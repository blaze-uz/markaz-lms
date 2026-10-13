<?php

namespace Tests\Unit\Queue;

use App\Jobs\BaseJob;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RunAfterCommitTest extends TestCase
{
    private string $database;

    protected function setUp(): void
    {
        parent::setUp();

        // A file, not :memory: — every connection to :memory: is its own database,
        // which would hide what these tests are about: what a reconnect does to work
        // the request has not committed yet.
        $this->database = tempnam(sys_get_temp_dir(), 'lms-context-');

        config([
            'database.default' => 'probe',
            'database.connections.probe' => [
                'driver' => 'sqlite',
                'database' => $this->database,
                'prefix' => '',
                'foreign_key_constraints' => false,
            ],
            'queue.default' => 'sync',
        ]);
        DB::purge('probe');

        Schema::create('probe_rows', fn (Blueprint $table) => $table->id());

        ContextProbeJob::$ran = 0;
        ContextProbeJob::$seenConnection = null;
        FailingProbeJob::$attempts = 0;
        FailingProbeJob::$failedWith = null;
    }

    protected function tearDown(): void
    {
        DB::purge('probe');
        @unlink($this->database);

        parent::tearDown();
    }

    public function test_a_sync_job_does_not_end_the_transaction_it_was_dispatched_in(): void
    {
        // StudentPaymentService::payment() dispatches the payment SMS job in the
        // middle of its transaction, and the sync queue runs the job right there.
        DB::beginTransaction();
        DB::table('probe_rows')->insert(['id' => 1]);

        ContextProbeJob::dispatch();

        DB::commit();

        $this->assertSame(1, ContextProbeJob::$ran);
        $this->assertSame(1, DB::table('probe_rows')->count(), 'The dispatching request lost the row it inserted before the job ran.');
    }

    public function test_a_sync_job_runs_on_the_connection_of_the_request_that_dispatched_it(): void
    {
        DB::beginTransaction();
        DB::table('probe_rows')->insert(['id' => 1]);

        ContextProbeJob::dispatch();

        DB::commit();

        $this->assertSame('probe', ContextProbeJob::$seenConnection);
    }

    public function test_a_sync_job_waits_for_the_transaction_that_dispatched_it_to_commit(): void
    {
        // The SMS for a payment must not run while the payment can still
        // roll back, and the row it reads must be committed by then.
        DB::beginTransaction();
        DB::table('probe_rows')->insert(['id' => 1]);

        ContextProbeJob::dispatch();
        $ranBeforeCommit = ContextProbeJob::$ran;

        DB::commit();

        $this->assertSame(0, $ranBeforeCommit);
        $this->assertSame(1, ContextProbeJob::$ran);
    }

    public function test_a_sync_job_waits_for_the_outermost_commit_not_a_savepoint(): void
    {
        // StudentPaymentService::payment() opens a transaction and
        // createPaymentWithTransaction() nests one inside it; the payment SMS is
        // dispatched between the inner commit and the outer one.
        DB::beginTransaction();
        DB::beginTransaction();
        DB::table('probe_rows')->insert(['id' => 1]);
        DB::commit();

        ContextProbeJob::dispatch();
        $ranBeforeOuterCommit = ContextProbeJob::$ran;

        DB::commit();

        $this->assertSame(0, $ranBeforeOuterCommit);
        $this->assertSame(1, ContextProbeJob::$ran);
    }

    public function test_a_sync_job_never_runs_for_a_transaction_that_rolled_back(): void
    {
        DB::beginTransaction();
        DB::table('probe_rows')->insert(['id' => 1]);

        ContextProbeJob::dispatch();

        DB::rollBack();

        $this->assertSame(0, ContextProbeJob::$ran, 'An SMS went out for a payment that was never saved.');
        $this->assertSame(0, DB::table('probe_rows')->count());
    }

    public function test_a_failing_sync_job_does_not_undo_or_interrupt_the_request_that_dispatched_it(): void
    {
        // On Redis a failing SMS never touched the payment that queued it. On
        // sync it used to throw into the request after the payment was saved,
        // which is an error page inviting the operator to enter it again.
        DB::beginTransaction();
        DB::table('probe_rows')->insert(['id' => 1]);

        FailingProbeJob::dispatch();

        DB::commit();

        $this->assertSame(1, DB::table('probe_rows')->count());
        $this->assertSame(1, FailingProbeJob::$attempts);
        $this->assertInstanceOf(\RuntimeException::class, FailingProbeJob::$failedWith, 'The job\'s failed() hook was not told.');
    }

    public function test_a_failing_sync_job_outside_a_transaction_runs_at_once_and_is_not_thrown(): void
    {
        FailingProbeJob::dispatch();

        $this->assertSame(1, FailingProbeJob::$attempts);
        $this->assertInstanceOf(\RuntimeException::class, FailingProbeJob::$failedWith);
    }

    public function test_a_sync_job_that_asks_to_be_retried_later_is_logged_because_it_will_not_be(): void
    {
        Log::spy();

        ReleasingProbeJob::dispatch();

        Log::shouldHaveReceived('warning')
            ->withArgs(fn (string $message, array $context) => str_contains($message, 'cannot retry')
                && $context['job'] === ReleasingProbeJob::class)
            ->once();
    }
}

class ContextProbeJob extends BaseJob
{
    public static int $ran = 0;

    public static ?string $seenConnection = null;

    public function handle(): void
    {
        static::$ran++;
        static::$seenConnection = config('database.default');
    }
}

class FailingProbeJob extends BaseJob
{
    public static int $attempts = 0;

    public static ?\Throwable $failedWith = null;

    public function handle(): void
    {
        static::$attempts++;

        throw new \RuntimeException('SMS gateway is down');
    }

    public function failed(\Throwable $exception): void
    {
        static::$failedWith = $exception;
    }
}

class ReleasingProbeJob extends BaseJob
{
    public function handle(): void
    {
        $this->release(60);
    }
}
