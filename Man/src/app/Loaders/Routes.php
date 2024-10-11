<?php

namespace Man\App\Loaders;

use Man\App\Exceptions\RoutesException;
use Man\App\Entities\Route;

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
        var_dump(self::$routes);
        if (!isset(self::$routes[$name])) {
            throw new RoutesException('Route inconnue');
        }

        return self::$routes[$name];
    }
}
