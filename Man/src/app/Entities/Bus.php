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
 * Représente un bus.
 * Cette classe gère l'état du bus, les personnes à bord, les timers associés,
 * et l'interaction avec les arrêts lors du parcours du bus.
 */
class Bus extends Position implements TimeInterface, StateInterface
{
    /**
     * Capacité maximale du bus (le nombre maximal de passagers).
     * @var int
     */
    public readonly int $capacite;

    /**
     * Vitesse de chargement du bus (en unités par tick).
     * @var float
     */
    public readonly int $vitesseChargement;

    /**
     * Vitesse de déplacement du bus (en unités par tick).
     * @var int
     */
    public readonly int $vitesseDeplacement;

    /**
     * Type du bus.
     * @var string
     */
    public readonly string $type;

    /**
     * Parcours du bus, qui contient la liste des arrêts à desservir.
     * @var Parcours
     */
    protected Parcours $parcours;

    /**
     * Liste des personnes présentes à bord du bus.
     * @var Personne[]
     */
    protected array $personnes = [];

    /**
     * Liste des personnes qui sont descendues du bus.
     * @var Personne[]
     */
    protected array $personnesDescendu = [];

    /**
     * L'état actuel du bus (en déplacement ou en flux de voyageurs).
     * @var BusStateEnum
     */
    protected BusStateEnum $state = BusStateEnum::DEPLACEMENT;

    /**
     * Liste des timers associés à chaque arrêt (par identifiant d'arrêt).
     * @var array
     */
    protected array $timers = [];

    /**
     * Temps de la dernière action effectuée par le bus, utilisé pour éviter des actions répétées dans le même tick.
     * @var int
     */
    private int $previousActionTime = 0;

    /**
     * Constructeur de la classe Bus.
     * @param int $capacite La capacité maximale du bus.
     * @param float $vitesseChargement La vitesse de chargement des passagers.
     * @param float $vitesseDeplacement La vitesse de déplacement du bus.
     * @param string $type Le type du bus.
     * @param Parcours $parcours Le parcours du bus.
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

    /**
     * Retourne la liste des personnes présentes à bord du bus.
     * @return Personne[] Liste des personnes dans le bus.
     */
    public function getPersonnes(): array
    {
        return $this->personnes;
    }

    /**
     * Modifie l'état du bus et réinitialise les personnes descendues.
     * @param BusStateEnum $state Le nouvel état du bus.
     */
    public function setState(BusStateEnum $state): void
    {
        $this->tick = 0;
        $this->personnesDescendu = [];
        $this->state = $state;
        if (count($this->personnes) > 0) {
            Message::log(microtime(true) . " & Bus " . spl_object_id($this) . " & à l'arrêt " . $this->parcours->currentArret . " (" . $this->parcours->getCurrentArretObj()->nom . ")");
        }
    }

    /**
     * Ajoute un timer pour un arrêt spécifique.
     * @param Arret $arret L'arrêt auquel associer le timer.
     * @param Timer $timer Le timer à ajouter.
     */
    public function addTimer(Arret $arret, Timer $timer): void
    {
        Message::log("Enregistrement du timer pour le bus " . spl_object_id($this) . " à l'arrêt " . $arret->nom);
        $this->timers[spl_object_id($arret)] = $timer;
    }

    /**
     * Supprime un timer pour un arrêt spécifique.
     * @param Arret $arret L'arrêt dont le timer doit être supprimé.
     */
    public function removeTimer(Arret $arret): void
    {
        Message::log("Suppression du timer pour le bus " . spl_object_id($this) . " à l'arrêt " . $arret->nom);
        unset($this->timers[spl_object_id($arret)]);
    }

    /**
     * Retourne le parcours du bus.
     * @return Parcours Le parcours du bus.
     */
    public function getParcours(): Parcours
    {
        return $this->parcours;
    }

    /**
     * Vérifie si le bus peut prendre une personne.
     * Conditions : Le bus n'est pas plein et la personne n'a pas encore été prise en charge.
     * @param Personne $personne La personne à vérifier.
     * @return bool True si le bus peut prendre la personne, sinon false.
     */
    public function canTake(Personne $personne): bool
    {
        if ($this->previousActionTime === Time::getTick()) {
            Message::log("Le bus " . spl_object_id($this) . " a déjà effectué une action dans ce tick", Message::DEBUG_DETAIL);
            return false;
        }

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
     * Retourne une représentation sous forme de chaîne de caractères du bus.
     * @return string Description détaillée du bus.
     */
    public function __toString(): string
    {
        return 'Bus @' . spl_object_id($this)
            . ' (Capacité : ' . $this->capacite
            . ' | Vitesse de chargement : ' . $this->vitesseChargement
            . ' | Vitesse de déplacement : ' . $this->vitesseDeplacement
            . ' | Parcours : ' . $this->parcours->nom
            . ' | Position : ' . $this->parcours->currentArret . ' tick : ' . $this->tick
            . ' | Personnes : '
            . implode(', ', array_map(
                fn($personne) => 'Personne ' . $personne->nom . ' @' . spl_object_id($personne),
                $this->personnes
            ))
            . ')';
    }

    /**
     * Ajoute une personne à bord du bus.
     * @param Personne $personne La personne à ajouter au bus.
     */
    public function addPersonne(Personne $personne): void
    {
        Message::log("Ajout de la personne {$personne->nom} dans le bus " . spl_object_id($this));
        $this->personnes[] = $personne;
        $this->previousActionTime = Time::getTick();
    }

    /**
     * Fait descendre une personne du bus.
     * @param Personne $personne La personne à faire descendre.
     * @throws BusException Si la personne n'est pas à bord du bus.
     */
    public function descentePassager(Personne $personne): void
    {
        Message::log("Suppression de la personne {$personne->nom} du bus " . spl_object_id($this), Message::DEBUG_DETAIL);
        $index = array_search($personne, $this->personnes);
        if ($index === false) {
            throw new BusException("La personne {$personne->nom} n'est pas dans le bus");
        }
        unset($this->personnes[$index]);
        $this->personnes = array_values($this->personnes); // Réindexer le tableau après suppression
        $this->personnesDescendu[] = $personne;
    }

    /**
     * Vérifie si le bus est plein.
     * @return bool True si le bus est plein, sinon false.
     */
    public function isFull(): bool
    {
        return count($this->personnes) >= $this->capacite;
    }

    /**
     * Vérifie si le bus peut desservir un trajet entre deux arrêts donnés.
     * @param Arret $depart L'arrêt de départ.
     * @param Arret $destination L'arrêt de destination.
     * @return bool True si le bus peut desservir ce trajet, sinon false.
     */
    public function peutDesservir(Arret $depart, Arret $destination): bool
    {
        $parcours = $this->getParcours()->arretsAFaire;
        $departIndex = array_search($depart, $parcours);
        $destinationIndex = array_search($destination, $parcours);

        return $departIndex !== false && $destinationIndex !== false;
    }

    /**
     * Incrémente le tick du bus et gère les actions associées, comme l'arrivée à un nouvel arrêt.
     */
    public function incrementTick(): void
    {
        if ($this->state === BusStateEnum::FLUX_VOYAGEURS) {
            /** @var Timer $timer */
            foreach ($this->timers as $timer) {
                $timer->incrementTicks();
            }
        }

        // Logique de déplacement
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

        // Eviter deux actions dans un même tick
        if ($this->state === BusStateEnum::DEPLACEMENT) {
            $this->tick += 1;
            $this->previousActionTime = Time::getTick();
        }
    }

    /**
     * Calcule et enregistre le prochain passage du bus à un arrêt spécifique.
     * @param Arret $arret L'arrêt pour lequel calculer le prochain passage.
     */
    public function calculEtEnregistrementProchainPassage(Arret $arret): void
    {
        Message::log("Calcul du prochain passage du bus " . spl_object_id($this) . " à l'arrêt " . $arret->nom, Message::DEBUG_DETAIL);
        $timer = new Timer($this->tickToNextComming($this->parcours, $this->vitesseDeplacement));
        Message::log("Enregistrement du prochain passage du bus " . spl_object_id($this) . " à l'arrêt " . $arret->nom . " dans " . $timer->getRemainingTicks() . " ticks", Message::INFO);
        $arret->addBusEnApproche($this, $timer);
        $this->addTimer($arret, $timer);
    }

    /**
     * Exporte l'état du bus sous forme d'un tableau.
     * @return array L'état actuel du bus sous forme de tableau.
     */
    public function export(): array
    {
        return [
            'capacite' => $this->capacite,
            'vitesseChargement' => $this->vitesseChargement,
            'vitesseDeplacement' => $this->vitesseDeplacement,
            'type' => $this->type,
            'parcours' => $this->parcours->nom,
            'personnes' => array_map(
                fn($personne) => $personne->nom,
                $this->personnes
            ),
            'personnesDescendu' => array_map(
                fn($personne) => $personne->nom,
                $this->personnesDescendu
            ),
            'state' => $this->state->name,
            'timers' => array_map(
                fn($timer) => $timer->getRemainingTicks(),
                $this->timers
            ),
            'tick' => $this->tick,
        ];
    }

    /**
     * Méthode de restauration non implémentée.
     * @throws \Exception Toujours levée car non implémentée.
     */
    public function restore(array $state): void
    {
        throw new \Exception('Not implemented');
    }
}
