<?php

namespace App\Entities;

use DateTime;

class Trajet
{
    public Route $route;
    public DateTime $heureDepart;
    public Arret $depart;
    public Arret $arrivee;

    public function __construct(Route $route, DateTime $heureDepart, Arret $depart, Arret $arrivee)
    {
        $this->route = $route;
        $this->heureDepart = $heureDepart;
        $this->depart = $depart;
        $this->arrivee = $arrivee;
    }
}
