<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeadReminder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'lead_id',
        'reminder_date',
        'reminder_time',
        'reminder_note',
        'reminder_status',
        'created_by',
    ];

    protected array $dates = ['reminder_date', 'reminder_time'];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'lead_id', 'id');
    }

    public function causer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function reads(): HasMany
    {
        return $this->hasMany(LeadReminderRead::class);
    }

    public function scopeDue(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->whereDate('reminder_date', '<', now()->toDateString())
                ->orWhere(function (Builder $q2) {
                    $q2->whereDate('reminder_date', now()->toDateString())
                        ->whereTime('reminder_time', '<=', now()->format('H:i:s'));
                });
        });
    }

    public function scopeNotCompleted(Builder $query): Builder
    {
        return $query->where('reminder_status', '!=', 'completed');
    }
}
