<?php

namespace App\Entities;

use App\Actions\BusActions;
use App\Enums\BusStateEnum;
use App\Interfaces\TimeInterface;

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
    protected float $vitesseChargement;

    /**
     * Vitesse de déplacement
     *
     * @var float
     */
    protected float $vitesseDeplacement;

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
        $this->state = BusStateEnum::ARRET;
        $this->arret = $parcours->arretsAFaire[0];
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
        throw new \Exception("Not implemented", 1);
        
    }
}
