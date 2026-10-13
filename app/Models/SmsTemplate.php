<?php

namespace App\Models;

use App\Traits\UsesDynamicConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SmsTemplate extends Model
{
    use SoftDeletes, UsesDynamicConnection;

    protected $fillable = [
        'name',
        'content',
        'type', //teacher, student, parent
        'status',
        'eskiz_status',
        'eskiz_template_id',
        'message',
        'template',
        'sample',
        'sent_to_moderation_at',
        'moderation_response_at',
        'moderation_response'
    ];

    protected $casts = [
        'sent_to_moderation_at' => 'datetime',
        'moderation_response_at' => 'datetime',
    ];

    const STATUS_NOT_REGISTERED = 'not_registered';
    const STATUS_MODERATION = 'moderation';
    const STATUS_REGISTERED = 'registered';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_NOT_REGISTERED => __('translation.sms.status_not_registered'),
            self::STATUS_MODERATION => __('translation.sms.status_moderation'),
            self::STATUS_REGISTERED => __('translation.sms.status_registered'),
        ];
    }

    public static function search($search)
    {
        return empty($search) ? static::query()
            : static::query()->where('name', 'ilike', '%' . $search . '%');
    }

    /**
     * Reset template to not_registered status and clear moderation tracking fields
     */
    public function resetToNotRegistered()
    {
        $this->update([
            'status' => self::STATUS_NOT_REGISTERED,
            'sent_to_moderation_at' => null,
            'moderation_response_at' => null,
            'moderation_response' => null,
        ]);
    }
}
