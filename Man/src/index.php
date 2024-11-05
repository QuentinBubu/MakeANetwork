<?php

use App\Man;
use Dotenv\Dotenv;
use App\Timer\Time;
use App\Loaders\Bus;
use App\Log\Message;
use App\State\State;
use App\Loaders\Arrets;
use App\Loaders\Personnes;
use Dotenv\Repository\RepositoryBuilder;

require_once 'vendor/autoload.php';

$repository = RepositoryBuilder::createWithDefaultAdapters()->make();
$dotenv = Dotenv::create($repository, './');
$dotenv->load();
$dotenv->required(['UNIVERS_START', 'UNIVERS_END']);

$man = new Man(__DIR__ . '/data');
$man->setStates([
        // ['class' => Arrets::class, 'method' => 'export', 'name' => 'arrets'],
        // ['class' => Bus::class, 'method' => 'export', 'name' => 'bus'],
        // ['class' => Parcours::class, 'method' => 'export', 'name' => 'parcours'],
        ['class' => Personnes::class, 'method' => 'export', 'name' => 'personnes'],
        // ['class' => Routes::class, 'method' => 'export', 'name' => 'routes'],
        // ['class' => Trajets::class, 'method' => 'export', 'name' => 'trajets'],
        // ['class' => Time::class, 'method' => 'export', 'name' => 'time'],
    ])
    ->setMessageLevel(Message::DATA)
    ->build();
    
$man->runAll();
Message::log(State::exportData(), Message::INFO);
// $ss = new SocketServer($man);
// $ss->start(8080);
