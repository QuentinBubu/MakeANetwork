<?php

namespace App\Entities;

use App\Entities\Arret;
use App\Interfaces\TimeInterface;
use App\Loaders\Trajets;

/**
 * Représente une position entre deux arrêts dans un parcours.
 * Cette classe permet de calculer le temps (en ticks) entre des arrêts et d'obtenir les distances parcourues.
 */
abstract class Position implements TimeInterface
{
    /**
     * Le temps écoulé (en ticks) depuis le départ.
     *
     * @var int
     */
    public int $tick = 0;

    /**
     * Calcule le temps nécessaire pour atteindre un arrêt donné depuis la position actuelle.
     * 
     * Cette méthode additionne les distances entre les arrêts à partir de la position actuelle jusqu'à l'arrêt de destination.
     * 
     * Complexité: O(n)
     * 
     * @param Parcours $parcours Le parcours dans lequel la position est calculée.
     * @param Arret $arret L'arrêt de destination.
     * @param int $multiplicateur Un facteur multiplicatif appliqué à la distance totale (par défaut 1).
     * @return int Le temps en ticks nécessaire pour atteindre l'arrêt cible.
     */
    public function tickTo(Parcours $parcours, Arret $arret, int $multiplicateur = 1): int
    {
        // Initialisation de la position de départ
        $from = $parcours->currentArret;
        $dist = 0;

        // Parcours des arrêts jusqu'à atteindre l'arrêt cible
        while ($parcours->getArretWithIndex($from) !== $arret) {
            $from = $parcours->findNextArret($from);
            $dist += $this->getDistanceBetweenStops($parcours, $from);
        }

        // Retourne le temps en ticks, en appliquant un multiplicateur
        return ($dist * $multiplicateur) - $this->tick;
    }

    /**
     * Calcule le temps nécessaire pour faire une boucle complète des arrêts à partir de la position actuelle.
     * 
     * Cette méthode additionne les distances entre tous les arrêts du parcours et revient au point de départ.
     * 
     * Complexité: O(n)
     * 
     * @param Parcours $parcours Le parcours dans lequel la position est calculée.
     * @param int $multiplicateur Un facteur multiplicatif appliqué à la distance totale (par défaut 1).
     * @return int Le temps en ticks nécessaire pour parcourir tous les arrêts jusqu'au retour à la position initiale.
     */
    public function tickToNextComming(Parcours $parcours, int $multiplicateur = 1): int
    {
        // Initialisation de la position de départ
        $from = $parcours->currentArret;
        $dist = 0;

        // Parcours des arrêts du parcours en boucle
        do {
            $from = $parcours->findNextArret($from);
            $dist += $this->getDistanceBetweenStops($parcours, $from);
        } while ($parcours->getArretWithIndex($from) !== $parcours->getArretWithIndex($parcours->currentArret));

        // Retourne le temps en ticks, en appliquant un multiplicateur
        return ($dist * $multiplicateur) - $this->tick;
    }

    /**
     * Calcule la distance entre deux arrêts consécutifs dans un parcours.
     * 
     * @param Parcours $parcours Le parcours dans lequel la position est calculée.
     * @param int $from L'index de l'arrêt de départ.
     * @return int La distance entre l'arrêt de départ et l'arrêt suivant.
     */
    private function getDistanceBetweenStops(Parcours $parcours, int $from): int
    {
        $nextArret = $parcours->findNextArret($from);
        return Trajets::findTrajetWithArret(
            depart: $parcours->getArretWithIndex($from),
            arrivee: $parcours->getArretWithIndex($nextArret)
        )->distance;
    }

    /**
     * Méthode abstraite pour incrémenter le tick de la position.
     */
    abstract public function incrementTick(): void;
}
