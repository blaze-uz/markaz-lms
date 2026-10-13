<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('branch_group', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('group_id');
            $table->timestamps();
        });

        $branch = \App\Models\Branch::first();
        $groups = \App\Models\Group::withTrashed()->get();
        foreach ($groups as $group) {
            $branch->groups()->attach($group->id);
        }

        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn('branch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branch_group');

        Schema::table('groups', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id')->nullable();
        });
    }
};
