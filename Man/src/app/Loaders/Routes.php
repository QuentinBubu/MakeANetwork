<?php

namespace App\Loaders;

use App\Log\Message;
use App\Entities\Route;
use App\Exceptions\RoutesException;
use App\Entities\Arret;

/**
 * La classe Routes est responsable de la gestion des routes dans le système.
 * Elle permet de charger des routes, d'obtenir une route spécifique, de rechercher une route entre deux arrêts, et d'exporter les informations des routes.
 */
class Routes
{
    /**
     * Tableau statique contenant toutes les routes.
     *
     * @var Route[]
     */
    public static array $routes = [];

    /**
     * Charge un ensemble de routes à partir des données fournies.
     * 
     * Cette méthode crée des instances de Route et les ajoute au tableau statique des routes.
     * 
     * Complexité: O(n), où n est le nombre de routes à charger, car chaque route est parcourue une seule fois.
     * 
     * @param array $routes Liste des données des routes à charger, chaque élément contenant le nom et la distance.
     */
    public static function load(array $routes): void
    {
        foreach ($routes as $name => $data) {
            Message::log("Construction de la route {$name}", Message::DEBUG_ALL);
            $route = new Route($name, $data['distance']);
            self::$routes[$name] = $route;
        }
    }

    /**
     * Récupère une route en fonction de son nom.
     * 
     * Si la route n'existe pas, une exception est levée.
     * 
     * Complexité: O(1), car l'accès à un élément dans un tableau associatif se fait en temps constant.
     * 
     * @param string $name Le nom de la route à récupérer.
     * @return Route L'objet Route correspondant.
     * @throws RoutesException Si la route n'existe pas.
     */
    public static function getRoute(string $name): Route
    {
        if (!isset(self::$routes[$name])) {
            throw new RoutesException("Route {$name} inconnue");
        }

        return self::$routes[$name];
    }

    /**
     * Recherche une route entre deux arrêts donnés.
     * 
     * Cette méthode filtre les routes existantes pour trouver celle qui relie les deux arrêts donnés.
     * Si aucune route ne correspond, une exception est levée.
     * 
     * Complexité: O(n), où n est le nombre de routes. Chaque route est parcourue une seule fois.
     * 
     * @param string $arretA Le nom du premier arrêt.
     * @param string $arretB Le nom du deuxième arrêt.
     * @return Route La route qui relie les deux arrêts.
     * @throws RoutesException Si aucune route n'est trouvée pour les deux arrêts.
     */
    public static function getRouteStr(string $arretA, string $arretB): Route
    {
        // Récupération des objets Arret pour les arrêts donnés
        $arrets = [Arrets::getArret($arretA), Arrets::getArret($arretB)];

        // Recherche d'une route qui contient les deux arrêts
        $route = array_filter(self::$routes, function ($route) use ($arrets) {
            return in_array($arrets[0], $route->getArrets()) && in_array($arrets[1], $route->getArrets());
        });

        if (count($route) == 0) {
            // Si aucune route n'est trouvée, une exception est levée
            throw new RoutesException("Route {$arretA} <-> {$arretB} inconnue");
        }

        // Retourne la première route trouvée (dans le cas où il y aurait plusieurs routes correspondant)
        return array_values($route)[0];
    }

    /**
     * Exporte toutes les informations des routes enregistrées sous forme de tableau.
     * 
     * Complexité: O(n), où n est le nombre de routes. Chaque route est parcourue une seule fois.
     * 
     * @return array Tableau associatif des routes, indexé par leur nom.
     */
    public static function export(): array
    {
        $data = [];
        /** @var Route $route */
        foreach (self::$routes as $route) {
            $data[$route->nom] = $route->export();
        }
        return $data;
    }
}
