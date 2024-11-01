<?php

use App\Entities\Bus;
use SplPriorityQueue;
use App\Entities\Arret;
use App\Entities\Route;
use App\Loaders\Arrets;
use App\Entities\Personne;

class PathFinder {
    
    public static function findBestPath(Arret $arretFrom, Arret $arretTo, Personne $personne) {
        $distances = [];
        $previous = [];
        $queue = new SplPriorityQueue();

        // Initialisation des distances et de la file d'attente
        foreach (Arrets::$arrets as $arret) {
            $distances[$arret->nom] = INF;
            $previous[$arret->nom] = null;
            $queue->insert($arret, INF);
        }

        // Distance du point de départ
        $distances[$arretFrom->nom] = 0;
        $queue->insert($arretFrom, 0);

        // Dijkstra pour trouver le chemin le plus court
        while (!$queue->isEmpty()) {
            $arretCourant = $queue->extract();

            if ($arretCourant->nom === $arretTo->nom) break;

            // Parcourir les routes connectées à l'arrêt courant
            foreach ($arretCourant->getRoutes() as $route) {
                foreach ($route->getBus() as $bus) {
                    // Obtenir le temps de passage du bus à cet arrêt
                    $tempsPassage = $arretCourant->getTempsPassage($bus);
                    $tempsTrajet = self::calculateTimeWithBus($route, $bus);

                    // Calcul du nouveau temps pour atteindre l'arrêt de destination de la route
                    $nouvelleDistance = $distances[$arretCourant->nom] + $tempsPassage + $tempsTrajet;

                    // Mettre à jour la distance minimale si la nouvelle distance est plus courte
                    if ($nouvelleDistance < $distances[$route->arrivee->nom]) {
                        $distances[$route->arrivee->nom] = $nouvelleDistance;
                        $previous[$route->arrivee->nom] = [$arretCourant, $bus];
                        $queue->insert($route->arrivee, -$nouvelleDistance);
                    }
                }
            }
        }

        // Reconstruire le chemin optimal
        return self::reconstructPath($previous, $arretTo);
    }

    private static function calculateTimeWithBus(Route $route, Bus $bus) {
        // Calcule du temps en utilisant vitesse * distance pour éviter les chiffres à virgule
        return $route->distance / $bus->vitesseDeplacement;
    }

    private static function reconstructPath($previous, Arret $arretTo) {
        $chemin = [];
        $etape = 0;

        while (isset($previous[$arretTo->nom])) {
            list($arret, $bus) = $previous[$arretTo->nom];
            $chemin["et{$etape}"] = [
                'busAPrendre' => $bus,
                'arretMontee' => $arret,
                'arretDescente' => $arretTo
            ];
            $arretTo = $arret;
            $etape++;
        }

        return array_reverse($chemin);
    }
}
