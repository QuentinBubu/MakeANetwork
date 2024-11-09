<?php

namespace App\Interfaces;

/**
 * Interface pour les objets qui ont un état pouvant être exporté et restauré.
 * 
 * Cette interface impose deux méthodes : export pour obtenir une représentation de l'état sous forme de tableau,
 * et restore pour restaurer un objet depuis un état préalablement sauvegardé.
 */
interface StateInterface
{
    public function export(): array;
}
