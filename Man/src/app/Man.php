<?php

namespace App;

use App\Enums\ManEnum;
use App\Exceptions\ManException;
use App\Timer\Time;
use App\Loaders\Bus;
use App\Log\Message;
use App\State\State;
use App\Loaders\Arrets;
use App\Loaders\Routes;
use App\Loaders\Parcours;
use App\Loaders\Personnes;

/**
 * Class Man
 *
 * Représente l'entité de gestion de la simulation des bus et des personnes.
 */
class Man
{
    /**
     * Répertoire où sont stockées les données JSON utilisées pour initialiser la simulation.
     * @var string
     */
    private string $dataDir;

    /**
     * Tableau des données JSON chargées dans la simulation.
     * @var array
     */
    private array $json = [];

    /**
     * Liste des fichiers nécessaires pour la simulation.
     * @var string[]
     */
    public static $requiredFiles = ['bus', 'arrets', 'routes', 'parcours', 'buses', 'peoples'];

    /**
     * Liste des états, représenté sous forme de chaîne JSON.
     * @var array
     */
    private array $exportedStates = [];

    /**
     * État actuel de la simulation.
     * @var ManEnum
     */
    public ManEnum $state;

    /**
     * Man constructor.
     * 
     * Initialise la simulation avec un répertoire de données.
     * L'état initial de la simulation est UNUNITIALIZED.
     * 
     * @param string $dataDir Répertoire contenant les fichiers JSON.
     */
    public function __construct(string $dataDir)
    {
        $this->dataDir = $dataDir;
        $this->state = ManEnum::UNUNITIALIZED;
    }

    /**
     * Définit les fonctions à exécuter pour exporter l'état de la simulation.
     * 
     * @param array $states Liste des fonctions à enregistrer (classe, méthode, clé).
     * @return $this
     */
    public function setStates(array $states): self
    {
        foreach ($states as $state) {
            State::registerFunction($state['class'], $state['method'], $state['name']);
        }
        return $this;
    }

    /**
     * Ajoute une fonction spécifique pour exporter l'état de la simulation.
     * 
     * @param string $class Classe contenant la méthode.
     * @param string $method Méthode à appeler pour exporter l'état.
     * @param string $name Nom associé à cette fonction.
     * @return $this
     */
    public function addState(string $class, string $method, string $name): self
    {
        State::registerFunction($class, $method, $name);
        return $this;
    }

    /**
     * Définit le niveau de journalisation des messages dans la simulation.
     * 
     * @param int $level Niveau de journalisation.
     * @return $this
     */
    public function setMessageLevel(int $level): self
    {
        Message::setLevel($level);
        return $this;
    }

    /**
     * Définit le fichier de sortie des messages de la simulation.
     *
     * @param string $output Fichier de sortie.
     * @return $this
     */
    public function setMessageOutput(string $output): self
    {
        Message::setOutput($output);
        return $this;
    }

    /**
     * Initialise la simulation en chargeant les données JSON et en les transformant en objets.
     * 
     * @param array|null $data Données JSON à charger (si null, charge les fichiers depuis le répertoire).
     * @return $this
     * 
     * Complexité: O(n + m) où n est le nombre de fichiers JSON et m est le nombre d'objets à charger après le parsing.
     */
    public function build(?array $data = null): self
    {
        Message::log('----- DEBUT -----', Message::INFO);
        if (is_null($data)) {
            $this->loadJson();
        } else {
            $this->json = $data;
        }
        $this->loadAsObject();
        $this->exportedStates[Time::getTick()] = State::exportData();
        Message::log('----- FIN -----', Message::INFO);
        $this->state = ManEnum::WAITING_START;
        return $this;
    }

    /**
     * Charge les fichiers JSON nécessaires à la simulation.
     * 
     * @throws \Exception Si un fichier JSON est manquant ou corrompu.
     * 
     * Complexité: O(n) où n est le nombre de fichiers JSON à charger.
     */
    private function loadJson()
    {
        Message::log('----- DEBUT CHARGEMENT JSON -----', Message::DEBUG_DETAIL);
        foreach (self::$requiredFiles as $file) {
            $this->json[$file] = json_decode(file_get_contents($this->dataDir . '/' . $file . '.json'), true);
        }
        Message::log('----- FIN CHARGEMENT JSON -----', Message::DEBUG_DETAIL);
    }

    /**
     * Charge les données JSON en objets et effectue les associations nécessaires (parcours, bus, arrêts, etc.).
     * 
     * Complexité: O(n * m) où n est le nombre d'objets à charger (par exemple, arrêts, routes) et m est le nombre d'associations à faire.
     */
    private function loadAsObject()
    {
        Message::log('Chargement des arrêts', Message::DEBUG_DETAIL);
        Arrets::load(arrets: $this->json['arrets']);

        Message::log('Chargement des routes', Message::DEBUG_DETAIL);
        Routes::load(routes: $this->json['routes']);

        Message::log('Mapping des routes');
        Arrets::map();

        Message::log('Chargement des parcours', Message::DEBUG_DETAIL);
        Parcours::load(parcours: $this->json['parcours']);

        Message::log('Chargement des bus', Message::DEBUG_DETAIL);
        Bus::load(bus: $this->json['buses'], config: $this->json['bus']);

        foreach (Bus::$buses as $bus) {
            Message::log("Bus : " . spl_object_id($bus) . " affecté au parcours " . $bus->getParcours()->nom, Message::DEBUG_DETAIL);
        }

        Message::log('Chargement des personnes', Message::DEBUG_DETAIL);
        Personnes::load(personnesList: $this->json['peoples']);
    }

    /**
     * Lance l'ensemble de la simulation, effectuant chaque tick jusqu'à ce que l'univers prenne fin ou que toutes les personnes aient été transportées.
     * 
     * Complexité: O(t * n) où t est le nombre de ticks et n est le nombre de personnes à gérer.
     * 
     * @return bool Retourne true lorsque la simulation se termine avec succès.
     */
    public function runAll(): bool
    {
        $this->state = ManEnum::RUNNING;
        while (Time::getTick() <= $_ENV['UNIVERS_END'] && count(Personnes::$personnes) > 0) {
            while ($this->state === ManEnum::PAUSED) {
                sleep(seconds: 1); // Pause de 1s
            }
            Time::run();
            Time::incrementTick();
            Message::log(State::exportData(), Message::DATA);
            // $this->lastState = State::exportData(); Pas besoin de sauvegarder l'état pour debug
            $this->checkUnicitePersonne();
        }
        $this->state = ManEnum::SUCCEEDED;
        return true;
    }

    /**
     * Exécute un seul tick de la simulation.
     * 
     * @return ManEnum L'état actuel de la simulation.
     */
    public function runOnce(): ManEnum
    {
        if ($this->state !== ManEnum::RUNNING) {
            return $this->state;
        }

        if (Time::getTick() <= $_ENV['UNIVERS_END'] && count(Personnes::$personnes) > 0) {
            Time::run();
            Time::incrementTick();
            $this->exportedStates[Time::getTick()] = State::exportData();
            $this->checkUnicitePersonne();
            return ManEnum::RUNNING;
        }
        $this->state = ManEnum::SUCCEEDED;
        return ManEnum::SUCCEEDED;
    }

    /**
     * Vérifie l'unicité des personnes dans la simulation, s'assurant qu'elles sont présentes aux arrêts ou dans les bus.
     * 
     * Complexité : O(n + m) où n est le nombre de personnes et m est le nombre de bus et d'arrêts à parcourir.
     * 
     * @return bool Retourne true si toutes les personnes sont présentes et uniques dans la simulation.
     * @throws ManException Si une personne est introuvable ou en trop.
     */
    private function checkUnicitePersonne(): bool
    {
        $personnes = Personnes::$personnes;
        $personnesFind = [];

        foreach (Arrets::$arrets as $arret) {
            foreach (clone $arret->fileAttente as $personne) {
                $personnesFind[] = $personne['data'];
            }
        }

        foreach (Bus::$buses as $bus) {
            foreach ($bus->getPersonnes() as $personne) {
                $personnesFind[] = $personne;
            }
        }

        foreach ($personnes as $personne) {
            $ind = array_search($personne, $personnesFind);
            if ($ind !== false) {
                unset($personnesFind[$ind]);
            } else {
                Message::log(State::exportData(), Message::INFO);
                throw new ManException("Personne non trouvée : " . $personne->nom);
            }
        }

        if (!empty($personnesFind)) {
            Message::log(State::exportData(), Message::INFO);
            throw new ManException('Personne en trop :' . implode(',', array_keys($personnesFind)));
        }

        return true;
    }

    /**
     * Retourne l'état de la simulation sous forme JSON.
     *
     * @return string L'état actuel sous forme JSON.
     */
    public function getLastState(): ?string
    {
        return $this->exportedStates[count($this->exportedStates) - 1];
    }

    public function getState(int $state): ?string
    {
        return $this->exportedStates[$state] ?? null;
    }

    public function getTick(): int
    {
        return Time::getTick();
    }
}
