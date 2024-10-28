<?php

namespace App\Actions;

use App\Entities\Bus;
use App\Entities\Personne;
use App\Enums\BusStateEnum;
use App\Loaders\Trajets;
use App\Timer\Timer;
use SplPriorityQueue;

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
     * Lignes de bus
     * @var array [spl_object_id(Bus) => nbPersonnes]
     */
    public array $interetsBus = [];

    /**
     * File d'attente des personnes
     * @var SplPriorityQueue
     */
    public SplPriorityQueue $personnesEnAttente;

    public function addPersonne(Personne $personne): void
    {
        $personne->busAPrendre = Trajets::calculTrajetOptimise($personne->getTrajetEnCours()); // Détermine le meilleur bus
        // Logique d'ajout à la file d'attente reste inchangée
        $priorite = -$personne->getTrajetEnCours()->arrivee;
        $this->personnesEnAttente->insert($personne, [$priorite, $personne->nom]);

        // Mise à jour de l'intérêt pour la ligne préférée
        $lignePreferee = spl_object_id($personne->busAPrendre);
        if (!isset($this->interetsBus[$lignePreferee])) {
            $this->interetsBus[$lignePreferee] = 0;
        }
        $this->interetsBus[$lignePreferee]++;
    }

    public function assignerPersonnesAuxBus()
    {
        $fileTemporaire = new SplPriorityQueue();
        $personnesParBus = [];
    
        // Regrouper les personnes par bus de préférence
        foreach (clone $this->personnesEnAttente as $personne) {
            $busId = spl_object_id($personne->busAPrendre);
            $personnesParBus[$busId][] = $personne;
        }
    
        foreach ($this->vehiculesEnAttente as $bus) {
            $busId = spl_object_id($bus);
    
            if (!isset($personnesParBus[$busId])) continue; // Si aucun passager n'attend ce bus, passer
    
            foreach ($personnesParBus[$busId] as $personne) {
                if ($bus->hasSpace()) {
                    $bus->chargerPersonne($personne);
                } else {
                    $fileTemporaire->insert($personne, [$personne->priorite, $personne->nom]);
                }
            }
    
            $this->verifierEtatBus($busId);
        }
    
        $this->personnesEnAttente = $fileTemporaire;
    }

    private function verifierEtatBus($numeroBus)
    {
        if ($this->interetsBus[$numeroBus] <= 0) {
            $bus = $this->lignesDeBus[$numeroBus] ?? null;
            if ($bus) {
                $bus->setState(BusStateEnum::DEPLACEMENT);
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

    public function getBusEnApproche(Bus $bus): array
    {
        return $this->vehiculesEnApproche[spl_object_id($bus)];
    }

    public function fluxVoyageurs(Bus $bus): void
    {
        $this->vehiculesEnAttente[] = $bus;
        $this->removeBusEnApproche($bus);
    }

    public function incrementTick(): void
    {
        // Assigner les personnes aux bus disponibles en attente
        $this->assignerPersonnesAuxBus();
    
        // Mise à jour des bus en déplacement
        foreach ($this->vehiculesEnAttente as $bus) {
            // Vérifier s'il y a des personnes assignées au bus
            $busId = spl_object_id($bus);
            if (!isset($this->interetsBus[$busId]) || $this->interetsBus[$busId] <= 0) {
                // Si le bus ne transporte plus de passagers
                $bus->setState(BusStateEnum::DEPLACEMENT);
            }
        }
    }
}
