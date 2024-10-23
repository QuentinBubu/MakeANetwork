<?php

namespace App\Entities;

use App\Timer\Time;

class File
{
    private array $personnes = [];
    private int $tick;

    public function __construct()
    {
        $this->tick = Time::getTick();
    }

    public function addPersonne(Personne $personne): void
    {
        $this->personnes[] = $personne;
    }

    public function getNPersonnes(int $n): array
    {
        $personnes = [];
        for ($i = 0; $i < $n; $i++) {
            $personnes[] = array_shift($this->personnes);
        }
        return $personnes;
    }

    public function isEmpty(): bool
    {
        return empty($this->personnes);
    }

    public function getTick(): int
    {
        return $this->tick;
    }
}
