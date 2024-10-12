<?php

namespace App\Entities;

use App\Entities\Route;

/**
 * @Entity
 *
 * Un parcours est décrit par un ensemble de routes
 */
class Parcours
{
    public string $nom;
    public array $routes = [];

    public function __construct(string $nom, Route ...$routes)
    {
        $this->nom = $nom;
        $this->routes = $routes;
    }

    public function __tostring(): string
    {
        return implode(
            separator: ' -> ',
            array: array_map(
                callback: function ($route) {
                    return $route->nom;
                },
                array: $this->routes
            )
        );
    }
}
