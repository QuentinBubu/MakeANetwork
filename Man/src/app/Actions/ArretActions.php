<?php

namespace App\Actions;

use App\Abstracts\AbstractVehicule;

class ArretActions
{
    public array $vehiculesEnApproche = [];

    public function enregistrementVehicule(AbstractVehicule $vehicule): void
    {
        $this->vehiculesEnApproche[] = $vehicule;
    }
}
