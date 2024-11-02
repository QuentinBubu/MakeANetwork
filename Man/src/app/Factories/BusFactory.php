<?php

namespace App\Factories;

use App\Log\Message;
use App\Entities\Bus;
use App\Loaders\Parcours;

class BusFactory
{
    public static function make(array $bus, array $config): Bus
    {
        Message::log("Construction du bus {$bus['type']}", Message::DEBUG_DETAIL);
        return new Bus(
            $config[$bus['type']]['capacite-max'],
            $config[$bus['type']]['vitesse-chargement'],
            $config[$bus['type']]['vitesse-deplacement'],
            $bus['type'],
            Parcours::getParcours($bus['parcours'])
        );
    }
}
