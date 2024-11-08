<?php

namespace App\Timer;

use App\Interfaces\TimeInterface;
use App\Timer\Time;

/**
 * La classe Timer gère un minuteur qui décrémente un compteur de ticks à chaque unité de temps.
 * Ce minuteur est intégré dans le système global de gestion du temps (Time).
 */
class Timer implements TimeInterface
{
    /**
     * Le nombre de ticks restants sur ce minuteur.
     * 
     * @var int
     */
    private int $remainingTicks = 0;

    /**
     * Constructeur de la classe Timer.
     * 
     * Initialise un minuteur avec un nombre de ticks restants.
     * Le minuteur est ensuite enregistré dans le gestionnaire de temps global (Time).
     *
     * @param int $remainingTicks Le nombre de ticks restants pour ce minuteur.
     */
    public function __construct(int $remainingTicks)
    {
        $this->remainingTicks = $remainingTicks;
        Time::registerClass($this);  // Enregistrement dans le gestionnaire de temps global
    }

    /**
     * Retourne le nombre actuel de ticks restants.
     * 
     * @return int Le nombre de ticks restants.
     */
    public function getRemainingTicks(): int
    {
        return $this->remainingTicks;
    }

    /**
     * Incrémente le nombre de ticks restants de 1.
     * Cette méthode peut être utilisée pour ajouter des ticks supplémentaires si nécessaire.
     */
    public function incrementTicks(): void
    {
        $this->remainingTicks++;
    }

    /**
     * Décrémente le nombre de ticks restants de 1.
     * Cette méthode est appelée à chaque tick global pour faire avancer le minuteur.
     */
    public function incrementTick(): void
    {
        $this->remainingTicks--;
    }

    /**
     * Retourne une représentation lisible de l'état actuel du minuteur.
     * Affiche le nombre de ticks restants sous forme de chaîne de caractères.
     * 
     * @return string Représentation sous forme de chaîne de caractères du minuteur.
     */
    public function __toString()
    {
        return "Temps restant : " . $this->remainingTicks;
    }
}
