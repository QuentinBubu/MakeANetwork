<?php

namespace App\Entities;

use App\Entities\Arret;

class Position
{
    public Arret $arretDepart;
    public Arret $arretDestination;
    public int $tick = 0;

    public function __construct(Arret $arretDepart, Arret $arretDestination)
    {
        $this->arretDepart = $arretDepart;
        $this->arretDestination = $arretDestination;
    }
}
