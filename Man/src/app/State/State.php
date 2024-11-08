<?php

namespace App\State;

class State
{
    /**
     * Liste des fonctions à exécuter pour exporter les données.
     * Chaque entrée est un tableau contenant :
     * - Le nom de la classe (string).
     * - Le nom de la méthode (string).
     * - La clé pour associer la donnée exportée (string).
     * 
     * @var array
     */
    public static array $states = [];

    /**
     * Enregistre une fonction à exécuter pour l'exportation des données.
     * 
     * Cette méthode ajoute une entrée dans la liste $states, qui inclut la classe, la méthode à appeler, et une clé optionnelle pour nommer la donnée exportée.
     * 
     * @param string $class Le nom de la classe contenant la méthode.
     * @param string $method Le nom de la méthode à appeler.
     * @param string $key La clé sous laquelle la donnée sera exportée. Si vide, aucune clé n'est utilisée.
     */
    public static function registerFunction(string $class, string $method, string $key = ""): void
    {
        self::$states[] = [$class, $method, $key];
    }

    /**
     * Exporte toutes les données enregistrées au format JSON.
     * 
     * Cette méthode appelle chaque fonction enregistrée, récupère les données et les assemble dans un tableau.
     * Elle associe chaque résultat à une clé si une clé a été fournie lors de l'enregistrement de la fonction, puis retourne le tout sous forme de JSON.
     * 
     * Complexité: O(n), où n est le nombre de fonctions enregistrées.
     * 
     * @return string Le résultat sous forme de JSON.
     */
    public static function exportData(): string
    {
        $data = [];
        foreach (self::$states as $state) {
            // Appelle la méthode de la classe et associe le résultat à la clé (si présente)
            $data[] = [
                $state[2] => call_user_func([$state[0], $state[1]])
            ];
        }

        // Retourne les données sous forme de JSON
        return json_encode($data);
    }
}
