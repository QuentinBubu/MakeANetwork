<?php

namespace App\Entities;

use App\Loaders\Arrets;

class PersonneObjectif
{
    public readonly Arret $depuis;
    public readonly Arret $vers;

    public function __construct(string $depuis, string $vers)
    {
        $this->depuis = Arrets::getArret($depuis);
        $this->vers = Arrets::getArret($vers);
    }
}
