<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // For PostgreSQL, we need to use USING clause to convert string to boolean
            // First, normalize the data: non-empty strings become '1', empty/null become '0'
            DB::statement("UPDATE attendances SET homework = CASE WHEN homework IS NOT NULL AND homework != '' THEN '1' ELSE '0' END");
            
            // Change column type to boolean with explicit USING clause for PostgreSQL
            DB::statement("ALTER TABLE attendances ALTER COLUMN homework TYPE BOOLEAN USING (homework::text = '1' OR homework::text = 'true')");
            
            // Set default value
            DB::statement("ALTER TABLE attendances ALTER COLUMN homework SET DEFAULT false");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // For PostgreSQL, convert boolean back to string with explicit USING clause
            DB::statement("ALTER TABLE attendances ALTER COLUMN homework TYPE VARCHAR USING (CASE WHEN homework = true THEN 'yes' ELSE NULL END)");
            
            // Make nullable
            DB::statement("ALTER TABLE attendances ALTER COLUMN homework DROP DEFAULT");
        });
    }
};
