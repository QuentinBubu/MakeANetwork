<?php

namespace App\Loaders;

use App\Log\Message;
use App\Entities\Arret;
use App\Entities\Route;
use App\Loaders\Arrets;
use App\Entities\Trajet;
use App\Exceptions\ArretsException;

/**
 * Classe Trajets
 * 
 * Cette classe gère l'ajout, la recherche, et le calcul des trajets entre les arrêts dans le système de transport.
 * Elle implémente l'algorithme de Dijkstra pour trouver le meilleur trajet entre deux arrêts, basé sur les routes disponibles.
 */
class Trajets
{
    /**
     * Liste des trajets existants, chaque trajet étant une paire de départ-arrivée avec ses informations.
     * 
     * @var array [
     *   "A, B" => [
     *     "routes" => [Route, Route, ...],
     *     "distance" => int
     *   ]
     * ]
     */
    public static array $trajets = [];

    /**
     * Génère une clé unique pour un trajet entre deux arrêts.
     * 
     * @param string $a Le nom de l'arrêt de départ.
     * @param string $b Le nom de l'arrêt d'arrivée.
     * 
     * @return string La clé unique du trajet "A, B".
     */
    final public static function key(string $a, string $b): string
    {
        return $a . ", " . $b;
    }

    /**
     * Ajoute un trajet à la liste des trajets existants à partir d'une route donnée.
     * 
     * @param Route $route La route à ajouter au trajet.
     */
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

    /**
     * Ajoute un trajet long à la liste des trajets existants, utilisant plusieurs routes et une distance cumulée.
     * 
     * @param Arret $depart L'arrêt de départ du trajet.
     * @param Arret $arrivee L'arrêt d'arrivée du trajet.
     * @param array $routes Liste des routes composant le trajet.
     * @param int $distance La distance totale du trajet.
     */
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

    /**
     * Trouve un trajet entre deux arrêts, ou le calcule s'il n'existe pas déjà.
     * 
     * @param Arret $depart L'arrêt de départ.
     * @param Arret $arrivee L'arrêt d'arrivée.
     * 
     * @return Trajet Le trajet trouvé ou calculé.
     * 
     * @throws ArretsException Si l'un des arrêts est introuvable.
     */
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

    /**
     * Trouve un trajet entre deux arrêts, en utilisant leurs noms sous forme de chaînes de caractères.
     * 
     * @param string $depart Le nom de l'arrêt de départ.
     * @param string $arrivee Le nom de l'arrêt d'arrivée.
     * 
     * @return Trajet Le trajet trouvé ou calculé.
     */
    public static function findTrajet(string $depart, string $arrivee): Trajet
    {
        return self::findTrajetWithArret(Arrets::getArret($depart), Arrets::getArret($arrivee));
    }

    /**
     * Calcule le meilleur trajet entre deux arrêts en utilisant l'algorithme de Dijkstra.
     * 
     * @param string $arretA Le nom de l'arrêt de départ.
     * @param string $arretB Le nom de l'arrêt d'arrivée.
     * 
     * @return array Contient deux éléments :
     *  - "routes" : Liste des routes du trajet.
     *  - "distance" : La distance totale du trajet.
     * 
     * @throws ArretsException Si l'un des arrêts est introuvable.
     */
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

        $distances[$arretA] = 0; // Distance de départ à 0
        $minHeap->insert($arretA, 0); // Insertion de l'arrêt de départ dans la queue
        $inQueue[$arretA] = true;

        Message::log("Début du calcul du trajet de $arretA à $arretB", Message::DEBUG_DETAIL);

        while (!$minHeap->isEmpty()) {
            $currentArretNom = $minHeap->extract();
            unset($inQueue[$currentArretNom]);
            $currentArret = Arrets::getArret($currentArretNom);

            Message::log("Traitement de l'arrêt : $currentArretNom, Distance actuelle : {$distances[$currentArretNom]}", Message::DEBUG_ALL);

            if ($currentArretNom === $arretB) {
                Message::log("Destination atteinte", Message::DEBUG_ALL);
                break;
            }

            // Exploration des voisins de l'arrêt courant
            foreach ($currentArret->getNeighbors() as $neighbor) {
                $route = $neighbor->route;
                $neighbor = $neighbor->arret;
                if (in_array($currentArret, $route->getArrets())) {
                    $alt = $distances[$currentArretNom] + $route->distance;

                    Message::log("  Voisin : {$neighbor->nom}, Route : {$route->nom}, Distance : {$route->distance}, Distance totale potentielle : $alt", Message::DEBUG_ALL);

                    if ($alt < $distances[$neighbor->nom]) {
                        $distances[$neighbor->nom] = $alt;
                        $precedent[$neighbor->nom] = $currentArretNom;
                        $routes[$neighbor->nom] = $route;

                        if (!isset($inQueue[$neighbor->nom])) {
                            $inQueue[$neighbor->nom] = true;
                        }
                        $minHeap->insert($neighbor->nom, -$alt);  // Insertion dans le min-heap avec la nouvelle distance

                        Message::log("  Mise à jour : {$neighbor->nom}, Nouvelle distance totale : $alt", Message::DEBUG_ALL);
                    }
                }
            }
        }

        $routeList = [];
        $distanceTotale = 0;

        // Reconstruction du chemin en partant de l'arrivée
        Message::log("Reconstruction du chemin :", Message::DEBUG_ALL);
        for ($at = $arretB; $at !== null; $at = $precedent[$at]) {
            Message::log("  Arrêt : $at", Message::DEBUG_ALL);
            if (isset($routes[$at])) {
                array_unshift($routeList, $routes[$at]);
                $distanceTotale += $routes[$at]->distance;
                Message::log("    Route ajoutée : {$routes[$at]->nom}, Distance : {$routes[$at]->distance}", Message::DEBUG_ALL);
            }
        }

        Message::log("Chemin final : " . implode(" -> ", array_map(function ($route) {
            return $route->nom;
        }, $routeList)), Message::DEBUG_DETAIL);
        Message::log("Distance totale : $distanceTotale", Message::DEBUG_DETAIL);

        return [
            "routes" => $routeList,
            "distance" => $distanceTotale
        ];
    }

    /**
     * Exporte les données des trajets sous forme de tableau.
     * 
     * @return array Un tableau des données des trajets.
     */
    public static function export(): array
    {
        $data = [];
        /** @var Trajet $trajet */
        foreach (self::$trajets as $key => $trajet) {
            $data[$key] = $trajet->export();
        }
        return $data;
    }
}
