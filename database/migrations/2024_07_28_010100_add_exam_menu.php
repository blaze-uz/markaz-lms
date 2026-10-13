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
        $menu = \App\Models\Menu::where('name', 'Imtihonlar')->first();
        if ($menu) {
            return;
        }
        \App\Models\Menu::create([
            'name' => [
                'uz' => 'Imtihonlar',
                'ru' => 'Экзамены',
            ],
            'route' => 'admin.exams.index',
            'menu_active_route' => ["admin.exams.*"],
            'icon' => 'mdi mdi-book-open-page-variant-outline',
            'permissions' => [],
            'parent_id' => null,
            'order' => 13,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
