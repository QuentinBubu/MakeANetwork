<?php

namespace Man\App\Entities;

class Bus
{
    public int $capacite;
    public float $vitesseChargement;
    public float $vitesseDeplacement;
    public array $patcours;

    public function __construct(int $capacite, float $vitesseChargement, float $vitesseDeplacement, array $patcours)
    {
        $this->capacite = $capacite;
        $this->vitesseChargement = $vitesseChargement;
        $this->vitesseDeplacement = $vitesseDeplacement;
        $this->patcours = $patcours;
    }
}
