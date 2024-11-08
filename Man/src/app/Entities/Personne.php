<?php

namespace App\Entities;

use App\Timer\Time;
use App\Log\Message;
use App\State\State;
use App\Entities\Arret;
use App\Loaders\Personnes;
use App\Loaders\PathFinder;
use App\Enums\TrajetEnCoursEnum;
use App\Entities\PersonneObjectif;
use App\Interfaces\StateInterface;

/**
 * @Entity
 * 
 * Représente une personne avec un trajet aller et un trajet retour.
 * La personne peut calculer son trajet optimal, signaler des arrêts de descente, et gérer son état au cours de son trajet.
 */
class Personne implements StateInterface
{
    /**
     * Nom de la personne.
     *
     * @var string
     */
    public string $nom;

    /**
     * Trajet aller de la personne.
     *
     * @var PersonneObjectif
     */
    public PersonneObjectif $aller;

    /**
     * Trajet retour de la personne.
     *
     * @var PersonneObjectif
     */
    public PersonneObjectif $retour;

    /**
     * Trajet en cours de la personne (aller ou retour).
     *
     * @var TrajetEnCoursEnum
     */
    public TrajetEnCoursEnum $trajetEnCours;

    /**
     * Trajet optimisé calculé pour la personne.
     *
     * @var array
     */
    public array $trajetOptimise = [];

    /**
     * Signaux de descente de la personne (les arrêts où elle doit descendre).
     *
     * @var Arret[]
     */
    private array $signalDescente = [];

    /**
     * Constructeur de la personne.
     *
     * @param PersonneObjectif $aller Trajet aller de la personne.
     * @param PersonneObjectif $retour Trajet retour de la personne.
     * @param string $nom Nom de la personne.
     */
    public function __construct(PersonneObjectif $aller, PersonneObjectif $retour, string $nom)
    {
        $this->aller = $aller;
        $this->retour = $retour;
        $this->nom = $nom;
        $this->trajetEnCours = TrajetEnCoursEnum::ALLER;
        $aller->depuis->addPersonne($this); // Ajoute cette personne à l'arrêt de départ du trajet aller.
    }

    /**
     * Récupère le trajet en cours (aller ou retour) de la personne.
     *
     * @return PersonneObjectif Le trajet en cours.
     */
    public function getTrajetEnCours(): PersonneObjectif
    {
        return $this->trajetEnCours === TrajetEnCoursEnum::ALLER ? $this->aller : $this->retour;
    }

    /**
     * Marque la fin du trajet et la désinscrit de la liste des personnes.
     */
    public function finFinal()
    {
        Message::log("La personne {$this->nom} a terminé son trajet", Message::INFO);
        Personnes::unregister(personne: $this); // Désinscrit la personne.
    }

    /**
     * Calcule le trajet optimal entre deux arrêts en utilisant le PathFinder.
     * Affiche également les étapes du trajet.
     *
     * @param Arret $arretFrom Arrêt de départ.
     * @param Arret $arretTo Arrêt d'arrivée.
     * @return array Le trajet optimisé sous forme d'étapes.
     */
    public function calculTrajet(Arret $arretFrom, Arret $arretTo): array
    {
        // Utilisation du PathFinder pour obtenir le meilleur trajet.
        $meilleurTrajet = PathFinder::findBestPath($this, $arretFrom, $arretTo);

        // Parcours des étapes du trajet et affichage de l'information.
        foreach ($meilleurTrajet as $etape => $info) {
            Message::log("{$this->nom} : Étape $etape : Prendre le bus " . spl_object_id($info['busAPrendre']) .
                " de {$info['arretMontee']} à {$info['arretDescente']}");
        }

        return $meilleurTrajet;
    }

    /**
     * Définit un arrêt où la personne doit descendre.
     *
     * @param Arret $arret L'arrêt où la personne doit descendre.
     */
    public function setSignalDescente(Arret $arret)
    {
        $this->signalDescente[] = $arret; // Ajoute l'arrêt à la liste des arrêts de descente.
    }

    /**
     * Vérifie si la personne doit descendre à un arrêt donné.
     *
     * @param Arret $arret L'arrêt à vérifier.
     * @return bool True si la personne doit descendre à cet arrêt, false sinon.
     */
    public function veutDescendre(Arret $arret): bool
    {
        // Vérifie si l'arrêt est dans la liste des arrêts de descente.
        return in_array($arret, $this->signalDescente, true);
    }

    /**
     * Effectue la descente de la personne à un arrêt.
     * Met à jour l'état de la personne (trajet aller ou retour).
     *
     * @param Arret $arret L'arrêt où la personne descend.
     */
    public function descendArret(Arret $arret): void
    {
        // Retire l'arrêt de la liste des arrêts de descente.
        $this->removeSignalDescente($arret);

        // Met à jour le trajet optimisé en supprimant la première étape.
        array_shift($this->trajetOptimise);

        if ($arret === $this->aller->vers) {
            Message::log("La personne {$this->nom} est arrivée à bout de son trajet aller", logLevel: Message::INFO);
            $this->trajetEnCours = TrajetEnCoursEnum::RETOUR; // Change le trajet en cours à retour.
        } elseif ($arret === $this->retour->vers) {
            $this->finFinal(); // La personne a terminé son trajet retour.
            return;
        }

        $arret->addPersonne($this); // Ajoute la personne à l'arrêt où elle est descendue.
        Message::log(State::exportData(), Message::INFO);
    }

    /**
     * Supprime un arrêt de la liste des arrêts de descente.
     *
     * @param Arret $arret L'arrêt à supprimer.
     */
    private function removeSignalDescente(Arret $arret)
    {
        $this->signalDescente = array_filter(
            $this->signalDescente,
            fn($item) => $item !== $arret // Filtre l'arrêt à supprimer.
        );
    }

    /**
     * Vérifie si la personne peut prendre un bus à un arrêt donné.
     * Cela prend en compte l'heure de départ et le trajet optimisé.
     *
     * @param Bus $bus Le bus à vérifier.
     * @param Arret $arret L'arrêt à vérifier.
     * @return bool True si la personne peut prendre le bus, false sinon.
     */
    public function canTake(Bus $bus, Arret $arret): bool
    {
        // Vérifie si le départ du trajet est avant ou à l'heure actuelle.
        if ($this->getTrajetEnCours()->tickDepart > Time::getTick()) {
            Message::log("Personne {$this->nom} est en attente de la tick de départ {$this->getTrajetEnCours()->tickDepart} (tick actuel : " . Time::getTick() . ")", Message::INFO);
            return false;
        }

        // Si le trajet optimisé est vide, calcule-le.
        if (empty($this->trajetOptimise)) {
            Message::log("Calcul du trajet optimisé pour la personne {$this->nom} à l'arrêt {$arret->nom}", Message::DEBUG_DETAIL);
            $this->trajetOptimise = $this->calculTrajet($arret, $this->getTrajetEnCours()->vers);
            Message::log("Trajet optimisé pour la personne {$this->nom} à l'arrêt {$arret->nom} : " . json_encode($this->trajetOptimise), Message::DEBUG_DETAIL);
        }

        // Vérifie si le bus à prendre correspond à l'étape du trajet optimisé.
        if (empty($this->trajetOptimise) || $this->trajetOptimise[0]['busAPrendre'] !== $bus) {
            return false;
        }

        return true;
    }

    /**
     * Exporte les données de la personne sous forme de tableau.
     *
     * @return array Les données exportées.
     */
    public function export(): array
    {
        return [
            'nom' => $this->nom,
            'aller' => $this->aller->export(),
            'retour' => $this->retour->export(),
            'trajetEnCours' => $this->trajetEnCours->name,
            'signalDescente' => array_map(fn($arret) => $arret->nom, $this->signalDescente),
            // 'trajetOptimise' => $this->trajetOptimise, // Ne pas exporter pour l'instant.
        ];
    }

    /**
     * Méthode de restauration de l'état, non implémentée.
     *
     * @throws \Exception Lance une exception car la méthode n'est pas implémentée.
     */
    public function restore(array $state): void
    {
        throw new \Exception("Not implemented");
    }
}
