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
                foreach ($neighbor->routes as $route) {
                    if (in_array($currentArret, $route->getArrets())) {
                        $alt = $distances[$currentArretNom] + $route->distance;
    
                        echo "  Voisin : {$neighbor->nom}, Route : {$route->nom}, Distance : {$route->distance}, Distance totale potentielle : $alt\n";
    
                        if ($alt < $distances[$neighbor->nom]) {
                            $distances[$neighbor->nom] = $alt;
                            $precedent[$neighbor->nom] = $currentArretNom;
                            $routes[$neighbor->nom] = $route;
                            
                            if (!isset($inQueue[$neighbor->nom])) {
                                $minHeap->insert($neighbor->nom, -$alt);
                                $inQueue[$neighbor->nom] = true;
                            } else {
                                $minHeap->insert($neighbor->nom, -$alt);
                            }
    
                            echo "  Mise à jour : {$neighbor->nom}, Nouvelle distance totale : $alt\n";
                        }
                        break;  // On a trouvé la bonne route, pas besoin de vérifier les autres
                    }
                }
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
    
        echo "Chemin final : " . implode(" -> ", array_map(function($route) { return $route->nom; }, $routeList)) . "\n";
        echo "Distance totale : $distanceTotale\n";
    
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
