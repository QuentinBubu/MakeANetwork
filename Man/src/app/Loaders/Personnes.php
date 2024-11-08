<?php

namespace App\Loaders;

use App\Log\Message;
use App\Entities\Personne;
use App\Entities\PersonneObjectif;

/**
 * La classe Personnes gère un ensemble d'objets Personne.
 * Elle permet de charger des personnes, les enregistrer, les supprimer, et exporter leurs informations.
 */
class Personnes
{
    /**
     * Tableau statique contenant toutes les personnes.
     * 
     * @var Personne[]
     */
    public static array $personnes = [];

    /**
     * Charge une liste de personnes à partir des données fournies.
     * 
     * Cette méthode crée des instances de Personne pour chaque entrée dans la liste des personnes,
     * en initialisant leur objectif de départ et de retour.
     * 
     * Complexité :
     * - \(O(n)\), où \(n\) est le nombre total de personnes à charger. Pour chaque personne, la méthode crée un objet Personne et ses objectifs associés.
     * 
     * @param array $personnesList Liste des personnes à charger.
     */
    public static function load(array $personnesList): void
    {
        foreach ($personnesList as $personne) {
            for ($i = 0; $i < $personne['nombre']; $i++) {
                // Création de l'objet Personne avec les objectifs de départ et retour
                Message::log("Chargement de la personne {$personne['nom']}{$i}", Message::DEBUG_DETAIL);
                $passager = new Personne(
                    aller: new PersonneObjectif(
                        depuis: $personne['aller']['depart'],
                        vers: $personne['aller']['arrivee'],
                        tickDepart: $personne['aller']['temps']
                    ),
                    retour: new PersonneObjectif(
                        depuis: $personne['retour']['depart'],
                        vers: $personne['retour']['arrivee'],
                        tickDepart: $personne['retour']['temps']
                    ),
                    nom: $personne['nom'] . $i
                );

                // Enregistrement de la personne dans le tableau statique
                self::$personnes[spl_object_id($passager)] = $passager;
            }
        }
    }

    /**
     * Supprime une personne du tableau statique.
     * 
     * Cette méthode retire une personne spécifique du tableau en utilisant son ID unique généré par spl_object_id.
     * 
     * Complexité :
     * - \(O(1)\), car l'accès et la suppression dans un tableau associatif en PHP sont en temps constant.
     * 
     * @param Personne $personne La personne à retirer.
     */
    public static function unregister(Personne $personne): void
    {
        unset(self::$personnes[spl_object_id($personne)]);
    }

    /**
     * Exporte les données de toutes les personnes enregistrées.
     * 
     * Cette méthode parcourt toutes les personnes et récupère leurs informations via la méthode export() de l'objet Personne.
     * Les données sont retournées sous forme de tableau associatif avec le nom de la personne comme clé.
     * 
     * Complexité: O(n), où n est le nombre de personnes enregistrées, car chaque personne est parcourue pour exporter ses données.
     * 
     * @return array Tableau associatif des données des personnes, indexé par leur nom.
     */
    public static function export(): array
    {
        $data = [];
        /** @var Personne $personne */
        foreach (self::$personnes as $personne) {
            $data[$personne->nom] = $personne->export();
        }
        return $data;
    }
}
