<?php

namespace App\Log;

class Message
{
    /**
     * Niveau de log actuel (0: aucune sortie, 1: INFO, 2: DEBUG_DETAIL, 3: DEBUG_ALL).
     * 
     * @var int
     */
    public static int $logLevel = 1;

    /**
     * Fichier de sortie des logs.
     * 
     * @var string
     */
    public static string $output = 'php://stdout';

    /**
     * Liste des messages à ignorer dans les logs.
     * Ces messages ne seront pas affichés même si le niveau de log est suffisant.
     * 
     * @var string[]
     */
    private static array $ignoredMessages = [
        "Tick de l'arrêt",
        "Chargement de la personne",
        "au tick 0",
        "Recherche de la route entre l'arrêt",
    ];

    /**
     * Constantes représentant les différents niveaux de log.
     */
    public const DATA = -1;
    public const NONE = 0;
    public const INFO = 1;
    public const DEBUG_DETAIL = 2;
    public const DEBUG_ALL = 3;

    /**
     * Enregistre un message dans les logs si le niveau de log est suffisant et si le message n'est pas ignoré.
     * 
     * La méthode vérifie si le niveau de log est supérieur ou égal au niveau défini dans self::$logLevel.
     * Si le message commence ou finit par un texte figurant dans self::$ignoredMessages, il est ignoré.
     * 
     * Complexité: O(n)
     * 
     * @param string $message Le message à enregistrer.
     * @param int $logLevel Le niveau de log du message. Par défaut, il est DEBUG_DETAIL.
     */
    public static function log(string $message, int $logLevel = self::DEBUG_DETAIL): void
    {
        // Si le niveau de log du message est inférieur au niveau de log global, on ignore le message
        if ($logLevel > self::$logLevel) {
            return;
        }

        // Vérifie si le message doit être ignoré en fonction des messages ignorés définis
        foreach (self::$ignoredMessages as $iMessage) {
            // Si le message commence ou se termine par un texte de la liste des messages ignorés, on l'ignore
            if (str_starts_with($message, $iMessage) || str_ends_with($message, $iMessage)) {
                return;
            }
        }

        // Affiche le message s'il ne doit pas être ignoré
        file_put_contents(self::$output, $message . PHP_EOL, FILE_APPEND);
    }

    /**
     * Modifie le niveau de log global. Seuls les messages dont le niveau est inférieur ou égal à ce niveau seront affichés.
     * 
     * @param int $level Le niveau de log à définir.
     */
    public static function setLevel(int $level): void
    {
        self::$logLevel = $level;
    }

    /**
     * Modifie le fichier de sortie des logs.
     * 
     * @param string $output Le fichier de sortie des logs.
     */
    public static function setOutput(string $output): void
    {
        self::$output = $output;
    }
}
