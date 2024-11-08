<?php

namespace App\Enums;

/**
 * Enumération représentant les différents états d'un bus.
 * Cette énumération permet de définir de manière claire et lisible les états dans lesquels un bus peut se trouver.
 */
enum BusStateEnum
{
    /**
     * FLUX_VOYAGEURS : L'état où le bus est en train de charger ou décharger des voyageurs.
     */
    case FLUX_VOYAGEURS;

    /**
     * DEPLACEMENT : L'état où le bus roule.
     */
    case DEPLACEMENT;
}
