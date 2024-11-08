<?php

namespace App\Interfaces;


/**
 * Cette interface impose la méthode incrementTick(), qui est utilisée pour mettre à jour l'état de l'objet
 * à chaque passage de "tick".
 */
interface TimeInterface
{
    public function incrementTick(): void;
}
