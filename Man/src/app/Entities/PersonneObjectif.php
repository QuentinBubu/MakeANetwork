<?php

namespace App\Entities;

use App\Interfaces\StateInterface;
use App\Loaders\Arrets;

class PersonneObjectif implements StateInterface
{
    public readonly Arret $depuis;
    public readonly Arret $vers;
    public readonly int $tickDepart;

    public function __construct(string $depuis, string $vers, int $tickDepart)
    {
        $this->depuis = Arrets::getArret($depuis);
        $this->vers = Arrets::getArret($vers);
        $this->tickDepart = $tickDepart;
    }

    public function export(): array
    {
        return [
            'depuis' => $this->depuis->nom,
            'vers' => $this->vers->nom,
            'tickDepart' => $this->tickDepart,
        ];
    }

    public function restore(array $state): void
    {
        $this->depuis = Arrets::getArret($state['depuis']);
        $this->vers = Arrets::getArret($state['vers']);
        $this->tickDepart = $state['tickDepart'];
    }
}
