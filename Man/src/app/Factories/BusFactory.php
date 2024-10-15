<?php

namespace App\Factories;

use App\Entities\Bus;
use App\Loaders\Parcours;

class BusFactory
{
    public static function make(array $bus, array $config): Bus
    {
        return new Bus(
            $config[$bus['type']]['capacite-max'],
            $config[$bus['type']]['vitesse-chargement'],
            $config[$bus['type']]['vitesse-deplacement'],
            Parcours::getParcours($bus['parcours'])
        );
    }
}
