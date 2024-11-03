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
use WebServer\SocketServer;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use React\EventLoop\Factory;
use Ratchet\WebSocket\WsServer;
use Dotenv\Repository\RepositoryBuilder;

require_once 'vendor/autoload.php';

$repository = RepositoryBuilder::createWithDefaultAdapters()->make();
$dotenv = Dotenv::create($repository, './');
$dotenv->load();
$dotenv->required(['UNIVERS_START', 'UNIVERS_END']);

$personnesList = [];
loadPersonnes(personnesList: $personnesList);

$man = new Man(__DIR__ . '/data');
$man->setPersonnes($personnesList)
    ->setStates([
        ['class' => Arrets::class, 'method' => 'export', 'name' => 'arrets'],
        ['class' => Bus::class, 'method' => 'export', 'name' => 'bus'],
        ['class' => Parcours::class, 'method' => 'export', 'name' => 'parcours'],
        ['class' => Personnes::class, 'method' => 'export', 'name' => 'personnes'],
        ['class' => Routes::class, 'method' => 'export', 'name' => 'routes'],
        ['class' => Trajets::class, 'method' => 'export', 'name' => 'trajets'],
        ['class' => Time::class, 'method' => 'export', 'name' => 'time'],
    ])
    ->setMessageLevel(Message::INFO)
    ->build();

$ss = new SocketServer($man);
$ss->start(8080);
function loadPersonnes(array &$personnesList)
{
    for ($i = 0; $i < 6; $i++) {
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

    for ($i = 0; $i < 12; $i++) {
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

    for ($i = 0; $i < 45; $i++) {
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
