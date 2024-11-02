<?php

namespace App\Entities;

use App\Timer\Time;
use App\Log\Message;
use App\Loaders\Personnes;
use App\Entities\PathFinder;
use App\Enums\TrajetEnCoursEnum;
use App\Interfaces\StateInterface;
use App\State\State;

/**
 * @Entity
 *
 * Une personne est décrite par leur nom, un trajet aller et un trajet retour
 */
class Personne implements StateInterface
{
    /**
     * Trajet aller de la personne
     *
     * @var PersonneObjectif
     */
    public PersonneObjectif $aller;

    /**
     * Trajet retour
     *
     * @var PersonneObjectif
     */
    public PersonneObjectif $retour;

    public TrajetEnCoursEnum $trajetEnCours;

    /**
     * Nom de la personne
     *
     * @var string
     */
    public string $nom;

    public array $trajetOptimise;

    /**
     * Signaux de descente
     * @var Arret[]
     */
    private array $signalDescente = [];

    /**
     * Constructeur
     *
     * @param PersonneObjectif $aller
     * @param PersonneObjectif $retour
     * @param string $nom
     */
    public function __construct(PersonneObjectif $aller, PersonneObjectif $retour, string $nom)
    {
        $this->aller = $aller;
        $this->retour = $retour;
        $this->nom = $nom;
        $this->trajetEnCours = TrajetEnCoursEnum::ALLER;
        $aller->depuis->addPersonne($this);
    }

    public function getTrajetEnCours(): PersonneObjectif
    {
        return $this->trajetEnCours === TrajetEnCoursEnum::ALLER ? $this->aller : $this->retour;
    }

    public function finFinal()
    {
        Message::log("La personne {$this->nom} a terminé son trajet", Message::INFO);
        Personnes::unregister(personne: $this);
    }

        /**
     * Calcule le trajet optimal pour cette personne
     * entre deux arrêts, en prenant en compte les bus disponibles.
     *
     * @param Arret $arretFrom
     * @param Arret $arretTo
     * @return array Trajet optimisé sous forme d'étapes
     */
    public function calculTrajet(Arret $arretFrom, Arret $arretTo): array
    {
        $pathFinder = new PathFinder();
        
        // Utilisation de PathFinder pour obtenir le trajet optimal
        $meilleurTrajet = $pathFinder->findBestPath($this, $arretFrom, $arretTo);

        // Parcours de chaque étape et affichage du trajet
        foreach ($meilleurTrajet as $etape => $info) {
            Message::log($this->nom . " : Étape $etape : Prendre le bus " . spl_object_id($info['busAPrendre']) .
                 " de " . $info['arretMontee'] . " à " . $info['arretDescente']);
        }

        return $meilleurTrajet;
    }

    public function setSignalDescente(Arret $arret) {
        $this->signalDescente[] = $arret; // Stocke les arrêts où le passager doit descendre
    }

    public function veutDescendre(Arret $arret): bool {
        // Calculer s'il est plus intéressant de descendre à cet arrêt ou de rester dans le bus suivant la file d'attente à l'arrêt
        return in_array($arret, $this->signalDescente);
    }

    public function descendArret(Arret $arret): void
    {
        $this->removeSignalDescente($arret);
        if ($arret === $this->aller->vers) {
            Message::log("La personne {$this->nom} est arrivée à bout de son trajet aller", logLevel: Message::INFO);
            $this->trajetEnCours = TrajetEnCoursEnum::RETOUR;
        } elseif ($arret === $this->retour->vers) {
            $this->finFinal();
            return;
        }
        $arret->addPersonne($this);
        Message::log(State::exportData(), Message::INFO);
    }

    private function removeSignalDescente(Arret $arret) {
        $this->signalDescente = array_filter(
            $this->signalDescente,
            fn($item) => $item !== $arret
        );
    }



    public function export(): array
    {
        return [
            'nom' => $this->nom,
            // 'trajetAller' => $this->trajetAller->nom,
            // 'trajetRetour' => $this->trajetRetour->nom,
            'trajetEnCours' => $this->trajetEnCours->name,
        ];
    }

    public function restore(array $state): void
    {
        throw new \Exception("Not implemented");
    }
}
