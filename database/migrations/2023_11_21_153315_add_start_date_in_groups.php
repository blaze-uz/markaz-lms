<?php

use App\Models\Group;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE groups ADD COLUMN start_date TIMESTAMP');
        DB::statement('ALTER TABLE groups ADD COLUMN state VARCHAR(255) DEFAULT \'demo\'');

        $groups = Group::withTrashed()->get();
        foreach($groups as $group) {
            if($group->payment_type != null){
                $group->state = 'active';
                $group->update();
            }

            if($group->payment_date){
                $group->start_date = now()->day($group->payment_date);
            } else {
                $group->start_date = $group->created_at;
            }

            $group->update();
        }

        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn('payment_date');
        });
        DB::statement('ALTER TABLE groups ALTER COLUMN start_date SET NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->date('payment_date')->nullable();
        });
        DB::statement('ALTER TABLE groups DROP COLUMN start_date');
        DB::statement('ALTER TABLE groups DROP COLUMN state');
    }
};
