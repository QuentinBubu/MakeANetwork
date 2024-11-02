<?php

namespace App\Entities;

use App\Log\Message;
use App\Loaders\Trajets;
use App\Exceptions\RouteException;
use App\Interfaces\StateInterface;

/**
 * @Entity
 *
 * Une route est un ensemble de deux arrêts et une distance
 */
class Route implements StateInterface
{
    public string $nom;

    /**
     * @var Arret[]
     */
    public array $arrets = [];

    /**
     * Liste des bus qui passent par cette route
     * @var Bus[]
     */
    public array $bus = [];

    public int $distance;

    public function __construct(string $nom, int $distance)
    {
        $this->nom = $nom;
        $this->distance = $distance;
    }

    public function registerArret(Arret $arret): self
    {
        Message::log("Ajout de l'arrêt {$arret->nom} à la route {$this->nom}", Message::DEBUG_ALL);
        if (count($this->arrets) == 2) {
            throw new RouteException('Un arrêt ne peut pas être ajouté à plus de deux routes');
        }

        $this->arrets[] = $arret;
        if (count($this->arrets) == 2) {
            Message::log("Ajout de la route {$this->nom} à la liste des trajets", Message::DEBUG_ALL);
            Trajets::addTrajet($this);
        }

        return $this;
    }

    public function calculDistanceAvecBus(Bus $bus): int
    {
        return $this->distance * $bus->vitesseDeplacement;
    }

    public function getNextArret(Arret $arret): Arret
    {
        return array_values(
            array_filter(
                $this->arrets,
                function ($a) use ($arret) {
                    return $a !== $arret;
                }
            )
        )[0];
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

    public function export(): array
    {
        return [
            'nom' => $this->nom,
            'distance' => $this->distance,
            'arrets' => array_map(
                function ($arret) {
                    return $arret->nom;
                },
                $this->arrets
            ),
            'bus' => array_map(
                function ($bus) {
                    return spl_object_id($bus);
                },
                $this->bus
            ),
        ];
    }

    public function hasArret(Arret $arret): bool
    {
        return in_array($arret, $this->arrets);
    }

    public function restore(array $state): void
    {
        throw new \Exception('Not implemented');
    }
}
