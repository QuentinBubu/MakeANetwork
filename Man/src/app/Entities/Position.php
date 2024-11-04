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

        /** @var Arret $arret */
        while ($parcours->getArretWithIndex($from) !== $arret) {
            $nextArret = $parcours->findNextArret($from);
            $dist += Trajets::findTrajetWithArret(
                depart: $parcours->getArretWithIndex($from),
                arrivee: $parcours->getArretWithIndex($nextArret)
            )->distance;
            $from = $nextArret;
        }

        return ($dist * $multiplicateur) - $this->tick;
    }

    public function tickToNextComming(Parcours $parcours, int $multiplicateur = 1): int
    {
        $from = $parcours->currentArret;
        $dist = 0;

        /** @var Arret $arret */
        do {
            $nextArret = $parcours->findNextArret($from);
            $dist += Trajets::findTrajetWithArret(
                depart: $parcours->getArretWithIndex($from),
                arrivee: $parcours->getArretWithIndex($nextArret)
            )->distance;
            $from = $nextArret;
        } while ($parcours->getArretWithIndex($nextArret) !== $parcours->getArretWithIndex($parcours->currentArret));

        return ($dist * $multiplicateur) - $this->tick;
    }

    abstract public function incrementTick(): void;
}
