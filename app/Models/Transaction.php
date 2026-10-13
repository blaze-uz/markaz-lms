<?php

namespace App\Models;

use App\Enums\TransactionTypeEnum;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends BaseEntity
{
    use SoftDeletes;

    protected $fillable = [
        'description',
        'from_account_id',
        'to_account_id',
        'amount',
        'type',
        'number',
        'created_by',
        'transactionable_id',
        'transactionable_type'
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i',
        'type' => TransactionTypeEnum::class
    ];

    protected static function booting(): void
    {
        parent::booting();

        self::creating(function ($model) {
            $model->number = self::generateNumber();

            if ($model->from_account_id) {
                $fromAccount = Account::find($model->from_account_id);
                if ($fromAccount) {
                    $fromAccount->balance -= $model->amount;
                    $fromAccount->save();
                }
            }

            if ($model->to_account_id) {
                $toAccount = Account::find($model->to_account_id);
                if ($toAccount) {
                    $toAccount->balance += $model->amount;
                    $toAccount->save();
                }
            }
        });

        self::updating(function ($model) {
            if ($model->isDirty('from_account_id')) {
                $fromAccount = Account::find($model->getOriginal('from_account_id'));
                if ($fromAccount) {
                    $fromAccount->balance += $model->getOriginal('amount');
                    $fromAccount->balance -= $model->amount;
                    $fromAccount->save();
                }
            }

            if ($model->isDirty('to_account_id')) {
                $toAccount = Account::find($model->getOriginal('to_account_id'));
                if ($toAccount) {
                    $toAccount->balance -= $model->getOriginal('amount');
                    $toAccount->balance += $model->amount;
                    $toAccount->save();
                }
            }
        });

        self::deleting(function ($model) {
            if ($model->from_account_id) {
                $fromAccount = Account::find($model->from_account_id);
                if ($fromAccount) {
                    $fromAccount->balance += $model->amount;
                    $fromAccount->save();
                }
            }

            if ($model->to_account_id) {
                $toAccount = Account::find($model->to_account_id);
                if ($toAccount) {
                    $toAccount->balance -= $model->amount;
                    $toAccount->save();
                }
            }
        });
    }

    public static function generateNumber(): string
    {
        $maxId = self::withTrashed()
            ->max('id');

        return "TRA-" . str_pad(strval($maxId + 1), 7, "0", STR_PAD_LEFT);
    }

    public function creator(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function transactionable(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }

    public function from_account()
    {
        return $this->belongsTo(Account::class, 'from_account_id');
    }

    public function to_account()
    {
        return $this->belongsTo(Account::class, 'to_account_id');
    }
}
