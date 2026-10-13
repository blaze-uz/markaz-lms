<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sms', function (Blueprint $table): void {
            $table->string('gateway_batch_id')->nullable()->index()->after('external_id');
        });

        $this->widenExternalId();
    }

    /**
     * Reverse the migrations.
     *
     * `external_id` is deliberately left as a string: narrowing it back would
     * throw away every gateway id already stored in it.
     */
    public function down(): void
    {
        Schema::table('sms', function (Blueprint $table): void {
            $table->dropColumn('gateway_batch_id');
        });
    }

    private function widenExternalId(): void
    {
        $connection = Schema::getConnection();

        if ($connection->getDriverName() === 'pgsql') {
            // Postgres refuses to cast bigint to varchar on its own.
            $connection->statement('ALTER TABLE sms ALTER COLUMN external_id TYPE VARCHAR(255) USING external_id::varchar');

            return;
        }

        Schema::table('sms', function (Blueprint $table): void {
            $table->string('external_id')->nullable()->change();
        });
    }
};
