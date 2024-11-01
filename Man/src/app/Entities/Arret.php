<?php

namespace App\Entities;

use App\Timer\Time;
use App\Timer\Timer;
use App\Entities\Bus;
use SplPriorityQueue;
use App\Entities\Route;
use App\Loaders\Routes;
use App\Entities\Personne;
use App\Enums\BusStateEnum;
use App\Interfaces\StateInterface;
use App\Interfaces\TimeInterface;
use App\State\State;

/**
 * Représente un arrêt de bus
 */
class Arret implements TimeInterface, StateInterface
{
        /**
     * Tableau des véhicules en approche
     * [spl_object_id(Bus) => [Bus, Timer]]
     * @var array
     */
    public array $vehiculesEnApproche = [];

    public array $vehiculesEnAttente = [];

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
     * @var SplPriorityQueue
     */
    public SplPriorityQueue $fileAttente;

    /**
     * Liste des routes sous forme de string
     *
     * @var string[]
     */
    private array $genericRoutes;

    /**
     * Constructeur
     *
     * @param string $nom
     * @param array $genericRoutes
     * @param array $genericFile
     */
    public function __construct(string $nom, array $genericRoutes)
    {
        $this->nom = $nom;
        $this->genericRoutes = $genericRoutes;
        $this->fileAttente = new SplPriorityQueue();
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
            echo "Mapping de la route {$route} pour l'arrêt {$this->nom}\n";
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
        echo "Enregistrement de la route {$route->nom} pour l'arrêt {$this->nom}\n";
        $this->routes[] = $route;
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

    public function addPersonne(Personne $personne): void
    {
        echo "Arrivée de la personne {$personne->nom} à l'arrêt {$this->nom} au tick " . Time::getTick() . PHP_EOL;
        $priorite = [-Time::getTick(), $personne->nom]; // Priorité : tick en premier (en négatif pour ordre croissant), puis nom alphabétique
        $this->fileAttente->insert($personne, $priorite);
    }

    public function addBusEnApproche(Bus $bus, Timer $tick): void
    {
        $this->vehiculesEnApproche[spl_object_id($bus)] = [$bus, $tick];
    }

    public function removeBusEnApproche(Bus $bus): void
    {
        unset($this->vehiculesEnApproche[spl_object_id($bus)]);
    }

    public function getBusEnApproche(): array
    {
        return $this->vehiculesEnApproche;
    }

    public function arriveeBus(Bus $bus): void
    {
        echo "Arrivée du bus " . spl_object_id($bus) . " à l'arrêt {$this->nom}\n";
        $this->vehiculesEnAttente[] = $bus;
        $this->removeBusEnApproche($bus);
        $bus->setState(BusStateEnum::FLUX_VOYAGEURS);
    }

    public function departBus(Bus $bus): void
    {
        $bus->calculEtEnregistrementProchainPassage($this);
        $bus->setState(BusStateEnum::DEPLACEMENT);
        $this->removeBusEnAttente($bus);
    }

    public function removeBusEnAttente(Bus $bus): void
    {
        $key = array_search($bus, $this->vehiculesEnAttente);
        if ($key !== false) {
            unset($this->vehiculesEnAttente[$key]);
        }
    }

    public function incrementTick(): void
    {
        // Mise à jour des bus en déplacement
        foreach ($this->vehiculesEnAttente as $bus) {
            // Vérifier s'il y a des personnes assignées au bus
            if ($bus->isFull() || $this->fileAttente->isEmpty()) {
                $this->departBus($bus);
            }
        }
    }

    public function export(): array
    {
        return [
            'nom' => $this->nom,
            'routes' => array_map(
                function ($route) {
                    return $route->nom;
                },
                $this->routes
            ),
            'fileAttente' => array_map(
                fn($personne) => $personne->nom,
                iterator_to_array(clone $this->fileAttente)
            ),
            'vehiculesEnApproche' => array_map(
                function ($vehicule) {
                    return [spl_object_id($vehicule[0]), $vehicule[1]->getRemainingTicks()];
                },
                $this->vehiculesEnApproche
            ),
            'vehiculesEnAttente' => array_map(
                function ($vehicule) {
                    return spl_object_id($vehicule);
                },
                $this->vehiculesEnAttente
            ),
        ];
    }

    public function restore(array $state): void
    {
        throw new \Exception('Not implemented');
    }
}
