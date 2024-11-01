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
                    $neighbors[$route->nom] = (object) ['route' => $route, 'arret' => $neighbor];
                }
            }
        }
        return $neighbors;
    }

    /**
     * Map les routes
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
     */
    public function registerRoute(Route $route): self
    {
        echo "Enregistrement de la route {$route->nom} pour l'arrêt {$this->nom}\n";
        $this->routes[] = $route;
        return $this;
    }

    public function addPersonne(Personne $personne): void
    {
        echo "Arrivée de la personne {$personne->nom} à l'arrêt {$this->nom} au tick " . Time::getTick() . PHP_EOL;
        $priorite = [-Time::getTick(), $personne->nom];
        $this->fileAttente->insert($personne, $priorite);
    }

    public function removePersonne(Personne $personne): void
    {
        foreach ($this->fileAttente as $personneFile) {
            if ($personneFile === $personne) {
                $this->fileAttente->extract();
                break;
            }
        }
    }

    public function addBusEnApproche(Bus $bus, Timer $tick): void
    {
        $this->vehiculesEnApproche[spl_object_id($bus)] = [$bus, $tick];
    }

    public function removeBusEnApproche(Bus $bus): void
    {
        unset($this->vehiculesEnApproche[spl_object_id($bus)]);
    }

    public function removeBusEnAttente(Bus $bus): void
    {
        $key = array_search($bus, $this->vehiculesEnAttente);
        if ($key !== false) {
            unset($this->vehiculesEnAttente[$key]);
        }
    }

    public function arriveeBus(Bus $bus): void
    {
        echo "Arrivée du bus " . spl_object_id($bus) . " à l'arrêt {$this->nom}\n";
        $this->vehiculesEnAttente[] = $bus;
        $this->removeBusEnApproche($bus);
        $bus->setState(BusStateEnum::FLUX_VOYAGEURS);

        /** @var Personne $personne */
        foreach ($this->fileAttente as $personne) {
            if ($bus->isFull()) {
                break;
            }

            if (in_array($personne, $bus->personnesDescendu)) {
                continue;
            }

            // Récupère le trajet en cours de la personne
            $trajetEnCours = $personne->getTrajetEnCours();

            // Détermine le prochain arrêt du trajet
            $prochainArret = $trajetEnCours->getProchainArret($this);

            // Si un prochain arrêt est déterminé
            if ($prochainArret) {
                // Calcule le trajet optimisé de l'arrêt actuel vers le prochain arrêt
                $trajetOptimise = $personne->calculTrajet($this, $prochainArret);

                // Vérifie que le trajet optimisé est valide avant de l’enregistrer
                if (!empty($trajetOptimise)) {
                    // Enregistre le trajet optimisé dans l'attribut correspondant de la personne
                    $personne->trajetOptimise = $trajetOptimise;

                    if ($trajetOptimise[0]['busAPrendre'] !== $bus) {
                        continue;
                    }

                    /** @var Bus $bus */
                    $trajetOptimise[0]['busAPrendre']->addPersonne($personne);

                    $this->removePersonne($personne);
                }
            }
        }
    }

    public function departBus(Bus $bus): void
    {
        $bus->calculEtEnregistrementProchainPassage($this);
        $bus->setState(BusStateEnum::DEPLACEMENT);
        $this->removeBusEnAttente($bus);
    }

    public function incrementTick(): void
    {
        foreach ($this->vehiculesEnAttente as $bus) {
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
            'vehiculesEnApproche' => array_map(fn($item) => spl_object_id($item[0]), $this->vehiculesEnApproche),
            'vehiculesEnAttente' => array_map(fn($bus) => spl_object_id($bus), $this->vehiculesEnAttente),
        ];
    }

    public function restore(array $state): void
    {
        throw new \Exception("Not implemented");
    }
}
