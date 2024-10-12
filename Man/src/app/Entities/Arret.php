<?php

namespace App\Entities;

use App\Loaders\Routes;

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

    public function getNeighbors() {
        $neighbors = [];
        foreach ($this->routes as $route) {
            foreach ($route->getArrets() as $neighbor) {
                if ($neighbor !== $this) {
                    $neighbors[$neighbor->nom] = $neighbor;
                }
            }
        }
        return $neighbors;
    }

    public function mapRoutes()
    {
        $this->routes = array_map(function ($route) {
            return Routes::getRoute($route)->registerArret($this);
        }, $this->genericRoutes);
    }

    public function registerRoute(Route $route): self
    {
        $this->routes[] = $route;
        return $this;
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
