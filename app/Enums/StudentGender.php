<?php

namespace App\Enums;

enum StudentGender: string
{
    case MALE = 'male';
    case FEMALE = 'female';

    public function getText(): string
    {
        return __("attr." . $this->value);
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::MALE => 'mdi mdi-human-male',
            self::FEMALE => 'mdi mdi-human-female',
        };
    }
}
