<?php

namespace App\Entities;

/**
 * @Entity
 *
 * Un parcours est décrit par un ensemble de trajets
 */
class Parcours
{
    public string $nom;
    public array $trajets = [];

    public function __construct(string $nom, Trajet ...$trajets)
    {
        $this->nom = $nom;
        $this->trajets = $trajets;
    }

    public function addTrajet(Trajet $trajet): self
    {
        $this->trajets[] = $trajet;
        return $this;
    }

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
