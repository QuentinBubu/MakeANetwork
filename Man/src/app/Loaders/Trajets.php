<?php

namespace App\Loaders;

use App\Entities\Arret;
use App\Entities\Route;
use App\Loaders\Arrets;
use App\Entities\Trajet;
use App\Exceptions\ArretsException;

// /!\ Attention, trajet pour personnes => prendre en compte la vitesse du bus + positions des bus (incomming)
class Trajets
{
    /**
     * Liste des trajets
     * @var array [
     * "A, B" => [
     *  "routes" => [Route, Route, ...],
     *  "distance" => int
     * ]
     */
    public static array $trajets = [];

    final public static function key(string $a, string $b): string
    {
        return $a . ", " . $b;
    }

    public static function addTrajet(Route $route): void
    {
        $nom = self::key($route->arrets[0]->nom, $route->arrets[1]->nom);
        self::$trajets[$nom] = new Trajet(
            nom: $nom,
            route: [$route],
            depart: Arrets::getArret($route->arrets[1]->nom),
            arrivee: Arrets::getArret($route->arrets[0]->nom),
            distance: $route->distance
        );
    }

    public static function addLongTrajet(Arret $depart, Arret $arrivee, array $routes, int $distance): void
    {
        $nom = self::key($depart->nom, $arrivee->nom);
        self::$trajets[$nom] = new Trajet(
            nom: $nom,
            route: $routes,
            depart: $depart,
            arrivee: $arrivee,
            distance: $distance
        );
    }

    public static function findTrajetWithArret(Arret $depart, Arret $arrivee): Trajet
    {
        $cle = self::key($depart->nom, $arrivee->nom);

        if (isset(self::$trajets[$cle])) {
            return self::$trajets[$cle];
        }

        $trajets = self::calculTrajet($depart->nom, $arrivee->nom);
        self::addLongTrajet($depart, $arrivee, $trajets['routes'], $trajets['distance']);

        return self::$trajets[$cle];
    }

    public static function findTrajet(string $depart, string $arrivee): Trajet
    {
        return self::findTrajetWithArret(Arrets::getArret($depart), Arrets::getArret($arrivee));
    }

    public static function calculTrajet(string $arretA, string $arretB): array
    {
        if (!isset(Arrets::$arrets[$arretA]) || !isset(Arrets::$arrets[$arretB])) {
            throw new ArretsException("Arrêt introuvable");
        }

        $distances = array_fill_keys(array_keys(Arrets::$arrets), PHP_INT_MAX);
        $precedent = array_fill_keys(array_keys(Arrets::$arrets), null);
        $routes = array_fill_keys(array_keys(Arrets::$arrets), null);
        $minHeap = new \SplPriorityQueue();
        $inQueue = [];

        $distances[$arretA] = 0;
        $minHeap->insert($arretA, 0);
        $inQueue[$arretA] = true;

        echo "Début du calcul du trajet de $arretA à $arretB\n";

        while (!$minHeap->isEmpty()) {
            $currentArretNom = $minHeap->extract();
            unset($inQueue[$currentArretNom]);
            $currentArret = Arrets::getArret($currentArretNom);

            echo "Traitement de l'arrêt : $currentArretNom, Distance actuelle : {$distances[$currentArretNom]}\n";

            if ($currentArretNom === $arretB) {
                echo "Destination atteinte\n";
                break;
            }

            foreach ($currentArret->getNeighbors() as $neighbor) {
                $route = $neighbor->route;
                $neighbor = $neighbor->arret;
                if (in_array($currentArret, $route->getArrets())) {
                    $alt = $distances[$currentArretNom] + $route->distance;

                    echo "  Voisin : {$neighbor->nom}, Route : {$route->nom}, Distance : {$route->distance}, Distance totale potentielle : $alt\n";

                    if ($alt < $distances[$neighbor->nom]) {
                        $distances[$neighbor->nom] = $alt;
                        $precedent[$neighbor->nom] = $currentArretNom;
                        $routes[$neighbor->nom] = $route;

                        if (!isset($inQueue[$neighbor->nom])) {
                            $inQueue[$neighbor->nom] = true;
                        }
                        $minHeap->insert($neighbor->nom, -$alt);

                        echo "  Mise à jour : {$neighbor->nom}, Nouvelle distance totale : $alt\n";
                    }
                    break;  // On a trouvé la bonne route, pas besoin de vérifier les autres
                }
                //}
            }
        }

        $routeList = [];
        $distanceTotale = 0;
        echo "Reconstruction du chemin :\n";
        for ($at = $arretB; $at !== null; $at = $precedent[$at]) {
            echo "  Arrêt : $at\n";
            if (isset($routes[$at])) {
                array_unshift($routeList, $routes[$at]);
                $distanceTotale += $routes[$at]->distance;
                echo "    Route ajoutée : {$routes[$at]->nom}, Distance : {$routes[$at]->distance}\n";
            }
        }

        echo "Chemin final : " . implode(" -> ", array_map(function ($route) {
            return $route->nom;
        }, $routeList)) . "\n";
        echo "Distance totale : $distanceTotale\n";

        return [
            "routes" => $routeList,
            "distance" => $distanceTotale
        ];
    }

    public static function export(): array
    {
        $data = [];
        /** @var Trajet $trajet */
        foreach (self::$trajets as $trajet) {
            $data[spl_object_id($trajet)] = $trajet->export();
        }
        return $data;
    }
}
