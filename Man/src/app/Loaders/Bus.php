<?php

namespace App\Loaders;

use App\Entities\Bus as EntitiesBus;
use App\Factories\BusFactory;

class Bus
{
    public static array $buses = [];

    public static function load(array $bus, array $config): void
    {
        /** @var array $b */
        foreach ($bus as $b) {
            self::$buses[] = BusFactory::make($b, $config);
        }
    }

    public static function export(): array
    {
        $data = [];
        /** @var EntitiesBus $bus */
        foreach (self::$buses as $bus) {
            $data[spl_object_id($bus)] = $bus->export();
        }
        return $data;
    }
}
