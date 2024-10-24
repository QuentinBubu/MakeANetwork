<?php

namespace App\Enums;

enum BusStateEnum
{
    /**
     * FLUX_VOYAGEURS : Le bus est en train de charger ou décharger des voyageurs
     */
    case FLUX_VOYAGEURS;

    /**
     * DEPLACEMENT : Le bus roule
     */
    case DEPLACEMENT;
}
