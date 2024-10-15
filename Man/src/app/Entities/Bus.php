<?php

namespace App\Entities;

class Bus
{
    protected int $capacite;
    protected float $vitesseChargement;
    protected float $vitesseDeplacement;
    protected Parcours $parcours;
    protected array $personnes = [];

    /**
     * @var mixed Arret|Route
     */
    protected $position = 0;

    public function __construct(int $capacite, float $vitesseChargement, float $vitesseDeplacement, Parcours $parcours)
    {
        $this->capacite = $capacite;
        $this->vitesseChargement = $vitesseChargement;
        $this->vitesseDeplacement = $vitesseDeplacement;
        $this->parcours = $parcours;
    }

    public function dechargerPersonnes(): void
    {
        if ($this->position instanceof Arret) {
            foreach ($this->personnes as $personne) {
                $personne->setArret($this->position);
            }
        }
    }

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
