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
        Schema::create('branch_room', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('room_id');
            $table->timestamps();
        });

        $branch = \App\Models\Branch::first();
        $rooms = \App\Models\Room::all();

        foreach ($rooms as $room) {
            $branch->rooms()->attach($room->id);
        }

        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn('branch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branch_room');

        Schema::table('rooms', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id')->nullable();
        });
    }
};
