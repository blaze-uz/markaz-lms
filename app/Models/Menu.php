<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Menu extends Model
{
    use HasTranslations;

    protected $fillable = [
        'name',
        'route',
        'menu_active_route',
        'icon',
        'parent_id',
        'order',
        'permissions'
    ];

    public $translatable = ['name'];

    protected $casts = [
        'permissions' => 'array',
        'menu_active_route' => 'array'
    ];

    public function parent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    public function children(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Menu::class, 'parent_id');
    }

    public function users(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_menu', 'menu_id', 'user_id')
            ->withPivot('order');
    }
}
