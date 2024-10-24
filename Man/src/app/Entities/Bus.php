<?php

namespace App\Entities;

use App\Timer\Timer;
use App\Actions\BusActions;
use App\Enums\BusStateEnum;
use App\Interfaces\TimeInterface;
use App\Timer\Time;

/**
 * Représente un bus
 */
class Bus extends Position implements TimeInterface
{
    use BusActions;
    /**
     * Capacité max du bus
     *
     * @var integer
     */
    protected int $capacite;

    /**
     * Vitesse de chargement
     *
     * @var float
     */
    protected int $vitesseChargement;

    /**
     * Vitesse de déplacement
     *
     * @var int
     */
    protected int $vitesseDeplacement;

    /**
     * Parcours du bus
     *
     * @var Parcours
     */
    protected Parcours $parcours;

    /**
     * Personnes dans le bus
     *
     * @var Personne[]
     */
    protected array $personnes = [];

    protected BusStateEnum $state;

    /**
     * Liste de ses timers
     * [spl_object_id(Arret) => Timer]
     * @var array
     */
    protected array $timers = [];

    /**
     * Constructeur
     *
     * @param integer $capacite
     * @param float $vitesseChargement
     * @param float $vitesseDeplacement
     * @param Parcours $parcours
     */
    public function __construct(int $capacite, float $vitesseChargement, float $vitesseDeplacement, Parcours $parcours)
    {
        $this->capacite = $capacite;
        $this->vitesseChargement = $vitesseChargement;
        $this->vitesseDeplacement = $vitesseDeplacement;
        $this->parcours = $parcours;
        $this->arret = $parcours->arretsAFaire[0];
        $this->state = BusStateEnum::FLUX_VOYAGEURS;
        Time::registerClass($this);
    }

    public function addTimer(Arret $arret, Timer $timer): void
    {
        $this->timers[spl_object_id($arret)] = $timer;
    }

    /**
     * Retourne la place disponible dans le bus
     *
     * @return void
     */
    public function getPlaceDisponible(): int
    {
        return $this->capacite - count($this->personnes);
    }

    public function getCapacite(): int
    {
        return $this->capacite;
    }

    public function getVitesseChargement(): float
    {
        return $this->vitesseChargement;
    }

    public function getVitesseDeplacement(): float
    {
        return $this->vitesseDeplacement;
    }

    public function getParcours(): Parcours
    {
        return $this->parcours;
    }

    public function getPersonnes(): array
    {
        return $this->personnes;
    }

    public function setState(BusStateEnum $state): void
    {
        $this->state = $state;
    }

    public function getState(): BusStateEnum
    {
        return $this->state;
    }

    /**
     * Retourne le bus sous forme de string
     *
     * @return string
     */
    public function __tostring(): string
    {
        return 'Bus @' . spl_object_id($this)
            . ' (Capacité : ' . $this->capacite
            . ' | Vitesse de chargement : ' . $this->vitesseChargement
            . ' | Vitesse de déplacement : ' . $this->vitesseDeplacement
            . ' | Parcours : ' . $this->parcours->nom
            . ' | Position : ' . $this->arret . ' tick : ' . $this->tick
            . ' | Personnes : '
            . implode(
                separator: ', ',
                array: array_map(
                    callback: function ($personne) {
                        return 'Personne ' . $personne->nom . ' @' . spl_object_id($personne);
                    },
                    array: $this->personnes
                )
            )
            . ')';
    }

    public function incrementTick(): void
    {
        switch ($this->state) {
            case BusStateEnum::DEPLACEMENT:
                // Se déplacer
                break;
            case BusStateEnum::FLUX_VOYAGEURS:
                // Charger et décharger les personnes
                break;
            default:
                break;
        }
    }
}
