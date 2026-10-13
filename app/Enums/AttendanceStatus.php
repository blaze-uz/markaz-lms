<?php

namespace App\Enums;

enum AttendanceStatus: string
{
    case PRESENT = 'present';
    case ABSENT = 'absent';

    public function getText(): string
    {
        return __("attr." . $this->value);
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::PRESENT => 'mdi mdi-human-male',
            self::ABSENT => 'mdi mdi-human-female',
        };
    }

    public function getBgColor(): string
    {
        return match ($this) {
            self::PRESENT => 'bg-success-subtle',
            self::ABSENT => 'bg-danger-subtle',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::PRESENT => 'text-success',
            self::ABSENT => 'text-danger',
        };
    }
}
