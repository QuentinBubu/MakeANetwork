<?php

namespace App\Actions;

use App\Abstracts\AbstractVehicule;
use App\Entities\Personne;

class ArretActions
{
    public array $vehiculesEnApproche = [];

    public function addPersonne(Personne $personne): void
    {
        $this->vehiculesEnApproche[] = $personne;
    }

    public function enregistrementVehicule(AbstractVehicule $vehicule): void
    {
        $this->vehiculesEnApproche[] = $vehicule;
    }
}
