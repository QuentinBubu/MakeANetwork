<?php

namespace App\Interfaces;

use App\Entities\Personne;

interface BusActionsInterface
{
    /**
     * @param Personne[] $personnes
     */
    function chargerPersonnes(array $personnes): void;

    /**
     * @return Personne[]
     */
    function dechargerPersonnes(): void;
}
