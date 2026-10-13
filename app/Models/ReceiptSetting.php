<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ReceiptSetting extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'header_text',
        'footer_text',
        'logo_position',
        'show_logo',
        'show_header',
        'show_payment_id',
        'show_student_info',
        'show_group_info',
        'show_payment_type',
        'show_cashier',
        'show_amount',
        'show_footer',
    ];

    protected $casts = [
        'show_logo' => 'boolean',
        'show_header' => 'boolean',
        'show_payment_id' => 'boolean',
        'show_student_info' => 'boolean',
        'show_group_info' => 'boolean',
        'show_payment_type' => 'boolean',
        'show_cashier' => 'boolean',
        'show_amount' => 'boolean',
        'show_footer' => 'boolean',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')
            ->singleFile();
    }
}
