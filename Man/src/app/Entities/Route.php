<?php

namespace App\Entities;

use App\Exceptions\RouteException;

/**
 * @Entity
 *
 * Une route est un ensemble de deux arrêts et une distance
 */
class Route
{
    public string $nom;
    public array $arrets = [];
    public int $distance;

    public function __construct(string $nom, int $distance)
    {
        $this->nom = $nom;
        $this->distance = $distance;
    }

    public function registerArret(Arret $arret): self
    {
        if (count($this->arrets) == 2) {
            throw new RouteException('Un arret ne peut pas être ajouté à plus de deux routes');
        }

        $this->arrets[] = $arret;

        return $this;
    }

    public function __tostring(): string
    {
        return $this->nom . ' @' . spl_object_id($this)
            . ' (Distance : ' . $this->distance
            . ' | Arrets : '
            . implode(
                separator: ', ',
                array: array_map(
                    callback: function ($arret) {
                        return 'Arret ' . $arret->nom . ' @' . spl_object_id($arret);
                    },
                    array: $this->arrets
                )
            )
            . ')';
    }

    public function getArrets(): array
    {
        return $this->arrets;
    }
}
