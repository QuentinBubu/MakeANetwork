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

        $allNextArrets = $parcours->findAllNextArretsObj($parcours->currentArret);

        /** @var Arret $row */
        foreach ($allNextArrets as $row) {
            $dist += Trajets::findTrajetWithArret($from, $row)->distance;
            $from = $row;
        }

        $dist += Trajets::findTrajetWithArret($from, $arret)->distance;

        return ($dist * $multiplicateur) - $this->tick;
    }

    abstract public function incrementTick(): void;
}
