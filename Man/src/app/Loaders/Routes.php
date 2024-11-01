<?php

namespace App\Loaders;

use App\Exceptions\RoutesException;
use App\Entities\Route;

class Routes
{
    public static array $routes = [];

    public static function load($routes): void
    {
        foreach ($routes as $name => $data) {
            $route = new Route($name, $data['distance']);
            self::$routes[$name] = $route;
        }
    }

    public static function getRoute(string $name): Route
    {
        if (!isset(self::$routes[$name])) {
            throw new RoutesException("Route {$name} inconnue");
        }

        return self::$routes[$name];
    }

    public static function getRouteStr(string $arretA, string $arretB): Route
    {
        $arrets = [Arrets::getArret($arretA), Arrets::getArret($arretB)];

        $route = array_filter(self::$routes, function ($route) use ($arrets) {
            return in_array($arrets[0], $route->getArrets()) && in_array($arrets[1], $route->getArrets());
        });

        if (count($route) == 0) {
            // Ajout d'un booléen pour savoir si l'arrêt est le terminus et donc trajet retour en express
            throw new RoutesException("Route {$arretA} <-> {$arretB} inconnue");
        }

        return array_values($route)[0];
    }
}
