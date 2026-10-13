<?php

namespace App\Models;

use App\Traits\UsesDynamicConnection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasMedia
{
    use HasApiTokens, HasFactory, HasRoles, InteractsWithMedia, LogsActivity, Notifiable, SoftDeletes, UsesDynamicConnection;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'phone',
        'birthday',
        'percentage',
        'stars',
        'account_id',
        'current_branch_id',
        'telegram_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $appends = ['age', 'avatar_url'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        self::created(function ($model) {
            cache()->forget('teachers');
        });

        self::updated(function ($model) {
            cache()->forget('teachers');
        });

        self::deleted(function ($model) {
            cache()->forget('teachers');
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->dontSubmitEmptyLogs()
            ->logOnlyDirty();
    }

    public function getLogNameToUse(): string
    {
        return 'user';
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image');
        $this->addMediaCollection('avatar');
    }

    public function getAvatarUrlAttribute(): ?string
    {
        $avatar = $this->getFirstMedia('avatar');

        return $avatar ? $avatar->getUrl() : null;
    }

    public function getFormattedPhone()
    {
        $phone = $this->phone;
        if (strlen($phone) == 12) {
            return '+'.substr($phone, 0, 3).'('.substr($phone, 3, 2).')'.substr($phone, 5, 3).'-'.substr($phone, 8, 2).'-'.substr($phone, 10, 2);
        } elseif (strlen($phone) == 9) {
            return '+998('.substr($phone, 0, 2).')'.substr($phone, 2, 3).'-'.substr($phone, 5, 2).'-'.substr($phone, 7, 2);
        }

        return $phone;
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function groups(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Group::class, 'teacher_id');
    }

    public function activeGroups(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Group::class, 'teacher_id')->where('state', 'active');
    }

    public function completedGroups(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Group::class, 'teacher_id')->where('state', 'completed');
    }

    public function staffPayments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(StaffPayment::class);
    }

    public function branches(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Branch::class, 'branch_user');
    }

    public function departments(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Department::class, 'department_user');
    }

    public function getBranchIds(): array
    {
        return $this->branches()->pluck('branch_id')->toArray();
    }

    public function currentBranch(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Branch::class, 'current_branch_id');
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('Admin');
    }

    public function isTeacher(): bool
    {
        return $this->hasRole('Teacher');
    }

    public function penalties(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(StaffPenalty::class);
    }

    public function payments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Payment::class, 'created_by');
    }

    public function expenses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Expense::class, 'created_by');
    }

    public function user_contract(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(UserContract::class);
    }

    public function user_contracts(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(UserContract::class, 'user_user_contract')->withPivot('paid_at', 'transaction_id');
    }

    public function user_telegrams(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserTelegram::class);
    }

    public function text_suggestions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TextSuggestion::class);
    }

    public function getAgeAttribute(): int
    {
        return now()->diffInYears($this->birthday);
    }

    public function refund_payments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(RefundPayment::class, 'created_by');
    }

    public function menus(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Menu::class, 'user_menu')
            ->withPivot('order')
            ->orderBy('order');
    }

    public function messages(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserMessage::class);
    }

    public function assignedStudentReminders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(StudentReminder::class, 'assigned_user_id');
    }
}
