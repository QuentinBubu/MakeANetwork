<?php

namespace App\Entities;

use App\Timer\Time;
use App\Log\Message;
use App\Interfaces\StateInterface;

/**
 * @Entity
 * 
 * Représente un parcours d'un bus, composé d'un ensemble de trajets et d'arrêts.
 * Le parcours gère l'ordre des arrêts et l'évolution de la position du bus au fil du temps.
 */
class Parcours implements StateInterface
{
    /**
     * Nom du parcours.
     *
     * @var string
     */
    public string $nom;

    /**
     * Liste des trajets associés au parcours.
     *
     * @var Trajet[]
     */
    public array $trajets = [];

    /**
     * Liste des arrêts à faire durant le parcours.
     *
     * @var Arret[]
     */
    public array $arretsAFaire = [];

    /**
     * Index de l'arrêt actuel du bus dans la liste des arrêts à faire.
     *
     * @var int|null
     */
    public ?int $currentArret = null;

    /**
     * Index de l'arrêt suivant après l'arrêt actuel.
     *
     * @var int|null
     */
    public ?int $nextArret = null;

    /**
     * Index de l'arrêt précédent du bus dans la liste des arrêts.
     *
     * @var int|null
     */
    public ?int $previousArret = null;

    /**
     * Constructeur de la classe Parcours.
     *
     * @param string $nom Nom du parcours.
     * @param Arret[] $arretsAFaire Liste des arrêts à effectuer durant le parcours.
     * @param Trajet ...$trajets Liste des trajets associés au parcours.
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
     * Ajoute un trajet au parcours.
     *
     * @param Trajet $trajet Le trajet à ajouter.
     * @return self
     */
    public function addTrajet(Trajet $trajet): self
    {
        $this->trajets[] = $trajet;
        return $this;
    }

    /**
     * Trouve le prochain arrêt après l'arrêt donné.
     * Si l'arrêt donné est nul, le premier arrêt sera retourné.
     *
     * @param Arret|null $arret L'arrêt actuel pour calculer le prochain arrêt.
     * @return Arret|null Le prochain arrêt.
     */
    public function findNextArretObj(?Arret $arret): ?Arret
    {
        if (is_null($arret)) {
            return $this->arretsAFaire[0]; // Si l'arrêt est nul, retour du premier arrêt.
        }

        $index = array_search($arret, $this->arretsAFaire);
        // Si c'est le dernier arrêt, retourne le premier arrêt (comportement cyclique).
        return $index === array_key_last($this->arretsAFaire) ? $this->arretsAFaire[0] : $this->arretsAFaire[$index + 1];
    }

    /**
     * Trouve l'index du prochain arrêt après celui donné.
     * Si l'arrêt est nul, retourne l'index 0.
     *
     * @param int|null $arret L'index de l'arrêt actuel pour calculer le prochain.
     * @return int L'index du prochain arrêt.
     */
    public function findNextArret(?int $arret): int
    {
        if (is_null($arret)) {
            return 0; // Si l'arrêt est nul, retourne le premier arrêt.
        }

        // Si c'est le dernier arrêt, retourne 0 (comportement cyclique).
        return $arret === array_key_last($this->arretsAFaire) ? 0 : $arret + 1;
    }

    /**
     * Retourne tous les arrêts suivants à partir de l'arrêt donné,
     * en boucle autour de la liste des arrêts.
     *
     * @param int $arret L'index de l'arrêt de départ.
     * @return Arret[] Liste des arrêts suivants, de manière cyclique.
     */
    public function findAllNextArretsObj(int $arret): array
    {
        return array_merge(
            array_slice($this->arretsAFaire, $arret + 1),  // Arrêts après l'arrêt donné
            array_slice($this->arretsAFaire, 0, $arret + 1) // Arrêts avant l'arrêt donné
        );
    }

    /**
     * Appelé lorsqu'un bus arrive à un arrêt du parcours.
     * Le parcours met à jour les arrêts courant, suivant et précédent.
     *
     * @param Bus $bus Le bus qui arrive à cet arrêt.
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
        Message::log("Le bus " . spl_object_id($bus) . " faisant le parcours {$bus->getParcours()->nom} est arrivé à l'arrêt {$this->getCurrentArretObj()->nom}", Message::INFO);
    }

    /**
     * Retourne l'objet de l'arrêt actuel du parcours.
     *
     * @return Arret L'objet de l'arrêt actuel.
     */
    public function getCurrentArretObj(): Arret
    {
        return $this->arretsAFaire[$this->currentArret];
    }

    /**
     * Retourne l'objet de l'arrêt précédent du parcours.
     *
     * @return Arret L'objet de l'arrêt précédent.
     */
    public function getPreviousArretObj(): Arret
    {
        return $this->arretsAFaire[$this->previousArret];
    }

    /**
     * Retourne l'objet de l'arrêt suivant du parcours.
     *
     * @return Arret L'objet de l'arrêt suivant.
     */
    public function getNextArretObj(): Arret
    {
        return $this->arretsAFaire[$this->nextArret];
    }

    /**
     * Retourne l'objet d'un arrêt spécifique basé sur son index.
     *
     * @param int $index L'index de l'arrêt.
     * @return Arret L'objet de l'arrêt spécifié.
     */
    public function getArretWithIndex(int $index): Arret
    {
        return $this->arretsAFaire[$index];
    }

    /**
     * Retourne une représentation textuelle du parcours, sous la forme d'une chaîne de trajets.
     *
     * @return string La représentation textuelle du parcours.
     */
    public function __toString(): string
    {
        return implode(
            separator: ' -> ',
            array: array_map(
                callback: function ($trajet) {
                    return $trajet->nom;
                },
                array: $this->trajets
            )
        );
    }

    /**
     * Exporte l'état du parcours sous forme de tableau.
     *
     * @return array L'état actuel du parcours.
     */
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
}
