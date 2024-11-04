<?php

use App\Man;
use Dotenv\Dotenv;
use App\Timer\Time;
use App\Loaders\Bus;
use App\Log\Message;
use App\Loaders\Arrets;
use App\Loaders\Routes;
use App\Loaders\Trajets;
use App\Loaders\Parcours;
use App\Loaders\Personnes;
use App\State\State;
use WebServer\SocketServer;
use Dotenv\Repository\RepositoryBuilder;

require_once 'vendor/autoload.php';

$repository = RepositoryBuilder::createWithDefaultAdapters()->make();
$dotenv = Dotenv::create($repository, './');
$dotenv->load();
$dotenv->required(['UNIVERS_START', 'UNIVERS_END']);

$personnesList = [];
loadPersonnes(personnesList: $personnesList, nbPersonnes: [
    'Albert' => 3,
    'Bob' => 2,
    'Charles' => 3,
    'Damien' => 1,
    'Edouard' => 1,
]);

$man = new Man(__DIR__ . '/data');
$man->setPersonnes($personnesList)
    ->setStates([
        ['class' => Arrets::class, 'method' => 'export', 'name' => 'arrets'],
        ['class' => Bus::class, 'method' => 'export', 'name' => 'bus'],
        // ['class' => Parcours::class, 'method' => 'export', 'name' => 'parcours'],
        // ['class' => Personnes::class, 'method' => 'export', 'name' => 'personnes'],
        // ['class' => Routes::class, 'method' => 'export', 'name' => 'routes'],
        // ['class' => Trajets::class, 'method' => 'export', 'name' => 'trajets'],
        ['class' => Time::class, 'method' => 'export', 'name' => 'time'],
    ])
    ->setMessageLevel(Message::DATA)
    ->build();

var_dump(Trajets::findTrajet('B', 'E'));

$man->runAll();
Message::log(State::exportData(), Message::INFO);
// $ss = new SocketServer($man);
// $ss->start(8080);
function loadPersonnes(array &$personnesList, array $nbPersonnes = [
    'Albert' => 6,
    'Bob' => 12,
    'Charles' => 12,
    'Damien' => 12,
    'Edouard' => 45,
])
{
    for ($i = 0; $i < $nbPersonnes['Albert']; $i++) {
        $personnesList[] = [
            'nom' => "Albert{$i}",
            'aller' => [
                'depart' => 'B',
                'arrivee' => 'D',
                'temps' => 0,
            ],
            'retour' => [
                'depart' => 'D',
                'arrivee' => 'A',
                'temps' => 500,
            ]
        ];
    }

    for ($i = 0; $i < $nbPersonnes['Bob']; $i++) {
        $personnesList[] = [
            'nom' => "Bob{$i}",
            'aller' => [
                'depart' => 'B',
                'arrivee' => 'C',
                'temps' => 0,
            ],
            'retour' => [
                'depart' => 'C',
                'arrivee' => 'B',
                'temps' => 500,
            ]
        ];
    }

    for ($i = 0; $i < $nbPersonnes['Charles']; $i++) {
        $personnesList[] = [
            'nom' => "Charles{$i}",
            'aller' => [
                'depart' => 'B',
                'arrivee' => 'C',
                'temps' => 0,
            ],
            'retour' => [
                'depart' => 'C',
                'arrivee' => 'B',
                'temps' => 500,
            ]
        ];
    }

    for ($i = 0; $i < $nbPersonnes['Damien']; $i++) {
        $personnesList[] = [
            'nom' => "Damien{$i}",
            'aller' => [
                'depart' => 'E',
                'arrivee' => 'A',
                'temps' => 0,
            ],
            'retour' => [
                'depart' => 'A',
                'arrivee' => 'E',
                'temps' => 600,
            ]
        ];
    }

    for ($i = 0; $i < $nbPersonnes['Edouard']; $i++) {
        $personnesList[] = [
            'nom' => "Edouard{$i}",
            'aller' => [
                'depart' => 'A',
                'arrivee' => 'C',
                'temps' => 0,
            ],
            'retour' => [
                'depart' => 'C',
                'arrivee' => 'A',
                'temps' => 300,
            ]
        ];
    }
}
