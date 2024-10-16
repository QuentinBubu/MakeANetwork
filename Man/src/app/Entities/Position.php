<?php

namespace App\Entities;

use App\Entities\Arret;
use App\Interfaces\TimeInterface;

/**
 * Représente une position entre deux arrêts
 */
abstract class Position implements TimeInterface
{
    /**
     * Arrêt de départ
     *
     * @var Arret
     */
    public ?Arret $arretDepart;
    public ?Arret $arretDestination;
    public int $tick = 0;

    public function __construct(Arret $arretDepart, Arret $arretDestination)
    {
        $this->arretDepart = $arretDepart;
        $this->arretDestination = $arretDestination;
    }

    public function setArret(Arret $arret): void
    {
        $this->arretDepart = $arret;
        $this->tick = 0;
    }

    public function getPosition(): Arret|int
    {
        return $this->arretDepart ?? $this->tick;
    }

    abstract public function incrementTick(): void;
}
