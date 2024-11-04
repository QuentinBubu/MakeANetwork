<?php

namespace App\Log;

class Message
{
    public static int $logLevel = 1;
    private static array $ignoredMessages = [
        "Tick de l'arrêt",
        "Chargement de la personne",
        "au tick 0",
        "Recherche de la route entre l'arrêt",
    ];

    public const DATA = -1;
    public const NONE = 0;
    public const INFO = 1;
    public const DEBUG_DETAIL = 2;
    public const DEBUG_ALL = 3;
    
    public static function log(string $message, int $logLevel = self::DEBUG_DETAIL): void
    {
        if ($logLevel > self::$logLevel) {
            return;
        }

        foreach (self::$ignoredMessages as $iMessage) {
            if (str_starts_with($message, $iMessage) || str_ends_with($message, $iMessage)) {
                return;
            }
        }

        echo $message . PHP_EOL;
    }

    public static function setLevel(int $level): void
    {
        self::$logLevel = $level;
    }
}
