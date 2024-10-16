<?php

namespace App\Entities;

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
     * Constructeur
     *
     * @param string $nom
     * @param Trajet ...$trajets
     */
    public function __construct(string $nom, Trajet ...$trajets)
    {
        $this->nom = $nom;
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
