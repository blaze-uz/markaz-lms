<?php

namespace App\Traits;

trait HasDateRange
{
    public $date;
    public $startDate;
    public $endDate;

    public function updatedDate($value)
    {
        if ($value) {
            $dates = explode(' - ', $value);
            $this->startDate = $dates[0];
            $this->endDate = $dates[1] ?? $dates[0];
        } else {
            $this->startDate = null;
            $this->endDate = null;
        }
    }
}
