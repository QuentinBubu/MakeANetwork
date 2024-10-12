<?php

namespace App\Loaders;

use App\Entities\Route;
use App\Loaders\Arrets;
use App\Exceptions\ArretsException;

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
        return $a < $b ? "{$a}, {$b}" : "{$b}, {$a}";
    }

    public static function registerTrajet(string $nom, array $routes)
    {
        self::$trajets[$nom] = $routes;
    }

    public static function addTrajet(Route $route): void
    {
        self::$trajets[self::key($route->arrets[0], $route->arrets[1])] = [
            "routes" => [$route],
            "distance" => $route->distance,
        ];
    }

    public static function findTrajet(string $depart, string $arrivee)
    {
        $cle = self::key($depart, $arrivee);

        if (isset(self::$trajets[$cle])) {
            return self::$trajets[$cle];
        }

        $trajets = self::calculTrajet($depart, $arrivee);
        self::$trajets[$cle] = $trajets;

        return $trajets;

    }

    public static function calculTrajet(string $arretA, string $arretB): array
    {
        // Vérification de l'existence des arrêts
        if (!isset(Arrets::$arrets[$arretA]) || !isset(Arrets::$arrets[$arretB])) {
            throw new ArretsException("Arrêt introuvable");
        }

        $distances = [];
        $precedent = [];
        $routes = [];
        $minHeap = new \SplPriorityQueue();
    
        // Initialisation des distances et du tableau précédent
        foreach (Arrets::$arrets as $arret) {
            $distances[$arret->nom] = PHP_INT_MAX; // Initialisation des distances à l'infini
            $precedent[$arret->nom] = null;
            $routes[$arret->nom] = null; // Stocker la route prise pour chaque arrêt
            $minHeap->insert($arret->nom, PHP_INT_MAX); // Insertion avec priorité max
        }
    
        // Distance du point de départ
        $distances[$arretA] = 0;
        $minHeap->insert($arretA, 0);
    
        while (!$minHeap->isEmpty()) {
            // Extraction de l'arrêt avec la plus petite distance
            $currentArretNom = $minHeap->extract();
    
            // Si l'arrêt actuel est l'arrêt de destination, on sort
            if ($currentArretNom === $arretB) {
                break;
            }
    
            // Récupération de l'arret actuel
            $currentArret = Arrets::$arrets[$currentArretNom];
    
            // Parcours des voisins de l'arrêt actuel
            foreach ($currentArret->getNeighbors() as $neighbor) {
                $route = $neighbor->routes[0]; // On suppose qu'il y a toujours une route
                $alt = $distances[$currentArretNom] + $route->distance; // Calculer la distance alternative
    
                if ($alt < $distances[$neighbor->nom]) {
                    $distances[$neighbor->nom] = $alt;
                    $precedent[$neighbor->nom] = $currentArret;
                    $routes[$neighbor->nom] = $route; // Enregistrer la route empruntée
    
                    // MàJ de la priorité dans le min-heap
                    $minHeap->insert($neighbor->nom, $alt);
                }
            }
        }
    
        // Reconstruction de la liste des routes et calcul de la distance totale
        $routeList = [];
        $distanceTotale = 0;
        for ($at = $arretB; $at !== null; $at = $precedent[is_object($at) ? $at->nom : $at]) {
            $arretNom = is_object($at) ? $at->nom : $at;
        
            if (isset($routes[$arretNom])) {
                $routeList[] = $routes[$arretNom];
                $distanceTotale += $routes[$arretNom]->distance;
            }
        }
    
        $routeList = array_reverse($routeList); // Inverser les routes pour correspondre à l'ordre des arrêts
    
        return [
            "routes" => $routeList,
            "distance" => $distanceTotale
        ];
    }

    public static function getTrajets()
    {
        return self::$trajets;
    }

    public static function toString(): string
    {
        $str = "";
        foreach (self::$trajets as $key => $trajet) {
            $str .= "Trajet {$key} : " . implode(', ', array_map(function ($route) {
                return $route->nom;
            }, $trajet['routes'])) . " ({$trajet['distance']} M)" . PHP_EOL;
        }
        return $str;
    }
}
