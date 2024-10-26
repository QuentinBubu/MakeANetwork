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
        $from = $parcours->getCurrentArretObj();
        $dist = 0;

        while ($parcours->findNextArretObj($from) !== null && $from !== $arret) {
            $next = $parcours->findNextArretObj($from);
            $dist += Trajets::findTrajetWithArret($from, $next)->distance;
            $from = $next;
        }
        return ($dist * $multiplicateur) - $this->tick;
    }

    abstract public function incrementTick(): void;
}
