<?php

namespace App\Enums;

/**
 * Enumération représentant les deux états d'un trajet en cours.
 * Cette énumération est utilisée pour définir si le trajet est dans le sens "ALLER" ou "RETOUR".
 */
enum TrajetEnCoursEnum
{
    /**
     * ALLER : Indique que la personne effectue son trajet aller.
     */
    case ALLER;

    /**
     * RETOUR : Indique que la personne effectue son trajet retour.
     */
    case RETOUR;
}
