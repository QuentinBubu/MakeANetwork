<?php

namespace App\Loaders;

use App\Log\Message;
use App\Entities\Personne;
use App\Entities\PersonneObjectif;

class Personnes
{
    public static array $personnes = [];

    public static function load(array $personnesList): void
    {
        foreach ($personnesList as $personne) {
            Message::log("Chargement de la personne {$personne['nom']}", Message::DEBUG_DETAIL);
            $passager = new Personne(
                aller: new PersonneObjectif(depuis: $personne['aller']['depart'], vers: $personne['aller']['arrivee']),
                retour: new PersonneObjectif(depuis: $personne['retour']['depart'], vers: $personne['retour']['arrivee']),
                nom: $personne['nom']
            );

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
