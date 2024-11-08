<?php

namespace App\Interfaces;

use App\Entities\Personne;

/**
 * Interface qui définit les actions qu'un bus peut effectuer
 * en termes de gestion des passagers.
 * 
 * Les actions incluent le chargement et le déchargement de personnes.
 */
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
