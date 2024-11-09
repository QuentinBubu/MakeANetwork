<?php

namespace App\Entities;

use App\Interfaces\StateInterface;
use App\Loaders\Arrets;

/**
 * Représente un objectif de trajet pour une personne (d'un arrêt à un autre à un moment donné).
 */
class PersonneObjectif implements StateInterface
{
    /**
     * L'arrêt de départ de la personne.
     *
     * @var Arret
     */
    public readonly Arret $depuis;

    /**
     * L'arrêt d'arrivée de la personne.
     *
     * @var Arret
     */
    public readonly Arret $vers;

    /**
     * L'heure de départ de la personne (en ticks).
     *
     * @var int
     */
    public readonly int $tickDepart;

    /**
     * Constructeur de l'objectif de trajet.
     *
     * @param string $depuis Le nom de l'arrêt de départ.
     * @param string $vers Le nom de l'arrêt de destination.
     * @param int $tickDepart Le tick de départ.
     */
    public function __construct(string $depuis, string $vers, int $tickDepart)
    {
        // On suppose qu'Arrets::getArret() récupère l'objet Arret par son nom.
        $this->depuis = Arrets::getArret($depuis);
        $this->vers = Arrets::getArret($vers);
        $this->tickDepart = $tickDepart;
    }

    /**
     * Exporte l'état actuel de l'objectif sous forme de tableau.
     *
     * @return array L'état exporté sous forme de tableau.
     */
    public function export(): array
    {
        return [
            'depuis' => $this->depuis->nom,
            'vers' => $this->vers->nom,
            'tickDepart' => $this->tickDepart,
        ];
    }
}
