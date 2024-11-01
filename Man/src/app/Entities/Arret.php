<?php

namespace App\Entities;

use App\Timer\Time;
use SplPriorityQueue;
use App\Loaders\Routes;
use App\Actions\ArretActions;
use App\Interfaces\TimeInterface;

/**
 * Représente un arrêt de bus
 */
class Arret implements TimeInterface
{
    use ArretActions;
    /**
     * Nom de l'arrêt
     *
     * @var string
     */
    public string $nom;

    /**
     * Routes qui passent par cet arrêt
     *
     * @var Route[]
     */
    public array $routes;

    /**
     * Personnes en attente à l'arrêt
     * @var [spl_object_id(Route) => SplPriorityQueue]
     */
    public array $filesDattenteRoutes;

    /**
     * Liste des routes sous forme de string
     *
     * @var string[]
     */
    private array $genericRoutes;

    /**
     * Liste personnes sous forme de string
     *
     * @var string[]
     */
    private array $genericFile;

    /**
     * Constructeur
     *
     * @param string $nom
     * @param array $genericRoutes
     * @param array $genericFile
     */
    public function __construct(string $nom, array $genericRoutes, array $genericFile)
    {
        $this->nom = $nom;
        $this->genericRoutes = $genericRoutes;
        $this->genericFile = $genericFile;
        Time::registerClass($this);
    }

    /**
     * Retourne les voisins de l'arrêt
     *
     * @return array
     */
    public function getNeighbors(): array
    {
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

    /**
     * Map les routes
     *
     * @return array
     */
    public function mapRoutes(): void
    {
        foreach ($this->genericRoutes as $route) {
            $this->registerRoute(Routes::getRoute($route)->registerArret($this));
        }
    }

    /**
     * Enregistre une route dans sa mémoire
     *
     * @return Arret
     */
    public function registerRoute(Route $route): self
    {
        $this->routes[] = $route;
        $this->filesDattenteRoutes[spl_object_id($route)] = new SplPriorityQueue();
        return $this;
    }

    /**
     * Retourne une représentation textuelle de l'arrêt
     *
     * @return string
     */
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
