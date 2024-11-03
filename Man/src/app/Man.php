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

class Man
{
    private string $dataDir;

    private array $json = [];

    private array $personnes = [];

    public static $requiredFiles = ['bus', 'arrets', 'routes', 'parcours', 'buses'];

    private string $lastState = '';

    public ManEnum $state;

    public function __construct(string $dataDir)
    {
        $this->dataDir = $dataDir;
        $this->state = ManEnum::UNUNITIALIZED;
    }

    public function setPersonnes(array $personnes): self
    {
        $this->personnes = $personnes;
        return $this;
    }

    public function setStates(array $states): self
    {
        foreach ($states as $state) {
            State::registerFunction($state['class'], $state['method'], $state['name']);
        }
        return $this;
    }

    public function addState(string $class, string $method, string $name): self
    {
        State::registerFunction($class, $method, $name);
        return $this;
    }

    public function setMessageLevel(int $level): self
    {
        Message::setLevel($level);
        return $this;
    }

    public function build(): self
    {
        Message::log('----- DEBUT -----', Message::INFO);
        $this->loadJson();
        $this->loadAsObject();
        Message::log('----- FIN -----', Message::INFO);
        $this->state = ManEnum::WAITING_START;
        return $this;
    }

    private function loadJson()
    {
        Message::log('----- DEBUT CHARGEMENT JSON -----', Message::DEBUG_DETAIL);
        foreach (self::$requiredFiles as $file) {
            $this->json[$file] = json_decode(file_get_contents($this->dataDir . '/' . $file . '.json'), true);
        }
        Message::log('----- FIN CHARGEMENT JSON -----', Message::DEBUG_DETAIL);
    }

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
        Personnes::load(personnesList: $this->personnes);
    }

    public function runAll(): bool
    {
        $this->state = ManEnum::RUNNING;
        while (Time::getTick() <= $_ENV['UNIVERS_END'] && count(Personnes::$personnes) > 0) {
            while ($this->state === ManEnum::PAUSED) {
                sleep(seconds: 1); // Pause de 1s
            }
            Time::run();
            Time::incrementTick();
            // $this->lastState = State::exportData(); Pas besoin de sauvegarder l'état pour debug
            $this->checkUnicitePersonne();
        }
        $this->state = ManEnum::SUCCEEDED;
        return true;
    }

    public function runOnce(): ManEnum
    {
        if ($this->state !== ManEnum::RUNNING) {
            return $this->state;
        }

        if (Time::getTick() <= $_ENV['UNIVERS_END'] && count(Personnes::$personnes) > 0) {
            Time::run();
            Time::incrementTick();
            $this->lastState = State::exportData();
            $this->checkUnicitePersonne();
            return ManEnum::RUNNING;
        }

        return ManEnum::SUCCEEDED;
    }

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
            throw new ManException('Personne en trop :' . implode(',', array_map(fn($personne) => $personne->nom, $personnesFind)));
        }

        return true;
    }

    public function getLastState(): string
    {
        return $this->lastState;
    }
}
