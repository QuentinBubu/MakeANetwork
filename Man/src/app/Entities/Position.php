<?php

namespace App\Entities;

use App\Entities\Arret;

/**
 * Représente une position entre deux arrêts
 */
class Position
{
    /**
     * Undocumented variable
     *
     * @var Arret
     */
    public Arret $arretDepart;
    public Arret $arretDestination;
    public int $tick = 0;

    public function __construct(Arret $arretDepart, Arret $arretDestination)
    {
        $this->arretDepart = $arretDepart;
        $this->arretDestination = $arretDestination;
    }
}
