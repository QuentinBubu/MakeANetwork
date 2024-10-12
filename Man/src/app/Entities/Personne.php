<?php

namespace App\Entities;

/**
 * @Entity
 *
 * Une personne est décrite par leur nom, un trajet aller et un trajet retour
 */
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

    public function setArretActuel(Arret $arret): void
    {
        
    }
}
