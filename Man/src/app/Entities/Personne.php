<?php

namespace App\Entities;

use App\Loaders\Personnes;
use App\Loaders\PathFinder;
use App\Enums\TrajetEnCoursEnum;
use App\Timer\Time;

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

    public TrajetEnCoursEnum $trajetEnCours;

    /**
     * Dernier bus pris (pour éviter qu'il remonte dans le même bus)
     *
     * @var Bus
     */
    public ?Bus $lastBus;

    /**
     * Nom de la personne
     *
     * @var string
     */
    public string $nom;

    public Position $position;

    public Bus $busAPrendre;

    public array $arretsVisites = [];

    public Route $routeEnCours;

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
        $this->trajetEnCours = TrajetEnCoursEnum::ALLER;
        $this->position = new PersonnePosition();
        $this->lastBus = null;
        $this->setArretActuel($trajetAller->depart);
    }

    public function setArretActuel(Arret $arret): void
    {
        $this->arretsVisites[] = $arret;
        $this->routeEnCours = $this->getTrajetEnCours()->getRouteFromArret($arret, $this->arretsVisites);
        $arret->addPersonne($this, $this->routeEnCours, Time::getTick());
    }

    public function getTrajetEnCours(): Trajet
    {
        return $this->trajetEnCours === TrajetEnCoursEnum::ALLER ? $this->trajetAller : $this->trajetRetour;
    }

    public function finFinal()
    {
        Personnes::unregister($this);
    }
}
