<?php

namespace App\Entities;

use App\Loaders\Arrets;

class PersonneObjectif
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
}
