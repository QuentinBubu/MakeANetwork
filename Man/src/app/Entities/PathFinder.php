<?php

namespace App\Entities;

use App\Entities\Arret;
use App\Entities\Bus;
use App\Entities\Personne;
use App\Loaders\Arrets;

class PathFinder
{
    public function findBestPath(Personne $personne, Arret $arretFrom, Arret $arretTo): array
    {
        $distances = [];
        $previousArrets = [];
        $busTaken = [];
        $queue = new \SplPriorityQueue();

        // Initialiser les distances avec l'infini sauf pour l'arrêt de départ
        foreach (Arrets::$arrets as $arret) {
            $distances[$arret->nom] = PHP_INT_MAX;
            $previousArrets[$arret->nom] = null;
        }
        $distances[$arretFrom->nom] = 0;
        $queue->insert($arretFrom, 0);

        // Algorithme de Dijkstra
        while (!$queue->isEmpty()) {
            $currentArret = $queue->extract();

            // Arrêt de la recherche si on atteint l'arrêt de destination
            if ($currentArret->nom === $arretTo->nom) break;

            foreach ($currentArret->getNeighbors() as $neighborData) {
                $route = $neighborData->route;
                $neighborArret = $neighborData->arret;
            
                foreach ($neighborArret->vehiculesEnApproche as $busData) {
                    /** @var Bus $bus */
                    $bus = $busData[0];
                    $arrivalTime = $busData[1];
            
                    // Calcul du temps total en fonction de la vitesse du bus
                    $routeDistance = $route->distance;
                    $travelTime = $routeDistance / $bus->vitesseDeplacement;
            
                    $newTime = $distances[$currentArret->nom] + $arrivalTime->getRemainingTicks() + $travelTime;
            
                    if ($newTime < $distances[$neighborArret->nom]) {
                        $distances[$neighborArret->nom] = $newTime;
                        $previousArrets[$neighborArret->nom] = $currentArret->nom;
                        $busTaken[$neighborArret->nom] = $bus;
                        $queue->insert($neighborArret, -$newTime); // -$newTime pour la priorité croissante
                    }
                }
            }
        }

        // Reconstruire le chemin et le formatage de la réponse
        $path = [];
        $arret = $arretTo->nom;

        while ($previousArrets[$arret] !== null) {
            $path[] = [
                'busAPrendre' => $busTaken[$arret],
                'arretMontee' => $previousArrets[$arret],
                'arretDescente' => $arret
            ];
    
            // Définir le signal de descente pour cet arrêt
            $personne->setSignalDescente($arretTo);
    
            $arret = $previousArrets[$arret];
        }

        return array_reverse($path); // Retourne le chemin du départ vers l’arrivée
    }
}
