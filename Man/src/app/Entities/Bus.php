<?php

namespace App\Entities;

/**
 * Représente un bus
 */
class Bus
{
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

    /**
     * @var mixed Arret|Route
     */
    protected $position = 0;

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
    }

    /**
     * Décharge les personnes du bus
     *
     * @return void
     */
    public function dechargerPersonnes(): void
    {
        if ($this->position instanceof Arret) {
            foreach ($this->personnes as $personne) {
                // $personne->setArret($this->position);
            }
        }
    }

    /**
     * Charge les personnes dans le bus
     *
     * @param Personne[] $personnes
     * @return void
     */
    public function chargerPersonnes(array $personnes): void
    {
        foreach ($personnes as $personne) {
            if (count($this->personnes) < $this->capacite) {
                $this->personnes[] = $personne;
                echo "Chargement de la personne {$personne->nom} dans le véhicule\n";
            } else {
                echo "Le véhicule est plein\n";
            }
        }
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

    public function getPosition(): int
    {
        return $this->position;
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
            . ' | Position : ' . $this->position
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
}
