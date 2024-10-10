<?php

namespace Man\App\Loaders;

use Man\App\Entities\Arret;
use Man\App\Entities\Route;

class Routes
{
    public static array $routes = [];

    public static function load($routes): void
    {
        foreach ($routes as $name => $data) {
            $route = new Route($name, $data['arrets'], $data['distance']);
            self::$routes[$name] = $route;
        }
    }
}
