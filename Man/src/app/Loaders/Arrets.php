<?php

namespace App\Loaders;

use App\Log\Message;
use App\Entities\Arret;
use App\Exceptions\ArretsException;

class Arrets
{
    public static array $arrets = [];

    public static function load(array $arrets): void
    {
        foreach ($arrets as $name => $data) {
            Message::log("Construction de l'arrêt {$name}", Message::DEBUG_DETAIL);
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
            $data[$arret->nom] = $arret->export();
        }
        return $data;
    }
}
