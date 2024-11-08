<?php

namespace App\Timer;

use App\Interfaces\TimeInterface;

class Time
{
    /**
     * Le compteur de "ticks" global du système.
     * Ce compteur s'incrémente à chaque appel de incrementTick().
     * 
     * @var int
     */
    private static int $tick = 0;

    /**
     * Liste des classes enregistrées pour être exécutées à chaque tick.
     * Chaque classe doit implémenter l'interface TimeInterface et
     * avoir une méthode incrementTick().
     * 
     * @var TimeInterface[]
     */
    private static array $registredClass = [];

    /**
     * Incrémente le compteur de tick de 1.
     * Cette méthode doit être appelée à chaque unité de temps pour
     * faire avancer le temps global.
     */
    public static function incrementTick(): void
    {
        self::$tick++;
    }

    /**
     * Retourne la valeur actuelle du tick.
     * 
     * @return int La valeur actuelle du tick.
     */
    public static function getTick(): int
    {
        return self::$tick;
    }

    /**
     * Réinitialise le compteur de tick à zéro.
     * Cette méthode est utilisée pour remettre à zéro l'état de l'application.
     */
    public static function resetTick(): void
    {
        self::$tick = 0;
    }

    /**
     * Enregistre une classe qui implémente l'interface TimeInterface.
     * La classe enregistrée devra avoir une méthode incrementTick()
     * qui sera appelée à chaque tick.
     * 
     * @param TimeInterface $class L'instance d'une classe implémentant l'interface TimeInterface.
     */
    public static function registerClass(TimeInterface $class): void
    {
        self::$registredClass[] = $class;
    }

    /**
     * Exécute la méthode incrementTick() de toutes les classes enregistrées.
     * Chaque classe exécutera des actions propres à chaque tick de temps.
     * 
     * Complexité: O(n), chaque classe est appelée une fois pour son incrementTick().
     */
    public static function run(): void
    {
        foreach (self::$registredClass as $class) {
            $class->incrementTick();
        }
    }

    /**
     * Exporte l'état actuel du compteur de tick sous forme de tableau.
     * Cela peut être utilisé pour sauvegarder ou loguer l'état actuel.
     * 
     * @return array Un tableau contenant la valeur actuelle du tick.
     */
    public static function export(): array
    {
        return [
            'tick' => self::$tick
        ];
    }
}
