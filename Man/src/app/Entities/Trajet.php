<?php

namespace App\Entities;

/**
 * @Entity
 *
 * Un trajet est un ensemble de routes pour se rendre d'un point A à un point B
 */
class Trajet
{
    public string $nom;
    /**
     * @var Route[]
     */
    public array $routes;
    public Arret $depart;
    public Arret $arrivee;
    public int $distance;
    public int $tickArrivee; // Stats sur temps attente ? :)

    public function __construct(string $nom, array $route, Arret $depart, Arret $arrivee, int $distance)
    {
        $this->nom = $nom;
        $this->routes = $route;
        $this->depart = $depart;
        $this->arrivee = $arrivee;
        $this->distance = $distance;
        $this->tickArrivee = $distance;
    }

    public function getEtapes(): array
    {
        $etapes = [];
        foreach ($this->routes as $route) {
            foreach ($route->getArrets() as $arret) {
                // Ajouter les arrêts aux étapes si ce n'est pas déjà le cas
                if (!in_array($arret, $etapes)) {
                    $etapes[] = $arret;
                }
            }
        }
        return $etapes;
    }
}
