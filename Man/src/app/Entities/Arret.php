<?php

namespace Man\App\Entities;

class Arret
{
    public string $nom;
    public array $routes;

    private array $genericRoutes;
    private array $genericFile;

    public function __construct(string $nom, array $genericRoutes, array $genericFile)
    {
        $this->nom = $nom;
        $this->genericRoutes = $genericRoutes;
        $this->genericFile = $genericFile;
    }
}
