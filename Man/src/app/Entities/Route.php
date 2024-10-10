<?php

namespace Man\App\Entities;

class Route
{
    public string $nom;
    public Arret $depart;
    public Arret $arrivee;
    public int $distance;

    private array $genericArrets;

    public function __construct(string $nom, array $arrets, int $distance)
    {
        $this->nom = $nom;
        $this->genericArrets = $arrets;
        $this->distance = $distance;
    }
}
