<?php

namespace App\Loaders;

use App\Entities\Arret;
use App\Exceptions\ArretsException;

class Arrets
{
    public static array $arrets = [];

    public static function load(array $arrets): void
    {
        foreach ($arrets as $name => $data) {
            $arret = new Arret(nom: $name, genericRoutes: $data['routes'], genericFile: $data['file']);
            self::$arrets[$name] = $arret;
        }
    }

    public static function map(): void
    {
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
}
