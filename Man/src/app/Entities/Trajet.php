<?php

namespace App\Entities;

use DateTime;

/**
 * @Entity
 *
 * Un trajet est un ensemble de routes pour se rendre d'un point A à un point B
 */
class Trajet
{
    public string $nom;
    /**
     * @var Route[]
     */
    public array $routes;
    public Arret $depart;
    public Arret $arrivee;

    public function __construct(string $nom, array $route, Arret $depart, Arret $arrivee)
    {
        $this->nom = $nom;
        $this->routes = $route;
        $this->depart = $depart;
        $this->arrivee = $arrivee;
    }
}
