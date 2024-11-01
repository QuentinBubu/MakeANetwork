<?php

namespace App\Loaders;

use App\Entities\Arret;
use App\Exceptions\ArretsException;
use App\Interfaces\StateInterface;

class Arrets
{
    public static array $arrets = [];

    public static function load(array $arrets): void
    {
        foreach ($arrets as $name => $data) {
            echo "Construction de l'arrêt {$name}\n";
            $arret = new Arret(nom: $name, genericRoutes: $data['routes']);
            self::$arrets[$name] = $arret;
        }
    }

    public static function map(): void
    {
        /** @var Arret $arret */
        foreach (self::$arrets as $arret) {
            $arret->mapRoutes();
        }
    }

    public static function getArret(string $name): Arret
    {
        if (!isset(self::$arrets[$name])) {
            throw new ArretsException("Arrêt {$name} inconnu");
        }
        return self::$arrets[$name];
    }

    public static function export(): array
    {
        $data = [];
        /** @var Arret $arret */
        foreach (self::$arrets as $arret) {
            $data[spl_object_id($arret)] = $arret->export();
        }
        return $data;
    }
}
