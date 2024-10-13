<?php

namespace Tests;

use App\Loaders\Arrets;
use App\Loaders\Routes;
use App\Loaders\Trajets;
use PHPUnit\Framework\TestCase;

class TrajetsTest extends TestCase
{
    public function setUp(): void
    {
        require_once __DIR__ . '/../vendor/autoload.php';
        
        $arretsJson = json_decode(json: file_get_contents(filename: __DIR__ . '/data/arrets.json'), associative: true);
        $routesJson = json_decode(json: file_get_contents(filename: __DIR__ . '/data/routes.json'), associative: true);

        Arrets::load(arrets: $arretsJson);
        Routes::load(routes: $routesJson);

        Arrets::map();
    }

    public function testTrajetSimple()
    {
        $result = Trajets::calculTrajet('A', 'B');
        $this->assertEquals(10, $result['distance']);
        $this->assertCount(1, $result['routes']);
        $this->assertEquals('route1', $result['routes'][0]->nom);
    }


    public function testDistSimple(): void
    {
        $this->assertEquals(14, Trajets::calculTrajet(arretA: 'B', arretB: 'C')['distance']);
    }

    public function testDistOpti(): void
    {
        $this->assertEquals(11, Trajets::calculTrajet(arretA: 'B', arretB: 'E')['distance']);
    }

    public function testTrajetAvecEscale()
    {
        $result = Trajets::calculTrajet('A', 'D');
        $this->assertEquals(16, $result['distance']);
        $this->assertCount(2, $result['routes']);
        $this->assertEquals(['route2', 'route3'], array_map(fn($route) => $route->nom, $result['routes']));
    }

    public function testTrajetLong()
    {
        $result = Trajets::calculTrajet('A', 'F');
        $this->assertEquals(9, $result['distance']);
        $this->assertCount(3, $result['routes']);
        $this->assertEquals(['route2', 'route4', 'route5'], array_map(fn($route) => $route->nom, $result['routes']));
    }

    public function testTrajetAlternatif()
    {
        $result = Trajets::calculTrajet('B', 'D');
        $this->assertEquals(26, $result['distance']);
        $this->assertCount(3, $result['routes']);
        $this->assertEquals(['route1', 'route2', 'route3'], array_map(fn($route) => $route->nom, $result['routes']));
    }

    public function testTrajetImpossible()
    {
        $this->expectException(\App\Exceptions\ArretsException::class);
        Trajets::calculTrajet('A', 'Z');  // Z n'existe pas
    }

    public function testTrajetCyclique()
    {
        $result = Trajets::calculTrajet('A', 'A');
        $this->assertEquals(0, $result['distance']);
        $this->assertEmpty($result['routes']);
    }
}
