<?php

namespace Man\App\Entities;

use Man\App\Loaders\Routes;

class Arret
{
    public string $nom;

    /**
     * @var Route[]
     */
    public array $routes;

    /**
     * @var string[]
     */
    private array $genericRoutes;
    private array $genericFile;

    public function __construct(string $nom, array $genericRoutes, array $genericFile)
    {
        $this->nom = $nom;
        $this->genericRoutes = $genericRoutes;
        $this->genericFile = $genericFile;
    }

    public function mapRoutes()
    {
        $this->routes = array_map(function ($route) {
            return Routes::getRoute($route)->registerArret($this);
        }, $this->genericRoutes);
    }

    public function __tostring(): string
    {
        return $this->nom . ' @' . spl_object_id($this)
            . ' (Routes : ' . implode(', ', array_map(
                function ($route) {
                    return $route->nom . ' @' . spl_object_id($route);
                },
                $this->routes
            ))
            . ')';
    }
}
