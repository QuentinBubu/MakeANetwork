<?php

namespace App\Entities;

use App\Timer\Time;
use App\Log\Message;
use App\Timer\Timer;
use App\Entities\Bus;
use SplPriorityQueue;
use App\Entities\Route;
use App\Loaders\Routes;
use App\Entities\Personne;
use App\Enums\BusStateEnum;
use App\Interfaces\TimeInterface;
use App\Interfaces\StateInterface;

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
        $this->fileAttente->setExtractFlags(SplPriorityQueue::EXTR_BOTH);
        Time::registerClass($this);
    }

    /**
     * Retourne les voisins de l'arrêt
     *
     * @return array
     */
    public function getNeighbors(): array
    {
        Message::log("Recherche des voisins de l'arrêt {$this->nom}", Message::DEBUG_ALL);
        $neighbors = [];
        /** @var Route $route */
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
        /** @var string $route */
        foreach ($this->genericRoutes as $route) {
            Message::log("Mapping de la route {$route} pour l'arrêt {$this->nom}", Message::DEBUG_DETAIL);
            $this->registerRoute(Routes::getRoute($route)->registerArret($this));
        }
    }

    /**
     * Enregistre une route dans sa mémoire
     */
    private function registerRoute(Route $route): self
    {
        Message::log("Enregistrement de la route {$route->nom} pour l'arrêt {$this->nom}", Message::DEBUG_DETAIL);
        $this->routes[] = $route;
        return $this;
    }

    public function addPersonne(Personne $personne): void
    {
        Message::log("Arrivée de la personne {$personne->nom} à l'arrêt {$this->nom} au tick " . Time::getTick(), Message::INFO);
        $priorite = [-Time::getTick(), $personne->nom];
        $this->fileAttente->insert($personne, $priorite);
    }

    private function removePersonne(Personne $personne): void
    {
        Message::log("Suppression de la personne {$personne->nom} de la file d'attente de l'arrêt {$this->nom}", Message::INFO);
    
        // Nouvelle file pour réinsérer les éléments
        $nouvelleFile = new SplPriorityQueue();
        $nouvelleFile->setExtractFlags(SplPriorityQueue::EXTR_BOTH);
    
        while (!$this->fileAttente->isEmpty()) {
            $element = $this->fileAttente->extract(); // Récupère un élément

            if ($element['data'] !== $personne) {
                $nouvelleFile->insert($element['data'], $element['priority']);
            }
        }

        $this->fileAttente = $nouvelleFile;
    }

    public function addBusEnApproche(Bus $bus, Timer $tick): void
    {
        Message::log("Bus " . spl_object_id($bus) . " en approche de l'arrêt {$this->nom}", Message::INFO);
        $this->vehiculesEnApproche[spl_object_id($bus)] = [$bus, $tick];
    }

    private function removeBusEnApproche(Bus $bus): void
    {
        Message::log("Bus " . spl_object_id($bus) . " n'est plus en approche de l'arrêt {$this->nom}", Message::INFO);
        unset($this->vehiculesEnApproche[spl_object_id($bus)]);
    }

    private function removeBusEnAttente(Bus $bus): void
    {
        Message::log("Bus " . spl_object_id($bus) . " n'est plus en attente de l'arrêt {$this->nom}", Message::INFO);
        $key = array_search($bus, $this->vehiculesEnAttente);
        if ($key !== false) {
            unset($this->vehiculesEnAttente[$key]);
        }
    }

    public function arriveeBus(Bus $bus): void
    {
        Message::log("Arrivée du bus " . spl_object_id($bus) . " à l'arrêt {$this->nom}", Message::INFO);
        $bus->setState(BusStateEnum::FLUX_VOYAGEURS);
        $this->verifierSignauxDescente($bus);
        $this->vehiculesEnAttente[] = $bus;
        $this->removeBusEnApproche($bus);

        Message::log("Début de l'embarquement des passagers dans le bus " . spl_object_id($bus) . " à l'arrêt {$this->nom}", Message::INFO);

        foreach (clone $this->fileAttente as $personne) {
            /** @var Personne $personne */
            $personne = $personne['data'];

            if (!$bus->canTake($personne)) {
                continue;
            }

            if (!$personne->canTake($bus, $this)) {
                continue;
            }

            $bus->addPersonne($personne);
            $this->removePersonne($personne);
        }

        $this->departBus($bus);
    }

    // Attention, tous les passagers descendent en même temps là alors que c'est 1 par tick
    private function verifierSignauxDescente(Bus $bus)
    {
        Message::log("Vérification des signaux de descente du bus " . spl_object_id($this) . " à l'arrêt " . $this->nom);
        /** @var Personne $passager */
        foreach ($bus->getPersonnes() as $passager) {
            if ($passager->veutDescendre($this)) {
                // Le passager souhaite descendre à cet arrêt
                $bus->descentePassager($passager);
                Message::log("Le passager {$passager->nom} descend du bus " . spl_object_id($this) . " à l'arrêt " . $this->nom);
                $passager->descendArret($this);
            }
        }
    }

    public function getRouteTo(Arret $arret): Route
    {
        Message::log("Recherche de la route entre l'arrêt {$this->nom} et l'arrêt {$arret->nom}", Message::DEBUG_DETAIL);
        /** @var Route $route */
        foreach ($this->routes as $route) {
            if ($route->hasArret($arret)) {
                return $route;
            }
        }
        throw new \RuntimeException("Aucune route trouvée entre l'arrêt {$this->nom} et l'arrêt {$arret->nom}");
    }

    public function departBus(Bus $bus): void
    {
        Message::log("Départ du bus " . spl_object_id($bus) . " de l'arrêt {$this->nom}", Message::INFO);
        $bus->setState(BusStateEnum::DEPLACEMENT);
        $this->removeBusEnAttente(bus: $bus);
        $bus->calculEtEnregistrementProchainPassage($this);
    }

    public function incrementTick(): void
    {
        Message::log("Tick de l'arrêt {$this->nom}", Message::DEBUG_DETAIL);
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
                fn($personne) => [$personne['data']->nom, $personne['priority']],
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
