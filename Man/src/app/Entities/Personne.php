<?php

namespace App\Entities;

use App\Interfaces\StateInterface;
use App\Loaders\Personnes;
use App\Loaders\PathFinder;
use App\Enums\TrajetEnCoursEnum;
use App\Timer\Time;

/**
 * @Entity
 *
 * Une personne est décrite par leur nom, un trajet aller et un trajet retour
 */
class Personne implements StateInterface
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

    public array $arretsVisites = [];

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
        $this->lastBus = null;
        $this->setArretActuel($trajetAller->depart);
    }

    public function setArretActuel(Arret $arret): void
    {
        echo "La personne {$this->nom} est à l'arrêt {$arret->nom}\n";
        $this->arretsVisites[] = $arret;
        $arret->addPersonne($this);
    }

    public function getTrajetEnCours(): Trajet
    {
        return $this->trajetEnCours === TrajetEnCoursEnum::ALLER ? $this->trajetAller : $this->trajetRetour;
    }

    public function finFinal()
    {
        Personnes::unregister($this);
    }

    public function export(): array
    {
        return [
            'nom' => $this->nom,
            'trajetAller' => $this->trajetAller->nom,
            'trajetRetour' => $this->trajetRetour->nom,
            'trajetEnCours' => $this->trajetEnCours->name,
            'lastBus' => $this->lastBus ? spl_object_id($this->lastBus) : null,
            'arretsVisites' => array_map(fn ($arret) => $arret->nom, $this->arretsVisites)
        ];
    }

    public function restore(array $state): void
    {
        throw new \Exception("Not implemented");
    }
}
