<?php

namespace App\State;

class State
{
    /**
     * Liste des fonctions à exécuter pour exporter les données
     */
    public static array $states = [];

    public static function registerFunction(string $class, string $method, string $key = ""): void
    {
        self::$states[] = [$class, $method, $key];
    }

    public static function exportData(): string
    {
        $data = [];
        foreach (self::$states as $state) {
            $data[] = [
                $state[2] => call_user_func([$state[0], $state[1]])
            ];
        }
        return json_encode($data);
    }
        
}
