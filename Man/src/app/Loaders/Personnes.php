<?php

namespace App\Loaders;

use App\Entities\Personne;

class Personnes
{
    public static array $personnes = [];

    public static function load(array $personnesList): void
    {
        foreach ($personnesList as $personne) {
            $passager = new Personne(
                trajetAller: Trajets::findTrajet(depart: $personne['aller']['depart'], arrivee: $personne['aller']['arrivee']),
                trajetRetour: Trajets::findTrajet(depart: $personne['retour']['depart'], arrivee: $personne['retour']['arrivee']),
                nom: $personne['nom']
            );
            self::$personnes[spl_object_id($passager)] = $passager;
        }
    }

    public static function unregister(Personne $personne): void
    {
        unset(self::$personnes[spl_object_id($personne)]);
    }
}
