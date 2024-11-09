<?php

namespace App\Entities;

use App\Log\Message;
use App\Loaders\Trajets;
use App\Exceptions\RouteException;
use App\Interfaces\StateInterface;

/**
 * @Entity
 *
 * Représente une route qui relie deux arrêts avec une distance spécifique.
 * Cette classe permet d'enregistrer des arrêts, récupérer l'arrêt suivant,
 * et gérer les informations liées aux bus qui passent par cette route.
 */
class Route implements StateInterface
{
    public string $nom;

    /**
     * Liste des arrêts de la route (exactement 2 arrêts).
     * 
     * @var Arret[]
     */
    public array $arrets = [];

    /**
     * Distance totale de la route entre les deux arrêts.
     * 
     * @var int
     */
    public int $distance;

    /**
     * Constructeur de la route.
     * 
     * @param string $nom Nom de la route.
     * @param int $distance Distance de la route entre les deux arrêts.
     */
    public function __construct(string $nom, int $distance)
    {
        $this->nom = $nom;
        $this->distance = $distance;
    }

    /**
     * Enregistre un arrêt à la route. Un arrêt ne peut être ajouté qu'à deux routes maximum.
     * 
     * @param Arret $arret L'arrêt à ajouter à la route.
     * @return self
     * @throws RouteException Si plus de deux arrêts sont ajoutés à la route.
     */
    public function registerArret(Arret $arret): self
    {
        Message::log("Ajout de l'arrêt {$arret->nom} à la route {$this->nom}", Message::DEBUG_ALL);
        
        // Vérifie si la route a déjà deux arrêts
        if (count($this->arrets) == 2) {
            throw new RouteException('Un arrêt ne peut pas être ajouté à plus de deux routes');
        }

        $this->arrets[] = $arret;
        
        // Si deux arrêts sont ajoutés, on enregistre la route dans le système
        if (count($this->arrets) == 2) {
            Message::log("Ajout de la route {$this->nom} à la liste des trajets", Message::DEBUG_ALL);
            Trajets::addTrajet($this);
        }

        return $this;
    }

    /**
     * Retourne l'arrêt suivant par rapport à un arrêt donné.
     *
     * @param Arret $arret L'arrêt dont on veut connaître l'arrêt suivant.
     * @return Arret L'arrêt suivant.
     * @throws RouteException Si l'arrêt passé en paramètre ne fait pas partie de la route.
     */
    public function getNextArret(Arret $arret): Arret
    {
        // Filtre les arrêts pour obtenir l'arrêt suivant
        $nextArret = array_values(
            array_filter(
                $this->arrets,
                function ($a) use ($arret) {
                    return $a !== $arret;
                }
            )
        );

        // Si l'arrêt donné n'est pas dans la liste des arrêts
        if (empty($nextArret)) {
            throw new RouteException("L'arrêt spécifié n'existe pas dans la route {$this->nom}");
        }

        return $nextArret[0];
    }

    /**
     * Retourne une représentation sous forme de chaîne de caractères de la route.
     * 
     * @return string La représentation de la route sous forme de chaîne de caractères.
     */
    public function __tostring(): string
    {
        return $this->nom . ' @' . spl_object_id($this)
            . ' (Distance : ' . $this->distance
            . ' | Arrets : '
            . implode(
                separator: ', ',
                array: array_map(
                    callback: function ($arret) {
                        return 'Arret ' . $arret->nom . ' @' . spl_object_id($arret);
                    },
                    array: $this->arrets
                )
            )
            . ')';
    }

    /**
     * Récupère les arrêts associés à cette route.
     * 
     * @return Arret[] Liste des arrêts associés à cette route.
     */
    public function getArrets(): array
    {
        return $this->arrets;
    }

    /**
     * Exporte les données de la route sous forme d'un tableau.
     *
     * @return array Données exportées sous forme de tableau associatif.
     */
    public function export(): array
    {
        return [
            'nom' => $this->nom,
            'distance' => $this->distance,
            'arrets' => array_map(
                function ($arret) {
                    return $arret->nom;
                },
                $this->arrets
            ),
        ];
    }

    /**
     * Vérifie si un arrêt fait partie de cette route.
     *
     * @param Arret $arret L'arrêt à vérifier.
     * @return bool Retourne true si l'arrêt est présent, sinon false.
     */
    public function hasArret(Arret $arret): bool
    {
        return in_array($arret, $this->arrets);
    }
}
