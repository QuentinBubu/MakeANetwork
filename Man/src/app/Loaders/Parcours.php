<?php

namespace App\Loaders;

use App\Log\Message;
use App\Entities\Parcours as EntityParcours;

/**
 * Classe qui gère les parcours dans le système.
 * 
 * Un parcours est une séquence ordonnée d'arrêts (par exemple, A -> B -> C).
 * Un bus peut suivre un parcours complet ou un sous-ensemble de ce parcours (par exemple, A -> C sans s'arrêter à B).
 */
class Parcours
{
    /**
     * Tableau statique qui contient tous les parcours chargés.
     * 
     * @var EntityParcours[]
     */
    public static array $parcours = [];

    /**
     * Charge un ensemble de parcours à partir d'un tableau de données.
     * 
     * Cette méthode crée une instance de EntityParcours pour chaque parcours,
     * et pour chaque parcours, elle ajoute des trajets (des liens entre arrêts) à partir des arrêts fournis.
     * 
     * Complexité: O(n * m), où n est le nombre de parcours, et m est le nombre d'arrêts par parcours.
     * 
     * @param array $parcours Liste des parcours à charger.
     */
    public static function load(array $parcours): void
    {
        foreach ($parcours as $name => $arrets) {
            Message::log("Construction du parcours {$name}", Message::DEBUG_DETAIL);

            // Mapping des arrêts du parcours
            $arretsMap = array_map(fn ($arret) => Arrets::getArret($arret), $arrets);

            Message::log("Construction des trajets pour le parcours {$name}", Message::DEBUG_DETAIL);

            // Création de l'instance de parcours
            $parc = new EntityParcours(nom: $name, arretsAFaire: $arretsMap);

            // Ajout des trajets entre chaque paire d'arrêts
            for ($i = 0; $i < count($arretsMap) - 1; $i++) {
                $arretA = $arrets[$i];
                $arretB = $arrets[$i + 1];
                $parc->addTrajet(Trajets::findTrajet($arretA, $arretB));
            }

            self::$parcours[$name] = $parc;
        }
    }

    /**
     * Retourne un parcours par son nom.
     * 
     * Cette méthode permet de récupérer un parcours spécifique à partir de son nom.
     * 
     * @param string $nom Le nom du parcours à récupérer.
     * @return EntityParcours Le parcours correspondant au nom.
     */
    public static function getParcours(string $nom): EntityParcours
    {
        return self::$parcours[$nom];
    }

    /**
     * Exporte les données de tous les parcours.
     * 
     * Cette méthode parcourt tous les parcours et récupère leurs informations via la méthode export()
     * des objets EntityParcours, puis les retourne sous forme de tableau.
     * 
     * Complexité: O(n) où n est le nombre de parcours, car chaque parcours est parcouru pour récupérer ses données.
     * 
     * @return array Tableau des données des parcours, indexé par le nom du parcours.
     */
    public static function export(): array
    {
        $data = [];
        /** @var EntityParcours $parcoursElement */
        foreach (self::$parcours as $parcoursElement) {
            $data[$parcoursElement->nom] = $parcoursElement->export();
        }
        return $data;
    }
}
