<?php

namespace App\Loaders;

use App\Entities\Bus as EntitiesBus;
use App\Factories\BusFactory;

/**
 * Classe responsable de la gestion des bus dans le système.
 * 
 * Cette classe permet de charger une collection de bus depuis des données externes,
 * d'initialiser les objets bus avec des configurations et d'exporter leurs données.
 */
class Bus
{
    /**
     * Tableau statique contenant tous les bus chargés.
     * 
     * @var EntitiesBus[]
     */
    public static array $buses = [];

    /**
     * Charge une liste de bus à partir de données et d'une configuration.
     * 
     * Pour chaque bus, la méthode utilise la fabrique BusFactory pour créer un objet Bus
     * et l'ajoute à la collection de bus statique.
     * 
     * Complexité: O(n), où n est le nombre de bus à charger, car chaque bus est parcouru
     * pour créer un objet Bus.
     * 
     * @param array $bus Liste des bus à charger.
     * @param array $config Configuration des bus, contenant des informations spécifiques comme la capacité ou la vitesse.
     */
    public static function load(array $bus, array $config): void
    {
        /** @var array $b */
        foreach ($bus as $b) {
            self::$buses[] = BusFactory::make($b, $config);
        }
    }

    /**
     * Exporte les données des bus sous forme de tableau.
     * 
     * Cette méthode parcourt tous les bus et récupère leurs informations via la méthode export() 
     * des objets Bus, puis les retourne sous forme de tableau.
     * 
     * Complexité: O(n), où n est le nombre de bus chargés, car on parcourt chaque bus pour exporter ses données.
     * 
     * @return array Tableau des données des bus, indexé par l'ID de l'objet bus.
     */
    public static function export(): array
    {
        $data = [];
        /** @var EntitiesBus $bus */
        foreach (self::$buses as $bus) {
            $data[spl_object_id($bus)] = $bus->export();
        }
        return $data;
    }
}
