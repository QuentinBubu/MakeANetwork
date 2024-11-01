<?php

namespace App\Loaders;

use App\Entities\Arret;
use App\Loaders\Bus;
use App\Entities\Bus as BusEntity;
use App\Entities\Personne;
use App\Entities\Trajet;
use App\Timer\Time;
use SplPriorityQueue;

class PathFinder {
    private array $cache = [];

    /**
     * Calcule le meilleur chemin pour une personne
     */
    public function findBestPath(Personne $personne): array {
        $trajet = $personne->getTrajetEnCours();
        $depart = $trajet->depart;
        $arrivee = $trajet->arrivee;
        
        // Structure pour stocker les résultats
        $distances = [];
        $precedents = [];
        $busUtilises = [];
        $temps = [];
        
        // Initialisation
        foreach (Bus::$buses as $bus) {
            foreach ($bus->getParcours()->arretsAFaire as $arret) {
                $key = $this->getNodeKey($arret, $bus);
                $distances[$key] = PHP_FLOAT_MAX;
                $precedents[$key] = null;
                $busUtilises[$key] = null;
                $temps[$key] = PHP_FLOAT_MAX;
            }
        }
        
        // File de priorité pour Dijkstra
        $queue = new SplPriorityQueue();
        
        // Point de départ
        $startKey = $this->getNodeKey($depart, null);
        $distances[$startKey] = 0;
        $temps[$startKey] = Time::getTick();
        $queue->insert($startKey, 0);
        
        while (!$queue->isEmpty()) {
            $currentKey = $queue->extract();
            list($currentArret, $currentBus) = $this->parseNodeKey($currentKey);
            
            // Si on est arrivé, on s'arrête
            if ($currentArret === $arrivee) {
                break;
            }
            
            // Parcours des options depuis cet arrêt
            foreach ($this->getOptionsFromStop($currentArret, $currentBus, $personne) as $option) {
                $nextKey = $this->getNodeKey($option['arret'], $option['bus']);
                $newTime = $temps[$currentKey] + $this->calculateTime($option);
                
                if ($newTime < $temps[$nextKey]) {
                    $temps[$nextKey] = $newTime;
                    $distances[$nextKey] = $distances[$currentKey] + 1;
                    $precedents[$nextKey] = $currentKey;
                    $busUtilises[$nextKey] = $option['bus'];
                    $queue->insert($nextKey, -$newTime);
                }
            }
        }
        
        // Reconstruction du chemin
        return $this->reconstructPath($arrivee, $precedents, $busUtilises, $temps);
    }
    
    /**
     * Calcule le temps nécessaire pour une option de trajet
     */
    private function calculateTime(array $option): float {
        $temps = 0;
        
        // Temps d'attente du bus
        $temps += $this->calculateWaitingTime($option['bus'], $option['arret']);
        
        // Temps de trajet
        if ($option['bus']) {
            $temps += $option['bus']->vitesseDeplacement * $option['distance'];
            // Temps de chargement/déchargement
            $temps += $option['bus']->vitesseChargement;
        }
        
        return $temps;
    }
    
    /**
     * Calcule le temps d'attente pour un bus à un arrêt
     */
    private function calculateWaitingTime(BusEntity $bus, Arret $arret): float {
        // Position actuelle du bus
        $currentPosition = $bus->getParcours()->getCurrentArretObj();
        
        // Si le bus est à l'arrêt
        if ($currentPosition === $arret) {
            return 0;
        }
        
        // Calcul du temps jusqu'à l'arrivée du bus
        $distance = 0;
        $currentArret = $currentPosition;
        
        while ($currentArret !== $arret) {
            $nextArret = $bus->getParcours()->findNextArretObj($currentArret);
            $distance += $this->getDistance($currentArret, $nextArret);
            $currentArret = $nextArret;
        }
        
        return $distance * $bus->vitesseDeplacement;
    }
    
    /**
     * Retourne toutes les options possibles depuis un arrêt
     */
    private function getOptionsFromStop(Arret $arret, ?BusEntity $currentBus, Personne $personne): array {
        $options = [];
        
        // Pour chaque bus passant par cet arrêt
        foreach (Bus::$buses as $bus) {
            // Évite de reprendre le même bus
            if ($bus === $currentBus || $bus === $personne->lastBus) {
                continue;
            }
            
            // Vérifie si le bus passe par cet arrêt
            if (in_array($arret, $bus->getParcours()->arretsAFaire)) {
                foreach ($bus->getParcours()->arretsAFaire as $nextArret) {
                    if ($nextArret !== $arret) {
                        $options[] = [
                            'arret' => $nextArret,
                            'bus' => $bus,
                            'distance' => $this->getDistance($arret, $nextArret)
                        ];
                    }
                }
            }
        }
        
        return $options;
    }
    
    /**
     * Reconstruit le chemin à partir des précédents
     */
    private function reconstructPath(Arret $arrivee, array $precedents, array $busUtilises, array $temps): array {
        $path = [];
        $currentKey = $this->getNodeKey($arrivee, null);
        
        while ($currentKey !== null) {
            $path[] = [
                'arret' => $this->parseNodeKey($currentKey)[0],
                'bus' => $busUtilises[$currentKey],
                'temps' => $temps[$currentKey]
            ];
            $currentKey = $precedents[$currentKey];
        }
        
        return array_reverse($path);
    }
    
    /**
     * Génère une clé unique pour un nœud (arrêt + bus)
     */
    private function getNodeKey(Arret $arret, ?BusEntity $bus): string {
        return $arret->nom . ':' . ($bus ? spl_object_id($bus) : 'null');
    }
    
    /**
     * Parse une clé de nœud
     */
    private function parseNodeKey(string $key): array {
        list($arretNom, $busId) = explode(':', $key);
        return [
            Arrets::getArret($arretNom),
            $busId === 'null' ? null : Bus::$buses[array_search($busId, array_map('spl_object_id', Bus::$buses))]
        ];
    }
    
    /**
     * Récupère la distance entre deux arrêts
     */
    private function getDistance(Arret $depart, Arret $arrivee): int {
        $key = $depart->nom . '-' . $arrivee->nom;
        
        if (!isset($this->cache[$key])) {
            $this->cache[$key] = Trajets::findTrajetWithArret($depart, $arrivee)->distance;
        }
        
        return $this->cache[$key];
    }
}