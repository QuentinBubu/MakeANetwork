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
    public int $tick = 0;

    public function tickTo(Parcours $parcours, Arret $arret, int $multiplicateur = 1): int
    {
        $from = $parcours->currentArret;
        $dist = 0;

        while ($parcours->findNextArret($from) !== null && $parcours->getArretWithIndex($from) !== $arret) {
            $next = $parcours->findNextArret($from);
            $dist += Trajets::findTrajetWithArret($parcours->getArretWithIndex($from), $parcours->getArretWithIndex($next))->distance;
            $from = $next;
        }
        return ($dist * $multiplicateur) - $this->tick;
    }

    abstract public function incrementTick(): void;
}
