<?php

namespace App\Entities;

use App\Interfaces\StateInterface;

/**
 * @Entity
 *
 * Un trajet est un ensemble de routes pour se rendre d'un point A à un point B
 */
class Trajet implements StateInterface
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

    public function getRouteFromArret(Arret $depart, array $arretsVisites): Route
    {
        foreach ($this->routes as $route) {
            $arrets = $route->arrets;
            $indexDepart = array_search($depart, $arrets, true);
    
            // Si l'arrêt de départ existe dans la route
            if ($indexDepart !== false) {
                // Calcul de l'indice du prochain arrêt dans un parcours circulaire
                $indexProchainArret = ($indexDepart + 1) % count($arrets);
                $prochainArret = $arrets[$indexProchainArret];
    
                // Vérifie que le prochain arrêt n'a pas déjà été visité
                if (!in_array($prochainArret, $arretsVisites, true)) {
                    return $route;
                }
            }
        }
        throw new \Exception("Aucune route trouvée pour l'arrêt $depart");
    }

    public function export(): array
    {
        return [
            'nom' => $this->nom,
            'depart' => $this->depart->nom,
            'arrivee' => $this->arrivee->nom,
            'distance' => $this->distance,
            'tickArrivee' => $this->tickArrivee,
            'routes' => array_map(
                function ($route) {
                    return $route->nom;
                },
                $this->routes
            ),
        ];
    }

    public function restore(array $state): void
    {
        throw new \Exception('Not implemented');
    }
}
