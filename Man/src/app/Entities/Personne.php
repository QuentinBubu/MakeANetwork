<?php

namespace App\Entities;

use App\Interfaces\StateInterface;
use App\Loaders\Personnes;
use App\Entities\PathFinder;
use App\Enums\TrajetEnCoursEnum;
use App\Timer\Time;

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
     * @var Trajet
     */
    public Trajet $trajetAller;

    /**
     * Trajet retour
     *
     * @var Trajet
     */
    public Trajet $trajetRetour;

    public TrajetEnCoursEnum $trajetEnCours;

    /**
     * Nom de la personne
     *
     * @var string
     */
    public string $nom;

    public array $arretsVisites = [];

    public array $trajetOptimise;

    /**
     * Signaux de descente
     * @var Arret[]
     */
    private array $signalDescente = [];

    /**
     * Constructeur
     *
     * @param Trajet $trajetAller
     * @param Trajet $trajetRetour
     * @param string $nom
     */
    public function __construct(Trajet $trajetAller, Trajet $trajetRetour, string $nom)
    {
        $this->trajetAller = $trajetAller;
        $this->trajetRetour = $trajetRetour;
        $this->nom = $nom;
        $this->trajetEnCours = TrajetEnCoursEnum::ALLER;
        $this->setArretActuel($trajetAller->depart);
    }

    public function setArretActuel(Arret $arret): void
    {
        echo "La personne {$this->nom} est à l'arrêt {$arret->nom}\n";
        $this->arretsVisites[] = $arret;
        $arret->addPersonne($this);
    }

    public function getTrajetEnCours(): Trajet
    {
        return $this->trajetEnCours === TrajetEnCoursEnum::ALLER ? $this->trajetAller : $this->trajetRetour;
    }

    public function finFinal()
    {
        echo "La personne {$this->nom} a terminé son trajet\n";
        Personnes::unregister($this);
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
            echo $this->nom . " : Étape $etape : Prendre le bus " . spl_object_id($info['busAPrendre']) .
                 " de " . $info['arretMontee'] . " à " . $info['arretDescente'] . PHP_EOL;
        }

        return $meilleurTrajet;
    }

    public function setSignalDescente(Arret $arret) {
        $this->signalDescente[] = $arret; // Stocke les arrêts où le passager doit descendre
    }

    public function veutDescendre(Arret $arret): bool {
        return in_array($arret, $this->signalDescente, true);
    }

    public function descendArret(Arret $arret): void
    {
        $this->setArretActuel($arret);
        $this->removeSignalDescente($arret);
        if ($arret == $this->trajetAller->arrivee) {
            echo "La personne {$this->nom} est arrivée à bout de son trajet aller\n";
            $this->trajetEnCours = TrajetEnCoursEnum::RETOUR;
        } elseif ($arret == $this->trajetRetour->arrivee) {
            $this->finFinal();
        }
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
            'trajetAller' => $this->trajetAller->nom,
            'trajetRetour' => $this->trajetRetour->nom,
            'trajetEnCours' => $this->trajetEnCours->name,
            'arretsVisites' => array_map(fn ($arret) => $arret->nom, $this->arretsVisites)
        ];
    }

    public function arriveeArret(Arret $arret): void
    {
    }

    public function restore(array $state): void
    {
        throw new \Exception("Not implemented");
    }
}
