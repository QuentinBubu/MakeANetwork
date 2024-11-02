<?php

namespace App\Entities;

use stdClass;
use App\Loaders\Bus;
use App\Log\Message;
use SplPriorityQueue;
use App\Entities\Arret;
use App\Loaders\Arrets;
use App\Entities\Personne;

class PathFinder
{
    public function findBestPath(Personne $personne, Arret $arretFrom, Arret $arretTo): array
    {
        // Initialisation de Dijkstra
        $distances = array_fill_keys(array_keys(Arrets::$arrets), INF);
        $distances[$arretFrom->nom] = 0;
        $previousArrets = [];
        $busTaken = [];
        $queue = new SplPriorityQueue();

        $queue->insert($arretFrom->nom, 0);

        Message::log("Début de l'algorithme de Dijkstra depuis l'arrêt de départ : {$arretFrom->nom}", Message::DEBUG_DETAIL);

        // Exécution de Dijkstra
        while (!$queue->isEmpty()) {
            $currentArretNom = $queue->extract();

            if ($currentArretNom === $arretTo->nom) {
                Message::log("Arrêt destination atteint : {$arretTo->nom}", Message::DEBUG_ALL);
                break;
            }

            Message::log("Traitement de l'arrêt : {$currentArretNom}, Distance actuelle : {$distances[$currentArretNom]}", Message::DEBUG_ALL);

            $currentArret = Arrets::getArret($currentArretNom);
            /** @var stdClass $voisin */
            foreach ($currentArret->getNeighbors() as $voisin) {
                foreach (Bus::$buses as $bus) {
                    if (!$bus->peutDesservir($currentArret, $voisin->arret)) {
                        Message::log("   -> Bus {$bus->type} parcours {$bus->getParcours()->nom} ne dessert pas {$voisin->arret->nom} depuis {$currentArretNom}", Message::DEBUG_ALL);
                        continue;
                    }

                    $route = $currentArret->getRouteTo($voisin->arret);
                    $time = $route->distance * $bus->vitesseDeplacement;
                    $newDistance = $distances[$currentArretNom] + $time;

                    Message::log("   -> Tentative de mise à jour pour voisin : {$voisin->arret->nom} avec bus {$bus->type} (Distance : {$newDistance})", Message::DEBUG_ALL);

                    if ($newDistance < $distances[$voisin->arret->nom]) {
                        $distances[$voisin->arret->nom] = $newDistance;
                        $previousArrets[$voisin->arret->nom] = $currentArretNom;
                        $busTaken[$voisin->arret->nom] = $bus;
                        $queue->insert($voisin->arret->nom, -$newDistance);

                        Message::log("   -> Mise à jour réussie pour {$voisin->arret->nom} avec bus {$bus->type}. Nouvelle distance : {$newDistance}", Message::DEBUG_ALL);
                    } else {
                        Message::log("   -> Non mis à jour : distance existante plus courte ou boucle détectée.", Message::DEBUG_ALL);
                    }
                }
            }

            Message::log("Fin du traitement de l'arrêt : {$currentArretNom}", Message::DEBUG_ALL);
        }

        // Vérifier si le chemin complet est atteint
        if (!isset($previousArrets[$arretTo->nom]) && $arretFrom->nom !== $arretTo->nom) {
            throw new \RuntimeException("Impossible de rejoindre l'arrêt de départ. Dernier arrêt atteint : {$currentArretNom}");
        }

        Message::log("Fin de l'algorithme de Dijkstra", Message::DEBUG_DETAIL);

        // Reconstruction du chemin
        $path = [];
        $visitedArrets = [];
        $arret = $arretTo->nom;

        Message::log("Reconstruction du chemin depuis l'arrêt destination : {$arretTo->nom}", Message::DEBUG_DETAIL);
        $maxSteps = count(Arrets::$arrets);
        $steps = 0;

        while ($arret !== $arretFrom->nom) {
            if (isset($visitedArrets[$arret])) {
                throw new \RuntimeException("Boucle détectée lors de la reconstruction du chemin à l'arrêt : {$arret}");
            }
            $visitedArrets[$arret] = true;

            if (++$steps > $maxSteps) {
                throw new \RuntimeException("Nombre maximal d'étapes dépassé lors de la reconstruction du chemin.");
            }

            // Vérifier si un previousArret existe
            if (!isset($previousArrets[$arret])) {
                throw new \RuntimeException("Erreur lors de la reconstruction : Pas de précédent pour l'arrêt {$arret}. Chemin incomplet.");
            }

            // Récupérer l'arrêt précédent
            $previous = $previousArrets[$arret];

            // Vérifier que le bus associé à cet arrêt est bien défini
            if (!isset($busTaken[$arret])) {
                throw new \RuntimeException("Erreur lors de la reconstruction : Pas de bus défini pour l'arrêt {$arret} vers {$previous}.");
            }

            $personne->setSignalDescente(Arrets::getArret($arret));

            $path[] = [
                'busAPrendre' => $busTaken[$arret],
                'arretMontee' => $previous,
                'arretDescente' => $arret
            ];

            Message::log("Étape : Bus {$busTaken[$arret]->type} de {$previous} à {$arret}", Message::DEBUG_ALL);

            // Mettre à jour l'arrêt en cours pour continuer la reconstruction
            $arret = $previous;
        }

        // Vérification finale pour s'assurer que le chemin est bien complet
        if ($arret !== $arretFrom->nom) {
            throw new \RuntimeException("Impossible de rejoindre l'arrêt de départ. Dernier arrêt atteint : {$arret}");
        }

        Message::log("Fin de la reconstruction du chemin", Message::DEBUG_DETAIL);
        return array_reverse($path);
    }
}
