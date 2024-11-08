<?php

namespace App\Loaders;

use App\Log\Message;
use App\Entities\Arret;
use App\Exceptions\ArretsException;

/**
 * Classe responsable de la gestion des arrêts de transport.
 * 
 * Cette classe est un gestionnaire statique permettant de charger, mapper, 
 * récupérer et exporter les arrêts dans le système.
 */
class Arrets
{
    /**
     * Tableau contenant les arrêts indexés par leur nom.
     * 
     * @var Arret[]
     */
    public static array $arrets = [];

    /**
     * Charge les données des arrêts dans le système.
     * 
     * Pour chaque arrêt, un objet Arret est créé et ajouté au tableau statique
     * 
     * Complexité: O(n), où n est le nombre d'arrêts à charger, car on parcourt
     * chaque élément du tableau d'arrêts pour créer un objet Arret.
     * 
     * @param array $arrets Données des arrêts à charger (nom et routes associées).
     */
    public static function load(array $arrets): void
    {
        foreach ($arrets as $name => $data) {
            Message::log("Construction de l'arrêt {$name}", Message::DEBUG_DETAIL);
            $arret = new Arret(nom: $name, genericRoutes: $data['routes']);
            self::$arrets[$name] = $arret;
        }
    }

    /**
     * Associe les routes à chaque arrêt.
     * 
     * Appelle la méthode mapRoutes() sur chaque objet Arret, ce qui permet
     * de lier les routes correspondantes à chaque arrêt.
     * 
     * Complexité: O(n), où n est le nombre d'arrêts, car on itère sur chaque arrêt
     * pour appeler la méthode mapRoutes().
     */
    public static function map(): void
    {
        /** @var Arret $arret */
        foreach (self::$arrets as $arret) {
            $arret->mapRoutes();
        }
    }

    /**
     * Récupère un arrêt par son nom.
     * 
     * Si l'arrêt n'existe pas, une exception ArretsException est levée.
     * 
     * Complexité: O(1), car on accède directement à l'élément du tableau.
     * 
     * @param string $name Nom de l'arrêt à récupérer.
     * @return Arret L'objet Arret correspondant au nom.
     * @throws ArretsException Si l'arrêt n'existe pas.
     */
    public static function getArret(string $name): Arret
    {
        if (!isset(self::$arrets[$name])) {
            throw new ArretsException("Arrêt {$name} inconnu");
        }
        return self::$arrets[$name];
    }

    /**
     * Exporte les données des arrêts sous forme de tableau.
     * 
     * Cette méthode permet de récupérer toutes les informations des arrêts sous
     * une forme exploitable, par exemple pour les sauvegarder dans un fichier
     * ou les transmettre à une autre partie du système.
     * 
     * Complexité: O(n), où n est le nombre d'arrêts, car on parcourt chaque arrêt
     * pour exporter ses données.
     * 
     * @return array Tableau des données des arrêts.
     */
    public static function export(): array
    {
        $data = [];
        /** @var Arret $arret */
        foreach (self::$arrets as $arret) {
            $data[$arret->nom] = $arret->export();
        }
        return $data;
    }
}
