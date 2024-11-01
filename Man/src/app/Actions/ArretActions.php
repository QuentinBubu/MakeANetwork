<?php

namespace App\Actions;

use App\Timer\Timer;
use App\Entities\Bus;
use SplPriorityQueue;
use App\Entities\Route;
use App\Entities\Personne;
use App\Enums\BusStateEnum;

trait ArretActions
{
    /**
     * Tableau des véhicules en approche
     * [spl_object_id(Bus) => [Bus, Timer]]
     * @var array
     */
    public array $vehiculesEnApproche = [];

    public array $vehiculesEnAttente = [];

    public function addPersonne(Personne $personne, Route $route, int $tickArrivee): void
    {
        $priorite = [-$tickArrivee, $personne->nom]; // Priorité : tick en premier (en négatif pour ordre croissant), puis nom alphabétique
        $this->filesDattenteRoutes[spl_object_id($route)]->insert($personne, $priorite);
    }

    /**
     * Récupère la file d'attente d'une route spécifique
     */
    public function getFileDattente(Route $route): SplPriorityQueue
    {
        return $this->filesDattenteRoutes[spl_object_id($route)];
    }


    public function addBusEnApproche(Bus $bus, Timer $tick): void
    {
        $this->vehiculesEnApproche[spl_object_id($bus)] = [$bus, $tick];
    }

    public function removeBusEnApproche(Bus $bus): void
    {
        unset($this->vehiculesEnApproche[spl_object_id($bus)]);
    }

    public function getBusEnApproche(Bus $bus): array
    {
        return $this->vehiculesEnApproche[spl_object_id($bus)];
    }

    public function arriveeBus(Bus $bus): void
    {
        $this->vehiculesEnAttente[] = $bus;
        $this->removeBusEnApproche($bus);
        $bus->setState(BusStateEnum::FLUX_VOYAGEURS);
    }

    public function departBus(Bus $bus): void
    {
        $bus->setState(BusStateEnum::DEPLACEMENT);
        $this->removeBusEnAttente($bus);
    }

    public function removeBusEnAttente(Bus $bus): void
    {
        unset($this->vehiculesEnAttente[spl_object_id($bus)]);
    }

    public function incrementTick(): void
    {
        // Mise à jour des bus en déplacement
        foreach ($this->vehiculesEnAttente as $bus) {
            // Vérifier s'il y a des personnes assignées au bus
            if ($bus->isFull() || $this->filesDattenteRoutes[spl_object_id($bus->getParcours()->findNextRoute())]->isEmpty()) {
                $this->departBus($bus);
            }
        }
    }
}
