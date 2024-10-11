<?php

namespace Man\App\Entities;

use App\Exceptions\RouteException;

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
        return json_encode($this);
    }

    public function jsonSerialize(): array
    {
        return [
            'nom' => $this->nom,
            'distance' => $this->distance,
            'arrets' => $this->arrets
        ];
    }
}
