<?php

namespace Tests\Feature\Livewire\Fixtures;

class UpdateHydrationCanary
{
    public static int $instances = 0;

    public function __construct()
    {
        self::$instances++;
    }
}
