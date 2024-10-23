<?php

namespace App\Entities;

use App\Loaders\Trajets;

/**
 * @Entity
 *
 * Un parcours est décrit par un ensemble de trajets
 */
class Parcours
{
    /**
     * Nom du parcours
     *
     * @var string
     */
    public string $nom;

    /**
     * Liste des trajets
     *
     * @var Trajet[]
     */
    public array $trajets = [];

    /**
     * Liste des arrêts à faire
     *
     * @var Arret[]
     */
    public array $arretsAFaire = [];

    /**
     * Constructeur
     *
     * @param string $nom
     * @param Arret[] $arretsAFaire
     * @param Trajet ...$trajets
     */
    public function __construct(string $nom, array $arretsAFaire, Trajet ...$trajets)
    {
        $this->nom = $nom;
        $this->arretsAFaire = $arretsAFaire;
        $this->trajets = $trajets;
    }

    /**
     * Ajoute un trajet au parcours
     *
     * @param Trajet $trajet
     * @return self
     */
    public function addTrajet(Trajet $trajet): self
    {
        $this->trajets[] = $trajet;
        return $this;
    }

    public function getNextArret(Arret $arret): ?Arret
    {
        $index = array_search($arret, $this->arretsAFaire);
        return $index == array_key_last($this->arretsAFaire) ? null : $this->arretsAFaire[$index + 1];
    }

    /**
     * Retourne une représentation textuelle du parcours
     *
     * @return string
     */
    public function __tostring(): string
    {
        return implode(
            separator: ' -> ',
            array: array_map(
                callback: function ($route) {
                    return $route->nom;
                },
                array: $this->trajets
            )
        );
    }
}
