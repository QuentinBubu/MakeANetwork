<?php

namespace App\Actions;

use App\Entities\Bus;
use App\Timer\Time;
use App\Entities\File;
use App\Entities\Personne;
use App\Enums\BusStateEnum;
use App\Timer\Timer;

trait ArretActions
{
    /**
     * Tableau des véhicules en approche
     * [spl_object_id(Bus) => [Bus, Timer]]
     * @var array
     */
    public array $vehiculesEnApproche = [];

    public array $vehiculesEnAttente = [];

    /**
     * Tableau des files d'attente suivant les ticks
     * @var array
     */
    private array $files = [];

    public function addPersonne(Personne $personne): void
    {
        $this->files[Time::getTick()]->addPersonne($personne);
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

    // public function enregistrementVehicule(AbstractVehicule $vehicule): void
    // {
    //     $this->vehiculesEnApproche[] = $vehicule;
    // }

    public function incrementTick(): void
    {
        $busEnAttenteAddr = [];

        foreach ($this->vehiculesEnAttente as $bus) {
            if ($bus->state === BusStateEnum::FLUX_VOYAGEURS) {
                $busEnAttenteAddr[] = spl_object_id($bus);
            }
        }
        return;
        foreach ($this->files as $file) {
            // if ()
            if ($file->isEmpty() === 0) {
                unset($this->files[Time::getTick()]);
            }
        }

        $this->files[Time::getTick()] = new File();
    }
}
