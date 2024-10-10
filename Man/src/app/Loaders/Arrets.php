<?php

namespace Man\App\Loaders;

use Man\App\Entities\Arret;

class Arrets
{
    public static array $arrets = [];

    public static function load(array $arrets): void
    {
        foreach ($arrets as $name => $data) {
            $arret = new Arret($name, $data['routes'], $data['file']);
            self::$arrets[$name] = $arret;
        }
    }

    public static function getArret(string $name): Arret
    {
        return self::$arrets[$name];
    }
}
