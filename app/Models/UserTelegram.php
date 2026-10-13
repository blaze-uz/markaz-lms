<?php

namespace App\Models;

use App\Traits\UsesDynamicConnection;
use Illuminate\Database\Eloquent\Model;

class UserTelegram extends Model
{
    use UsesDynamicConnection;
    
    protected $fillable = [
        'user_id',
        'chat_id',
        'status',
    ];

    /**
     * Get the user that owns the telegram connection
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
