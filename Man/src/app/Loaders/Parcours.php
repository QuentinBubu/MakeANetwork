<?php

namespace App\Loaders;

use App\Loaders\Routes;
use App\Exceptions\RoutesException;

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
        self::$parcours = $parcours;
    }
}
