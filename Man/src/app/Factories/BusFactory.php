<?php

namespace App\Factories;

use App\Log\Message;
use App\Entities\Bus;
use App\Loaders\Parcours;

/**
 * La classe BusFactory est responsable de la création d'objets Bus.
 * 
 * Cette factory simplifie la construction d'un objet Bus en encapsulant 
 * la logique de création et la gestion des paramètres de configuration 
 * associés au bus.
 */
class BusFactory
{
    /**
     * Crée une instance de Bus en fonction des données fournies et de la configuration.
     * 
     * @param array $bus Les données du bus, y compris le type de bus et le parcours associé.
     * @param array $config La configuration des bus, contenant des informations comme la capacité,
     *                      la vitesse de chargement et la vitesse de déplacement pour chaque type de bus.
     * 
     * @return Bus Une instance du bus configuré.
     */
    public static function make(array $bus, array $config): Bus
    {
        Message::log("Construction du bus {$bus['type']}", Message::DEBUG_DETAIL);
        
        // Création du bus en utilisant les données et la configuration
        return new Bus(
            capacite: $config[$bus['type']]['capacite-max'],
            vitesseChargement: $config[$bus['type']]['vitesse-chargement'],
            vitesseDeplacement: $config[$bus['type']]['vitesse-deplacement'],
            type: $bus['type'],
            parcours: Parcours::getParcours($bus['parcours'])
        );
    }
}
