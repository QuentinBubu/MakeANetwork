<?php

use Man\App\Loaders\Arrets;
use Man\App\Loaders\Routes;

require_once 'vendor/autoload.php';

$arretsJson = json_decode(file_get_contents('data/arrets.json', true), true);
$routesJson = json_decode(file_get_contents('data/routes.json'), true);

Arrets::load($arretsJson);
Routes::load($routesJson);
