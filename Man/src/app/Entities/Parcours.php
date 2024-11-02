<?php

namespace App\Entities;

use App\Timer\Time;
use App\Log\Message;
use App\Loaders\Routes;
use App\Interfaces\StateInterface;

/**
 * @Entity
 *
 * Un parcours est décrit par un ensemble de trajets
 */
class Parcours implements StateInterface
{
    /**
     * Nom du parcours
     *
     * @var string
     */
    public string $nom;

    /**
     * Liste des trajets
     *
     * @var Trajet[]
     */
    public array $trajets = [];

    /**
     * Liste des arrêts à faire
     *
     * @var Arret[]
     */
    public array $arretsAFaire = [];

    /**
     * Index arrêt de présence
     *
     * @var int
     */
    public ?int $currentArret = null;

    /**
     * Index arrêt suivant
     *
     * @var int
     */
    public ?int $nextArret = null;

    /**
     * Index arrêt précédent
     *
     * @var int
     */
    public ?int $previousArret = null;


    /**
     * Constructeur
     *
     * @param string $nom
     * @param Arret[] $arretsAFaire
     * @param Trajet ...$trajets
     */
    public function __construct(string $nom, array $arretsAFaire, Trajet ...$trajets)
    {
        $this->nom = $nom;
        $this->arretsAFaire = $arretsAFaire;
        $this->trajets = $trajets;
        $this->currentArret = 0;
        $this->nextArret = 0;
    }

    /**
     * Ajoute un trajet au parcours
     *
     * @param Trajet $trajet
     * @return self
     */
    public function addTrajet(Trajet $trajet): self
    {
        $this->trajets[] = $trajet;
        return $this;
    }

    public function findNextArretObj(?Arret $arret): ?Arret
    {
        if (is_null($arret)) {
            return $this->arretsAFaire[0];
        }

        $index = array_search($arret, $this->arretsAFaire);

        return $index == array_key_last($this->arretsAFaire) ? $this->arretsAFaire[0] : $this->arretsAFaire[$index + 1];
    }

    public function findNextArret(?int $arret): int
    {
        if (is_null($arret)) {
            return 0;
        }

        return $arret == array_key_last($this->arretsAFaire) ? 0 : $arret + 1;
    }

    /**
     * Retourne tous les arrêts suivants en bouclant jusqu'à l'arrêt donné
     * @param int $arret
     * @return Arret[]
     */
    public function findAllNextArretsObj(int $arret): array
    {
        return array_merge(
            array_slice($this->arretsAFaire, $arret + 1),
            array_slice($this->arretsAFaire, 0, $arret + 1)
        );
    }

    /**
     * Changement d'arrêt :
     *   - L'arrêt précédent devient celui où il est
     *   - L'arrêt courant devient le prochain
     *
     * Le bus fait A B C D, il démarre de A, previous = null, arret = A, next = B, tick = 0
     * Il arrive à B, previous = A, arret = B, next = C, tick = 0
     * @param Position $position
     * @return void
     */
    public function arriveArret(Bus $bus): void
    {
        Message::log("TIME : {$bus->tick} TIME GLOBAL : " . Time::getTick());
        $this->previousArret = $this->currentArret;
        if ($this->currentArret !== $this->nextArret) {
            $this->currentArret = $this->findNextArret($this->currentArret);
        }
        $this->nextArret = $this->findNextArret($this->currentArret);
        $this->getCurrentArretObj()->arriveeBus($bus);
        $bus->tick = 0;
        Message::log("Le bus " . spl_object_id($bus) . " faisaint le parcours {$bus->getParcours()->nom} est arrivé à l'arrêt {$this->getCurrentArretObj()->nom}", Message::INFO);
    }

    public function getCurrentArretObj(): Arret
    {
        return $this->arretsAFaire[$this->currentArret];
    }

    public function getPreviousArretObj(): Arret
    {
        return $this->arretsAFaire[$this->previousArret];
    }

    public function getNextArretObj(): Arret
    {
        return $this->arretsAFaire[$this->nextArret];
    }

    public function getArretWithIndex(int $index): Arret
    {
        return $this->arretsAFaire[$index];
    }

    /**
     * Retourne une représentation textuelle du parcours
     *
     * @return string
     */
    public function __tostring(): string
    {
        return implode(
            separator: ' -> ',
            array: array_map(
                callback: function ($route) {
                    return $route->nom;
                },
                array: $this->trajets
            )
        );
    }

    public function export(): array
    {
        return [
            'nom' => $this->nom,
            'trajets' => array_map(
                function ($trajet) {
                    return $trajet->nom;
                },
                $this->trajets
            ),
            'arretsAFaire' => array_map(
                function ($arret) {
                    return $arret->nom;
                },
                $this->arretsAFaire
            ),
            'currentArret' => $this->currentArret,
            'nextArret' => $this->nextArret,
            'previousArret' => $this->previousArret
        ];
    }

    public function restore(array $state): void
    {
        throw new \Exception("Not implemented");
    }
}
