<?php

namespace App\Loaders;

use App\Entities\Parcours as EntityParcours;

/**
 * @Entity
 *
 * Un parcours est décrit par un ensemble de routes, de manière ordonnée
 * Considérons un parcours A -> B -> C, un bus peut avoir le trajet A -> B -> C, mais aussi A -> C
 * Dans le cas de A -> C, le bus ne s'arrête pas à B, c'est à lui de décider
 */
class Parcours
{
    public static array $parcours = [];

    public static function load(array $parcours): void
    {
        foreach ($parcours as $name => $arrets) {
            echo "Construction du parcours {$name}\n";
            $arretsMap = array_map(fn ($arret) => Arrets::getArret($arret), $arrets);

            echo "Construction des trajets pour le parcours {$name}\n";
            $parc = new EntityParcours(nom: $name, arretsAFaire: $arretsMap);
            for ($i = 0; $i < count($arretsMap) - 1; $i++) {
                $arretA = $arrets[$i];
                $arretB = $arrets[$i + 1];
                $parc->addTrajet(Trajets::findTrajet($arretA, $arretB));
            }
            self::$parcours[$name] = $parc;
        }
    }

    public static function getParcours(string $nom): EntityParcours
    {
        return self::$parcours[$nom];
    }

    public static function export(): array
    {
        $data = [];
        /** @var \App\Entities\Parcours $parcoursElement */
        foreach (self::$parcours as $parcoursElement) {
            $data[spl_object_id($parcoursElement)] = $parcoursElement->export();
        }
        return $data;
    }
}
