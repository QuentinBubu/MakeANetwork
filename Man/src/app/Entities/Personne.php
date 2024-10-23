<?php

namespace App\Entities;

/**
 * @Entity
 *
 * Une personne est décrite par leur nom, un trajet aller et un trajet retour
 */
class Personne
{
    /**
     * Trajet aller de la personne
     *
     * @var Trajet
     */
    public Trajet $trajetAller;

    /**
     * Trajet retour
     *
     * @var Trajet
     */
    public Trajet $trajetRetour;

    /**
     * Dernier bus pris (pour éviter qu'il remonte dans le même bus)
     *
     * @var Bus
     */
    public Bus $lastBus;

    /**
     * Nom de la personne
     *
     * @var string
     */
    public string $nom;

    public Position $position;

    /**
     * Constructeur
     *
     * @param Trajet $trajetAller
     * @param Trajet $trajetRetour
     * @param string $nom
     */
    public function __construct(Trajet $trajetAller, Trajet $trajetRetour, string $nom)
    {
        $this->trajetAller = $trajetAller;
        $this->trajetRetour = $trajetRetour;
        $this->nom = $nom;
        $this->setArretActuel($trajetAller->depart);
        $this->position = new PersonnePosition();
    }

    public function setArretActuel(Arret $arret): void
    {
        // $this->position->setArret($arret);
    }

    public function setNextBus(): void
    {
        
    }

    public function getNextBus(): Bus
    {
        // $this->lastBus = $this->position->getNextBus();
        return $this->lastBus;
    }
}
