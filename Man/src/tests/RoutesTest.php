<?php

namespace Tests;

use App\Loaders\Arrets;
use App\Loaders\Routes;
use PHPUnit\Framework\TestCase;

class RoutesTest extends TestCase
{
    public function setUp(): void
    {
        require_once __DIR__ . '/../vendor/autoload.php';
        
        $arretsJson = json_decode(json: file_get_contents(filename: 'data/arrets.json'), associative: true);
        $routesJson = json_decode(json: file_get_contents(filename: 'data/routes.json'), associative: true);

        Arrets::load(arrets: $arretsJson);
        Routes::load(routes: $routesJson);

        Arrets::map();
    }

    public function testRoutes(): void
    {
        $this->assertCount(4, Routes::$routes);
    }

    public function testGetRouteStr(): void
    {
        $this->assertEquals('route1', Routes::getRouteStr(arretA: 'A', arretB: 'B')->nom);
        $this->assertEquals('route3', Routes::getRouteStr(arretA: 'D', arretB: 'C')->nom);
    }
}
