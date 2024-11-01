<?php

namespace App\Loaders;

use App\Entities\Personne;

class Personnes
{
    public static array $personnes = [];

    public static function load(array $personnesList): void
    {
        foreach ($personnesList as $personne) {
            echo "Chargement de la personne {$personne['nom']}" . PHP_EOL;
            $passager = new Personne(
                trajetAller: Trajets::findTrajet(depart: $personne['aller']['depart'], arrivee: $personne['aller']['arrivee']),
                trajetRetour: Trajets::findTrajet(depart: $personne['retour']['depart'], arrivee: $personne['retour']['arrivee']),
                nom: $personne['nom']
            );
            if (str_starts_with($personne['nom'], 'Edouard')) {
                echo "Edouard est enregistré" . PHP_EOL;
            }
            self::$personnes[spl_object_id($passager)] = $passager;
        }
    }

    public static function unregister(Personne $personne): void
    {
        unset(self::$personnes[spl_object_id($personne)]);
    }

    public static function export(): array
    {
        $data = [];
        /** @var Personne $personne */
        foreach (self::$personnes as $personne) {
            $data[spl_object_id($personne)] = $personne->export();
        }
        return $data;
    }
}
