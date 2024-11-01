<?php

namespace App\Entities;

use App\Timer\Timer;
use App\Enums\BusStateEnum;
use App\Interfaces\StateInterface;
use App\Interfaces\TimeInterface;
use App\State\State;
use App\Timer\Time;

/**
 * Représente un bus
 */
class Bus extends Position implements TimeInterface, StateInterface
{
    /**
     * Capacité max du bus
     *
     * @var integer
     */
    public int $capacite;

    /**
     * Vitesse de chargement
     *
     * @var float
     */
    public int $vitesseChargement;

    /**
     * Vitesse de déplacement
     *
     * @var int
     */
    public int $vitesseDeplacement;

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

    protected BusStateEnum $state = BusStateEnum::FLUX_VOYAGEURS;

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
        echo "Enregistrement du bus " . spl_object_id($this) . " sur le parcours " . $parcours->nom . PHP_EOL;
        Time::registerClass($this);
    }

    public function getState(): BusStateEnum
    {
        return $this->state;
    }

    public function setState(BusStateEnum $state): void
    {
        $this->state = $state;
        if (count($this->personnes) > 0) {
            echo microtime(true) . " & Bus " . spl_object_id($this) . " & à l'arrêt " . $this->parcours->currentArret . " ( " . $this->parcours->getCurrentArretObj()->nom . " )" . PHP_EOL;
        }
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

    /**
     * Retourne le parcours du bus
     *
     * @return Parcours
     */
    public function getParcours(): Parcours
    {
        return $this->parcours;
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
            . ' | Position : ' . $this->parcours->currentArret . ' tick : ' . $this->tick
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

    public function isFull(): bool
    {
        return count($this->personnes) >= $this->capacite;
    }

    public function incrementTick(): void
    {
        $this->tick += 1;
        if (
            $this->state === BusStateEnum::DEPLACEMENT
            && $this->tick % $this->vitesseDeplacement === 0
            && $this->tickTo($this->parcours, $this->parcours->getNextArretObj(), $this->vitesseDeplacement) <= 0
        ) {
            $this->parcours->arriveArret($this);
            $this->setState(BusStateEnum::FLUX_VOYAGEURS);
        }
    }

    public function demarrerParcours(): void
    {
        $this->parcours->arriveArret($this);
        // Enregistrement des ticks sur les arrêts
        // array_slice offset 1 pour ne pas enregistrer le premier arrêt ???
        foreach (array_slice($this->parcours->arretsAFaire, 1) as $arret) {
            $this->calculEtEnregistrementProchainPassage($arret);
            // Attention à calculer tout les n+1 parcours
            /*
                Considérons les parcours BED
                Bus en E
                Personne en D veut aller en B ou E
                Il faut que le bus se soit enregistré dans X temps de nouveau à B et E
                Il doit donc déposer son prochain passage
            */
        }
        echo "Démarrage du bus\n";
    }

    public function calculEtEnregistrementProchainPassage(Arret $arret): void
    {
        echo "Calcul du prochain passage du bus " . spl_object_id($this) . " à l'arrêt " . $arret->nom . PHP_EOL;
        $timer = new Timer($this->tickTo($this->parcours, $arret, $this->vitesseDeplacement));
        $arret->addBusEnApproche($this, $timer);
        $this->addTimer($arret, $timer);
    }

    public function export(): array
    {
        return [
            'capacite' => $this->capacite,
            'vitesseChargement' => $this->vitesseChargement,
            'vitesseDeplacement' => $this->vitesseDeplacement,
            'parcours' => $this->parcours->nom,
            'personnes' => array_map(
                function ($personne) {
                    return $personne->nom;
                },
                $this->personnes
            ),
            'state' => $this->state->name,
            'timers' => array_map(
                function ($timer) {
                    return $timer->getRemainingTicks();
                },
                $this->timers
            ),
            'tick' => $this->tick,
        ];
    }

    public function restore(array $state): void
    {
        throw new \Exception('Not implemented');
    }
}
