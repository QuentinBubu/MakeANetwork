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
     * Clé: spl_object_id(Bus) => [Bus, Timer]
     * @var array
     */
    public array $vehiculesEnApproche = [];

    /**
     * Tableau des véhicules en attente
     * @var array
     */
    public array $vehiculesEnAttente = [];

    /**
     * Nom de l'arrêt
     * @var string
     */
    public string $nom;

    /**
     * Routes qui passent par cet arrêt
     * @var Route[]
     */
    public array $routes;

    /**
     * Liste des personnes en attente à l'arrêt
     * @var SplPriorityQueue
     */
    public SplPriorityQueue $fileAttente;

    /**
     * Liste des routes sous forme de chaîne de caractères
     * @var string[]
     */
    private array $genericRoutes;

    /**
     * Constructeur
     *
     * @param string $nom Nom de l'arrêt
     * @param array $genericRoutes Routes génériques associées à cet arrêt
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
     * Parcourt les routes et retourne les arrêts voisins de l'arrêt actuel.
     * Complexité: O(n * m), où n est le nombre de routes et m est le nombre d'arrêts par route
     *
     * @return array
     */
    public function getNeighbors(): array
    {
        Message::log("Recherche des voisins de l'arrêt {$this->nom}", Message::DEBUG_ALL);
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
     * Map les routes génériques à l'arrêt
     *
     * Cette méthode enregistre chaque route générique associée à l'arrêt.
     * Complexité: O(r), où r est le nombre de routes génériques
     */
    public function mapRoutes(): void
    {
        foreach ($this->genericRoutes as $route) {
            Message::log("Mapping de la route {$route} pour l'arrêt {$this->nom}", Message::DEBUG_DETAIL);
            $this->registerRoute(Routes::getRoute($route)->registerArret($this));
        }
    }

    /**
     * Enregistre une route dans l'arrêt
     *
     * Cette méthode ajoute une route à la liste des routes de l'arrêt.
     * Complexité: O(1)
     *
     * @param Route $route Route à enregistrer
     * @return self
     */
    private function registerRoute(Route $route): self
    {
        Message::log("Enregistrement de la route {$route->nom} pour l'arrêt {$this->nom}", Message::DEBUG_DETAIL);
        $this->routes[] = $route;
        return $this;
    }

    /**
     * Ajoute une personne à la file d'attente
     *
     * Cette méthode ajoute une personne à la file d'attente avec une priorité basée sur l'heure d'arrivée.
     * Complexité: O(log n), où n est le nombre de personnes dans la file d'attente
     *
     * @param Personne $personne Personne à ajouter
     */
    public function addPersonne(Personne $personne): void
    {
        Message::log("Arrivée de la personne {$personne->nom} à l'arrêt {$this->nom} au tick " . Time::getTick(), Message::INFO);
        $priorite = [-Time::getTick(), $personne->nom];
        $this->fileAttente->insert($personne, $priorite);
    }

    /**
     * Supprime une personne de la file d'attente
     *
     * Complexité: O(n), où n est le nombre de personnes dans la file d'attente, car chaque élément doit être vérifié
     *
     * @param Personne $personne Personne à supprimer
     */
    private function removePersonne(Personne $personne): void
    {
        Message::log("Suppression de la personne {$personne->nom} de la file d'attente de l'arrêt {$this->nom}", Message::INFO);
        $nouvelleFile = new SplPriorityQueue();
        $nouvelleFile->setExtractFlags(SplPriorityQueue::EXTR_BOTH);

        while (!$this->fileAttente->isEmpty()) {
            $element = $this->fileAttente->extract();
            if ($element['data'] !== $personne) {
                $nouvelleFile->insert($element['data'], $element['priority']);
            }
        }

        $this->fileAttente = $nouvelleFile;
    }

    /**
     * Ajoute un bus en approche à l'arrêt
     *
     * Ajoute le bus et son timer dans la liste des véhicules en approche.
     * Complexité: O(1)
     *
     * @param Bus $bus Le bus en approche
     * @param Timer $tick Le timer associé au bus
     */
    public function addBusEnApproche(Bus $bus, Timer $tick): void
    {
        Message::log("Bus " . spl_object_id($bus) . " en approche de l'arrêt {$this->nom}", Message::INFO);
        // Clé: spl_object_id(Bus), valeur: [Bus, Timer]
        $this->vehiculesEnApproche[spl_object_id($bus)] = [$bus, $tick];
    }

    /**
     * Supprime un bus de la liste des véhicules en approche
     *
     * Cette méthode supprime un bus de la liste des véhicules en approche et enlève son timer.
     * Complexité: O(1)
     *
     * @param Bus $bus Le bus à supprimer
     */
    private function removeBusEnApproche(Bus $bus): void
    {
        Message::log("Bus " . spl_object_id($bus) . " n'est plus en approche de l'arrêt {$this->nom}", Message::INFO);
        unset($this->vehiculesEnApproche[spl_object_id($bus)]);
        $bus->removeTimer($this);
    }

    /**
     * Supprime un bus de la liste des véhicules en attente
     *
     * Complexité: O(n), où n est le nombre de bus en attente
     *
     * @param Bus $bus Le bus à supprimer
     */
    private function removeBusEnAttente(Bus $bus): void
    {
        Message::log("Bus " . spl_object_id($bus) . " n'est plus en attente de l'arrêt {$this->nom}", Message::INFO);
        $key = array_search($bus, $this->vehiculesEnAttente);
        if ($key !== false) {
            unset($this->vehiculesEnAttente[$key]);
        }
    }

    /**
     * Gère l'arrivée d'un bus à l'arrêt
     *
     * Cette méthode change l'état du bus à "FLUX_VOYAGEURS", le retire des véhicules en approche et l'ajoute à la liste des bus en attente.
     * Complexité: O(1)
     *
     * @param Bus $bus Le bus qui arrive
     */
    public function arriveeBus(Bus $bus): void
    {
        Message::log("Arrivée du bus " . spl_object_id($bus) . " à l'arrêt {$this->nom}", Message::INFO);
        $bus->setState(BusStateEnum::FLUX_VOYAGEURS);
        $this->removeBusEnApproche($bus);
        $this->vehiculesEnAttente[] = $bus;
    }

    /**
     * Retourne la prochaine personne à embarquer pour un bus
     *
     * Complexité: O(n), où n est le nombre de personnes en attente, car chaque personne doit être vérifiée
     *
     * @param Bus $bus Le bus qui prend la personne
     * @return Personne|null La prochaine personne ou null si aucune personne n'est trouvée
     */
    public function obtenirProchainePersonnePourBus(Bus $bus): ?Personne
    {
        $fileTemporaire = new SplPriorityQueue();
        $fileTemporaire->setExtractFlags(SplPriorityQueue::EXTR_BOTH);
        $trouvee = null;

        while (!$this->fileAttente->isEmpty()) {
            $personne = $this->fileAttente->extract();
            $personneDt = $personne['data'];
            if ($bus->canTake($personneDt) && $personneDt->canTake($bus, $this) && $trouvee === null) {
                $trouvee = $personneDt;
            } else {
                $fileTemporaire->insert($personneDt, $personne['priority']);
            }
        }

        $this->fileAttente = $fileTemporaire;
        return $trouvee;
    }

    /**
     * Vérifie si la file est vide pour un bus donné
     *
     * Complexité: O(n), où n est le nombre de personnes en attente
     *
     * @param Bus $bus Le bus qui examine la file
     * @return bool True si la file est vide pour ce bus, false sinon
     */
    public function estFileVidePourBus(Bus $bus): bool
    {
        foreach (clone $this->fileAttente as $personne) {
            if ($bus->canTake($personne['data']) && $personne['data']->canTake($bus, $this)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Recherche une route entre cet arrêt et un autre
     *
     * Complexité: O(r), où r est le nombre de routes associées à l'arrêt
     *
     * @param Arret $arret L'arrêt cible
     * @return Route La route trouvée
     * @throws \RuntimeException Si aucune route n'est trouvée
     */
    public function getRouteTo(Arret $arret): Route
    {
        Message::log("Recherche de la route entre l'arrêt {$this->nom} et l'arrêt {$arret->nom}", Message::DEBUG_DETAIL);
        foreach ($this->routes as $route) {
            if ($route->hasArret($arret)) {
                return $route;
            }
        }
        throw new \RuntimeException("Aucune route trouvée entre l'arrêt {$this->nom} et l'arrêt {$arret->nom}");
    }

    /**
     * Gère le départ d'un bus de l'arrêt
     *
     * Cette méthode change l'état du bus et l'enlève de la liste des véhicules en attente.
     * Complexité: O(n), où n est le nombre de bus en attente
     *
     * @param Bus $bus Le bus qui part
     */
    public function departBus(Bus $bus): void
    {
        Message::log("Départ du bus " . spl_object_id($bus) . " de l'arrêt {$this->nom}", Message::INFO);
        $bus->setState(BusStateEnum::DEPLACEMENT);
        $this->removeBusEnAttente($bus);
        $bus->calculEtEnregistrementProchainPassage($this);
    }

    /**
     * Incrémente le tick pour gérer l'embarquement et le débarquement
     *
     * Complexité: O(b + n), où b est le nombre de bus en attente et n le nombre de personnes en attente
     */
    public function incrementTick(): void
    {
        Message::log("Tick de l'arrêt {$this->nom}", Message::DEBUG_DETAIL);
        foreach ($this->vehiculesEnAttente as $bus) {
            $this->debarquementProgressif($bus);

            if (!$bus->isFull() && !$this->estFileVidePourBus($bus)) {
                $personne = $this->obtenirProchainePersonnePourBus($bus);
                if ($personne !== null) {
                    $bus->addPersonne($personne);
                    $this->removePersonne($personne);
                    Message::log("Embarquement progressif: La personne {$personne->nom} monte dans le bus " . spl_object_id($bus) . " à l'arrêt {$this->nom} au tick : " . Time::getTick(), Message::INFO);
                }
            } else {
                $this->departBus($bus);
            }
        }
    }

    /**
     * Effectue le débarquement progressif d'un passager
     *
     * Complexité: O(p), où p est le nombre de passagers dans le bus
     *
     * @param Bus $bus Le bus qui effectue le débarquement
     */
    private function debarquementProgressif(Bus $bus): void
    {
        foreach ($bus->getPersonnes() as $passager) {
            if ($passager->veutDescendre($this)) {
                $bus->descentePassager($passager);
                Message::log("Débarquement progressif: Le passager {$passager->nom} descend du bus " . spl_object_id($bus) . " à l'arrêt " . $this->nom, Message::INFO);
                $passager->descendArret($this);
                break;
            }
        }
    }

    /**
     * Exporte l'état de l'arrêt sous forme de tableau
     * 
     * @return array État exporté de l'arrêt
     */
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
            'vehiculesEnApproche' => array_map(fn($item) => $item[1]->getRemainingTicks(), $this->vehiculesEnApproche),
            'vehiculesEnAttente' => array_map(fn($bus) => spl_object_id($bus), $this->vehiculesEnAttente),
        ];
    }

    /**
     * Restaurer l'état de l'arrêt (non implémenté)
     * 
     * @param array $state L'état de restauration
     * 
     * @throws \Exception Si la méthode n'est pas implémentée
     */
    public function restore(array $state): void
    {
        throw new \Exception("Not implemented");
    }
}
