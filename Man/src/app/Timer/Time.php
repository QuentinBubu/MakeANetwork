<?php

namespace App\Timer;

use App\Interfaces\TimeInterface;

class Time
{
    private static int $tick = 0;

    /**
     * Liste des fonctions à exécuter à chaque tick
     * @var TimeInterface[]
     */
    private static array $registredClass = [];

    public static function incrementTick(): void
    {
        self::$tick++;
    }

    public static function getTick(): int
    {
        return self::$tick;
    }

    public static function resetTick(): void
    {
        self::$tick = 0;
    }

    public static function registerClass(TimeInterface $class): void
    {
        self::$registredClass[] = $class;
    }

    public static function run(): void
    {
        foreach (self::$registredClass as $class) {
            $class->incrementTick();
        }
    }

    public static function export(): array
    {
        return [
            'tick' => self::$tick
        ];
    }
}
