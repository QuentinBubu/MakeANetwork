<?php

namespace App\Entities;

use App\Entities\Arret;
use App\Interfaces\TimeInterface;
use App\Loaders\Trajets;

/**
 * Représente une position entre deux arrêts
 */
abstract class Position implements TimeInterface
{
    /**
     * Arrêt de présence
     *
     * @var Arret
     */
    public ?Arret $arret;

    /**
     * Arrêt suivant
     *
     * @var Arret
     */
    public ?Arret $nextArret;

    /**
     * Arrêt précédent
     *
     * @var Arret
     */
    public ?Arret $previousArret;

    public int $tick = 0;

    /**
     * Arrive à un arrêt, réinitialise les ticks
     * @param Arret $arret
     * @return void
     */
    public function setArret(Arret $arret): void
    {
        $this->arret = $arret;
        $this->tick = 0;
    }

    /**
     * Met le prochain arrêt
     * @param Arret $arret
     * @return void
     */
    public function setNextArret(Arret $arret): void
    {
        $this->nextArret = $arret;
    }

    /**
     * Met l'arrêt suivant
     * @param Arret $arret
     * @return void
     */
    public function setPreviousArret(Arret $arret): void
    {
        $this->previousArret = $arret;
    }

    /**
     * Changement d'arrêt :
     *   - L'arrêt précédent devient celui où il est
     *   - L'arrêt courant devient le prochain
     *
     * Le bus fais A B C D, il démarre de A, previous = null, arret = A, next = B, tick = 0
     * Il arrive à B, previous = A, arret = B, next = C, tick = 0
     * @param Trajet $trajet
     * @return void
     */
    public function arriveArret(Trajet $trajet): void
    {
        // On fait un décallage
        $this->previousArret = $this->arret;
        $this->arret = $this->nextArret; // ou trajet->depart
        $this->nextArret = $trajet->arrivee;
        $this->tick = 0;
    }

    public function getPosition(): Arret|int
    {
        return $this->arret ?? $this->tick;
    }

    public function tickTo(Parcours $parcours, Arret $arret, int $multiplicateur = 1): int
    {
        $from = $this->arret;
        $dist = 0;

        while ($parcours->getNextArret($from) !== null && $from !== $arret) {
            $next = $parcours->getNextArret($from);
            $dist += Trajets::findTrajetWithArret($from, $next)->distance;
            $from = $next;
        }
        return ($dist * $multiplicateur) - $this->tick;
    }

    abstract public function incrementTick(): void;
}
