<?php

namespace App\Enums;

enum TransactionTypeEnum: string
{
    case EXPENSE = 'expense';
    case INCOME = 'income';
    case TRANSFER = 'transfer';

    public function text(): string
    {
        return match ($this) {
            self::EXPENSE => __("translation.transaction_types." . self::EXPENSE->value),
            self::INCOME => __("translation.transaction_types." . self::INCOME->value),
            self::TRANSFER => __("translation.transaction_types." . self::TRANSFER->value),
        };
    }

    public function class(): string
    {
        return match ($this) {
            self::EXPENSE => "text-danger",
            self::INCOME => 'text-success',
            self::TRANSFER => 'text-warning',
        };
    }

    public function typeClassIcon(): string
    {
        return match ($this) {
            self::EXPENSE => "mdi mdi-arrow-up-circle",
            self::INCOME => "mdi mdi-arrow-down-circle",
            self::TRANSFER => "mdi mdi-arrow-right-circle",
        };
    }
}
