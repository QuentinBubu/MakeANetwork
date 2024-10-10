<?php

namespace Man\App\Entities;

class Personne
{
    public Trajet $trajetAller;
    public Trajet $trajetRetour;
    public string $nom;

    public function __construct(Trajet $trajetAller, Trajet $trajetRetour, string $nom)
    {
        $this->trajetAller = $trajetAller;
        $this->trajetRetour = $trajetRetour;
        $this->nom = $nom;
    }
}
