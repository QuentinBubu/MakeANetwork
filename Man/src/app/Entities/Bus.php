<?php

namespace App\Entities;

use App\Timer\Time;
use App\Log\Message;
use App\Timer\Timer;
use App\Enums\BusStateEnum;
use App\Exceptions\BusException;
use App\Interfaces\TimeInterface;
use App\Interfaces\StateInterface;

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
    public readonly int $capacite;

    /**
     * Vitesse de chargement
     *
     * @var float
     */
    public readonly int $vitesseChargement;

    /**
     * Vitesse de déplacement
     *
     * @var int
     */
    public readonly int $vitesseDeplacement;

    public readonly string $type;

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
     * Personnes vennant de descendre du bus
     *
     * @var Personne[]
     */
    protected array $personnesDescendu = [];

    protected BusStateEnum $state = BusStateEnum::DEPLACEMENT;

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
    public function __construct(int $capacite, float $vitesseChargement, float $vitesseDeplacement, string $type, Parcours $parcours)
    {
        $this->capacite = $capacite;
        $this->vitesseChargement = $vitesseChargement;
        $this->vitesseDeplacement = $vitesseDeplacement;
        $this->type = $type;
        $this->parcours = $parcours;
        Message::log("Enregistrement du bus " . spl_object_id($this) . " sur le parcours " . $parcours->nom);
        Time::registerClass($this);
    }

    public function getPersonnes(): array
    {
        return $this->personnes;
    }

    public function setState(BusStateEnum $state): void
    {
        $this->tick = 0;
        $this->personnesDescendu = [];
        $this->state = $state;
        if (count($this->personnes) > 0) {
            Message::log(microtime(true) . " & Bus " . spl_object_id($this) . " & à l'arrêt " . $this->parcours->currentArret . " ( " . $this->parcours->getCurrentArretObj()->nom . " )");
        }
    }

    public function addTimer(Arret $arret, Timer $timer): void
    {
        Message::log("Enregistrement du timer pour le bus " . spl_object_id($this) . " à l'arrêt " . $arret->nom);
        $this->timers[spl_object_id($arret)] = $timer;
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

    public function canTake(Personne $personne): bool
    {
        if (in_array($personne, $this->personnesDescendu)) {
            Message::log("Personne {$personne->nom} est déjà descendue du bus " . spl_object_id($this), Message::DEBUG_DETAIL);
            return false;
        }

        if ($this->isFull()) {
            Message::log("Bus " . spl_object_id($this) . " plein", Message::DEBUG_DETAIL);
            return false;
        }

        return true;
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

    public function addPersonne(Personne $personne): void
    {
        Message::log("Ajout de la personne {$personne->nom} dans le bus " . spl_object_id($this));
        $this->personnes[] = $personne;
    }

    public function descentePassager(Personne $personne): void
    {
        Message::log("Suppression de la personne {$personne->nom} du bus " . spl_object_id($this), Message::DEBUG_DETAIL);
        $index = array_search($personne, $this->personnes);
        if ($index === false) {
            throw new BusException("La personne {$personne->nom} n'est pas dans le bus");
        }
        unset($this->personnes[$index]);
        $this->personnes = array_values($this->personnes);
        $this->personnesDescendu[] = $personne;
    }

    public function isFull(): bool
    {
        return count($this->personnes) >= $this->capacite;
    }

    public function peutDesservir(Arret $depart, Arret $destination): bool
    {
        $parcours = $this->getParcours()->arretsAFaire;
        $departIndex = array_search($depart, $parcours);
        $destinationIndex = array_search($destination, $parcours);

        return $departIndex !== false && $destinationIndex !== false;
    }


    public function incrementTick(): void
    {
        if (
            $this->state === BusStateEnum::DEPLACEMENT
            && $this->tick % $this->vitesseDeplacement === 0
            && ($this->tickTo($this->parcours, $this->parcours->getNextArretObj(), $this->vitesseDeplacement) <= 0
                || Time::getTick() === 0
            )
        ) {
            Message::log("GT : " . Time::getTick());
            $this->parcours->arriveArret($this);
        }

        $this->tick += 1;
    }

    public function calculEtEnregistrementProchainPassage(Arret $arret): void
    {
        // Pb (cf debug timer 2 arrets A) ?
        Message::log("Calcul du prochain passage du bus " . spl_object_id($this) . " à l'arrêt " . $arret->nom, Message::DEBUG_DETAIL);
        $timer = new Timer($this->tickTo($this->parcours, $arret, $this->vitesseDeplacement));
        Message::log("Enregistrement du prochain passage du bus " . spl_object_id($this) . " à l'arrêt " . $arret->nom . " dans " . $timer->getRemainingTicks() . " ticks", Message::INFO);
        $arret->addBusEnApproche($this, $timer);
        $this->addTimer($arret, $timer);
    }

    public function export(): array
    {
        return [
            'capacite' => $this->capacite,
            'vitesseChargement' => $this->vitesseChargement,
            'vitesseDeplacement' => $this->vitesseDeplacement,
            'type' => $this->type,
            'parcours' => $this->parcours->nom,
            'personnes' => array_map(
                function ($personne) {
                    return $personne->nom;
                },
                $this->personnes
            ),
            'personnesDescendu' => array_map(
                function ($personne) {
                    return $personne->nom;
                },
                $this->personnesDescendu
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
