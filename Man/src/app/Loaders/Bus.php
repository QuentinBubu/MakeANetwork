<?php

namespace App\Loaders;

use App\Factories\BusFactory;

class Bus
{
    public static array $buses = [];

    public static function load(array $bus, array $config): void
    {
        foreach ($bus as $b) {
            self::$buses[] = BusFactory::make($b, $config);
        }
    }
}
