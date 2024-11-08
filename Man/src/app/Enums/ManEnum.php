<?php

namespace App\Enums;

/**
 * Enumération représentant les différents états du programme.
 * Elle améliore la lisibilité et la gestion des états tout au long du programme.
 */
enum ManEnum
{
    /**
     * RUNNING : L'état où le processus est en cours d'exécution.
     */
    case RUNNING;

    /**
     * SUCCEEDED : L'état où le processus a été mené à bien avec succès.
     */
    case SUCCEEDED;

    /**
     * PAUSED : L'état où le processus a été mis en pause.
     */
    case PAUSED;

    /**
     * WAITING_START : L'état où le processus attend le démarrage ou une condition préalable avant de commencer.
     */
    case WAITING_START;

    /**
     * UNINITIALIZED : L'état où le processus n'a pas encore été initialisé.
     */
    case UNUNITIALIZED;
}
