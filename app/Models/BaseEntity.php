<?php

namespace App\Models;

use App\Traits\UsesDynamicConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BaseEntity extends Model
{

    use UsesDynamicConnection;
    
    protected string $url_key = 'id';

    /**
     * @var string[]
     */
    protected $guarded = ['id'];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [];

    public function getUrlKey()
    {
        return $this->{$this->url_key};
    }

    /**
     * @return BelongsTo
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withDefault([
            'full_name' => __("translation.system"),
        ]);
    }

    /**
     * @return BelongsTo
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by')->withDefault([
            'full_name' => __("translation.system"),
        ]);
    }
}
